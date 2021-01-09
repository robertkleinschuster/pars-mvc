<?php


namespace Pars\Mvc\View;


abstract class AbstractLayout extends HtmlElement implements LayoutInterface
{
    private ?ComponentList $componentList = null;
    protected array $staticFiles = [];

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
     * @param array $data
     * @return $this|LayoutInterface
     */
    public function setStaticFiles(array $data): LayoutInterface
    {
        $this->staticFiles = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getStaticFiles(): array
    {
        return $this->staticFiles;
    }



}
