<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Handler\MvcHandler;
use Pars\Mvc\Model\ModelInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ModelFactory
 * @package Pars\Mvc\Factory
 */
class ModelFactory
{


    protected ContainerInterface $container;

    /**
     * ControllerFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return ContainerInterface
     */
    public function getContainer(): ContainerInterface
    {
        return $this->container;
    }
    /**
     * @param ControllerRequest $request
     * @return mixed
     * @throws ControllerNotFoundException
     */
    public function createModel(ControllerRequest $request): ModelInterface
    {
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $model = $this->getModelClass($mvcConfig, $request->getController());
        return new $model($this->getContainer());
    }

    /**
     * @param array $config
     * @param string $code
     * @return string
     * @throws ControllerNotFoundException
     */
    protected function getModelClass(array $config, string $code): string
    {
        if (!isset($config['models'][$code])) {
            throw new ControllerNotFoundException(
                "No model class found for code '$code'. Check your mvc configuration."
            );
        }
        return $config['models'][$code];
    }
}
