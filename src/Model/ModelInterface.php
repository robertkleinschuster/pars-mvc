<?php

declare(strict_types=1);

namespace Pars\Mvc\Model;

use Pars\Helper\Parameter\IdListParameter;
use Pars\Helper\Parameter\IdParameter;
use Pars\Helper\Parameter\MoveParameter;
use Pars\Helper\Parameter\OrderParameter;
use Pars\Helper\Parameter\PaginationParameter;
use Pars\Helper\Parameter\SearchParameter;
use Pars\Helper\Parameter\SubmitParameter;

/**
 * Interface ModelInterface
 * @package Pars\Mvc\Model
 */
interface ModelInterface
{

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
     * @param IdParameter $idParameter
     * @param IdListParameter $idListParameter
     * @param array $attribute_List
     */
    public function handleSubmit(SubmitParameter $submitParameter, IdParameter $idParameter, IdListParameter $idListParameter, array $attribute_List);
}
