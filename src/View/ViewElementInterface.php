<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Type\Base\BeanAwareInterface;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Helper\Path\PathHelperAwareInterface;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\View\Event\ViewEvent;
use Pars\Mvc\View\State\ViewState;
use Pars\Mvc\View\State\ViewStatePersistenceInterface;
use Pars\Pattern\Attribute\AttributeAwareInterface;
use Pars\Pattern\Option\OptionAwareInterface;

interface ViewElementInterface extends
    RenderableInterface,
    BeanInterface,
    OptionAwareInterface,
    AttributeAwareInterface,
    BeanAwareInterface
{

    /**
     * @return string
     */
    public function getTag(): string;

    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setTag(?string $tag): self;

    /**
     * @return bool
     */
    public function hasTag(): bool;

    /**
     * @return string
     */
    public function getHtmlAttributes(BeanInterface $bean = null): string;

    /**
     * @return string
     */
    public function getCssClasses(BeanInterface $bean = null): string;

    /**
     * @return string
     */
    public function getId(BeanInterface $bean = null): string;

    /**
     * @param string $id
     * @return $this
     */
    public function setId(?string $id): self;

    /**
     * @return bool
     */
    public function hasId(): bool;

    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getPath(BeanInterface $bean = null);

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(?string $path): self;

    /**
     * @return bool
     */
    public function hasPath(): bool;

    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getContent(BeanInterface $bean = null): string;

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setContent(?string $value): self;

    /**
     * @return bool
     */
    public function hasContent(): bool;

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getGroup(BeanInterface $bean = null): string;

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup(?string $group): self;

    /**
     * @return bool
     */
    public function hasGroup(): bool;

    /**
     * @return ViewElementList
     */
    public function getElementList(): ViewElementList;

    /**
     * @param ViewElementList $elementList
     *
     * @return $this
     */
    public function setElementList(ViewElementList $elementList): self;

    /**
     * @return bool
     */
    public function hasElementList(): bool;


    /**
     * @param ViewElementInterface ...$element
     * @return $this
     */
    public function push(...$element): self;

    /**
     * @param mixed ...$element
     * @return $this
     */
    public function unshift(...$element): self;

    /**
     * @param string $role
     * @return mixed
     */
    public function setRole(string $role): self;

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function setData(string $key, string $value): self;

    /**
     * @param string $key
     * @param string $value
     * @return mixed
     */
    public function setAria(string $key, string $value): self;

    /**
     * @param bool $hidden
     * @return mixed
     */
    public function setHidden(bool $hidden): self;

    /**
     * @param string $value
     * @return mixed
     */
    public function setAccesskey(string $value): self;

    /**
     * @param string $id
     * @return ViewElement
     */
    public function getElementById(string $id): ?ViewElement;

    /**
     * @param string $class
     */
    public function getElementsByClassName(string $class);


    /**
     * @param string $tag
     */
    public function getElementsByTagName(string $tag);

    /**
     * @return ViewEvent
     */
    public function getEvent(): ViewEvent;

    /**
     * @param ViewEvent $event
     * @return $this
     */
    public function setEvent(ViewEvent $event): self;

    /**
     * @return bool
     */
    public function hasEvent(): bool;

    /**
     * @return ViewState
     */
    public function getState(): ViewState;

    /**
     * @param ViewState $state
     *
     * @return $this
     */
    public function setState(ViewState $state): self;

    /**
     * @return bool
     */
    public function hasState(): bool;

    public function setView(ViewInterface $view);
    public function getView(): ViewInterface;
    public function hasView(): bool;
}
