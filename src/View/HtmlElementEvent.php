<?php


namespace Pars\Mvc\View;


use Pars\Bean\Type\Base\AbstractBaseBean;

class HtmlElementEvent extends AbstractBaseBean
{
    public const TYPE_LINK = 'link';

    public string $type = self::TYPE_LINK;
    public ?string $path = null;
    public ?string $target = '#components';
    public bool $deleteCache = true;
}
