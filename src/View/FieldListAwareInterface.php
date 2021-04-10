<?php


namespace Pars\Mvc\View;

/**
 * Interface FieldListAwareInterface
 * @package Pars\Mvc\View
 */
interface FieldListAwareInterface
{
    /**
     * @return FieldList|null
     */
    public function getFieldList(): ?FieldList;

    /**
     * @param FieldList|null $fieldList
     * @return self
     */
    public function setFieldList(?FieldList $fieldList);
}
