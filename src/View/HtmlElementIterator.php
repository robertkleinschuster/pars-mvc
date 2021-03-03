<?php

namespace Pars\Mvc\View;

class HtmlElementIterator extends \IteratorIterator
{
    public function current(): HtmlElement
    {
        return parent::current();
    }
}
