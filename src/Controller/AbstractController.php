<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Laminas\Diactoros\CallbackStream;
use Pars\Helper\Parameter\FilterParameter;
use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Parameter\SearchParameter;
use Pars\Helper\Path\PathHelper;
use Pars\Helper\Validation\ValidationHelper;
use Pars\Helper\Validation\ValidationHelperAwareInterface;
use Pars\Mvc\Exception\ActionNotFoundException;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Exception\NotFoundException;
use Pars\Mvc\Factory\ModelFactory;
use Pars\Mvc\Factory\ServerResponseFactory;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\View\Event\ViewEvent;
use Pars\Mvc\View\LayoutInterface;
use Pars\Mvc\View\ViewException;
use Pars\Mvc\View\ViewInterface;
use Pars\Mvc\View\ViewRenderer;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Pars\Pattern\Exception\AttributeNotFoundException;
use Pars\Pattern\Exception\CoreException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class AbstractController
 * @package Pars\Mvc\Controller
 */
abstract class AbstractController implements ControllerInterface
{
    private ContainerInterface $container;

    /**
     * @var ControllerRequest
     */
    private ControllerRequest $controllerRequest;

    /**
     * @var ControllerResponse
     */
    private ControllerResponse $controllerResponse;

    /**
     * @var ServerResponseFactory
     */
    private ServerResponseFactory $responseFactory;

    /**
     * @var ModelInterface
     */
    private ModelInterface $model;

    /**
     * @var ViewInterface|null
     */
    private ?ViewInterface $view = null;

    /**
     * @var ControllerInterface|null
     */
    private ?ControllerInterface $parent = null;

    /**
     * @var ControllerSubActionContainer|null
     */
    private ?ControllerSubActionContainer $subActionContainer = null;

    /**
     * AbstractController constructor.
     * @param ContainerInterface $container
     * @param ControllerRequest $request
     * @throws ControllerNotFoundException
     */
    public function __construct(
        ContainerInterface $container,
        ControllerRequest $request
    )
    {
        $this->container = $container;
        $this->controllerRequest = $request;
        $this->responseFactory = $container->get(ResponseFactoryInterface::class);
        $this->model = $this->getModelFactory()->createModel($request);

    }

    /**
     * @return ControllerSubActionContainer
     */
    public function getSubActionContainer(): ?ControllerSubActionContainer
    {
        if (!$this->hasSubActionContainer()) {
            $this->subActionContainer = new ControllerSubActionContainer($this);
        }
        return $this->subActionContainer;
    }

    /**
     * @return bool
     */
    public function hasSubActionContainer(): bool
    {
        return isset($this->subActionContainer);
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @return ModelFactory
     */
    protected function getModelFactory(): ModelFactory
    {
        return $this->getContainer()->get(ModelFactory::class);
    }

    /**
     * @param string $controller
     * @param string $action
     * @param string $name
     * @return ControllerRequest
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    protected function pushAction(
        string $controller,
        string $action,
        string $name
    ): ControllerRequest
    {
        $childRequest = clone $this->getControllerRequest();
        $childRequest->setController($controller);
        $childRequest->setAction($action);
        $this->getSubActionContainer()->add(
            new ControllerSubAction($childRequest, $childRequest->getHash(), $name)
        );
        return $childRequest;
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
     * @return AbstractController
     */
    public function setParent(?ControllerInterface $parent): self
    {
        $this->parent = $parent;
        return $this;
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
        $this->handleView();
    }

    protected function handleView()
    {
        $this->injectStaticFiles();
        if ($this->hasView()) {
            $this->getView()->set('baseUrl', $this->getPathHelper()->getBaseUrl());
        }
    }

    /**
     * @return void
     * @throws MvcException
     */
    public function finalize()
    {
        $model = $this->getModel();
        if ($model instanceof ValidationHelperAwareInterface && $model->getValidationHelper()->hasError()) {
            $this->handleValidationError($model->getValidationHelper());
            if ($this->getControllerRequest()->hasSubmit()) {
                $this->getControllerResponse()->setRedirect($this->getPathHelper(true)->getPath());
            }
        }
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function error(Throwable $exception)
    {
        if ($exception instanceof NotFoundException) {
            return $this->notfound($exception);
        }
        if ($this->hasView()) {
            $this->view = null;
            $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_VIEW);
        }
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Error</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Error</h1><p>{$exception->getMessage()}</p></body></html>");
    }

    /**
     * @return void
     */
    public function unauthorized()
    {
        if ($this->hasView()) {
            $this->view = null;
        }
        $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_VIEW);
        $this->getControllerResponse()->setBody("<!DOCTYPE html><html><head><meta charset=\"utf-8\"><title>Unauthorized</title><meta name=\"author\" content=\"\"><meta name=\"description\" content=\"\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><h1>Unauthorized</h1><p>Permission to requested ressource was denied!</p></body></html>");
    }

    /**
     * @param Throwable $exception
     * @return void
     */
    public function notfound(Throwable $exception)
    {
        if ($this->hasView()) {
            $this->view = null;
        }
        $this->getControllerResponse()->removeOption(ControllerResponse::OPTION_RENDER_VIEW);
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

        if (
            $this->getControllerRequest()->isAjax()
            || $this->getControllerRequest()->hasEvent()
        ) {
            $this->getControllerResponse()->setMode(ControllerResponse::MODE_JSON);
        }
        if ($this->getControllerRequest()->hasEvent() && !$this->hasParent()) {
            $event = $this->getControllerRequest()->getEvent();
            $this->getControllerResponse()->setEvent($event);
            ViewEvent::getQueue()->push($event);
        }

        if ($this->getControllerRequest()->hasNav()) {
            $navParameter = $this->getControllerRequest()->getNav();
            if ($navParameter->hasId() && $navParameter->hasIndex()) {
                $this->handleNavigationState(
                    $navParameter->getId(),
                    $navParameter->getIndex()
                );
            }
        }

        if ($this->getControllerRequest()->hasCollapse()) {
            $collapseParameter = $this->getControllerRequest()->getCollapse();
            if ($collapseParameter->hasId() && $collapseParameter->hasExpanded()) {
                $this->handleCollapsableState(
                    $collapseParameter->getId(),
                    $collapseParameter->isExpanded()
                );
            }
        }

        if ($this->getControllerRequest()->hasSearch()) {
            if ($this->getControllerRequest()->hasPostData(SearchParameter::name())) {
                $path = $this->getPathHelper(true);
                $data = $this->getControllerRequest()->getPostData();
                if (isset($data[$path->getSearch()->name()])) {
                    $path->getSearch()->fromData($data[$path->getSearch()->name()])->removeEmpty();
                }
                $this->getControllerResponse()->setRedirect($path->getPath());
            }
            $searchParameter = $this->getControllerRequest()->getSearch();
            $this->getModel()->handleSearch($searchParameter);
        }

        if ($this->getControllerRequest()->hasOrder()) {
            $orderParameter = $this->getControllerRequest()->getOrder();
            if ($this->getControllerRequest()->acceptParameter($orderParameter)) {
                $this->getModel()->handleOrder($orderParameter);
            }
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
            if ($this->getControllerRequest()->hasPostData(FilterParameter::name())) {
                $path = $this->getPathHelper(true);
                $data = $this->getControllerRequest()->getPostData();
                if (isset($data[$path->getFilter()->name()])) {
                    $path->getFilter()->fromData($data[$path->getFilter()->name()])->removeEmpty();
                }
                $this->getControllerResponse()->setRedirect($path->getPath());
            }
            $filterParemter = $this->getControllerRequest()->getFilter()->removeEmpty();
            if ($this->getControllerRequest()->acceptParameter($filterParemter)) {
                $this->getModel()->handleFilter($filterParemter);
            }
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
     * persist collapsable states in session
     * @param string $id
     * @param bool $expanded
     * @return mixed
     * @throws MvcException
     */
    protected function handleCollapsableState(string $id, bool $expanded)
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
     * @param string $id
     * @return bool|null
     * @throws MvcException
     */
    public function getCollapsableState(string $id): ?bool
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
        if (!isset($this->controllerResponse)) {
            $this->controllerResponse = new ControllerResponse($this->getResponseFactory());
        }
        return $this->controllerResponse;
    }

    /**
     * @param bool $setParameter import current parameter to path
     * @return PathHelper
     */
    public function getPathHelper(bool $setParameter = false): PathHelper
    {
        return $this->getControllerRequest()->getPathHelper(!$setParameter);
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
        if (!$view->hasPathHelper()) {
            $this->getView()->setPathHelper($this->getPathHelper(true));
        }
        if (!$view->hasControllerRequest()) {
            $this->getView()->setControllerRequest($this->getControllerRequest());
        }
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
     * @return bool
     */
    public function hasViewLayout(): bool
    {
        return $this->hasView() && $this->getView()->hasLayout();
    }

    /**
     * @return LayoutInterface
     */
    public function getViewLayout(): LayoutInterface
    {
        return $this->getView()->getLayout();
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return true;
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed
     */
    protected function getMiddlewareAttribute(string $name, $default = null)
    {
        return $this->getControllerRequest()->getMiddlewareAttribute($name, $default);
    }

    /**
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory(): ServerResponseFactory
    {
        return $this->responseFactory;
    }

    /**
     * @param Throwable|null $throwable
     * @return mixed|ResponseInterface
     * @throws MvcException
     */
    public function executeError(?Throwable $throwable)
    {
        ob_start();
        $this->initialize();
        $this->error($throwable);
        $this->finalize();
        $output = ob_get_clean();
        return $this->renderResponse($output);
    }

    /**
     * @return ResponseInterface
     * @throws ActionNotFoundException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     * @throws ControllerNotFoundException
     * @throws MvcException
     * @throws ViewException
     */
    public function execute(): ResponseInterface
    {
        ob_start();
        $this->initialize();
        $this->action();
        $this->finalize();
        $output = ob_get_clean();
        return $this->renderResponse($output);
    }

    protected function action()
    {
        if ($this->isAuthorized()) {
            $methodBlacklist = get_class_methods(ControllerInterface::class);
            $actionMethod = $this->getActionMethod($this->getControllerRequest()->getAction());
            if (method_exists($this, $actionMethod) && !in_array($actionMethod, $methodBlacklist)) {
                $this->{$actionMethod}();
            } else {
                throw new ActionNotFoundException("Controller action $actionMethod not found.");
            }
        } else {
            $this->getControllerResponse()->setStatusCode(401);
            $this->unauthorized();
        }
    }

    /**
     * @param string $output
     * @return ResponseInterface
     * @throws MvcException
     */
    protected function renderResponse(string $output): ResponseInterface
    {
        if ($this->hasParent()) {
            echo $output;
        }
        $render = function () use ($output) {
            if ($this->getControllerResponse()->isMode(ControllerResponse::MODE_HTML)) {
                echo $output;
                flush();
            }
            if ($this->getControllerResponse()->hasOption(ControllerResponse::OPTION_RENDER_VIEW)) {
                $this->getRunner()->runSubActions($this->getSubActionContainer());
                if (!$this->hasParent()) {
                    $this->renderView();
                }
            }
        };
        $stream = new CallbackStream($render);
        $this->getControllerResponse()->setBody($stream);
        return $this->getControllerResponse()->createServerResponse();
    }


    /**
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     * @throws ViewException
     */
    protected function renderView()
    {
        if ($this->hasView()) {
            $id = $this->getControllerRequest()->getEventTarget();
            $this->getViewRenderer()->display($this->getView(), $id);
            foreach ($this->getView()->getInjector()->getItemList() as $item) {
                $this->getControllerResponse()->getInjector()->addHtml(
                    $item->getElement()->render($this->getView()),
                    $item->getSelector(),
                    $item->getMode()
                );
            }
        }
    }

    /**
     * @return ControllerRunner
     */
    protected function getRunner(): ControllerRunner
    {
        return $this->getContainer()->get(ControllerRunner::class);
    }

    /**
     * @return ViewRenderer
     */
    protected function getViewRenderer(): ViewRenderer
    {
        return $this->getContainer()->get(ViewRenderer::class);
    }

    /**
     * @param string $actionCode
     * @return string
     */
    protected function getActionMethod(string $actionCode): string
    {
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $actionSuffix = $mvcConfig['action']['suffix'] ?? '';
        $actionPrefix = $mvcConfig['action']['prefix'] ?? '';
        return $actionPrefix . $actionCode . $actionSuffix;
    }

    protected function injectStaticFiles()
    {
        if ($this->hasView()) {
            try {
                $entrypoints = json_decode(file_get_contents('public/build/entrypoints.json'), true);
                if ($entrypoints && isset($entrypoints['entrypoints'])) {
                    $jsFiles = [];
                    $cssFiles = [];
                    $entrypoints = $entrypoints['entrypoints'];
                    foreach ($entrypoints as $entrypoint) {
                        $jsFiles = array_unique(array_merge($jsFiles, $entrypoint['js']));
                        $cssFiles = array_unique(array_merge($cssFiles, $entrypoint['css']));
                    }
                    $this->getView()->setJavascript($jsFiles);
                    $this->getView()->setStylesheets($cssFiles);
                }
            } catch (Throwable $exception) {
                $this->getLogger()->error($exception->getMessage(), ['exception' => $exception]);
            }
        }
    }
}
