<?php

namespace Pars\Mvc\View;

use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Bean\Converter\BeanConverterAwareTrait;
use Pars\Bean\Converter\ConverterBeanDecorator;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Bean\Type\Base\BeanAwareInterface;
use Pars\Bean\Type\Base\BeanException;
use Pars\Bean\Type\Base\BeanInterface;
use Pars\Helper\Placeholder\PlaceholderHelper;
use Pars\Mvc\View\Event\ViewEvent;
use Pars\Mvc\View\State\ViewState;
use Pars\Mvc\View\State\ViewStatePersistenceInterface;
use Pars\Pattern\Attribute\AttributeAwareInterface;
use Pars\Pattern\Attribute\AttributeAwareTrait;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Pars\Pattern\Exception\AttributeNotFoundException;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;

class HtmlElement extends AbstractBaseBean implements
    HtmlInterface,
    OptionAwareInterface,
    AttributeAwareInterface,
    BeanConverterAwareInterface
{
    use OptionAwareTrait;
    use AttributeAwareTrait;
    use BeanConverterAwareTrait;

    /**
     *
     */
    public const ATTRIBUTE_ID = 'id';

    /**
     * @var string|null
     */
    public ?string $tag = null;

    /**
     * @var string|null
     */
    public ?string $content = null;

    /**
     * @var string|null
     */
    public ?string $path = null;

    /**
     * @var string|null
     */
    public ?string $target = null;

    /**
     * @var string|null
     */
    public ?string $group = null;

    /**
     * @var array|null
     */
    public ?array $inlineStyles = [];

    /**
     * @var HtmlElementList|null
     */
    public ?HtmlElementList $elementList = null;

    /**
     * @var bool
     */
    private bool $initialized = false;

    /**
     * @var ViewEvent|null
     */
    protected ?ViewEvent $event = null;

    /**
     * @var ViewState|null
     */
    protected ?ViewState $state = null;

    /**
     * @var ViewStatePersistenceInterface|null
     */
    protected ?ViewStatePersistenceInterface $statePersistence = null;

    /**
     * HtmlElement constructor.
     * @param string|null $tag
     * @param string|null $content
     * @param array|null $attributes
     * @param string|null $path
     * @param string|null $group
     * @throws BeanException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    public function __construct(
        ?string $tag = null,
        ?string $content = null,
        ?array $attributes = null,
        ?string $path = null,
        ?string $group = null
    )
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
        $this->addOption($this->getElementClass());
        $this->tag = $tag;
        $this->group = $group;
        $this->path = $path;
        $this->content = $content;
        if (null !== $attributes) {
            foreach ($attributes as $key => $value) {
                $this->setAttribute($key, $value);
            }
        }
        $this->onConstruct();
    }

    public function getElementClass(): string
    {
        $type = static::class;
        $exp = array_slice(explode('\\', $type), -4);
        return strtolower(implode('-', $exp));
    }

    protected function onConstruct()
    {

    }


    /**
     * @return string
     */
    public function getTag(): string
    {
        return $this->tag ?? 'div';
    }

    /**
     * @param string|null $tag
     *
     * @return $this
     */
    public function setTag(?string $tag): self
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
     * @throws AttributeNotFoundException
     */
    public function getId(BeanInterface $bean = null): string
    {
        return $this->getAttribute(self::ATTRIBUTE_ID);
    }

    /**
     * @param string|null $id
     *
     * @return $this
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    public function setId(?string $id): self
    {
        if ($id == null) {
            $this->unsetAttribute(self::ATTRIBUTE_ID);
        } else {
            $this->setAttribute(self::ATTRIBUTE_ID, $id);
        }
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
        return implode(' ', $this->getOption_List());
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
        return implode(' ', $attributes);
    }

    /**
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
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
        if (!$bean instanceof ConverterBeanDecorator && $this->hasBeanConverter()) {
            $bean = $this->getBeanConverter()->convert($bean);
        }
        $placeholderHelper = new PlaceholderHelper();
        return $placeholderHelper->replacePlaceholder($str, $bean);
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getPath(BeanInterface $bean = null): ?string
    {
        return $this->path;
    }

    /**
     * @param string|null $path
     *
     * @return $this
     */
    public function setPath(?string $path): self
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
        return $this->group;
    }

    /**
     * @param string|null $group
     *
     * @return $this
     */
    public function setGroup(?string $group): self
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
     * @param BeanInterface|null $bean
     * @return string
     */
    public function getContent(BeanInterface $bean = null): string
    {
        return $this->content;
    }

    /**
     * @param string|null $content
     *
     * @return $this
     */
    public function setContent(?string $content): self
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function handleInitialize()
    {
        if (!$this->initialized) {
            if ($this->hasState()) {
                $this->getState()->init();
            }
            $this->initialize();
            $this->initialized = true;
            $this->handleInlineStyles();
        }
    }

    /**
     * @param BeanInterface|null $bean
     * @param bool $placeholders
     * @return string
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function render(BeanInterface $bean = null, bool $placeholders = false): string
    {
        $this->handleInitialize();
        if ($this instanceof BeanAwareInterface && $this->hasBean()) {
            $placeholders = true;
            $bean = $this->getBean();
        }
        $this->beforeRender($bean);
        $result = '';
        $result .= $this->renderOpenTag($bean);
        $result .= $this->renderValue($bean);
        $result .= $this->renderElements($bean);
        $result .= $this->renderCloseTag($bean);
        if ($bean !== null && $placeholders) {
            $result = $this->replacePlaceholder($result, $bean);
        }
        return $result;
    }

    /**
     * @param BeanInterface|null $bean
     */
    protected function beforeRender(BeanInterface $bean = null)
    {
        if ($this->hasEvent()) {
            if (!$this->getEvent()->isset('path') && $this->hasPath()) {
                $this->getEvent()->path = $this->getPath($bean);
            }
            foreach (ViewEvent::getQueue() as $event) {
                if ($this->getEvent()->isset('id') && $this->getEvent()->id === $event->id) {
                    $this->getEvent()->execute($this);
                }
            }
            if ($this->getEvent()->isset('path') && !$this->hasPath()) {
                $this->setPath($this->getEvent()->get('path'));
            }
            $this->setData('event', json_encode($this->getEvent()));
        }
        if ($this->hasState()) {
            $this->getState()->finalize();
        }
    }

    /**
     *
     */
    protected function initialize()
    {
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
     * @param mixed ...$element
     * @return $this|HtmlInterface
     */
    public function unshift(...$element): HtmlInterface
    {
        $this->getElementList()->unshift(...$element);
        return $this;
    }

    /**
     * @param string $role
     * @return $this
     * @throws AttributeExistsException
     * @throws AttributeLockException
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
     */
    public function setAria(string $key, string $value): self
    {
        $this->setAttribute('aria-' . $key, $value);
        return $this;
    }

    /**
     * @param bool $hidden
     * @return $this
     * @throws AttributeExistsException
     * @throws AttributeLockException
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
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
    public function removeInlineStyle(string $name): HtmlElement
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
                $this->injectDependencies($element);
                try {
                    $this->beforeRenderElement($element, $bean);
                    $result .= $element->render($bean);
                } catch (\Throwable $error) {
                    $result .= $error->getMessage();
                    $result .= str_replace(PHP_EOL, '<br>', $error->getTraceAsString());
                }
            }
        }
        return $result;
    }

    /***
     * @param HtmlElement $element
     */
    protected function injectDependencies(HtmlElement $element)
    {
        if (!$element->hasBeanConverter() && $this->hasBeanConverter()) {
            $element->setBeanConverter($this->getBeanConverter());
        }
        if (!$element->hasPersistence() && $this->hasPersistence()) {
            $element->setPersistence($this->getPersistence());
        }
    }

    /**
     * @param HtmlElement $element
     * @param BeanInterface|null $bean
     */
    protected function beforeRenderElement(HtmlElement $element, BeanInterface $bean = null)
    {
    }

    /**
     * @param BeanInterface|null $bean
     * @return string
     */
    public function renderOpenTag(BeanInterface $bean = null): string
    {
        $tag = '';
        $attributes = $this->getHtmlAttributes($bean, true);
        if ($this->hasPath()) {
            if ($this->hasTarget()) {
                if ($this->hasOption('text-decoration-none')) {
                    $tag .= "<a class='text-decoration-none' href='{$this->getPath($bean)}' target='{$this->getTarget()}'>";
                } else {
                    $tag .= "<a href='{$this->getPath($bean)}' target='{$this->getTarget()}>";
                }
            } else {
                if ($this->hasOption('text-decoration-none')) {
                    $tag .= "<a class='text-decoration-none' href='{$this->getPath($bean)}'>";
                } else {
                    $tag .= "<a href='{$this->getPath($bean)}'>";
                }
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
    public function renderCloseTag(BeanInterface $bean = null): string
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
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function generateId(): string
    {
        $this->setId(
            substr(str_shuffle(str_repeat($x = 'abcdefghijklmnopqrstuvwxyz', (int)(ceil(10 / strlen($x))))), 1, 10)
        );
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getTarget(): string
    {
        return $this->target;
    }

    /**
     * @param string $target
     *
     * @return $this
     */
    public function setTarget(string $target): self
    {
        $this->target = $target;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasTarget(): bool
    {
        return isset($this->target);
    }

    /**
     * @return string
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param BeanInterface|null $bean
     * @param bool $placeholders
     * @return string
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function __invoke(BeanInterface $bean = null, bool $placeholders = false): string
    {
        return $this->render($bean, $placeholders);
    }


    /**
     * @param string $id
     * @return HtmlElement|null
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function getElementById(string $id): ?HtmlElement
    {
        $this->handleInitialize();
        if ($this->hasId() && $this->getId() == $id) {
            return $this;
        }
        foreach ($this->getElementList() as $element) {
            $this->injectDependencies($element);
            $found = $element->getElementById($id);
            if ($found !== null) {
                return $found;
            }
        }
        return null;
    }

    /**
     * @param string $class
     * @return HtmlElementList
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function getElementsByClassName(string $class): HtmlElementList
    {
        $this->handleInitialize();
        $list = new HtmlElementList();
        if ($this->hasOption($class)) {
            $list->push($this);
        }
        foreach ($this->getElementList() as $element) {
            $this->injectDependencies($element);
            $found = $element->getElementsByClassName($class);
            $list->push(...$found);
        }
        return $list;
    }


    /**
     * @param string $tag
     * @return HtmlElementList
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function getElementsByTagName(string $tag): HtmlElementList
    {
        $this->handleInitialize();
        $list = new HtmlElementList();
        if ($this->getTag() == $tag) {
            $list->push($this);
        }
        foreach ($this->getElementList() as $element) {
            $this->injectDependencies($element);
            $found = $element->getElementsByTagName($tag);
            $list->push(...$found);
        }
        return $list;
    }

    /**
     * @return ViewEvent
     */
    public function getEvent(): ViewEvent
    {
        return $this->event;
    }

    /**
     * @param ViewEvent $event
     *
     * @return $this
     */
    public function setEvent(ViewEvent $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasEvent(): bool
    {
        return isset($this->event);
    }


    /**
     * @return ViewState
     */
    public function getState(): ViewState
    {
        return $this->state;
    }

    /**
     * @param ViewState $state
     *
     * @return $this
     */
    public function setState(ViewState $state): self
    {
        if ($this->hasPersistence() && !$state->hasPersistence()) {
            $state->setPersistence($this->getPersistence());
            $state->init();
        }
        $this->state = $state;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasState(): bool
    {
        return isset($this->state);
    }

    /**
     * @return ViewStatePersistenceInterface
     */
    public function getPersistence(): ViewStatePersistenceInterface
    {
        return $this->statePersistence;
    }

    /**
     * @param ViewStatePersistenceInterface $persistence
     *
     * @return $this
     */
    public function setPersistence(ViewStatePersistenceInterface $persistence): self
    {
        $this->statePersistence = $persistence;
        if ($this->hasState()) {
            $this->getState()->setPersistence($persistence);
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPersistence(): bool
    {
        return isset($this->statePersistence);
    }


}
