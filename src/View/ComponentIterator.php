<?php

namespace Pars\Mvc\View;

class ComponentIterator extends \IteratorIterator
{
    /**
     * @return ComponentInterface
     */
    public function current(): ViewElementInterface
    {
        return parent::current();
    }
}
