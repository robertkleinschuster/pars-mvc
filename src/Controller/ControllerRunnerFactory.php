<?php


namespace Pars\Mvc\Controller;


use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

class ControllerRunnerFactory
{
    public function __invoke(ContainerInterface $container)
    {
        return new ControllerRunner($container);
    }

}
