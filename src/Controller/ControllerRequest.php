<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Mezzio\Router\RouteResult;
use Niceshops\Core\Attribute\AttributeAwareInterface;
use Niceshops\Core\Attribute\AttributeAwareTrait;
use Niceshops\Core\Option\OptionAwareInterface;
use Niceshops\Core\Option\OptionAwareTrait;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\MoveParameter;
use Pars\Helper\Parameter\NavParameter;
use Pars\Helper\Parameter\OrderParameter;
use Pars\Helper\Parameter\PaginationParameter;
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

    public const ATTRIBUTE_SUBMIT = 'submit';
    public const ATTRIBUTE_ID = 'id';
    public const ATTRIBUTE_REDIRECT = 'redirect';
    public const ATTRIBUTE_NAV = 'nav';
    public const ATTRIBUTE_PAGINATION = 'pagination';
    public const ATTRIBUTE_SEARCH = 'search';
    public const ATTRIBUTE_ORDER = 'order';
    public const ATTRIBUTE_MOVE = 'move';


    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $serverRequest;

    /**
     * @var RouteResult
     */
    private RouteResult $routeResult;

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
            $this->setAttribute($key, urldecode($value));
        }

        foreach ($serverRequest->getUploadedFiles() as $key => $value) {
            $this->setAttribute($key, $value);
        }
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
        return $this->hasAttribute(self::ATTRIBUTE_ID);
    }

    /**
     * @return IdParameter
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
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
    public function hasRedirect(): bool
    {
        return $this->hasAttribute(self::ATTRIBUTE_REDIRECT);
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
    public function hasSubmit(): bool
    {
        return $this->hasAttribute(self::ATTRIBUTE_SUBMIT);
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
        return $this->hasAttribute(self::ATTRIBUTE_NAV);
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
    public function hasPagingation(): bool
    {
        return $this->hasAttribute(self::ATTRIBUTE_PAGINATION);
    }

    /**
     * @return PaginationParameter
     * @throws \Niceshops\Core\Exception\AttributeExistsException
     * @throws \Niceshops\Core\Exception\AttributeLockException
     * @throws \Niceshops\Core\Exception\AttributeNotFoundException
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
        return $this->hasAttribute(self::ATTRIBUTE_SEARCH);
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
        return $this->hasAttribute(self::ATTRIBUTE_ORDER);
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
        return $this->hasAttribute(self::ATTRIBUTE_MOVE);
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
     *
     * @return bool
     */
    public function isAjax(): bool
    {
        return strtolower($this->getServerRequest()->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }
}
