<?php

namespace Pars\Mvc\Controller;

use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Mvc\Handler\MvcHandler;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class ControllerRunner
{
    private ContainerInterface $container;

    /**
     * ControllerRunner constructor.
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
     * @param ServerRequestInterface|ControllerRequest $request
     * @return ResponseInterface
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ControllerNotFoundException
     * @throws MvcException
     */
    public function run($request): ResponseInterface
    {
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $errorController = $mvcConfig['error_controller'];
        try {
            return $this->createController($request)->execute();
        } catch (\Throwable $exception) {
            return $this->createController($this->createErrorRequest($request, $errorController))->execute($exception);
        }
    }

    /**
     * @param ControllerSubActionContainer $subActionContainer
     * @return ResponseInterface|null
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ControllerNotFoundException
     * @throws MvcException
     */
    public function runSubAction(ControllerSubActionContainer $subActionContainer)
    {
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $errorController = $mvcConfig['error_controller'];
        foreach ($subActionContainer as $action) {

            /**
             * @var $action ControllerSubAction
             */
            try {
                return $this->createController($action->getControllerRequest())
                    ->setParent($subActionContainer->getParent())
                    ->execute(null, $action);
            } catch (\Throwable $exception) {
                return $this->createController($this->createErrorRequest($action->getControllerRequest(), $errorController))
                    ->setParent($subActionContainer->getParent())
                    ->execute($exception, $action);
            }
        }
        return null;
    }

    /**
     * @param ServerRequestInterface|ControllerRequest $request
     * @param string $errorController
     * @return ControllerRequest
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    protected function createErrorRequest($request, string $errorController): ControllerRequest
    {
        return $this->getControllerFactory()
            ->createControllerRequest($request)
            ->setController($errorController)
            ->setAction('error');
    }

    /**
     * @param $request
     * @return ControllerInterface
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ControllerNotFoundException
     * @throws MvcException
     */
    protected function createController($request): ControllerInterface
    {
        switch (true) {
            case $request instanceof ControllerRequest:
                $controllerRequest = $request;
                break;
            case $request instanceof ServerRequestInterface:
                $controllerRequest = $this->getControllerFactory()->createControllerRequest($request);
                break;
            default:
                throw new MvcException('Invalid request type');
        }
        return $this->getControllerFactory()->createController($controllerRequest);
    }

    /**
     * @return ControllerFactory
     */
    protected function getControllerFactory(): ControllerFactory
    {
        return $this->getContainer()->get(ControllerFactory::class);
    }
}
