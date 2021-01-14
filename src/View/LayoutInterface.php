<?php


namespace Pars\Mvc\View;


interface LayoutInterface extends HtmlInterface
{
    /**
     * @return ComponentList
     */
    public function getComponentList(): ComponentList;
    public function getComponentListBefore(): ComponentList;
    public function getComponentListAfter(): ComponentList;

    /***
     * @param array $data
     * @return $this
     */
    public function setStaticFiles(array $data): self;
}
