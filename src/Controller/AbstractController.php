<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Helper\Path\PathHelper;
use Pars\Helper\Validation\ValidationHelper;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\Parameter\PaginationParameter;
use Pars\Mvc\View\ViewInterface;
use Throwable;

/**
 * Class AbstractController
 * @package Pars\Mvc\Controller
 */
abstract class AbstractController implements ControllerInterface
{

    /**
     * @var ControllerRequest
     */
    private ControllerRequest $controllerRequest;

    /**
     * @var ControllerResponse
     */
    private ControllerResponse $controllerResponse;

    /**
     * @var ModelInterface
     */
    private ModelInterface $model;

    /**
     * @var PathHelper
     */
    private PathHelper $pathHelper;

    /**
     * @var ViewInterface
     */
    private ?ViewInterface $view = null;

    /**
     * @var string|null
     */
    private ?string $template = null;

    /**
     * AbstractController constructor.
     * @param ControllerRequest $controllerRequest
     * @param ControllerResponse $controllerResponse
     * @param ModelInterface $model
     * @param PathHelper $pathHelper
     */
    public function __construct(
        ControllerRequest $controllerRequest,
        ControllerResponse $controllerResponse,
        ModelInterface $model,
        PathHelper $pathHelper
    ) {
        $this->model = $model;
        $this->controllerRequest = $controllerRequest;
        $this->controllerResponse = $controllerResponse;
        $this->pathHelper = $pathHelper;
    }

    /**
     * @var array
     */
    private array $subController_Map = [];

    /**
     * @param string $controllerCode
     * @param string $actionCode
     */
    protected function addSubController(string $controllerCode, string $actionCode)
    {
        $this->subController_Map[$controllerCode] = $actionCode;
    }

    /**
     * @return array
     */
    public function getSubControllerMap(): array
    {
        return $this->subController_Map;
    }

    /**
     * @return bool
     */
    public function hasSubControllerMap(): bool
    {
        return count($this->subController_Map) > 0;
    }


    /**
     * @return mixed|void
     */
    public function initialize()
    {
        $this->initView();
        $this->initModel();
        $this->handleParameter();
    }

    public function finalize()
    {
        $model = $this->getModel();
        if ($model instanceof ValidationHelperAwareInterface && $model->getValidationHelper()->hasError()) {
            $this->handleValidationError($model->getValidationHelper());
            $this->getControllerResponse()->setRedirect($this->getPathHelper(true)->getPath());
        }
    }

    /**
     * @param Throwable $exception
     * @return mixed|void
     */
    public function error(Throwable $exception)
    {
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Error</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Error</h1><p>{$exception->getMessage()}</p></body></html>");
    }

    /**
     * @return mixed|void
     */
    public function unauthorized()
    {
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Unauthorized</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Unauthorized</h1><p>Permission to requested ressource was denied!</p></body></html>");
    }


    /**
     * @return mixed
     */
    abstract protected function initView();

    /**
     * @return mixed
     */
    abstract protected function initModel();

    /**
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    protected function handleParameter()
    {
        if ($this->getControllerRequest()->isAjax()) {
            $this->getControllerResponse()->setMode(ControllerResponse::MODE_JSON);
        }

        if ($this->getControllerRequest()->hasNav()) {
            $navParameter = $this->getControllerRequest()->getNav();
            $this->handleNavigationState(
                $navParameter->getId(),
                $navParameter->getIndex()
            );
        }

        if ($this->getControllerRequest()->hasSearch()) {
            $searchParameter = $this->getControllerRequest()->getSearch();
            $this->getControllerResponse()->setRedirect($this->getPathHelper(true)->getPath());
            $this->getModel()->handleSearch($searchParameter);
        }

        if ($this->getControllerRequest()->hasOrder()) {
            $orderParameter = $this->getControllerRequest()->getOrder();
            $this->getModel()->handleOrder($orderParameter);
        }

        if ($this->getControllerRequest()->hasPagingation()) {
            $paginationParameter = $this->getControllerRequest()->getPagination();
            $this->getModel()->handlePagination($paginationParameter);
        } elseif ($this->getDefaultLimit() > 0) {
            $paginationParameter = new PaginationParameter();
            $paginationParameter->setLimit($this->getDefaultLimit())->setPage(0);
            $this->getModel()->handlePagination($paginationParameter);
        }

        if ($this->getControllerRequest()->hasId()) {
            $this->getModel()->handleId($this->getControllerRequest()->getId());
        }

        if ($this->getControllerRequest()->hasMove()) {
            $this->getModel()->handleMove($this->getControllerRequest()->getMove());
        }

        if ($this->getControllerRequest()->hasSubmit()) {
            if ($this->handleSubmitSecurity()) {
                $this->getModel()->handleSubmit(
                    $this->getControllerRequest()->getSubmit(),
                    $this->getControllerRequest()->getId(),
                    $this->getControllerRequest()->getAttribute_List()
                );
            }
        }

        if ($this->getControllerRequest()->hasRedirect()) {
            $this->getControllerResponse()->setRedirect($this->getControllerRequest()->getRedirect()->getLink());
        }
    }

    /**
     * @return int
     */
    protected function getDefaultLimit(): int
    {
        return 0;
    }

    /**
     * handle security checks e.g. csrf token before executing submit in model
     *
     * @return bool
     */
    abstract protected function handleSubmitSecurity(): bool;

    /**
     * gandle validation errors from model after submit
     * e.g. set to flash messanger to display them after redirect
     *
     * @param ValidationHelper $validationHelper
     * @return mixed
     */
    abstract protected function handleValidationError(ValidationHelper $validationHelper);

    /**
     * persist naviation states in session
     * @param string $id
     * @param int $index
     * @return mixed
     */
    abstract protected function handleNavigationState(string $id, int $index);

    /**
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest
    {
        return $this->controllerRequest;
    }


    /**
     * @return ControllerResponse
     */
    public function getControllerResponse(): ControllerResponse
    {
        return $this->controllerResponse;
    }

    /**
     * @param bool $setParameter
     * @return PathHelper
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getPathHelper(bool $setParameter = false): PathHelper
    {
        if ($setParameter) {
            $this->pathHelper->reset();
            if ($this->getControllerRequest()->hasId()) {
                $this->pathHelper->setId($this->getControllerRequest()->getId());
            }
            if ($this->getControllerRequest()->hasPagingation()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getPagination());
            }
            if ($this->getControllerRequest()->hasOrder()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getOrder());
            }
            if ($this->getControllerRequest()->hasSearch()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getSearch());
            }
            return $this->pathHelper;
        }
        return $this->pathHelper->reset();
    }

    /**
     *
     * /**
     * @return ModelInterface
     */
    public function getModel(): ModelInterface
    {
        return $this->model;
    }


    /**
     * @return ViewInterface
     */
    public function getView(): ViewInterface
    {
        return $this->view;
    }

    /**
     * @param ViewInterface $view
     * @return AbstractController
     */
    protected function setView(ViewInterface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasView(): bool
    {
        return null !== $this->view;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return $this->template !== null;
    }


    /**
     * @param string $key
     * @param $value
     * @return AbstractController
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    protected function setTemplateVariable(string $key, $value)
    {
        $this->getModel()->getTemplateData()->setData($key, $value);
        return $this;
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return true;
    }
}
