<?php

namespace Pars\Mvc\Parameter;

use Niceshops\Core\Attribute\AttributeAwareInterface;
use Niceshops\Core\Attribute\AttributeAwareTrait;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\Helper\ParameterMapHelper;

abstract class AbstractParameter implements AttributeAwareInterface
{
    use AttributeAwareTrait;

    /**
     * @var ParameterMapHelper
     */
    private ?ParameterMapHelper $parameterMapHelper = null;

    /**
     * @return ParameterMapHelper
     */
    public function getParameterMapHelper(): ParameterMapHelper
    {
        if (null === $this->parameterMapHelper) {
            $this->parameterMapHelper = new ParameterMapHelper();
        }
        return $this->parameterMapHelper;
    }

    /**
     * @param string $parameter
     * @return AbstractParameter
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function fromString(string $parameter)
    {
        $data = $this->getParameterMapHelper()->parseParameter($parameter);
        $this->fromArray($data);
        return $this;
    }

    /**
     * @param array $parameter
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function fromArray(array $parameter)
    {
        foreach ($parameter as $key => $value) {
            $this->setAttribute($key, $value);
        }
        return $this;
    }

    /**
     * @param ControllerRequest $request
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function fromRequest(ControllerRequest $request)
    {
        if ($request->hasAttribute($this->getParamterKey())) {
            $parameter = $request->getAttribute($this->getParamterKey());
            $this->fromData($parameter);
        }
    }

    /**
     * @param $data
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function fromData($data)
    {
        if (is_string($data)) {
            $this->fromString($data);
        }
        if (is_array($data)) {
            $this->fromArray($data);
        }
    }

    /**
     * @param string $attribute
     * @return string
     */
    public static function getFormKey(string $attribute)
    {
        return static::getParamterKey() . "[$attribute]";
    }


    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getParameterMapHelper()->generateParameter($this->getAttribute_List());
    }

    /**
     * @return string
     */
    abstract public static function getParamterKey(): string;
}
