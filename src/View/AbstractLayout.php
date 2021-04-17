<?php

namespace Pars\Mvc\View;

abstract class AbstractLayout extends ViewElement implements LayoutInterface
{
    private ?ComponentList $componentList = null;
    private ?ComponentList $componentListBefore = null;
    private ?ComponentList $componentListAfter = null;
    protected ?array $staticFiles = [];
    protected ?ViewInterface $view = null;
    /**
     * @return ComponentList
     */
    public function getComponentList(): ComponentList
    {
        if (null === $this->componentList) {
            $this->componentList = new ComponentList();
        }
        return $this->componentList;
    }


    /**
     * @return ComponentList
     */
    public function getComponentListAfter(): ComponentList
    {
        if (null === $this->componentListBefore) {
            $this->componentListBefore = new ComponentList();
        }
        return $this->componentListBefore;
    }


    /**
     * @return ComponentList
     */
    public function getComponentListSubAction(): ComponentList
    {
        if (null === $this->componentListAfter) {
            $this->componentListAfter = new ComponentList();
        }
        return $this->componentListAfter;
    }

    /**
     * @param array $data
     * @return $this|LayoutInterface
     */
    public function setStaticFiles(?array $data): LayoutInterface
    {
        $this->staticFiles = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getStaticFiles(): ?array
    {
        return $this->staticFiles ?? [];
    }

    /**
    * @return ViewInterface
    */
    public function getView(): ViewInterface
    {
        return $this->view;
    }

    /**
    * @param ViewInterface $view
    *
    * @return $this
    */
    public function setView(ViewInterface $view): self
    {
        $this->view = $view;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasView(): bool
    {
        return isset($this->view);
    }

}
