<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Helper\Path\PathHelper;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\View\LayoutInterface;
use Pars\Mvc\View\ViewInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Interface ControllerInterface
 * @package Pars\Mvc\Controller
 */
interface ControllerInterface
{

    /**
     * @return mixed
     */
    public function initialize();

    /**
     * @return mixed
     */
    public function finalize();

    /**
     * @param Throwable $exception
     * @return mixed
     */
    public function error(Throwable $exception);

    /**
     * @return mixed
     */
    public function unauthorized();

    /**
     * @param Throwable $exception
     * @return mixed
     */
    public function notfound(Throwable $exception);

    /**
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest;

    /**
     * @return ControllerResponse
     */
    public function getControllerResponse(): ControllerResponse;

    /**
     * @return ResponseFactoryInterface
     */
    public function getResponseFactory(): ResponseFactoryInterface;

    /**
     * @return PathHelper
     */
    public function getPathHelper(): PathHelper;

    /**
     * @return ModelInterface
     */
    public function getModel(): ModelInterface;

    /**
     * @return ViewInterface
     */
    public function getView(): ViewInterface;

    /**
     * @return bool
     */
    public function hasView(): bool;

    /**
     * @return bool
     */
    public function hasViewLayout(): bool;

    /**
     * @return LayoutInterface
     */
    public function getViewLayout(): LayoutInterface;

    /**
     * @return bool
     */
    public function isAuthorized(): bool;

    /**
     * @param Throwable|null $throwable
     * @return ResponseInterface
     */
    public function execute(): ResponseInterface;

    /**
     * @param Throwable|null $throwable
     * @return mixed
     */
    public function executeError(?Throwable $throwable);

    /**
     * @return ControllerInterface|null
     */
    public function getParent(): ?ControllerInterface;

    /**
     * @param ControllerInterface|null $parent
     */
    public function setParent(?ControllerInterface $parent): self;

    /**
     * @return bool
     */
    public function hasParent(): bool;
}
