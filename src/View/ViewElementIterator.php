<?php

namespace Pars\Mvc\View;

class ViewElementIterator extends \IteratorIterator
{
    public function current(): ViewElement
    {
        return parent::current();
    }
}
