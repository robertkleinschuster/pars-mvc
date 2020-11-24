<?php


namespace Pars\Mvc\View;


interface LayoutInterface extends HtmlInterface
{
    public function getComponentList(): ComponentList;
}
