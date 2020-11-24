<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

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

    public function render(BeanInterface $bean = null): string
    {
        $this->initialize();
        return parent::render($bean);
    }


    abstract protected function initialize();

}
