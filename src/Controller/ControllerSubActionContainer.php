<?php


namespace Pars\Mvc\Controller;


class ControllerSubActionContainer implements \IteratorAggregate
{
    protected array $list = [];
    protected array $idGroupMap = [];

    protected ControllerInterface $parent;

    /**
     * ControllerSubActionContainer constructor.
     * @param ControllerInterface $parent
     */
    public function __construct(ControllerInterface $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return ControllerInterface
     */
    public function getParent(): ControllerInterface
    {
        return $this->parent;
    }

    /**
     * @param ControllerInterface $parent
     * @return ControllerSubActionContainer
     */
    public function setParent(ControllerInterface $parent): ControllerSubActionContainer
    {
        $this->parent = $parent;
        return $this;
    }




    /**
     * @param ControllerSubAction $action
     * @return $this
     */
    public function add(ControllerSubAction $action): self
    {
        $this->idGroupMap[$action->getId()] = $action->getGroup();
        $this->list[$action->getGroup()][$action->getId()] = $action;
        return $this;
    }

    /**
     * @param string $id
     * @return ControllerSubAction
     */
    public function get(string $id): ControllerSubAction
    {
        return $this->list[$this->idGroupMap[$id]];
    }

    /**
     * @param string $group
     * @return $this
     */
    public function getGroup(string $group): self
    {
        $container = new static($this->getParent());
        foreach ($this->list[$group] as $item) {
            $container->add($item);
        }
        return $container;
    }

    /**
     * @return array
     */
    public function getGroupList(): array
    {
        return array_keys($this->list);
    }

    /**
     * @return \Generator
     */
    public function getItems()
    {
        foreach ($this->list as $group) {
            foreach ($group as $item) {
                /**
                 * @var $item ControllerSubAction
                 */
                yield $item;
            }
        }
    }

    /**
     * @return \Generator
     */
    public function getIterator()
    {
        return $this->getItems();
    }


}
