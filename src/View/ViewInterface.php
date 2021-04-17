<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Helper\Path\PathHelperAwareInterface;

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
}
