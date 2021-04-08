<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

interface FieldAcceptInterface
{
    public function __invoke(FieldInterface $field, ?BeanInterface $bean = null): bool;
}
