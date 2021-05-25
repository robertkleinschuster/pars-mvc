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
abstract class AbstractComponent extends ViewElement implements ComponentInterface
{
    use FieldListAwareTrait;

    public ?string $template = null;
    public ?string $name = null;

    private ?Toolbar $toolbar = null;
    protected ?Toolbar $subToolbar = null;
    private ?ViewElement $before = null;
    private ?ViewElement $after = null;
    private ?ViewElement $main = null;
    protected bool $showToolbar = true;

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function pushField(FieldInterface $field): self
    {
        $this->getFieldList()->push($field);
        return $this;
    }

    /**
     * @param FieldInterface $field
     * @return $this
     */
    public function unshiftField(FieldInterface $field): self
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
        $this->initBase();
        $this->handleName();
        $this->initAdditionalBefore();
        $this->handleAdditionalBefore();
        $this->initToolbar();
        $this->handleToolbar();
        $this->initFieldsBefore();
        $this->initFields();
        $this->initFieldsAfter();
        $this->handleFields();
        $this->handleMain();
        $this->initAdditionalAfter();
        $this->handleAdditionalAfter();
    }

    protected function initBase()
    {

    }

    protected function handleMain()
    {
        if ($this->hasMain()) {
            $this->push($this->getMain());
        }
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
        if ($this->hasBefore()) {
            $this->push($this->getBefore());
        }
    }

    protected function initToolbar()
    {

    }

    protected function handleToolbar()
    {
        if ($this->isShowToolbar()) {
            if ($this->hasToolbar()) {
                $this->getMain()->push($this->getToolbar());
            }
            if ($this->hasSubToolbar()) {
                $this->getMain()->push($this->getSubToolbar());
            }
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
        if ($this->hasAfter()) {

            $this->push($this->getAfter());
        }
    }

    protected function handleName()
    {
        if ($this->hasName()) {
            $this->getBefore()->unshift(new ViewElement('h3.mb-2.modal-hidden', $this->getName()));
        }
    }

    /**
     * @param BeanInterface|null $bean
     * @throws BeanException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    protected function beforeRender(BeanInterface $bean = null)
    {

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
            $this->toolbar = new Toolbar('div.component-toolbar');
        }
        return $this->toolbar;
    }

    public function getBefore(): ViewElement
    {
        if (null === $this->before) {
            $this->before = new ViewElement('div.component-before');
        }
        return $this->before;
    }

    public function hasBefore()
    {
        return isset($this->before);
    }

    public function getAfter(): ViewElement
    {
        if (null === $this->after) {
            $this->after = new ViewElement('div.component-after');
        }
        return $this->after;
    }

    public function hasAfter()
    {
        return isset($this->after);
    }

    public function getMain(): ViewElement
    {
        if (null === $this->main) {
            $this->main = new ViewElement('div.component-main');
        }
        return $this->main;
    }

    public function hasMain()
    {
        return isset($this->main);
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
            $this->subToolbar = new Toolbar('div.component-subtoolbar');
        }
        return $this->subToolbar;
    }

    /**
     * @return bool
     */
    public function isShowToolbar(): bool
    {
        return $this->showToolbar;
    }

    /**
     * @param bool $showToolbar
     * @return AbstractComponent
     */
    public function setShowToolbar(bool $showToolbar): AbstractComponent
    {
        $this->showToolbar = $showToolbar;
        return $this;
    }


}
