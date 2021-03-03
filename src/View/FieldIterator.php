<?php

namespace Pars\Mvc\View;

class FieldIterator extends \IteratorIterator
{
    public function current(): FieldInterface
    {
        return parent::current();
    }
}
