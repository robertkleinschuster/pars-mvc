<?php


namespace Pars\Mvc\View;


class ComponentGroup extends AbstractComponent
{
    protected ComponentList $componentList;

    protected function initialize()
    {
        foreach ($this->getComponentList() as $item) {
            $this->push($item);
        }
        parent::initialize();
    }


    /**
     * @return ComponentList
     */
    public function getComponentList(): ComponentList
    {
        if (!isset($this->componentList)) {
            $this->componentList = new ComponentList();
        }
        return $this->componentList;
    }

    /**
     * @param ComponentList $componentList
     * @return ComponentGroup
     */
    public function setComponentList(ComponentList $componentList): ComponentGroup
    {
        $this->componentList = $componentList;
        return $this;
    }

    /**
     * @param ComponentInterface $component
     * @return $this
     */
    public function pushComponent(ComponentInterface $component) {
        $this->getComponentList()->push($component);
        return $this;
    }
}
