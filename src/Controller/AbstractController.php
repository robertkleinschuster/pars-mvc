<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Helper\Parameter\ContextParameter;
use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Path\PathHelper;
use Pars\Helper\Validation\ValidationHelper;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\View\ViewInterface;
use Throwable;

/**
 * Class AbstractController
 * @package Pars\Mvc\Controller
 */
abstract class AbstractController implements ControllerInterface
{

    const SUB_ACTION_MODE_TABBED = 'append';
    const SUB_ACTION_MODE_STANDARD = 'prepend';

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

    private ?ControllerInterface $parent = null;

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
    )
    {
        $this->model = $model;
        $this->controllerRequest = $controllerRequest;
        $this->controllerResponse = $controllerResponse;
        $this->pathHelper = $pathHelper;
    }

    /**
     * @var array
     */
    private array $action_Map = [];

    /**
     * @param string $controller
     * @param string $action
     * @param string $name
     * @param string $mode
     * @param bool $ajax
     */
    protected function pushAction(string $controller, string $action, string $name, string $mode = self::SUB_ACTION_MODE_TABBED, bool $ajax = true)
    {
        $this->action_Map[$mode][] = [
            'controller' => $controller,
            'action' => $action,
            'name' => $name,
            'ajax' => $ajax,
        ];
    }

    /**
     * @param string $mode
     * @return array
     */
    public function getActionMap(string $mode): array
    {
        return $this->action_Map[$mode];
    }

    /**
     * @param string $mode
     * @return bool
     */
    public function hasActions(string $mode): bool
    {
        return isset($this->action_Map[$mode]) && count($this->action_Map[$mode]) > 0;
    }

    /**
     * @return ControllerInterface|null
     */
    public function getParent(): ?ControllerInterface
    {
        return $this->parent;
    }

    /**
     * @param ControllerInterface|null $parent
     */
    public function setParent(?ControllerInterface $parent): void
    {
        $this->parent = $parent;
    }

    /**
     * @return bool
     */
    public function hasParent(): bool
    {
        return isset($this->parent);
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
        if ($this->hasView()) {
            $this->view = null;
            $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_RESPONSE);
        }
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Error</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Error</h1><p>{$exception->getMessage()}</p></body></html>");
    }

    /**
     * @return mixed|void
     */
    public function unauthorized()
    {
        if ($this->hasView()) {
            $this->view = null;
        }
        $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_RESPONSE);
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Unauthorized</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Unauthorized</h1><p>Permission to requested ressource was denied!</p></body></html>");
    }

    public function notfound(Throwable $exception)
    {
        if ($this->hasView()) {
            $this->view = null;
        }
        $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_RESPONSE);
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Not found</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Not found</h1><p>The requested ressource was not found!</p><p>{$exception->getMessage()}</p></body></html>");
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
            if ($this->getControllerRequest()->acceptParameter($paginationParameter)) {
                $this->getModel()->handlePagination($paginationParameter);
            } elseif ($this->getDefaultLimit() > 0) {
                $paginationParameter = new PaginationParameter();
                $paginationParameter->setController($this->getControllerRequest()->getController());
                $paginationParameter->setAction($this->getControllerRequest()->getAction());
                $paginationParameter->setLimit($this->getDefaultLimit())->setPage(1);
                $this->getModel()->handlePagination($paginationParameter);
            }
        } elseif ($this->getDefaultLimit() > 0) {
            $paginationParameter = new PaginationParameter();
            $paginationParameter->setController($this->getControllerRequest()->getController());
            $paginationParameter->setAction($this->getControllerRequest()->getAction());
            $paginationParameter->setLimit($this->getDefaultLimit())->setPage(1);
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
                if ($this->getControllerRequest()->hasId()) {
                    $id = $this->getControllerRequest()->getId();
                } else {
                    $id = new IdParameter();
                }
                if ($this->getControllerRequest()->hasIdList()) {
                    $idList = $this->getControllerRequest()->getIdList();
                } else {
                    $idList = new IdListParameter();
                }
                $this->getModel()->handleSubmit(
                    $this->getControllerRequest()->getSubmit(),
                    $id,
                    $idList,
                    $this->getControllerRequest()->getAttribute_List()
                );
            }
        }

        if ($this->getControllerRequest()->hasRedirect()) {
            $this->getControllerResponse()->setRedirect($this->getControllerRequest()->getRedirect()->getPath());
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
     * handle validation errors from model after submit
     * e.g. set to flash messenger to display them after redirect
     *
     * @param ValidationHelper $validationHelper
     * @return mixed
     */
    abstract protected function handleValidationError(ValidationHelper $validationHelper);

    /**
     * persist navigation states in session
     * @param string $id
     * @param int $index
     * @return mixed
     */
    abstract protected function handleNavigationState(string $id, int $index);

    /**
     * @param string $id
     * @return int
     */
    public abstract function getNavigationState(string $id): int;

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
            if ($this->getControllerRequest()->hasEditLocale()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getEditLocale());
            }
            if ($this->getControllerRequest()->hasContext()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getContext());
            }
            if ($this->getControllerRequest()->hasNav()) {
                $this->pathHelper->addParameter($this->getControllerRequest()->getNav());
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
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return true;
    }
}
