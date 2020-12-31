<?php


namespace Pars\Mvc\View;


abstract class AbstractLayout extends HtmlElement implements LayoutInterface
{
    private ?ComponentList $componentList = null;

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
}
