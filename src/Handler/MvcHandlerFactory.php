<?php

declare(strict_types=1);

namespace Pars\Mvc\Handler;

use Mezzio\Template\TemplateRendererInterface;
use Pars\Mvc\Factory\ControllerFactory;
use Psr\Container\ContainerInterface;

/**
 * Class MvcHandlerFactory
 * @package Pars\Mvc\Handler
 */
class MvcHandlerFactory
{
    /**
     * @param ContainerInterface $container
     * @return MvcHandler
     */
    public function __invoke(ContainerInterface $container): MvcHandler
    {
        return new MvcHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(ControllerFactory::class),
            $container->get('config')['mvc']
        );
    }
}
