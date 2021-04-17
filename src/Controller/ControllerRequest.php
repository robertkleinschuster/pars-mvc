<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Mezzio\Router\RouteResult;
use Pars\Bean\Type\Base\AbstractBaseBean;
use Pars\Helper\Parameter\CollapseParameter;
use Pars\Mvc\View\Event\ViewEvent;
use Pars\Pattern\Attribute\AttributeAwareInterface;
use Pars\Pattern\Attribute\AttributeAwareTrait;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;
use Pars\Helper\Parameter\ContextParameter;
use Pars\Helper\Parameter\DataParameter;
use Pars\Helper\Parameter\EditLocaleParameter;
use Pars\Helper\Parameter\FilterParameter;
use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\MoveParameter;
use Pars\Helper\Parameter\NavParameter;
use Pars\Helper\Parameter\OrderParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Parameter\ParameterInterface;
use Pars\Helper\Parameter\RedirectParameter;
use Pars\Helper\Parameter\SearchParameter;
use Pars\Helper\Parameter\SubmitParameter;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerRequest
 * @package Pars\Mvc\Controller
 */
class ControllerRequest implements OptionAwareInterface, AttributeAwareInterface
{
    use OptionAwareTrait;
    use AttributeAwareTrait;

    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $serverRequest;

    /**
     * @var RouteResult
     */
    private RouteResult $routeResult;

    /**
     * @var string|null
     */
    private ?string $action = null;
    private ?string $controller = null;

    protected ?ViewEvent $event = null;

    /**
     * ControllerRequestProperties constructor.
     * @param ServerRequestInterface $serverRequest
     */
    public function __construct(ServerRequestInterface $serverRequest)
    {
        $this->serverRequest = $serverRequest;
        $this->routeResult = $serverRequest->getAttribute(RouteResult::class);
        // POST Params
        foreach ($serverRequest->getParsedBody() as $key => $value) {
            $this->setAttribute($key, $value);
        }

        // GET Params
        foreach ($serverRequest->getQueryParams() as $key => $value) {
            $this->setAttribute($key, $value);
        }

        foreach ($serverRequest->getUploadedFiles() as $key => $value) {
            $this->setAttribute($key, $value);
        }
        $event = json_decode($serverRequest->getHeaderLine('X-EVENT'), true);
        if ($event) {
            $this->event = new ViewEvent($event);
        }
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
     * @return ServerRequestInterface
     */
    public function getServerRequest(): ServerRequestInterface
    {
        return $this->serverRequest;
    }

    /**
     * @return RouteResult
     */
    public function getRouteResult(): RouteResult
    {
        return $this->routeResult;
    }

    /**
     * @return bool
     */
    public function hasId(): bool
    {
        return $this->hasAttribute(IdParameter::name());
    }

    /**
     * @return IdParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getId(): IdParameter
    {
        $idParameter = new IdParameter();
        $idParameter->fromData($this->getAttribute($idParameter->name()));
        return $idParameter;
    }

    /**
     * @return bool
     */
    public function hasIdList(): bool
    {
        return $this->hasAttribute(IdListParameter::name(false));
    }

    /**
     * @return IdParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getIdList(): IdListParameter
    {
        $idListParameter = new IdListParameter();
        $idListParameter->fromData($this->getAttribute($idListParameter::name(false)));
        return $idListParameter;
    }

    /**
     * @return bool
     */
    public function hasRedirect(): bool
    {
        return $this->hasAttribute(RedirectParameter::name());
    }

    /**
     * @return RedirectParameter
     */
    public function getRedirect(): RedirectParameter
    {
        $redirectParameter = new RedirectParameter();
        $redirectParameter->fromData($this->getAttribute($redirectParameter->name()));
        return $redirectParameter;
    }


    /**
     * @return bool
     */
    public function hasContext(): bool
    {
        return $this->hasAttribute(ContextParameter::name());
    }

    /**
     * @return ContextParameter
     */
    public function getContext(): ContextParameter
    {
        $parameter = new ContextParameter();
        $parameter->fromData($this->getAttribute($parameter->name()));
        return $parameter;
    }

    /**
     * @return bool
     */
    public function hasSubmit(): bool
    {
        return $this->hasAttribute(SubmitParameter::name());
    }

    /**
     * @return SubmitParameter
     */
    public function getSubmit(): SubmitParameter
    {
        $submitParameter = new SubmitParameter();
        $submitParameter->fromData($this->getAttribute($submitParameter->name()));
        return $submitParameter;
    }

    /**
     * @return bool
     */
    public function hasNav(): bool
    {
        return $this->hasAttribute(NavParameter::name());
    }

    /**
     * @return NavParameter
     */
    public function getNav(): NavParameter
    {
        $navParameter = new NavParameter();
        $navParameter->fromData($this->getAttribute($navParameter->name()));
        return $navParameter;
    }

    /**
     * @return bool
     */
    public function hasCollapse(): bool
    {
        return $this->hasAttribute(CollapseParameter::name());
    }

    /**
     * @return CollapseParameter
     */
    public function getCollapse(): CollapseParameter
    {
        $collapseParameter = new CollapseParameter();
        $collapseParameter->fromData($this->getAttribute($collapseParameter->name()));
        return $collapseParameter;
    }

    /**
     * @return bool
     */
    public function hasPagingation(): bool
    {
        return $this->hasAttribute(PaginationParameter::name());
    }

    /**
     * @return PaginationParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getPagination(): PaginationParameter
    {
        $paginationParameter = new PaginationParameter();
        $paginationParameter->fromData($this->getAttribute($paginationParameter->name()));
        return $paginationParameter;
    }

    /**
     * @return bool
     */
    public function hasSearch(): bool
    {
        return $this->hasAttribute(SearchParameter::name());
    }

    /**
     * @return bool
     */
    public function hasEditLocale(): bool
    {
        return $this->hasAttribute(EditLocaleParameter::name());
    }

    /**
     * @return EditLocaleParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getEditLocale(): EditLocaleParameter
    {
        $editLocaleParamter = new EditLocaleParameter($this->getAttribute(EditLocaleParameter::name()));
        return $editLocaleParamter;
    }

    /**
     * @return SearchParameter
     */
    public function getSearch(): SearchParameter
    {
        $searchParamter = new SearchParameter();
        $searchParamter->fromData($this->getAttribute($searchParamter->name()));
        return $searchParamter;
    }

    /**
     * @return bool
     */
    public function hasOrder(): bool
    {
        return $this->hasAttribute(OrderParameter::name());
    }

    /**
     * @return OrderParameter
     */
    public function getOrder(): OrderParameter
    {
        $orderParameter = new OrderParameter();
        $orderParameter->fromData($this->getAttribute($orderParameter->name()));
        return $orderParameter;
    }

    /**
     * @return bool
     */
    public function hasMove(): bool
    {
        return $this->hasAttribute(MoveParameter::name());
    }

    /**
     * @return MoveParameter
     */
    public function getMove(): MoveParameter
    {
        $moveParameter = new MoveParameter();
        $moveParameter->fromData($this->getAttribute($moveParameter->name()));
        return $moveParameter;
    }

    /**
     * @return bool
     */
    public function hasFilter(): bool
    {
        return $this->hasAttribute(FilterParameter::name());
    }

    /**
     * @return FilterParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getFilter(): FilterParameter
    {
        $filterParameter = new FilterParameter();
        $filterParameter->fromData($this->getAttribute($filterParameter->name()));
        return $filterParameter;
    }

    /**
     * @return bool
     */
    public function hasData(): bool
    {
        return $this->hasAttribute(DataParameter::name());
    }

    /**
     * @return DataParameter
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function getData(): DataParameter
    {
        $dataParameter = new DataParameter();
        $dataParameter->fromData($this->getAttribute($dataParameter->name()));
        return $dataParameter;
    }

    /**
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->getServerRequest()->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * @return string|null
     */
    public function getAction(): ?string
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     * @return ControllerRequest
     */
    public function setAction(?string $action): ControllerRequest
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getController(): ?string
    {
        return $this->controller;
    }

    /**
     * @param string|null $controller
     * @return ControllerRequest
     */
    public function setController(?string $controller): ControllerRequest
    {
        $this->controller = $controller;
        return $this;
    }

    /**
     * @param ParameterInterface $parameter
     * @return bool
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function acceptParameter(ParameterInterface $parameter): bool
    {
        return (!$parameter->hasAction() || $parameter->getAction() == $this->getAction())
           && (!$parameter->hasController() || $parameter->getController() == $this->getController());
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return strtolower($this->getServerRequest()->getMethod()) == 'post';
    }

    /**
     * @return array
     */
    public function getPostData(): array
    {
        return (array) $this->getServerRequest()->getParsedBody();
    }

    /**
     * @return bool
     */
    public function isGet(): bool
    {
        return strtolower($this->getServerRequest()->getMethod()) == 'get';
    }

    /**
     * @return array
     */
    public function getGetData(): array
    {
        return (array) $this->getServerRequest()->getQueryParams();
    }

    /**
     * @param string $name
     * @param $default
     * @return mixed
     */
    public function getMiddlewareAttribute(string $name, $default = null)
    {
        return $this->getServerRequest()->getAttribute($name, $default);
    }
}
