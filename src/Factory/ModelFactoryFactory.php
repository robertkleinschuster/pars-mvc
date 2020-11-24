<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Psr\Container\ContainerInterface;

/**
 * Class ModelFactoryFactory
 * @package Pars\Mvc\Factory
 */
class ModelFactoryFactory
{
    /**
     * @param ContainerInterface $container
     * @return ModelFactory
     */
    public function __invoke(ContainerInterface $container)
    {
        return new ModelFactory();
    }
}
