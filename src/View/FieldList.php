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
     * @param FieldInterface[] $field
     * @return $this
     */
    public function push(...$field): self
    {
        return parent::push(...$field);
    }

    /**
     * @param FieldInterface[] $field
     * @return $this
     */
    public function unshift(...$field): self
    {
        return parent::unshift(...$field);
    }

    /**
     * @return FieldIterator
     */
  /*  public function getIterator(): FieldIterator
    {
        return new FieldIterator(parent::getIterator());
    }*/
}
