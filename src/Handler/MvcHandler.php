<?php

declare(strict_types=1);

namespace Pars\Mvc\Handler;

use Exception;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;
use Niceshops\Bean\Type\Base\BeanException;
use Niceshops\Core\Exception\AttributeExistsException;
use Niceshops\Core\Exception\AttributeLockException;
use Niceshops\Core\Exception\AttributeNotFoundException;
use Pars\Core\Bundles\BundlesMiddleware;
use Pars\Helper\Parameter\NavParameter;
use Pars\Mvc\Controller\AbstractController;
use Pars\Mvc\Controller\ControllerInterface;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Exception\ActionNotFoundException;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Exception\NotFoundException;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Factory\ServerResponseFactory;
use Pars\Mvc\View\DefaultComponent;
use Pars\Mvc\View\ViewException;
use Pars\Mvc\View\ViewRenderer;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

/**
 * Class MvcHandler
 * @package Pars\Mvc\Handler
 */
class MvcHandler implements RequestHandlerInterface, MiddlewareInterface
{

    public const CONTROLLER_ATTRIBUTE = 'controller';
    public const ACTION_ATTRIBUTE = 'action';

    /**
     * @var TemplateRendererInterface
     */
    private TemplateRendererInterface $renderer;

    /**
     * @var ControllerFactory
     */
    private ControllerFactory $controllerFactory;

    /**
     * @var array
     */
    private array $appConfig;

    /**
     * MvcHandler constructor.
     * @param TemplateRendererInterface $renderer
     * @param ControllerFactory $controllerFactory
     * @param array $config
     */
    public function __construct(
        TemplateRendererInterface $renderer,
        ControllerFactory $controllerFactory,
        array $config
    ) {
        $this->renderer = $renderer;
        $this->controllerFactory = $controllerFactory;
        $this->appConfig = $config;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     * @throws BeanException
     * @throws ControllerNotFoundException
     * @throws MvcException
     * @throws ViewException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $mvcConfig = $this->appConfig['mvc'];
        $controllerCode = $request->getAttribute(self::CONTROLLER_ATTRIBUTE) ?? 'index';
        $actionCode = $request->getAttribute(self::ACTION_ATTRIBUTE) ?? 'index';
        $routeResult = $request->getAttribute(RouteResult::class);
        if (
            is_string($routeResult->getMatchedRouteName())
            && isset($mvcConfig['module'][$routeResult->getMatchedRouteName()])
        ) {
            $config = array_replace_recursive(
                $mvcConfig,
                $mvcConfig['module'][$routeResult->getMatchedRouteName()]
            );
        } else {
            $config = $mvcConfig;
        }
        $controller = $this->executeController($controllerCode, $actionCode, $config, $request);
        return (new ServerResponseFactory())($controller->getControllerResponse());
    }

    /**
     * @param string $controllerCode
     * @param string $actionCode
     * @param array $config
     * @param ServerRequestInterface $request
     * @param ControllerInterface|null $parent
     * @return ControllerInterface
     * @throws ControllerNotFoundException
     * @throws BeanException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ViewException
     */
    protected function executeController(
        string $controllerCode,
        string $actionCode,
        array $config,
        ServerRequestInterface $request,
        ControllerInterface $parent = null
    ): ControllerInterface {
        $mvcTemplateFolder = $config['template_folder'];
        $errorController = $config['error_controller'];
        $actionSuffix = $config['action']['suffix'] ?? '';
        $actionPrefix = $config['action']['prefix'] ?? '';
        $actionMethod = $actionPrefix . $actionCode . $actionSuffix;
        $elementId = $request->getQueryParams()['component'] ?? null;
        $componentonly = $request->getQueryParams()['componentonly'] ?? false;
        $controller = null;
        try {
            $controller = ($this->controllerFactory)($controllerCode, $request, $config, $this->appConfig);
            $controller->setParent($parent);
            $controller->getControllerRequest()->setAction($actionCode);
            $controller->getControllerRequest()->setController($controllerCode);
            $controller->initialize();
            if ($controller->isAuthorized()) {
                $this->executeControllerAction($controller, $actionMethod);
            } else {
                $controller->getControllerResponse()->setStatusCode(401);
                $controller->unauthorized();
            }
            $controller->finalize();
        } catch (NotFoundException $exception) {
            $controller = $this->getErrorController($controller, $errorController, $request, $config);
            $controller->getControllerResponse()->setStatusCode(404);
            $controller->notfound($exception);
        } catch (Throwable $exception) {
            $controller = $this->getErrorController($controller, $errorController, $request, $config);
            $controller->getControllerResponse()->setStatusCode(500);
            $controller->error($exception);
        }
        if ($controller->getControllerResponse()->hasOption(ControllerResponse::OPTION_RENDER_RESPONSE)) {
            if ($controller->hasView()) {
                if ($controller->hasActions(AbstractController::SUB_ACTION_MODE_STANDARD)) {
                    $this->handleActions(
                        AbstractController::SUB_ACTION_MODE_STANDARD,
                        $controllerCode,
                        $actionCode,
                        $config,
                        $request,
                        $controller
                    );
                }
                if ($controller->hasActions(AbstractController::SUB_ACTION_MODE_TABBED)) {
                    $this->handleActions(
                        AbstractController::SUB_ACTION_MODE_TABBED,
                        $controllerCode,
                        $actionCode,
                        $config,
                        $request,
                        $controller
                    );
                }
            }
            if ($parent === null) {
                if ($controller->hasView()) {
                    $viewRenderer = new ViewRenderer($this->renderer, $mvcTemplateFolder);
                    $view = $controller->getView();
                    if ($view->hasLayout()) {
                        $view->getLayout()->setStaticFiles($request->getAttribute(BundlesMiddleware::class)['list']);
                    }
                    $renderedOutput = $viewRenderer->render($view, $elementId, boolval($componentonly));
                    $controller->getControllerResponse()->setAttribute('component', $elementId);
                } elseif ($controller->hasTemplate()) {
                    $renderedOutput = $this->renderer->render(
                        "$mvcTemplateFolder::{$controller->getTemplate()}"
                    );
                } else {
                    $renderedOutput = $this->renderer->render(
                        "$mvcTemplateFolder::$controllerCode/$actionCode"
                    );
                }
                $controller->getControllerResponse()->setBody($renderedOutput);
            }
        }
        return $controller;
    }

    /**
     * @param string $mode
     * @param string $controller
     * @param string $action
     * @param array $config
     * @param ServerRequestInterface $request
     * @param ControllerInterface $parent
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws BeanException
     * @throws ControllerNotFoundException
     * @throws ViewException
     */
    protected function handleActions(
        string $mode,
        string $controller,
        string $action,
        array $config,
        ServerRequestInterface $request,
        ControllerInterface $parent
    ) {
        $active = 1;
        if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_STANDARD) {
            $id = $controller . $action . '-before';
            $active = $parent->getNavigationState($id . '__list');
            $parent->getView()->getLayout()->set('actionActiveBefore', $active);
            $parent->getView()->getLayout()->set('actionIdBefore', $id);
            $active = $parent->getNavigationState($id . '__list');
        }
        if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_TABBED) {
            $id = $controller . $action . '-after';
            $active = $parent->getNavigationState($id . '__list');
            $parent->getView()->getLayout()->set('actionActiveAfter', $active);
            $parent->getView()->getLayout()->set('actionIdAfter', $id);
        }
        foreach ($parent->getActionMap($mode) as $key => $item) {
            $path = $parent->getPathHelper(true)->addParameter((new NavParameter())->setId($id . '__list')->setIndex($key + 1))->getPath();

            if ($active == $key + 1 || !$item['ajax']) {
                $subController = $this->executeController(
                    $item['controller'],
                    $item['action'],
                    $config,
                    $request,
                    $parent
                );
                if ($subController->hasView() && $subController->getView()->hasLayout()) {
                    $components = $subController->getView()->getLayout()->getComponentList();
                    foreach ($components as $component) {
                        $component->setPath($path);
                        if (isset($item['name'])) {
                            $component->setName($item['name']);
                        }
                        if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_STANDARD) {
                            $parent->getView()->getLayout()->getComponentListAfter()->push($component);
                        }
                        if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_TABBED) {
                            $parent->getView()->getLayout()->getComponentListSubAction()->push($component);
                        }
                    }
                }
            } else {
                $ajaxTab = new DefaultComponent();
                $ajaxTab->setPath($path);
                if (isset($item['name'])) {
                    $ajaxTab->setName($item['name']);
                }
                if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_STANDARD) {
                    $parent->getView()->getLayout()->getComponentListAfter()->push($ajaxTab);
                }
                if ($parent->getView()->hasLayout() && $mode == AbstractController::SUB_ACTION_MODE_TABBED) {
                    $parent->getView()->getLayout()->getComponentListSubAction()->push($ajaxTab);
                }
            }
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param RequestHandlerInterface $handler
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $this->handle($request);
        if ($response->getStatusCode() === 404 && empty($response->getBody()->getSize())) {
            return $handler->handle($request);
        }
        return $response;
    }

    /**
     * @param $controller
     * @param $errorController
     * @param $request
     * @param $config
     * @return ControllerInterface
     * @throws ControllerNotFoundException
     */
    private function getErrorController($controller, $errorController, $request, $config): ControllerInterface
    {
        if (null === $controller) {
            $controller = ($this->controllerFactory)($errorController, $request, $config, $this->appConfig);
        }
        return $controller;
    }

    /**
     * @param ControllerInterface $controller
     * @param string $actionMethod
     * @throws ActionNotFoundException
     */
    protected function executeControllerAction(ControllerInterface $controller, string $actionMethod)
    {
        $methodBlacklist = get_class_methods(ControllerInterface::class);
        if (method_exists($controller, $actionMethod) && !in_array($actionMethod, $methodBlacklist)) {
            $controller->{$actionMethod}();
        } else {
            throw new ActionNotFoundException("Controller action $actionMethod not found.");
        }
    }

    /**
     * @param string $basePath
     * @return string
     */
    public static function getRoute(string $basePath = ''): string
    {
        return $basePath . '[/[{' . self::CONTROLLER_ATTRIBUTE . '}[/[{' . self::ACTION_ATTRIBUTE . '}[/]]]]]';
    }
}
