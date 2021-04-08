<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanException;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;

/**
 * Class AbstractComponent
 * @package Pars\Mvc\View
 */
abstract class AbstractComponent extends HtmlElement implements ComponentInterface
{

    public ?string $template = null;
    public ?string $name = null;

    /**
     * @param BeanInterface|null $bean
     * @throws BeanException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    protected function beforeRender(BeanInterface $bean = null)
    {
        if ($this->hasName()) {
            $this->unshift(new HtmlElement('h3.mb-1', $this->getName()));
        }
        parent::beforeRender($bean);
    }


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
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getName(BeanInterface $bean = null): string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     *
     * @return $this
     */
    public function setName(?string $name): self
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
}
