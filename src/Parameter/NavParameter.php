<?php

namespace Pars\Mvc\Parameter;

use Pars\Mvc\Controller\ControllerRequest;

/**
 * Class NavParameter
 * @package Pars\Mvc\Parameter
 */
class NavParameter extends AbstractParameter
{
    public const ATTRIBUTE_INDEX = 'index';
    public const ATTRIBUTE_ID = 'id';

    public static function getParamterKey(): string
    {
        return ControllerRequest::ATTRIBUTE_NAV;
    }


    /**
     * @param int $index
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setIndex(int $index)
    {
        $this->setAttribute(self::ATTRIBUTE_INDEX, strval($index));
        return $this;
    }

    /**
     * @return int
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getIndex(): int
    {
        return intval($this->getAttribute(self::ATTRIBUTE_INDEX));
    }

    /**
     * @param string $id
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setId(string $id)
    {
        $this->setAttribute(self::ATTRIBUTE_ID, $id);
        return $this;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getId(): string
    {
        return $this->getAttribute(self::ATTRIBUTE_ID);
    }
}
