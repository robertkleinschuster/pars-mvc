<?php


namespace Pars\Mvc\View;


class ComponentIterator extends \IteratorIterator
{
    public function current(): HtmlInterface
    {
        return parent::current();
    }

}
