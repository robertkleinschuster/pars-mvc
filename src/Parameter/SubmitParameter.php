<?php

namespace Pars\Mvc\Parameter;

use Pars\Mvc\Controller\ControllerRequest;

class SubmitParameter extends AbstractParameter
{
    public const ATTRIBUTE_MODE = 'mode';
    public const MODE_CREATE = 'create';
    public const MODE_SAVE = 'save';
    public const MODE_DELETE = 'delete';

    public static function getParamterKey(): string
    {
        return ControllerRequest::ATTRIBUTE_SUBMIT;
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
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setCreate()
    {
        $this->setMode(self::MODE_CREATE);
        return $this;
    }

    /**
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setDelete()
    {
        $this->setMode(self::MODE_DELETE);
        return $this;
    }

    /**
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setSave()
    {
        $this->setMode(self::MODE_SAVE);
        return $this;
    }
}
