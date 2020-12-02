<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Converter\AbstractBeanConverter;
use Niceshops\Bean\Type\Base\AbstractBaseBean;
use Niceshops\Bean\Type\Base\BeanInterface;
use Pars\Component\Base\Form\DateTimeLocal;
use Psr\Http\Message\UploadedFileInterface;

class ViewBeanConverter extends AbstractBeanConverter
{

    private ?string $timezone = null;

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
                        $timezone = 'Europe/Vienna';
                        if ($this->hasTimezone()) {
                            $timezone = $this->getTimezone();
                        }
                        $userTimezone = new \DateTimeZone($timezone);
                        $offset = $userTimezone->getOffset($value);
                        $myInterval = \DateInterval::createFromDateString((string)$offset . 'seconds');
                        $value->add($myInterval);
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
                return $this->sanitizeString($name, (string)$value);
            case BeanInterface::DATA_TYPE_INT:
                return (int)$value;
            case BeanInterface::DATA_TYPE_FLOAT:
                return (float)$value;
            case BeanInterface::DATA_TYPE_ARRAY:
                return json_decode($value);
            case BeanInterface::DATA_TYPE_BOOL:
                return $value === 'true' || $value === true;
            case \DateTime::class:
                $value = new \DateTime($value);
                $userTimezone = new \DateTimeZone('UTC');
                $offset = $userTimezone->getOffset($value);
                $myInterval = \DateInterval::createFromDateString((string)$offset . 'seconds');
                $value->add($myInterval);
                return $value;
            case UploadedFileInterface::class:
                return $value instanceof UploadedFileInterface ? $value : null;
            case BeanInterface::class:
                return AbstractBaseBean::createFromArray($value);
            default:
                return $value;
        }
    }

    /**
     * @param string $name
     * @param $value
     * @return string
     */
    protected function sanitizeString(string $name, $value)
    {
        if (in_array($name, ['ArticleTranslation_Teaser', 'ArticleTranslation_Text', 'ArticleTranslation_Footer'])) {
            $allowed = '<div><span><pre><p><br><hr><hgroup><h1><h2><h3><h4><h5><h6>
            <ul><ol><li><dl><dt><dd><strong><em><b><i><u>
            <img><a><abbr><address><blockquote>
            <form><fieldset><label><input><textarea>
            <caption><table><tbody><td><tfoot><th><thead><tr>';
            return strip_tags($value, $allowed);
        } else {
            return strip_tags($value);
        }
    }

    /**
     * @return string
     */
    public function getTimezone(): string
    {
        return $this->timezone;
    }

    /**
     * @param string $timezone
     *
     * @return $this
     */
    public function setTimezone(?string $timezone): self
    {
        $this->timezone = $timezone;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTimezone(): bool
    {
        return isset($this->timezone);
    }


}
