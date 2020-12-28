<?php

declare(strict_types=1);

namespace Pars\Mvc\Handler;

use Exception;
use Laminas\Diactoros\Response;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;
use Minifier\TinyMinify;
use Pars\Mvc\Controller\ControllerInterface;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Exception\ActionException;
use Pars\Mvc\Exception\ActionNotFoundException;
use Pars\Mvc\Exception\ControllerException;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\NotFoundException;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Factory\ServerResponseFactory;
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
    private $renderer;

    /**
     * @var ControllerFactory
     */
    private $controllerFactory;

    /**
     * @var array
     */
    private $config;

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
        $this->config = $config;
    }

    /**
     * @param $haystack
     * @param $needle
     * @return bool
     */
    protected function endsWith(string $haystack, string $needle)
    {
        $length = strlen($needle);
        if (!$length) {
            return true;
        }
        return substr($haystack, -$length) === $needle;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws ControllerNotFoundException
     * @throws \Niceshops\Bean\Type\Base\BeanException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     * @throws \Pars\Mvc\Exception\MvcException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $controllerCode = $request->getAttribute(self::CONTROLLER_ATTRIBUTE) ?? 'index';
        $actionCode = $request->getAttribute(self::ACTION_ATTRIBUTE) ?? 'index';
        $routeResult = $request->getAttribute(RouteResult::class);
        if (
            is_string($routeResult->getMatchedRouteName())
            && isset($this->config['module'][$routeResult->getMatchedRouteName()])
        ) {
            $config = array_replace_recursive(
                $this->config,
                $this->config['module'][$routeResult->getMatchedRouteName()]
            );
        } else {
            $config = $this->config;
        }
        $controller = $this->renderControllerAction($controllerCode, $actionCode, $config, $request);
        return (new ServerResponseFactory())($controller->getControllerResponse());
    }

    /**
     * @param string $controllerCode
     * @param string $actionCode
     * @param array $config
     * @param ServerRequestInterface $request
     * @return ControllerInterface
     * @throws ControllerNotFoundException
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    protected function renderControllerAction(
        string $controllerCode,
        string $actionCode,
        array $config,
        ServerRequestInterface $request
    ): ControllerInterface {
        $mvcTemplateFolder = $config['template_folder'];
        $errorController = $config['error_controller'];
        $actionSuffix = $config['action']['suffix'] ?? '';
        $actionPrefix = $config['action']['prefix'] ?? '';
        $actionMethod = $actionPrefix . $actionCode . $actionSuffix;
        $controller = null;
        try {
            $controller = ($this->controllerFactory)($controllerCode, $request, $config);
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
            if ($controller->hasSubControllerMap()) {
                foreach ($controller->getSubControllerMap() as $c => $a) {
                    $subController = $this->renderControllerAction($c, $a, $config, $request);
                    if ($subController->hasView() && $controller->hasView()) {
                        $components = $subController->getView()->getLayout()->getComponentList();
                        foreach ($components as $component) {
                            $controller->getView()->append($component);
                        }
                    }
                }
            }
            if ($controller->hasView()) {
                $viewRenderer = new ViewRenderer($this->renderer, $mvcTemplateFolder);
                $view = $controller->getView();
                $renderedOutput = $viewRenderer->render($view);
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
        return $controller;
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
    private function getErrorController($controller, $errorController, $request, $config)
    {
        if (null === $controller) {
            $controller = ($this->controllerFactory)($errorController, $request, $config);
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
