<?php


namespace Pars\Mvc\View;


use Pars\Mvc\Controller\ControllerResponseInjector;

class ViewInjector
{
    public const MODE_REPLACE = ControllerResponseInjector::MODE_REPLACE;
    public const MODE_APPEND = ControllerResponseInjector::MODE_APPEND;
    public const MODE_PREPEND = ControllerResponseInjector::MODE_PREPEND;

    /**
     * @var ViewInjectorItem[]
     */
    protected array $item_List = [];

    /**
     * @param ViewInjectorItem $item
     * @return $this
     */
    public function pushItem(ViewInjectorItem $item): self
    {
        $this->item_List[] = $item;
        return $this;
    }

    /**
     * @param ViewElementInterface $element
     * @param string $selector
     * @param string $mode
     * @return $this
     */
    public function addElement(ViewElementInterface $element, string $selector, string $mode) {
        $this->pushItem(new ViewInjectorItem($element, $selector, $mode));
        return $this;
    }

    /**
     * @return ViewInjectorItem[]
     */
    public function getItemList(): array
    {
        return $this->item_List;
    }

}
