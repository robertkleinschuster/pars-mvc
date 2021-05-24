<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareTrait;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Helper\Path\PathHelperAwareTrait;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\View\State\ViewStatePersistenceInterface;

abstract class AbstractView extends AbstractBaseBean implements ViewInterface
{
    use BeanConverterAwareTrait;
    use PathHelperAwareTrait;

    protected array $cssFiles = [];
    protected array $jsFiles = [];
    protected ?LayoutInterface $layout = null;
    public ?string $template = null;
    protected ViewInjector $injector;
    /**
     * @var ViewStatePersistenceInterface|null
     */
    protected ?ViewStatePersistenceInterface $statePersistence = null;

    /**
     * @var ViewRenderer|null
     */
    protected ?ViewRenderer $renderer;
    /**
     *
     */
    protected ?ControllerRequest $controllerRequest = null;

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

    public function addStylesheet(string $file)
    {
        if (!in_array($file, $this->cssFiles)) {
            $this->cssFiles[] = $file;
        }
    }

    public function addJavascript(string $file)
    {
        if (!in_array($file, $this->jsFiles)) {

            $this->jsFiles[] = $file;
        }
    }


    /**
     * @return array
     */
    public function getJavascript(): array
    {
        return $this->jsFiles;
    }

    /**
     * @return ViewInjector
     */
    public function getInjector(): ViewInjector
    {
        if (!isset($this->injector)) {
            $this->injector = new ViewInjector();
        }
        return $this->injector;
    }
    /**
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest
    {
        return $this->controllerRequest;
    }

    /**
     * @param ControllerRequest $controllerRequest
     *
     * @return $this
     */
    public function setControllerRequest(ControllerRequest $controllerRequest): self
    {
        $this->controllerRequest = $controllerRequest;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasControllerRequest(): bool
    {
        return isset($this->controllerRequest);
    }

    /**
     * @return ViewStatePersistenceInterface
     */
    public function getPersistence(): ViewStatePersistenceInterface
    {
        return $this->statePersistence;
    }

    /**
     * @param ViewStatePersistenceInterface $persistence
     *
     * @return $this
     */
    public function setPersistence(ViewStatePersistenceInterface $persistence): self
    {
        $this->statePersistence = $persistence;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPersistence(): bool
    {
        return isset($this->statePersistence);
    }


    /**
     * @return ViewRenderer
     */
    public function getRenderer(): ViewRenderer
    {
        return $this->renderer;
    }

    /**
     * @param ViewRenderer $renderer
     *
     * @return $this
     */
    public function setRenderer(ViewRenderer $renderer): self
    {
        $this->renderer = $renderer;
        return $this;
    }


    /**
     * @return bool
     */
    public function hasRenderer(): bool
    {
        return isset($this->renderer);
    }
}
