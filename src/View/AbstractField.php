<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

/**
 * Class AbstractField
 * @package Pars\Mvc\View
 */
abstract class AbstractField extends ViewElement implements FieldInterface
{

    public const OPTION_RESET_COLOR = 'text-reset';
    public const OPTION_MONOSPACE = 'text-monospace';
    public const OPTION_DECORATION_NONE = 'text-decoration-none';
    public const OPTION_ITALIC = 'font-italic';
    public const OPTION_WORD_BREAK = 'text-break';
    public const OPTION_TRUNCATE = 'text-truncate';

    public ?string $template = null;
    public ?string $label = null;
    public ?string $labelPath = null;
    public ?string $tooltip = null;
    public bool $iconField = false;
    public int $row = 1;
    public int $column = 1;

    public ?FieldAcceptInterface $accept = null;
    public ?FieldFormatInterface $format = null;

    /**
     * AbstractField constructor.
     * @param string|null $content
     * @param string|null $label
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    public function __construct(?string $content = null, ?string $label = null)
    {
        parent::__construct();
        $this->label = $label;
        $this->content = $content;
    }

    protected function initialize()
    {
        parent::initialize();

    }

    protected function beforeRender(BeanInterface $bean = null)
    {
        parent::beforeRender($bean);
        if ($this->hasTooltip()) {
            $this->setData('bs-toggle', 'tooltip');
            $this->setData('bs-placement', 'top');
            $this->setAttribute('title', $this->getTooltip());
        }
    }


    public function getTemplate(): string
    {
        if (null === $this->template) {
            $class = static::class;
            throw new ViewException("No template set in field '$class'.");
        }
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return null !== $this->template;
    }

    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getLabel(BeanInterface $bean = null): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     *
     * @return $this
     */
    public function setLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLabel(): bool
    {
        return $this->label !== null;
    }

    /**
     * @return callable
     */
    public function getAccept(): callable
    {
        return $this->accept;
    }

    /**
     * @param callable $accept
     *
     * @return $this
     */
    public function setAccept(callable $accept): self
    {
        $this->accept = $accept;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasAccept(): bool
    {
        return $this->accept !== null;
    }

    /**
     * @return callable
     */
    public function getFormat(): callable
    {
        return $this->format;
    }

    /**
     * @param callable $format
     *
     * @return $this
     */
    public function setFormat(callable $format): self
    {
        $this->format = $format;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasFormat(): bool
    {
        return $this->format !== null;
    }

    /**
     * @return string
     */
    public function getLabelPath(): string
    {
        return $this->labelPath;
    }

    /**
     * @param string $labelPath
     *
     * @return $this
     */
    public function setLabelPath(string $labelPath): self
    {
        $this->labelPath = $labelPath;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLabelPath(): bool
    {
        return isset($this->labelPath);
    }


    /**
     * @param BeanInterface|null $bean
     * @param bool $placeholders
     * @return string
     */
    public function render(BeanInterface $bean = null, bool $placeholders = false): string
    {
        if ($this->hasAccept()) {
            if (($this->getAccept())($this, $bean) === false) {
                return '';
            }
        }
        if ($this->hasFormat()) {
            $this->setContent(($this->getFormat())($this, $this->hasContent() ? $this->getContent($bean) : '', $bean));
        }
        return parent::render($bean, $placeholders);
    }




    /**
     * @return int
     */
    public function getRow(): int
    {
        return $this->row;
    }

    /**
     * @param int $row
     * @return AbstractField
     */
    public function setRow(?int $row): AbstractField
    {
        $this->row = $row ?? 1;
        return $this;
    }

    /**
     * @return int
     */
    public function getColumn(): int
    {
        return $this->column;
    }

    /**
     * @param int $column
     * @return AbstractField
     */
    public function setColumn(?int $column): AbstractField
    {
        $this->column = $column ?? 1;
        return $this;
    }


    /**
    * @return string
    */
    public function getTooltip(): ?string
    {
        return $this->tooltip;
    }

    /**
    * @param string $tooltip
    *
    * @return $this
    */
    public function setTooltip(?string $tooltip): self
    {
        $this->tooltip = $tooltip;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasTooltip(): bool
    {
        return isset($this->tooltip);
    }

    /**
     * @return bool
     */
    public function isIconField(): bool
    {
        return $this->iconField;
    }

    /**
     * @param bool $iconField
     * @return AbstractField
     */
    public function setIconField(bool $iconField): AbstractField
    {
        $this->iconField = $iconField;
        return $this;
    }



}
