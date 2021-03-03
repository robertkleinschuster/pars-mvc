<?php

namespace Pars\Mvc\View;

use Niceshops\Bean\Converter\BeanConverterAwareInterface;
use Niceshops\Bean\Converter\BeanConverterAwareTrait;
use Niceshops\Bean\Type\Base\AbstractBaseBean;

abstract class AbstractView extends AbstractBaseBean implements ViewInterface, BeanConverterAwareInterface
{
    use BeanConverterAwareTrait;

    protected ?LayoutInterface $layout = null;
    public ?string $template = null;

    /**
     * @return LayoutInterface
     */
    public function getLayout(): LayoutInterface
    {
        return $this->layout;
    }

    /**
     * @param LayoutInterface $layout
     * @return $this|ViewInterface
     */
    public function setLayout(LayoutInterface $layout): ViewInterface
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasLayout(): bool
    {
        return $this->layout !== null;
    }

    /**
     * @param string $template
     * @return $this|ViewInterface
     */
    public function setTemplate(string $template): ViewInterface
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return string
     * @throws ViewException
     */
    public function getTemplate(): string
    {
        if (null === $this->template) {
            $class = static::class;
            throw new ViewException("No template set in view '$class'.");
        }
        return $this->template;
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return null !== $this->template;
    }

    /**
     * @param ComponentInterface $component
     */
    public function append(ComponentInterface $component): self
    {
        if ($this->hasLayout()) {
            $this->getLayout()->getComponentList()->push($component);
        }
        return $this;
    }

    /**
     * @param ComponentInterface $component
     * @return mixed|void
     */
    public function prepend(ComponentInterface $component): self
    {
        if ($this->hasLayout()) {
            $this->getLayout()->getComponentList()->unshift($component);
        }
        return $this;
    }
}
