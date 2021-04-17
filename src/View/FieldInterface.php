<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

interface FieldInterface extends HtmlInterface
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

    /**
     * @return string
     */
    public function getLabelPath(): string;

    /**
     * @param string $labelPath
     *
     * @return $this
     */
    public function setLabelPath(string $labelPath): self;
    /**
     * @return bool
     */
    public function hasLabelPath(): bool;
}
