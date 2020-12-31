<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

interface HtmlInterface extends RenderableInterface
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
    public function setTag(string $tag): self;

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
    public function setId(string $id): self;

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
    public function setPath(string $path): self;

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
    public function setContent(string $value): self;

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
    public function setGroup(string $group): self;

    /**
     * @return bool
     */
    public function hasGroup(): bool;

    /**
     * @return HtmlElementList
     */
    public function getElementList(): HtmlElementList;

    /**
     * @param HtmlElementList $elementList
     *
     * @return $this
     */
    public function setElementList(HtmlElementList $elementList): self;

    /**
     * @return bool
     */
    public function hasElementList(): bool;


    /**
     * @param HtmlInterface ...$element
     * @return $this
     */
    public function push(...$element): self;

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
}
