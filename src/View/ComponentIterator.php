<?php

namespace Pars\Mvc\View;

class ComponentIterator extends \IteratorIterator
{
    /**
     * @return ComponentInterface
     */
    public function current(): HtmlInterface
    {
        return parent::current();
    }
}
