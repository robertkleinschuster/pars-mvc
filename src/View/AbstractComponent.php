<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanException;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Component\Base\Toolbar\Toolbar;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;

/**
 * Class AbstractComponent
 * @package Pars\Mvc\View
 */
abstract class AbstractComponent extends HtmlElement implements ComponentInterface
{
    use FieldListAwareTrait;

    public ?string $template = null;
    public ?string $name = null;

    private ?Toolbar $toolbar = null;
    protected ?Toolbar $subToolbar = null;
    private ?HtmlElement $before = null;
    private ?HtmlElement $after = null;

    /**
     * @param AbstractField $field
     * @return $this
     */
    public function pushField(AbstractField $field): self
    {
        $this->getFieldList()->push($field);
        return $this;
    }

    /**
     * @param AbstractField $field
     * @return $this
     */
    public function unshiftField(AbstractField $field): self
    {
        $this->getFieldList()->unshift($field);
        return $this;
    }

    protected function onConstruct()
    {
        parent::onConstruct();
        $this->initName();
        $this->initTemplate();
    }

    protected function initialize()
    {
        parent::initialize();
        $this->initAdditionalBefore();
        $this->handleAdditionalBefore();
        $this->initFieldsBefore();
        $this->initFields();
        $this->initFieldsAfter();
        $this->handleFields();
        $this->initAdditionalAfter();
        $this->handleAdditionalAfter();
    }

    protected function initName()
    {

    }

    protected function initTemplate()
    {

    }

    protected function initAdditionalBefore()
    {

    }

    protected function handleAdditionalBefore()
    {
        $this->push($this->getBefore());
        if ($this->hasToolbar()) {
            $this->push($this->getToolbar());
        }
        if ($this->hasSubToolbar()) {
            $this->push($this->getSubToolbar());
        }
    }

    protected function initFieldsBefore()
    {

    }

    protected function initFields()
    {

    }


    protected function initFieldsAfter()
    {

    }

    protected function handleFields()
    {

    }

    protected function initAdditionalAfter()
    {

    }

    protected function handleAdditionalAfter()
    {
        $this->push($this->getAfter());
    }


    /**
     * @param BeanInterface|null $bean
     * @throws BeanException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    protected function beforeRender(BeanInterface $bean = null)
    {
        if ($this->hasName()) {
            $this->unshift(new HtmlElement('h3.mb-1.modal-hidden', $this->getName()));
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

    public function getToolbar(): Toolbar
    {
        if (null === $this->toolbar) {
            $this->toolbar = new Toolbar();
        }
        return $this->toolbar;
    }

    public function getBefore(): HtmlElement
    {
        if (null === $this->before) {
            $this->before = new HtmlElement();
        }
        return $this->before;
    }

    public function getAfter(): HtmlElement
    {
        if (null === $this->after) {
            $this->after = new HtmlElement();
        }
        return $this->after;
    }

    public function hasToolbar(): bool
    {
        return isset($this->toolbar);
    }

    /**
     * @return bool
     */
    public function hasSubToolbar(): bool
    {
        return isset($this->subToolbar);
    }

    /**
     * @return Toolbar|null
     */
    public function getSubToolbar(): Toolbar
    {
        if (null == $this->subToolbar) {
            $this->subToolbar = new Toolbar();
        }
        return $this->subToolbar;
    }

}
