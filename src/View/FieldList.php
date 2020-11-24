<?php


namespace Pars\Mvc\View;

use Niceshops\Bean\Type\Base\AbstractBaseBeanList;

/**
 * Class ComponentList
 * @package Pars\Component
 */
class FieldList extends AbstractBaseBeanList
{
    /**
     * @param FieldInterface[] $component
     * @return $this
     */
    public function push(...$component): self
    {
        return parent::push(...$component);
    }

    /**
     * @param FieldInterface[] $component
     * @return $this
     */
    public function unshift(...$component): self
    {
        return parent::unshift(...$component);
    }

    /**
     * @return FieldIterator
     */
    public function getIterator(): FieldIterator
    {
        return new FieldIterator(parent::getIterator());
    }
}
