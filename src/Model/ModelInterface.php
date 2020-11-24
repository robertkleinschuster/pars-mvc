<?php

declare(strict_types=1);

namespace Pars\Mvc\Model;

use Pars\Mvc\Bean\TemplateDataBean;
use Pars\Mvc\Helper\ValidationHelper;
use Pars\Mvc\Parameter\IdParameter;
use Pars\Mvc\Parameter\MoveParameter;
use Pars\Mvc\Parameter\OrderParameter;
use Pars\Mvc\Parameter\PaginationParameter;
use Pars\Mvc\Parameter\SearchParameter;
use Pars\Mvc\Parameter\SubmitParameter;

/**
 * Interface ModelInterface
 * @package Pars\Mvc\Model
 */
interface ModelInterface
{
    /**
     * @return TemplateDataBean
     */
    public function getTemplateData(): TemplateDataBean;

    /**
     * initialize data source in model
     */
    public function initialize();

    /**
     * @param PaginationParameter $paginationParameter
     * @return mixed
     */
    public function handlePagination(PaginationParameter $paginationParameter);

    /**
     * @param SearchParameter $searchParameter
     * @return mixed
     */
    public function handleSearch(SearchParameter $searchParameter);

    /**
     * @param OrderParameter $orderParameter
     * @return mixed
     */
    public function handleOrder(OrderParameter $orderParameter);

    /**
     * @param IdParameter $idParameter
     * @return mixed
     */
    public function handleId(IdParameter $idParameter);

    /**
     * @param MoveParameter $moveParameter
     * @return mixed
     */
    public function handleMove(MoveParameter $moveParameter);

    /**
     * Handle form submit
     *
     * @param SubmitParameter $submitParameter
     * @param IdParameter $idParamter
     * @param array $attribute_List
     */
    public function handleSubmit(SubmitParameter $submitParameter, IdParameter $idParamter, array $attribute_List);
}
