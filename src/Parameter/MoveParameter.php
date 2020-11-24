<?php

namespace Pars\Mvc\Parameter;

use Pars\Mvc\Controller\ControllerRequest;

/**
 * Class MoveParameter
 * @package Pars\Mvc\Parameter
 */
class MoveParameter extends AbstractParameter
{
    public const ATTRIBUTE_STEPS = 'steps';
    public const ATTRIBUTE_FIELD = 'field';
    public const ATTRIBUTE_REFERENCE_FIELD = 'referenceField';
    public const ATTRIBUTE_REFERENCE_VALUE = 'referenceValue';


    public static function getParamterKey(): string
    {
        return ControllerRequest::ATTRIBUTE_MOVE;
    }


    /**
     * @param int $steps
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setSteps(int $steps)
    {
        $this->setAttribute(self::ATTRIBUTE_STEPS, strval($steps));
        return $this;
    }

    /**
     * @return int
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getSteps(): int
    {
        return intval($this->getAttribute(self::ATTRIBUTE_STEPS));
    }


    /**
     * @param string $field
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setField(string $field)
    {
        $this->setAttribute(self::ATTRIBUTE_FIELD, $field);
        return $this;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getField(): string
    {
        return $this->getAttribute(self::ATTRIBUTE_FIELD);
    }

    /**
     * @param string $referenceField
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setReferenceField(string $referenceField)
    {
        $this->setAttribute(self::ATTRIBUTE_REFERENCE_FIELD, $referenceField);
        return $this;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getReferenceField(): ?string
    {
        return $this->hasAttribute(self::ATTRIBUTE_REFERENCE_FIELD) ?
            $this->getAttribute(self::ATTRIBUTE_REFERENCE_FIELD) : null;
    }

    /**
     * @param string $referenceValue
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setReferenceValue($referenceValue)
    {
        $this->setAttribute(self::ATTRIBUTE_REFERENCE_VALUE, strval($referenceValue));
        return $this;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getReferenceValue(): ?string
    {
        return $this->hasAttribute(self::ATTRIBUTE_REFERENCE_VALUE) ?
            $this->getAttribute(self::ATTRIBUTE_REFERENCE_VALUE) : null;
    }

    /**
     * @param string $field
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setUp(string $field)
    {
        $this->setField($field);
        $this->setSteps(-1);
        return $this;
    }

    /**
     * @param string $field
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setDown(string $field)
    {
        $this->setField($field);
        $this->setSteps(1);
        return $this;
    }
}
