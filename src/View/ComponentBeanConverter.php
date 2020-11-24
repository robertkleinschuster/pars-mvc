<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Converter\AbstractBeanConverter;
use Niceshops\Bean\Type\Base\BeanInterface;

class ComponentBeanConverter extends AbstractBeanConverter
{
    public function convertValueFromBean(BeanInterface $bean, string $name, $value)
    {
        switch ($bean->type($name)) {
            case BeanInterface::DATA_TYPE_STRING:
            case BeanInterface::DATA_TYPE_INT:
            case BeanInterface::DATA_TYPE_FLOAT:
                return strval($value);
            case BeanInterface::DATA_TYPE_ARRAY:
                return json_encode($value);
            case BeanInterface::DATA_TYPE_BOOL:
                return $value ? 'true' : 'false';
            case \DateTime::class:
                try {
                    return new \DateTime($value);
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            default:
                return print_r($value, true);
        }
    }

    public function convertValueToBean(BeanInterface $bean, string $name, $value)
    {
        switch ($bean->type($name)) {
            case BeanInterface::DATA_TYPE_STRING:
                return strval($value);
            case BeanInterface::DATA_TYPE_INT:
                return intval($value);
            case BeanInterface::DATA_TYPE_FLOAT:
                return floatval($value);
            case BeanInterface::DATA_TYPE_ARRAY:
                return json_decode($value);
            case BeanInterface::DATA_TYPE_BOOL:
                return $value === 'true' || $value === true;
            case \DateTime::class:
                return new \DateTime($value);
            default:
                return '';
        }
    }

}
