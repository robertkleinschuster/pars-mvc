<?php

declare(strict_types=1);

namespace Pars\Mvc;

use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Factory\ControllerFactoryFactory;
use Pars\Mvc\Factory\ModelFactory;
use Pars\Mvc\Factory\ModelFactoryFactory;
use Pars\Mvc\Handler\MvcHandler;
use Pars\Mvc\Handler\MvcHandlerFactory;

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
            ],
            'module' => [
                // 'routeName' => [] <- same keys as main mvc config, keys will be recursivly replaced
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
            ],
        ];
    }
}
