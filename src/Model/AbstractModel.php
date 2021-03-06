<?php

declare(strict_types=1);

namespace Pars\Mvc\Model;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Converter\BeanConverterAwareTrait;
use Pars\Bean\Factory\BeanFactoryAwareInterface;
use Pars\Bean\Finder\BeanFinderAwareInterface;
use Pars\Bean\Finder\BeanFinderAwareTrait;
use Pars\Bean\Processor\BeanOrderProcessorAwareTrait;
use Pars\Bean\Processor\BeanProcessorAwareInterface;
use Pars\Bean\Processor\BeanProcessorAwareTrait;
use Pars\Bean\Type\Base\BeanException;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Bean\Type\Base\BeanListAwareInterface;
use Pars\Bean\Type\Base\BeanListInterface;
use Pars\Helper\Debug\DebugHelper;
use Pars\Helper\Parameter\FilterParameter;
use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\MoveParameter;
use Pars\Helper\Parameter\OrderParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Parameter\SearchParameter;
use Pars\Helper\Parameter\SubmitParameter;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Helper\Validation\ValidationHelperAwareTrait;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Exception\NotFoundException;
use Pars\Pattern\Exception\AttributeNotFoundException;
use Pars\Pattern\Exception\CoreException;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;
use Psr\Container\ContainerInterface;

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
    use BeanOrderProcessorAwareTrait;

    public const OPTION_CREATE_ALLOWED = 'create_allowed';
    public const OPTION_EDIT_ALLOWED = 'edit_allowed';
    public const OPTION_DELETE_ALLOWED = 'delete_allowed';

    private ContainerInterface $container;

    /**
     * @var array
     */
    protected array $applicationConfig = [];

    /**
     * AbstractModel constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->applicationConfig = $container->get('config');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }


    /**
     * @return array
     */
    public function getApplicationConfig(): array
    {
        return $this->applicationConfig;
    }

    public function initializeDependencies()
    {
    }

    public function initialize()
    {
    }


    /**
     * @param PaginationParameter $paginationParameter
     * @throws AttributeNotFoundException
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
     * @throws AttributeNotFoundException
     */
    public function handleSearch(SearchParameter $searchParameter)
    {
        if ($this->hasBeanFinder() && $searchParameter->hasText()) {
            $text = $searchParameter->getText();
            if (strlen(trim($text))) {
                $this->getBeanFinder()->search($text);
            }
        }
    }

    /**
     * @param OrderParameter $orderParameter
     * @return mixed|void
     * @throws AttributeNotFoundException
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
     * @param FilterParameter $filterParameter
     */
    public function handleFilter(FilterParameter $filterParameter)
    {
        $this->handleId($filterParameter);
    }

    /**
     * @param MoveParameter $moveParameter
     * @return mixed|void
     * @throws AttributeNotFoundException
     */
    public function handleMove(MoveParameter $moveParameter)
    {
        if ($this->hasOption(self::OPTION_EDIT_ALLOWED)) {
            if ($this->hasBeanOrderProcessor()) {
                $this->getBeanOrderProcessor()->move(
                    $this->getBeanFinder()->getBean(),
                    $moveParameter->getSteps(),
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
     * @param IdListParameter $idListParameter
     * @param array $attribute_List
     * @throws AttributeNotFoundException
     */
    public function handleSubmit(
        SubmitParameter $submitParameter,
        IdParameter $idParameter,
        IdListParameter $idListParameter,
        array $attribute_List
    )
    {
        switch ($submitParameter->getMode()) {
            case SubmitParameter::MODE_SAVE:
                if ($this->hasOption(self::OPTION_EDIT_ALLOWED)) {
                    $this->save($attribute_List);
                } else {
                    $this->handlePermissionDenied();
                }
                break;
            case SubmitParameter::MODE_SAVE_BULK:
                if ($this->hasOption(self::OPTION_EDIT_ALLOWED)) {
                    $this->save_bulk($idListParameter, $attribute_List);
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
            case SubmitParameter::MODE_CREATE_BULK:
                if ($this->hasOption(self::OPTION_CREATE_ALLOWED)) {
                    $this->create_bulk($idParameter, $idListParameter, $attribute_List);
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
            case SubmitParameter::MODE_DELETE_BULK:
                if ($this->hasOption(self::OPTION_DELETE_ALLOWED)) {
                    $this->delete_bulk($idListParameter, $attribute_List);
                } else {
                    $this->handlePermissionDenied();
                }
                break;
        }
    }

    protected function handlePermissionDenied()
    {
    }

    /**
     * @param IdParameter $idParameter
     * @param array $attributes
     */
    protected function create(IdParameter $idParameter, array &$attributes): void
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
                if ($this->hasBeanConverter()) {
                    $converter = $this->getBeanConverter();
                    $attributes = array_replace($attributes, $converter->convert($bean)->toArray(true));
                }
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
     * @param IdListParameter $idListParameter
     * @param array $attributes
     */
    protected function create_bulk(
        IdParameter $idParameter,
        IdListParameter $idListParameter,
        array $attributes
    ): void
    {
        $id = $idParameter->getAttribute_List();
        $ids = $idListParameter->getAttribute_List();
        $ids_new = [];
        foreach ($ids as $key => $values) {
            foreach ($values as $i => $value) {
                if (!isset($ids_new[$i])) {
                    $ids_new[$i] = $id;
                }
                $ids_new[$i][$key] = $value;
            }
        }
        if ($this->hasBeanFinder() && $this->hasBeanProcessor()) {
            $finder = $this->getBeanFinder();
            if ($finder instanceof BeanFactoryAwareInterface) {
                $factory = $finder->getBeanFactory();
                $beanList = $factory->getEmptyBeanList();
                foreach ($ids_new as $data) {
                    $bean = $factory->getEmptyBean($data);
                    if ($this->hasBeanConverter()) {
                        $converter = $this->getBeanConverter();
                        $bean = $converter->convert($bean, $data)->toBean();
                    }
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
                    $this->getValidationHelper()->merge(
                        $processor->getValidationHelper()
                    );
                }
            }
        }
    }

    /**
     * @param IdListParameter $idListParameter
     * @param array $attributes
     */
    protected function save_bulk(IdListParameter $idListParameter, array $attributes): void
    {
        if ($this->hasBeanFinder() && $this->hasBeanProcessor()) {
            $finder = $this->getBeanFinder();
            if ($finder instanceof BeanFactoryAwareInterface) {
                $data = $attributes;
                $this->getBeanFinder()->filter($idListParameter->getAttribute_List());
                $beanList = $this->getBeanFinder()->getBeanList();
                foreach ($beanList as $bean) {
                    if ($this->hasBeanConverter()) {
                        $converter = $this->getBeanConverter();
                        $converter->convert($bean)->fromArray($data);
                    }
                }
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
                    if ($this->hasBeanOrderProcessor()) {
                        foreach ($beanList as $bean) {
                            $this->getBeanOrderProcessor()->delete($bean);
                        }
                    }
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
     * @param IdListParameter $idListParameter
     * @param array $attributes
     */
    protected function delete_bulk(IdListParameter $idListParameter, array $attributes): void
    {
        if ($this->hasBeanFinder() && $this->getBeanProcessor() && count($idListParameter->getAttribute_List())) {
            $finder = $this->getBeanFinder();
            $finder->limit($finder->count(), 0);
            $finder->filter($idListParameter->getAttribute_List());
            $processor = $this->getBeanProcessor();
            $beanList = $finder->getBeanList(true);
            if ($processor instanceof BeanListAwareInterface) {
                $processor->setBeanList($beanList);
            }
            $processor->delete();
            if ($this->hasBeanOrderProcessor()) {
                foreach ($beanList as $bean) {
                    $this->getBeanOrderProcessor()->delete($bean);
                }
            }
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
     * @throws NotFoundException
     */
    public function getBean(): BeanInterface
    {
        try {
            if ($this->hasBeanFinder()) {
                return $this->getBeanFinder()->getBean();
            }
        } catch (BeanException $exception) {
            throw new NotFoundException(
                'Could not get bean!',
                CoreException::NOT_FOUND_EXCEPTION_CODE,
                $exception
            );
        }
        throw new NotFoundException('Could not get bean!');
    }

    /**
     * @return BeanListInterface
     * @throws NotFoundException
     */
    public function getBeanList(): BeanListInterface
    {
        try {
            if ($this->hasBeanFinder()) {
                return $this->getBeanFinder()->getBeanListDecorator();
            }
        } catch (BeanException $exception) {
            throw new NotFoundException(
                'Could not get bean list!',
                CoreException::NOT_FOUND_EXCEPTION_CODE,
                $exception
            );
        }
        throw new NotFoundException('Could not get bean list!');
    }
}
