<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Helper\Path\PathHelper;
use Pars\Mvc\Model\ModelInterface;
use Pars\Mvc\View\ViewInterface;
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
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest;

    /**
     * @return ControllerResponse
     */
    public function getControllerResponse(): ControllerResponse;

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
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param string $template
     *
     * @return $this
     */
    public function setTemplate(string $template): self;

    /**
     * @return bool
     */
    public function hasTemplate(): bool;

    /**
     * @return bool
     */
    public function isAuthorized(): bool;
}
