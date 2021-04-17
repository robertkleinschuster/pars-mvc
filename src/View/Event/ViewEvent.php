<?php


namespace Pars\Mvc\View\Event;


use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Bean\Type\Base\AbstractBaseBeanList;
use Pars\Mvc\View\HtmlElement;

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
     * @param HtmlElement $element
     * @return $this
     */
    public function execute(HtmlElement $element)
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

    /**
     * @param string $path
     * @param string $id
     * @param callable $callback
     * @return static
     */
    public static function createCallback(string $path, string $id, callable $callback): self
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
