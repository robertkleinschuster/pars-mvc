<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Converter\AbstractBeanConverter;
use Niceshops\Bean\Type\Base\BeanInterface;
use Pars\Component\Base\Form\DateTimeLocal;
use Psr\Http\Message\UploadedFileInterface;

class ViewBeanConverter extends AbstractBeanConverter
{
    public function convertValueFromBean(BeanInterface $bean, string $name, $value)
    {
        switch ($bean->type($name)) {
            case BeanInterface::DATA_TYPE_STRING:
            case BeanInterface::DATA_TYPE_INT:
            case BeanInterface::DATA_TYPE_FLOAT:
                return (string)$value;
            case BeanInterface::DATA_TYPE_ARRAY:
                return json_encode($value);
            case BeanInterface::DATA_TYPE_BOOL:
                return $value ? 'true' : 'false';
            case \DateTime::class:
                try {
                    if ($value instanceof \DateTime) {
                        return $value->format(DateTimeLocal::FORMAT);
                    } else {
                        return '';
                    }
                } catch (\Exception $e) {
                    return $e->getMessage();
                }
            case UploadedFileInterface::class:
                return $value === null ? '' : $value->getClientFilename();
            default:
                if (is_scalar($value)) {
                    return (string)$value;
                } elseif (is_array($value)) {
                    return json_encode($value);
                }
                return $value;
        }
    }

    public function convertValueToBean(BeanInterface $bean, string $name, $value)
    {
        if (empty($value)) {
            return null;
        }
        switch ($bean->type($name)) {
            case BeanInterface::DATA_TYPE_STRING:
                return (string)$value;
            case BeanInterface::DATA_TYPE_INT:
                return (int)$value;
            case BeanInterface::DATA_TYPE_FLOAT:
                return (float)$value;
            case BeanInterface::DATA_TYPE_ARRAY:
                return json_decode($value);
            case BeanInterface::DATA_TYPE_BOOL:
                return $value === 'true' || $value === true;
            case \DateTime::class:
                return new \DateTime($value);
            case UploadedFileInterface::class:
                return $value instanceof UploadedFileInterface ? $value : null;
            default:
                return $value;
        }
    }

}
