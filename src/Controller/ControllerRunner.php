<?php

namespace Pars\Mvc\Controller;

use Pars\Mvc\Exception\ControllerNotFoundException;
use Pars\Mvc\Exception\MvcException;
use Pars\Mvc\Factory\ControllerFactory;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerRunner
 * @package Pars\Mvc\Controller
 */
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
            return $this->createController($this->createErrorRequest($request, $errorController))->executeError($exception);
        }
    }

    /**
     * @param ControllerSubActionContainer $subActionContainer
     * @return array
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws ControllerNotFoundException
     * @throws MvcException
     */
    public function runSubActions(ControllerSubActionContainer $subActionContainer): array
    {
        $result = [];
        $config = $this->getContainer()->get('config');
        $mvcConfig = $config['mvc'];
        $errorController = $mvcConfig['error_controller'];
        $parent = $subActionContainer->getParent();
        foreach ($subActionContainer as $action) {
            try {
                $controller = $this->createController($action->getControllerRequest());
                $controller->setParent($parent);
                $response = $controller->execute();
            } catch (\Throwable $exception) {
                $controller = $this->createController($this->createErrorRequest($action->getControllerRequest(), $errorController));
                $controller->setParent($parent);
                $response = $controller->executeError($exception);
            }
            $this->handleSubActionView($controller, $parent, $action);
            $result[] = $response;
        }
        return $result;
    }

    /**
     * @param ControllerInterface $controller
     * @param ControllerInterface $parent
     * @param ControllerSubAction $subAction
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    protected function handleSubActionView(
        ControllerInterface $controller,
        ControllerInterface $parent,
        ControllerSubAction $subAction
    )
    {
        if ($controller->hasViewLayout() && $parent->hasViewLayout()) {
            $parentJs = $parent->getView()->getJavascript();
            $parentCss = $parent->getView()->getStylesheets();
            $actionJs = $controller->getView()->getJavascript();
            $actionCss = $controller->getView()->getStylesheets();
            $parent->getView()->setStylesheets(array_unique(array_merge($parentCss, $actionCss)));
            $parent->getView()->setJavascript(array_unique(array_merge($parentJs, $actionJs)));
            $parentLayout = $parent->getViewLayout();
            $subActionLayout = $controller->getViewLayout();
            $targetElement = $parentLayout->getElementById($subAction->getTargetId());
            $subActionElement = $subActionLayout->getElementById($subAction->getSourceId());
            if ($targetElement && $subActionElement) {
                $sourceView = clone $parent->getView();
                $sourceView->setControllerRequest($subAction->getControllerRequest());
                $subActionElement->setView($sourceView);
                $subActionElement->setId($subAction->getId());
                $subActionElement->removeOption('container-fluid');
                $targetElement->push($subActionElement);
            }
        }
    }

    /**
     * @param $request
     * @param string $errorController
     * @return ControllerRequest
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws MvcException
     */
    protected function createErrorRequest($request, string $errorController): ControllerRequest
    {
        return $this->createControllerRequest($request)
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
        $controllerRequest = $this->createControllerRequest($request);
        return $this->getControllerFactory()->createController($controllerRequest);
    }

    /**
     * @param $request
     * @return ControllerRequest
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws MvcException
     */
    protected function createControllerRequest($request): ControllerRequest
    {
        switch (true) {
            case $request instanceof ControllerRequest:
                return $request;
            case $request instanceof ServerRequestInterface:
                return $this->getControllerFactory()->createControllerRequest($request);
            default:
                throw new MvcException('Invalid request type');
        }
    }

    /**
     * @return ControllerFactory
     */
    protected function getControllerFactory(): ControllerFactory
    {
        return $this->getContainer()->get(ControllerFactory::class);
    }
}
