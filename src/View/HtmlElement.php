<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Converter\BeanConverterAwareInterface;
use Niceshops\Bean\Converter\BeanConverterAwareTrait;
use Niceshops\Bean\Type\Base\AbstractBaseBean;
use Niceshops\Bean\Type\Base\BeanInterface;
use Niceshops\Core\Attribute\AttributeAwareInterface;
use Niceshops\Core\Attribute\AttributeAwareTrait;
use Niceshops\Core\Option\OptionAwareInterface;
use Niceshops\Core\Option\OptionAwareTrait;

class HtmlElement extends AbstractBaseBean implements
    HtmlInterface,
    OptionAwareInterface,
    AttributeAwareInterface,
    BeanConverterAwareInterface
{
    use OptionAwareTrait;
    use AttributeAwareTrait;
    use BeanConverterAwareTrait;

    public const ATTRIBUTE_ID = 'id';

    public ?string $tag = null;
    public ?string $content = null;
    public ?string $path = null;
    public ?string $group = null;
    public ?array $inlineStyles = [];
    public ?HtmlElementList $elementList = null;

    /**
     * HtmlElement constructor.
     * @param string|null $tag
     * @param string|null $content
     * @param string|null $path
     * @param string|null $group
     * @throws \Niceshops\Bean\Type\Base\BeanException
     */
    public function __construct(?string $tag = null, ?string $content = null, ?array $attributes = null, ?string $path = null, ?string $group = null)
    {
        parent::__construct();
        $exp = explode('.', $tag);
        if (count($exp) > 1) {
            $tag = $exp[0];
            $tagExp = explode('#', $tag);
            if (count($tagExp) > 1) {
                $tag = $tagExp[0];
                $this->setId($tagExp[1]);
            }
            unset($exp[0]);
            foreach ($exp as $str) {
                $this->addOption($str);
            }
        }
        $this->tag = $tag;
        $this->group = $group;
        $this->path = $path;
        $this->content = $content;
        if (null !== $attributes) {
            foreach ($attributes as $key => $content) {
                $this->setAttribute($key, $content);
            }
        }
    }


    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag ?? 'div';
    }

    /**
     * @param string $tag
     *
     * @return $this
     */
    public function setTag(string $tag): self
    {
        $this->tag = $tag;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTag(): bool
    {
        return $this->tag !== null;
    }


    /**
     * @param BeanInterface|null $bean
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function getId(BeanInterface $bean = null): string
    {
        if (null !== $bean) {
            return $this->replacePlaceholder($this->getAttribute(self::ATTRIBUTE_ID), $bean);
        } else {
            return $this->getAttribute(self::ATTRIBUTE_ID);
        }
    }

    /**
     * @param string $id
     *
     * @return $this
     */
    public function setId(string $id): self
    {
        $this->setAttribute(self::ATTRIBUTE_ID, $id);
        return $this;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return $this->hasAttribute(self::ATTRIBUTE_ID);
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getCssClasses(BeanInterface $bean = null): string
    {
        if (null !== $bean) {
            return $this->replacePlaceholder(implode(' ', $this->getOption_List()), $bean);
        } else {
            return implode(' ', $this->getOption_List());
        }
    }

    /**
     * @param BeanInterface|null $bean
     * @param bool $class
     * @return string
     */
    public function getHtmlAttributes(BeanInterface $bean = null, bool $class = false): string
    {
        $attributes = [];
        foreach ($this->getAttribute_List() as $key => $value) {
            $attributes[] = "$key='$value'";
        }
        if ($class) {
            $classes = $this->getCssClasses($bean);
            if (!empty($classes)) {
                $attributes[] = "class='{$classes}'";
            }
        }
        if ($bean !== null) {
            return $this->replacePlaceholder(implode(' ', $attributes), $bean);
        } else {
            return implode(' ', $attributes);
        }
    }

    protected function handleInlineStyles()
    {
        $styles = "";
        if ($this->hasAttribute('style')) {
            $styles = $this->getAttribute('style');
        }
        foreach ($this->inlineStyles as $name => $value) {
            $styles .= " $name: $value;";
        }
        if (!empty($styles)) {
            $this->setAttribute('style', $styles);
        }
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
        $keys = [];
        $values = [];
        foreach ($bean->toArray() as $name => $value) {
            if (is_string($value)) {
                $keys[] = "{{$name}}";
                $keys[] = urlencode("{{$name}}");
                $values[] = $value;
                $values[] = urlencode($value);
            } else {
                $keys[] = "{{$name}}";
                $values[] = "$name not string";
            }
        }
        return str_replace($keys, $values, $str);
    }


    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getPath(BeanInterface $bean = null)
    {
        if ($bean !== null) {
            return $this->replacePlaceholder($this->path, $bean);
        } else {
            return $this->path;
        }
    }

    /**
     * @param string $path
     *
     * @return $this
     */
    public function setPath(string $path): self
    {
        $this->path = $path;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPath(): bool
    {
        return $this->path !== null;
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getGroup(BeanInterface $bean = null): string
    {
        if (null !== $bean) {
            return $this->replacePlaceholder($this->group, $bean);
        } else {
            return $this->group;
        }
    }

    /**
     * @param string $group
     *
     * @return $this
     */
    public function setGroup(string $group): self
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasGroup(): bool
    {
        return $this->group !== null;
    }


    /**
     * @param BeanInterface $bean
     * @return string
     */
    public function getContent(BeanInterface $bean = null): string
    {
        if ($bean !== null) {
            return $this->replacePlaceholder($this->content, $bean);
        } else {
            return $this->content;
        }
    }

    /**
     * @param string $content
     *
     * @return $this
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasContent(): bool
    {
        return $this->content !== null;
    }

    /**
     * @return HtmlElementList
     */
    public function getElementList(): HtmlElementList
    {
        if (null === $this->elementList) {
            $this->elementList = new HtmlElementList();
        }
        return $this->elementList;
    }

    /**
     * @param HtmlElementList $elementList
     *
     * @return $this
     */
    public function setElementList(HtmlElementList $elementList): self
    {
        $this->elementList = $elementList;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasElementList(): bool
    {
        return $this->elementList !== null && $this->elementList->count();
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function render(BeanInterface $bean = null): string
    {
        $result = '';
        $result .= $this->renderOpenTag($bean);
        $result .= $this->renderValue($bean);
        $result .= $this->renderElements($bean);
        $result .= $this->renderCloseTag($bean);
        return $result;
    }

    /**
     * @param mixed ...$element
     * @return $this|HtmlInterface
     */
    public function push(...$element): HtmlInterface
    {
        $this->getElementList()->push(...$element);
        return $this;
    }

    /**
     * @param string $role
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setRole(string $role): self
    {
        $this->setAttribute('role', $role);
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setData(string $key, string $value): self
    {
        $this->setAttribute('data-' . $key, $value);
        return $this;
    }

    /**
     * @param string $key
     * @param string $value
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setAria(string $key, string $value): self
    {
        $this->setAttribute('aria-' . $key, $value);
        return $this;
    }

    /**
     * @param bool $hidden
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setHidden(bool $hidden): self
    {
        if ($hidden) {
            $this->setAttribute('hidden', 'hidden');
        } else {
            $this->unsetAttribute('hidden');
        }
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     */
    public function setAccesskey(string $value): self
    {
        $this->setAttribute('accesskey', $value);
        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return $this
     */
    public function addInlineStyle(string $name, string $value)
    {
        $this->inlineStyles[$name] = $value;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeInlineStyle(string $name)
    {
        unset($this->inlineStyles[$name]);
        return $this;
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    protected function renderValue(BeanInterface $bean = null): string
    {
        $result = '';
        if ($this->hasContent()) {
            $result .= $this->getContent($bean);
        }
        return $result;
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    protected function renderElements(BeanInterface $bean = null): string
    {
        $result = '';
        if ($this->hasElementList()) {
            foreach ($this->getElementList() as $element) {
                if (!$element->hasBeanConverter() && $this->hasBeanConverter()) {
                    $element->setBeanConverter($this->getBeanConverter());
                }
                $result .= $element->render($bean);
            }
        }
        return $result;
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    protected function renderOpenTag(BeanInterface $bean = null): string
    {
        $this->handleInlineStyles();
        $tag = '';
        $attributes = $this->getHtmlAttributes($bean, true);
        if ($this->hasPath()) {
            if ($this->hasOption('text-decoration-none')) {
                $tag .= "<a class='text-decoration-none' href='{$this->getPath($bean)}'>";
            } else {
                $tag .= "<a href='{$this->getPath($bean)}'>";
            }
        }
        if (empty($attributes)) {
            $tag .= "<{$this->getTag()}>";
        } else {
            $tag .= "<{$this->getTag()} {$attributes}>";
        }
        return $tag;
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    protected function renderCloseTag(BeanInterface $bean = null): string
    {
        $tag = '';
        $tag .= "</{$this->getTag()}>";
        if ($this->hasPath()) {
            $tag .= "</a>";
        }
        return $tag;
    }

    /**
     * @return string
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
     */
    public function generateId(): string
    {
        $this->setId(substr(str_shuffle(str_repeat($x='abcdefghijklmnopqrstuvwxyz', intval(ceil(10/strlen($x))) )),1, 10));
        return $this->getId();
    }
}
