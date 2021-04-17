<?php


namespace Pars\Mvc\View\Event;


use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Mvc\View\ViewElement;

class ViewEvent extends AbstractBaseBean
{
    public const TYPE_LINK = 'link';
    public const TYPE_MODAL = 'modal';
    public const TYPE_SUBMIT = 'submit';
    public const TYPE_CALLBACK = 'callback';

    public const TRIGGER_CLICK = 'click';

    public ?string $id = null;
    public string $type = self::TYPE_LINK;
    public string $trigger = self::TRIGGER_CLICK;
    public ?string $path = null;
    public ?string $target = '#components';
    public bool $deleteCache = true;
    public bool $history = true;
    public ?string $form = null;
    public ?string $delegate = null;

    private $callback = null;

    public static ?ViewEventQueue $queue = null;

    /**
     * @return ViewEventQueue
     */
    public static function getQueue(): ViewEventQueue
    {
        if (self::$queue === null) {
            self::$queue = new ViewEventQueue();
        }
        return self::$queue;
    }

    /**
     * @param ViewElement $element
     * @return $this
     */
    public function execute(ViewElement $element)
    {
        if ($this->callback) {
            return ($this->callback)($element);
        }
        return $this;
    }

    /**
     * @param callable $callback
     * @return $this
     */
    public function setCallback(callable $callback): self
    {
        $this->callback = $callback;
        return $this;
    }

    public function hasPath(): bool
    {
        return $this->isset('path');
    }

    public function setPath(string $path) {
        $this->set('path', $path);
        return $this;
    }

    /**
     * @param string $target target selector
     */
    public function setTarget(string $target)
    {
        $this->set('target', $target);
        return $this;
    }

    public function hasTarget(): bool
    {
        return $this->isset('target');
    }

    public function getTarget()
    {
        return $this->get('target');
    }


    /**
     * @param string $targetId
     * @return $this
     */
    public function setTargetId(string $targetId)
    {
        $this->setTarget("#$targetId");
        return $this;
    }

    /**
     * @param string $delegate subelement selector
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    public function setDelegate(string $delegate)
    {
        $this->set('delegate', $delegate);
        return $this;
    }

    /**
     * @param callable $callback
     * @param string|null $path
     * @param string|null $id
     * @return static
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    public static function createCallback(callable $callback, string $path = null, string $id = null): self
    {
        return (new static([
            'type' => self::TYPE_CALLBACK,
            'path' => $path,
            'id' => $id,
            'target' => "#$id",
        ]))->setCallback($callback);
    }

    /**
     * @param string|null $path
     * @return static
     */
    public static function createLink(string $path = null): self
    {
        return new static([
            'type' => self::TYPE_LINK,
            'path' => $path,
        ]);
    }

    /**
     * @param string|null $path
     * @return static
     */
    public static function createModal(string $path = null): self
    {
        return new static([
            'type' => self::TYPE_MODAL,
            'path' => $path,
        ]);
    }

    /**
     * @param string|null $path
     * @return static
     */
    public static function createSubmit(string $path, string $form): self
    {
        return new static([
            'type' => self::TYPE_SUBMIT,
            'path' => $path,
            'form' => $form,
        ]);
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @param string|null $id
     * @return ViewEvent
     */
    public function setId(?string $id): ViewEvent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return isset($this->id);
    }
}
