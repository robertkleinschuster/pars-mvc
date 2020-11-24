<?php

namespace Pars\Mvc\Parameter;

use Pars\Mvc\Controller\ControllerRequest;

/**
 * Class IdParameter
 * @package Pars\Mvc\Parameter
 */
class IdParameter extends AbstractParameter
{
    /**
     * @param string $field
     * @param $value
     */
    public function addId(string $field, $value = null)
    {
        if (null === $value) {
            $value = "{{$field}}";
        }
        if (is_string($value)) {
            $this->setAttribute($field, $value);
        } elseif (is_integer($value)) {
            $this->setAttribute($field, strval($value));
        } elseif (is_bool($value)) {
            $this->setAttribute($field, strval($value));
        }
        return $this;
    }

    public static function getParamterKey(): string
    {
        return ControllerRequest::ATTRIBUTE_ID;
    }
}
