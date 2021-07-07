<?php

declare(strict_types=1);

namespace Pars\Mvc;

use Laminas\Diactoros\ResponseFactory;
use Pars\Mvc\Controller\ControllerRunner;
use Pars\Mvc\Controller\ControllerRunnerFactory;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Factory\ControllerFactoryFactory;
use Pars\Mvc\Factory\ModelFactory;
use Pars\Mvc\Factory\ModelFactoryFactory;
use Pars\Mvc\Factory\ServerResponseFactory;
use Pars\Mvc\Factory\ServerResponseFactoryFactory;
use Pars\Mvc\Handler\MvcHandler;
use Pars\Mvc\Handler\MvcHandlerFactory;
use Pars\Mvc\View\ViewRenderer;
use Pars\Mvc\View\ViewRendererFactory;

/**
 * Class ConfigProvider
 * @package Pars\Mvc
 */
class ConfigProvider
{

    public function __invoke()
    {
        require_once 'global_functions.php';
        return [
            'dependencies' => $this->getDependencies(),
            'mvc' => $this->getMvc(),
            'bundles' => [
                'entrypoints' => [
                    'mvc'
                ],
            ]
        ];
    }

    protected function getMvc()
    {
        return [
            'error_controller' => 'error',
            'controllers' => [],
            'models' => [],
            'template_folder' => 'mvc',
            'action' => [
                'prefix' => '',
                'suffix' => 'Action'
            ]
        ];
    }

    protected function getDependencies()
    {
        return [
            'factories' => [
                MvcHandler::class => MvcHandlerFactory::class,
                ControllerFactory::class => ControllerFactoryFactory::class,
                ModelFactory::class => ModelFactoryFactory::class,
                ControllerRunner::class => ControllerRunnerFactory::class,
                ServerResponseFactory::class => ServerResponseFactoryFactory::class,
                ViewRenderer::class => ViewRendererFactory::class
            ],
            'aliases' => [
                ResponseFactory::class => ServerResponseFactory::class
            ]
        ];
    }
}
