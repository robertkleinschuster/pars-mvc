<?php

namespace Pars\Mvc\Parameter;

use Pars\Mvc\Controller\ControllerRequest;

/**
 * Class OrderParameter
 * @package Pars\Mvc\Parameter
 */
class OrderParameter extends AbstractParameter
{
    public const ATTRIBUTE_MODE = 'mode';
    public const ATTRIBUTE_FIELD = 'field';

    public const MODE_ASC = 'asc';
    public const MODE_DESC = 'desc';

    public static function getParamterKey(): string
    {
        return ControllerRequest::ATTRIBUTE_ORDER;
    }


    /**
     * @param string $mode
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setMode(string $mode)
    {
        $this->setAttribute(self::ATTRIBUTE_MODE, $mode);
        return $this;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getMode(): string
    {
        return $this->getAttribute(self::ATTRIBUTE_MODE);
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
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setAscending()
    {
        $this->setMode(self::MODE_ASC);
        return $this;
    }

    /**
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setDescending()
    {
        $this->setMode(self::MODE_DESC);
        return $this;
    }
}
