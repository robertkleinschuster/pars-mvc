<?php

namespace Pars\Mvc\View;

interface LayoutInterface extends HtmlInterface
{
    public function setView(ViewInterface $view);
    public function getView(): ViewInterface;
    public function hasView(): bool;
    /**
     * @return ComponentList
     */
    public function getComponentList(): ComponentList;

    /**
     * @return ComponentList
     */
    public function getComponentListAfter(): ComponentList;

    /**
     * @return ComponentList
     */
    public function getComponentListSubAction(): ComponentList;

    /***
     * @param array $data
     * @return $this
     */
    public function setStaticFiles(?array $data): self;
}
