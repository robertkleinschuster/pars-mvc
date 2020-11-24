<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Psr\Container\ContainerInterface;

/**
 * Class ControllerFactoryFactory
 * @package Pars\Mvc\Factory
 */
class ControllerFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ControllerFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ControllerFactory($container);
    }
}
