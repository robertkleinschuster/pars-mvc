<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

interface ComponentInterface
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
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getName(BeanInterface $bean = null): string;

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(?string $name): self;

    /**
     * @return bool
     */
    public function hasName(): bool;

}
