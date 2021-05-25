<?php

declare(strict_types=1);

namespace Pars\Mvc\Handler;

use Exception;
use Mezzio\Router\RouteResult;
use Mezzio\Template\TemplateRendererInterface;
use Pars\Bean\Type\Base\BeanException;
use Pars\Helper\Debug\DebugHelper;
use Pars\Helper\Parameter\NavParameter;
use Pars\Mvc\Controller\AbstractController;
use Pars\Mvc\Controller\ControllerInterface;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Controller\ControllerRunner;
use Pars\Mvc\Controller\ControllerRunnerFactory;
use Pars\Mvc\Exception\ActionNotFoundException;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Exception\NotFoundException;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Factory\ServerResponseFactory;
use Pars\Mvc\View\ComponentGroup;
use Pars\Mvc\View\DefaultComponent;
use Pars\Mvc\View\ViewException;
use Pars\Mvc\View\ViewRenderer;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Pars\Pattern\Exception\AttributeNotFoundException;
use Psr\Container\ContainerInterface;
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
    public const STATIC_FILES_ATTRIBUTE = 'static_files';

    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

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
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->renderer = $container->get(TemplateRendererInterface::class);
        $this->controllerFactory = $container->get(ControllerFactory::class);
        $this->appConfig = $container->get('config');
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ControllerNotFoundException
     * @throws MvcException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->getRunner()->run($request);
    }

    /**
     * @return ControllerRunner
     */
    protected function getRunner(): ControllerRunner
    {
        return $this->getContainer()->get(ControllerRunner::class);
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
     * @param string $basePath
     * @return string
     */
    public static function getRoute(string $basePath = ''): string
    {
        return $basePath . '[/[{' . self::CONTROLLER_ATTRIBUTE . '}[/[{' . self::ACTION_ATTRIBUTE . '}]]]]';
    }
}
