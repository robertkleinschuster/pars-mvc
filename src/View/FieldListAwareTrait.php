<?php

namespace Pars\Mvc\View;

trait FieldListAwareTrait
{
    protected ?FieldList $fieldList = null;

    /**
     * @return FieldList|null|FieldInterface[]
     */
    public function getFieldList(): ?FieldList
    {
        if (!isset($this->fieldList)) {
            $this->fieldList = new FieldList();
        }
        return $this->fieldList;
    }

    /**
     * @param FieldList|null $fieldList
     * @return FieldListAwareTrait
     */
    public function setFieldList(?FieldList $fieldList)
    {
        $this->fieldList = $fieldList;
        return $this;
    }
}
