<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Converter\BeanConverterAwareTrait;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Helper\Path\PathHelperAwareTrait;

abstract class AbstractView extends AbstractBaseBean implements ViewInterface
{
    use BeanConverterAwareTrait;
    use PathHelperAwareTrait;

    protected array $cssFiles = [];
    protected array $jsFiles = [];
    protected ?LayoutInterface $layout = null;
    public ?string $template = null;

    /**
     * @return LayoutInterface
     */
    public function getLayout(): LayoutInterface
    {
        if (
             !$this->layout->hasBeanConverter()
            && $this->hasBeanConverter()
        ) {
            $this->layout->setBeanConverter($this->getBeanConverter());
        }
        return $this->layout;
    }

    /**
     * @param LayoutInterface $layout
     * @return $this|ViewInterface
     */
    public function setLayout(LayoutInterface $layout): ViewInterface
    {
        $layout->setView($this);
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
     * @return $this
     */
    public function pushComponent(ComponentInterface $component): self
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
    public function unshiftComponent(ComponentInterface $component): self
    {
        if ($this->hasLayout()) {
            $this->getLayout()->getComponentList()->unshift($component);
        }
        return $this;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setStylesheets(array $files)
    {
        $this->cssFiles = $files;
        return $this;
    }

    /**
     * @return array
     */
    public function getStylesheets(): array
    {
        return $this->cssFiles;
    }

    /**
     * @param array $files
     * @return $this
     */
    public function setJavascript(array $files)
    {
        $this->jsFiles = $files;
        return $this;
    }

    /**
     * @return array
     */
    public function getJavascript(): array
    {
        return $this->jsFiles;
    }


}
