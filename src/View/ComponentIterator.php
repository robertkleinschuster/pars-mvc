<?php


namespace Pars\Mvc\View;


class ComponentIterator extends \IteratorIterator
{
    public function current(): ComponentInterface
    {
        return parent::current();
    }

}
