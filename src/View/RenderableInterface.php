<?php

namespace Pars\Mvc\View;

use Pars\Bean\Type\Base\BeanInterface;

interface RenderableInterface
{
    public function render(BeanInterface $bean = null, bool $placeholders = false): string;
}
