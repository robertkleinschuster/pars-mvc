<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

/**
 * Class AbstractField
 * @package Pars\Mvc\View
 */
abstract class AbstractField extends HtmlElement implements FieldInterface
{

    public const OPTION_RESET_COLOR = 'text-reset';
    public const OPTION_MONOSPACE = 'text-monospace';
    public const OPTION_DECORATION_NONE = 'text-decoration-none';
    public const OPTION_ITALIC = 'font-italic';
    public const OPTION_WORD_BREAK = 'text-break';
    public const OPTION_TRUNCATE = 'text-truncate';

    public ?string $template = null;
    public ?string $label = null;

    public ?FieldAcceptInterface $accept = null;
    public ?FieldFormatInterface $format = null;

    /**
     * AbstractField constructor.
     * @param string|null $content
     * @param string|null $label
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    public function __construct(?string $content = null, ?string $label = null)
    {
        parent::__construct();
        $this->label = $label;
        $this->content = $content;
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
        if (!$this->hasBeanConverter()) {
            $this->setBeanConverter(new ViewBeanConverter());
        }
        if ($this->hasFormat()) {
            $this->setContent(($this->getFormat())($this, $this->hasContent() ? $this->getContent($bean) : '', $bean));
        }
        return parent::render($bean, $placeholders);
    }
}
