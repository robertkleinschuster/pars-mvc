<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Helper\Path\PathHelperAwareInterface;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\View\State\ViewStatePersistenceInterface;

/**
 * Interface ViewInterface
 * @package Pars\Mvc\View
 */
interface ViewInterface extends BeanInterface, BeanConverterAwareInterface, PathHelperAwareInterface
{
    /**
     * @return LayoutInterface
     */
    public function getLayout(): LayoutInterface;

    /**
     * @param LayoutInterface $layout
     * @return $this
     */
    public function setLayout(LayoutInterface $layout): self;

    /**
     * @return bool
     */
    public function hasLayout(): bool;

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): self;

    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @return bool
     */
    public function hasTemplate(): bool;

    /**
     * @param array $files
     * @return mixed
     */
    public function setStylesheets(array $files);

    /**
     * @return array
     */
    public function getStylesheets(): array;

    /**
     * @return array
     */
    public function setJavascript(array $files);

    public function addStylesheet(string $file);

    public function addJavascript(string $file);

    /**
     * @return array
     */
    public function getJavascript(): array;

    /**
     * @param ComponentInterface $component
     * @return $this
     */
    public function pushComponent(ComponentInterface $component): self;

    /**
     * @param ComponentInterface $component
     * @return $this
     */
    public function unshiftComponent(ComponentInterface $component): self;

    /**
     * @return ViewInjector
     */
    public function getInjector(): ViewInjector;

    /**
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest;

    /**
     * @param ControllerRequest $controllerRequest
     *
     * @return $this
     */
    public function setControllerRequest(ControllerRequest $controllerRequest): self;

    /**
     * @return bool
     */
    public function hasControllerRequest(): bool;

    /**
     * @return ViewStatePersistenceInterface
     */
    public function getPersistence(): ViewStatePersistenceInterface;

    /**
     * @param ViewStatePersistenceInterface $persistence
     *
     * @return $this
     */
    public function setPersistence(ViewStatePersistenceInterface $persistence): self;

    /**
     * @return bool
     */
    public function hasPersistence(): bool;

    /**
     * @return ViewRenderer
     */
    public function getRenderer(): ViewRenderer;

    /**
     * @param ViewRenderer $renderer
     *
     * @return $this
     */
    public function setRenderer(ViewRenderer $renderer): self;

    /**
     * @return bool
     */
    public function hasRenderer(): bool;
}
