<?php


namespace Pars\Mvc\View;


class ViewInjectorItem
{
    protected ViewElementInterface $element;
    protected string $selector;
    protected string $mode;

    /**
     * ViewInjectorItem constructor.
     * @param ViewElementInterface $element
     * @param string $selector
     * @param string $mode
     */
    public function __construct(ViewElementInterface $element, string $selector, string $mode)
    {
        $this->element = $element;
        $this->selector = $selector;
        $this->mode = $mode;
    }


    /**
     * @return ViewElementInterface
     */
    public function getElement(): ViewElementInterface
    {
        return $this->element;
    }

    /**
     * @param ViewElementInterface $element
     * @return ViewInjectorItem
     */
    public function setElement(ViewElementInterface $element): ViewInjectorItem
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return ViewInjectorItem
     */
    public function setMode(string $mode): ViewInjectorItem
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getSelector(): string
    {
        return $this->selector;
    }

    /**
     * @param string $selector
     * @return ViewInjectorItem
     */
    public function setSelector(string $selector): ViewInjectorItem
    {
        $this->selector = $selector;
        return $this;
    }


}
