<?php

declare(strict_types=1);

namespace Pars\Mvc\Model;

use Niceshops\Bean\Converter\BeanConverterAwareInterface;
use Niceshops\Bean\Converter\BeanConverterAwareTrait;
use Niceshops\Bean\Factory\BeanFactoryAwareInterface;
use Niceshops\Bean\Finder\BeanFinderAwareInterface;
use Niceshops\Bean\Finder\BeanFinderAwareTrait;
use Niceshops\Bean\Processor\BeanProcessorAwareInterface;
use Niceshops\Bean\Processor\BeanProcessorAwareTrait;
use Niceshops\Bean\Type\Base\BeanInterface;
use Niceshops\Bean\Type\Base\BeanListAwareInterface;
use Niceshops\Bean\Type\Base\BeanListInterface;
use Niceshops\Core\Option\OptionAwareInterface;
use Niceshops\Core\Option\OptionAwareTrait;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\MoveParameter;
use Pars\Helper\Parameter\OrderParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Parameter\SearchParameter;
use Pars\Helper\Parameter\SubmitParameter;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Helper\Validation\ValidationHelperAwareTrait;
use Pars\Mvc\Exception\MvcException;

/**
 * Class AbstractModel
 * @package Pars\Mvc\Model
 */
abstract class AbstractModel implements
    ModelInterface,
    OptionAwareInterface,
    BeanFinderAwareInterface,
    BeanProcessorAwareInterface,
    BeanConverterAwareInterface,
    ValidationHelperAwareInterface
{
    use OptionAwareTrait;
    use BeanFinderAwareTrait;
    use BeanProcessorAwareTrait;
    use BeanConverterAwareTrait;
    use ValidationHelperAwareTrait;

    public const OPTION_CREATE_ALLOWED = 'create_allowed';
    public const OPTION_EDIT_ALLOWED = 'edit_allowed';
    public const OPTION_DELETE_ALLOWED = 'delete_allowed';

    public function initialize()
    {

    }


    /**
     * @param PaginationParameter $paginationParameter
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function handlePagination(PaginationParameter $paginationParameter)
    {
        if ($this->hasBeanFinder()) {
            $limit = $paginationParameter->getLimit();
            $page = $paginationParameter->getPage();
            if ($limit > 0 && $page > 0) {
                $this->getBeanFinder()->limit($limit, $limit * ($page - 1));
            }
        }
    }

    /**
     * @param SearchParameter $searchParameter
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function handleSearch(SearchParameter $searchParameter)
    {
        if ($this->hasBeanFinder()) {
            $text = $searchParameter->getText();
            if (strlen(trim($text))) {
                $this->getBeanFinder()->search($text);
            }
        }
    }

    /**
     * @param OrderParameter $orderParameter
     * @return mixed|void
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function handleOrder(OrderParameter $orderParameter)
    {
        if ($this->hasBeanFinder()) {
            $this->getBeanFinder()->order([$orderParameter->getField() => $orderParameter->getMode()]);
        }
    }

    /**
     * @param IdParameter $idParameter
     */
    public function handleId(IdParameter $idParameter)
    {
        if ($this->hasBeanFinder()) {
            $this->getBeanFinder()->filter($idParameter->getAttribute_List());
        }
    }

    /**
     * @param MoveParameter $moveParameter
     * @return mixed|void
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function handleMove(MoveParameter $moveParameter)
    {
        if ($this->hasOption(self::OPTION_EDIT_ALLOWED)) {
            if ($this->hasBeanProcessor() && $this->hasBeanFinder()) {
                $model = clone $this;
                $model->initialize();
                $this->getBeanProcessor()->move(
                    $model->getBeanFinder(),
                    $this->getBeanFinder()->getBean(),
                    $moveParameter->getField(),
                    $moveParameter->getSteps(),
                    $moveParameter->getReferenceField(),
                    $moveParameter->getReferenceValue()
                );
            }
        } else {
            $this->handlePermissionDenied();
        }
    }

    /**
     * @param SubmitParameter $submitParameter
     * @param IdParameter $idParameter
     * @param array $attribute_List
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function handleSubmit(SubmitParameter $submitParameter, IdParameter $idParameter, array $attribute_List)
    {
        switch ($submitParameter->getMode()) {
            case SubmitParameter::MODE_SAVE:
                if ($this->hasOption(self::OPTION_EDIT_ALLOWED)) {
                    $this->save($attribute_List);
                } else {
                    $this->handlePermissionDenied();
                }
                break;
            case SubmitParameter::MODE_CREATE:
                if ($this->hasOption(self::OPTION_CREATE_ALLOWED)) {
                    $this->create($idParameter, $attribute_List);
                } else {
                    $this->handlePermissionDenied();
                }
                break;
            case SubmitParameter::MODE_DELETE:
                if ($this->hasOption(self::OPTION_DELETE_ALLOWED)) {
                    $this->delete($idParameter);
                } else {
                    $this->handlePermissionDenied();
                }
                break;
        }
    }

    abstract protected function handlePermissionDenied();

    /**
     * @param IdParameter $idParameter
     * @param array $attributes
     */
    protected function create(IdParameter $idParameter, array $attributes): void
    {
        if ($this->hasBeanFinder() && $this->hasBeanProcessor()) {
            $finder = $this->getBeanFinder();
            if ($finder instanceof BeanFactoryAwareInterface) {
                $data = array_replace($attributes, $idParameter->getAttribute_List());
                $factory = $finder->getBeanFactory();
                $bean = $factory->getEmptyBean($data);
                if ($this->hasBeanConverter()) {
                    $converter = $this->getBeanConverter();
                    $bean = $converter->convert($bean, $data)->toBean();
                }
                $beanList = $factory->getEmptyBeanList();
                $beanList->push($bean);
                $processor = $this->getBeanProcessor();
                if ($processor instanceof BeanListAwareInterface) {
                    $processor->setBeanList($beanList);
                }
                $processor->save();
                if ($processor instanceof ValidationHelperAwareInterface) {
                    $this->getValidationHelper()->addErrorFieldMap(
                        $processor->getValidationHelper()->getErrorFieldMap()
                    );
                }
            }
        }
    }


    /**
     * @param array $attributes
     */
    protected function save(array $attributes): void
    {
        if ($this->hasBeanFinder() && $this->hasBeanProcessor()) {
            $finder = $this->getBeanFinder();
            if ($finder instanceof BeanFactoryAwareInterface) {
                $factory = $finder->getBeanFactory();
                $data = $attributes;
                $bean = $this->getBeanFinder()->getBean(true);
                if ($this->hasBeanConverter()) {
                    $converter = $this->getBeanConverter();
                    $bean = $converter->convert($bean, $data)->toBean();
                }
                $beanList = $factory->getEmptyBeanList();
                $beanList->push($bean);
                $processor = $this->getBeanProcessor();
                if ($processor instanceof BeanListAwareInterface) {
                    $processor->setBeanList($beanList);
                }
                $processor->save();
                if ($processor instanceof ValidationHelperAwareInterface) {
                    $this->getValidationHelper()->addErrorFieldMap(
                        $processor->getValidationHelper()->getErrorFieldMap()
                    );
                }
            }
        }
    }

    /**
     * @param IdParameter $idParameter
     */
    protected function delete(IdParameter $idParameter): void
    {
        if ($this->hasBeanFinder() && $this->getBeanProcessor()) {
            $finder = $this->getBeanFinder();
            $processor = $this->getBeanProcessor();
            if ($processor instanceof BeanListAwareInterface) {
                if ($finder->count() == 1) {
                    $beanList = $finder->getBeanList(true);
                    $processor->setBeanList($beanList);
                }
            }
            $processor->delete();
            if ($processor instanceof ValidationHelperAwareInterface) {
                $this->getValidationHelper()->addErrorFieldMap(
                    $processor->getValidationHelper()->getErrorFieldMap()
                );
            }
        }
    }

    /**
     * @param array $data
     * @return BeanInterface
     * @throws MvcException
     */
    public function getEmptyBean(array $data = []): BeanInterface
    {
        if ($this->hasBeanFinder()) {
            $finder = $this->getBeanFinder();
            if ($finder instanceof BeanFactoryAwareInterface) {
                return $finder->getBeanFactory()->getEmptyBean($data);
            }
        }
        throw new MvcException('Could not create empty bean!');
    }

    /**
     * @return BeanInterface
     * @throws MvcException
     */
    public function getBean(): BeanInterface
    {
        if ($this->hasBeanFinder()) {
            return $this->getBeanFinder()->getBean();
        }
        throw new MvcException('Could not get bean!');
    }

    /**
     * @return BeanListInterface
     * @throws MvcException
     */
    public function getBeanList(): BeanListInterface
    {
        if ($this->hasBeanFinder()) {
            return $this->getBeanFinder()->getBeanListDecorator();
        }
        throw new MvcException('Could not get bean list!');
    }
}