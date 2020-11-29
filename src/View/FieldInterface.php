<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getTemplate(): string;

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): self;

    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getLabel(BeanInterface $bean = null): string;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setLabel(string $name): self;

    /**
     * @return bool
     */
    public function hasLabel(): bool;
}