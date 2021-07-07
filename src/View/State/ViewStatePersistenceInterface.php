<?php


namespace Pars\Mvc\View\State;


interface ViewStatePersistenceInterface
{
    /**
     * @param string $id
     * @param ViewState $state
     * @return mixed
     */
    public function save(string $id, ViewState $state);

    /**
     * @param string $id
     * @return ViewState
     */
    public function load(string $id): ViewState;
}
