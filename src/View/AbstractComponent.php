<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Converter\BeanConverterAwareInterface;
use Niceshops\Bean\Converter\BeanConverterAwareTrait;
use Niceshops\Bean\Type\Base\BeanInterface;

abstract class AbstractComponent extends HtmlElement implements ComponentInterface, BeanConverterAwareInterface
{
    use BeanConverterAwareTrait;

    public ?string $template = null;
    public ?string $name = null;

    /**
     * @return string
     * @throws ViewException
     */
    public function getTemplate(): string
    {
        if (null === $this->template) {
            $class = static::class;
            throw new ViewException("No template set in component '$class'.");
        }
        return $this->template;
    }

    /**
     * @param string $template
     * @return $this
     */
    public function setTemplate(string $template): self
    {
        $this->template = $template;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTemplate(): bool
    {
        return null !== $this->template;
    }

    /**
     * @param string $str
     * @param BeanInterface $bean
     * @return string
     */
    protected function replacePlaceholder(string $str, BeanInterface $bean): string
    {
        if ($this->hasBeanConverter()) {
            $bean = $this->getBeanConverter()->convert($bean);
        }
        return str_replace($bean->keys(), $bean->values(), $str);
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getName(BeanInterface $bean = null): string
    {
        if ($bean !== null) {
            return $this->replacePlaceholder($this->name, $bean);
        } else {
            return $this->name;
        }
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasName(): bool
    {
        return $this->name !== null;
    }

    public function render(BeanInterface $bean = null): string
    {
        if (!$this->hasBeanConverter()) {
            $this->setBeanConverter(new ViewBeanConverter());
        }
        $this->initialize();
        return parent::render($bean);
    }


    abstract protected function initialize();

}
