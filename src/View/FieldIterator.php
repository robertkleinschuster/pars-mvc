<?php

namespace Pars\Mvc\View;
/**
 * Class FieldIterator
 * @package Pars\Mvc\View
 */
class FieldIterator extends \IteratorIterator
{
    /**
     * @return FieldInterface
     */
    public function current(): FieldInterface
    {
        return parent::current();
    }
}
