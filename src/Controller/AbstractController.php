<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Niceshops\Core\Exception\AttributeExistsException;
use Niceshops\Core\Exception\AttributeLockException;
use Niceshops\Core\Exception\AttributeNotFoundException;
use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Path\PathHelper;
use Pars\Helper\Validation\ValidationHelper;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\View\ViewInterface;
use Throwable;

/**
 * Class AbstractController
 * @package Pars\Mvc\Controller
 */
abstract class AbstractController implements ControllerInterface
{

    public const SUB_ACTION_MODE_TABBED = 'append';
    public const SUB_ACTION_MODE_STANDARD = 'prepend';

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
     * @var ViewInterface|null
     */
    private ?ViewInterface $view = null;

    /**
     * @var string|null
     */
    private ?string $template = null;

    /**
     * @var ControllerInterface|null
     */
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
    ) {
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
    protected function pushAction(
        string $controller,
        string $action,
        string $name,
        string $mode = self::SUB_ACTION_MODE_TABBED,
        bool $ajax = true
    ) {
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

    /**
     * @return mixed|void
     * @throws MvcException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
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

    /**
     * @param Throwable $exception
     * @return mixed|void
     */
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     * @throws MvcException
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
            if ($this->getControllerRequest()->isPost()) {
                $this->getControllerResponse()->setRedirect($this->getPathHelper(true)->getPath());
            }
            $searchParameter = $this->getControllerRequest()->getSearch();
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

        if ($this->getControllerRequest()->hasFilter()) {
            $path = $this->getPathHelper(true);
            $idParameter = new IdParameter();
            $idParameter->addId_Map($this->getControllerRequest()->getFilter()->getAttributes());
            $path->addParameter($idParameter);
            $this->getControllerResponse()->setRedirect($path->getPath());
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
     * @throws MvcException
     */
    protected function handleSubmitSecurity(): bool
    {
        throw new MvcException(__METHOD__ . ' not implemented');
    }

    /**
     * handle validation errors from model after submit
     * e.g. set to flash messenger to display them after redirect
     *
     * @param ValidationHelper $validationHelper
     * @return mixed
     * @throws MvcException
     */
    protected function handleValidationError(ValidationHelper $validationHelper)
    {
        throw new MvcException(__METHOD__ . ' not implemented');
    }

    /**
     * persist navigation states in session
     * @param string $id
     * @param int $index
     * @return mixed
     * @throws MvcException
     */
    protected function handleNavigationState(string $id, int $index)
    {
        throw new MvcException(__METHOD__ . ' not implemented');
    }

    /**
     * @param string $id
     * @return int
     * @throws MvcException
     */
    public function getNavigationState(string $id): int
    {
        throw new MvcException(__METHOD__ . ' not implemented');
    }

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
     * @param bool $setParameter import current parameter to path
     * @return PathHelper
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
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
            return clone $this->pathHelper;
        }
        return clone $this->pathHelper->reset();
    }

    /**
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
    protected function setView(ViewInterface $view): self
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
