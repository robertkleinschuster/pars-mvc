<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

interface FieldInterface extends ViewElementInterface
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

    /**
     * @return int
     */
    public function getRow(): int;

    /**
     * @param int $row
     * @return AbstractField
     */
    public function setRow(?int $row): AbstractField;

    /**
     * @return int
     */
    public function getColumn(): int;
    /**
     * @param int $column
     * @return AbstractField
     */
    public function setColumn(?int $column): AbstractField;

    /**
     * @return string|null
     */
    public function getTooltip(): ?string;

    /**
     * @param string|null $tooltip
     *
     * @return $this
     */
    public function setTooltip(?string $tooltip): self;

    /**
     * @return bool
     */
    public function hasTooltip(): bool;

    /**
     * @return bool
     */
    public function isIconField(): bool;

    /**
     * @param bool $iconField
     * @return AbstractField
     */
    public function setIconField(bool $iconField): AbstractField;
}
