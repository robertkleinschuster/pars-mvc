<?php


namespace Pars\Mvc\View\State;


use Pars\Bean\Type\Base\AbstractBaseBean;

class ViewState extends AbstractBaseBean
{
    /**
     * @var ViewStatePersistenceInterface
     */
    protected ViewStatePersistenceInterface $elementStatePersistence;

    /**
     * @var string
     */
    public string $id = '';

    /**
     * ViewState constructor.
     * @param string $id
     * @param array $data
     * @throws \Pars\Bean\Type\Base\BeanException
     */
    public function __construct(string $id, array $data = [])
    {
        $this->id = $id;
        parent::__construct($data);
    }

    public function init()
    {
        if ($this->hasPersistence()) {
            $this->fromArray($this->getPersistence()->load($this->getId())->toArray());
        }
    }

    public function finalize()
    {
        if ($this->hasPersistence()) {
            $this->getPersistence()->save($this->getId(), $this);
        }
    }

    /**
     * @param string $name
     * @param null $default
     * @return mixed|null
     */
    public function get(string $name, $default = null)
    {
        if (!$this->isset($name)) {
            return $default;
        }
        return parent::get($name);
    }


    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }


    /**
     * @return ViewStatePersistenceInterface
     */
    public function getPersistence(): ViewStatePersistenceInterface
    {
        return $this->elementStatePersistence;
    }

    /**
     * @param ViewStatePersistenceInterface $elementStatePersistence
     *
     * @return $this
     */
    public function setPersistence(ViewStatePersistenceInterface $elementStatePersistence): self
    {
        $this->elementStatePersistence = $elementStatePersistence;
        return $this;
    }

    /**
     * @return bool
     */
    public function hasPersistence(): bool
    {
        return isset($this->elementStatePersistence);
    }

}
