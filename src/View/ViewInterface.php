<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

/**
 * Interface ViewInterface
 * @package Pars\Mvc\View
 */
interface ViewInterface extends BeanInterface
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
     * @param ComponentInterface $component
     * @return $this
     */
    public function append(ComponentInterface $component): self;

    /**
     * @param ComponentInterface $component
     * @return $this
     */
    public function prepend(ComponentInterface $component): self;
}
