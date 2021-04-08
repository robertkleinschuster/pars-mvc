<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

interface FieldFormatInterface
{
    public function __invoke(FieldInterface $field, string $value, ?BeanInterface $bean = null): string;
}
