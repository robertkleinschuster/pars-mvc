<?php


namespace Pars\Mvc\Factory;


use Psr\Container\ContainerInterface;

class ServerResponseFactoryFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ServerResponseFactory();
    }

}
