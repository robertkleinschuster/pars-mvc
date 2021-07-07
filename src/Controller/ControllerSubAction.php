<?php


namespace Pars\Mvc\Controller;


class ControllerSubAction
{
    protected ControllerRequest $controllerRequest;
    protected string $id;
    protected string $targetId = 'components';
    protected string $sourceId = 'components';
    protected string $group;

    /**
     * ControllerSubAction constructor.
     * @param ControllerRequest $controllerRequest
     * @param string $group
     */
    public function __construct(ControllerRequest $controllerRequest, string $id, string $group = 'default')
    {
        $this->controllerRequest = $controllerRequest;
        $this->group = $group;
        $this->id = $id;
    }


    /**
     * @return string
     */
    public function getTargetId(): string
    {
        return $this->targetId;
    }

    /**
     * @param string $targetId
     * @return ControllerSubAction
     */
    public function setTargetId(string $targetId): ControllerSubAction
    {
        $this->targetId = $targetId;
        return $this;
    }

    /**
     * @return string
     */
    public function getSourceId(): string
    {
        return $this->sourceId;
    }

    /**
     * @param string $sourceId
     * @return ControllerSubAction
     */
    public function setSourceId(string $sourceId): ControllerSubAction
    {
        $this->sourceId = $sourceId;
        return $this;
    }



    /**
     * @return string
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return ControllerSubAction
     */
    public function setId(string $id): ControllerSubAction
    {
        $this->id = $id;
        return $this;
    }



    /**
     * @return string
     */
    public function getGroup(): string
    {
        return $this->group;
    }

    /**
     * @param string $group
     * @return ControllerSubAction
     */
    public function setGroup(string $group): ControllerSubAction
    {
        $this->group = $group;
        return $this;
    }




    /**
     * @return ControllerRequest
     */
    public function getControllerRequest(): ControllerRequest
    {
        return $this->controllerRequest;
    }

    /**
     * @param ControllerRequest $controllerRequest
     * @return ControllerSubAction
     */
    public function setControllerRequest(ControllerRequest $controllerRequest): ControllerSubAction
    {
        $this->controllerRequest = $controllerRequest;
        return $this;
    }


}
