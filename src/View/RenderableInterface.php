<?php


namespace Pars\Mvc\View;


use Niceshops\Bean\Type\Base\BeanInterface;

interface RenderableInterface
{
    public function render(BeanInterface $bean = null): string;
}
