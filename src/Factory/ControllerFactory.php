<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Pars\Helper\Path\PathHelper;
use Pars\Mvc\Controller\AbstractController;
use Pars\Mvc\Controller\ControllerInterface;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Handler\MvcHandler;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Pars\Pattern\Exception\CoreException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerFactory
 * @package Pars\Mvc\Factory
 */
class ControllerFactory
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
     * @return ControllerInterface
     * @throws ControllerNotFoundException
     */
    public function createController(ControllerRequest $request): ControllerInterface
    {
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $controllerClass = $this->getControllerClass($mvcConfig, $request->getController());
        return new $controllerClass($this->getContainer(), $request);
    }

    /**
     * @param ServerRequestInterface $request
     * @return ControllerRequest
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    public function createControllerRequest(ServerRequestInterface $request): ControllerRequest
    {
        return new ControllerRequest($request, $this->getContainer()->get(PathHelper::class));
    }

    /**
     * @param array $config
     * @param string $code
     * @return string
     * @throws ControllerNotFoundException
     */
    protected function getControllerClass(array $config, string $code): string
    {
        if (!isset($config['controllers'][$code])) {
            throw new ControllerNotFoundException(
                "No controller class found for code '$code'. Check your mvc configuration."
            );
        }
        return $config['controllers'][$code];
    }
}
