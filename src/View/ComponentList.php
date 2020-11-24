<?php


namespace Pars\Mvc\View;

use Niceshops\Bean\Type\Base\AbstractBaseBeanList;

/**
 * Class ComponentList
 * @package Pars\Component
 */
class ComponentList extends AbstractBaseBeanList
{
    /**
     * @return ComponentIterator
     */
    public function getIterator()
    {
        return new ComponentIterator(parent::getIterator());
    }

    /**
     * @param ComponentInterface ...$values
     * @return $this
     */
    public function push(...$values): self
    {
        return parent::push(...$values);
    }

    /**
     * @param ComponentInterface ...$values
     * @return $this
     */
    public function unshift(...$values): self
    {
        return parent::unshift(...$values);
    }
}
