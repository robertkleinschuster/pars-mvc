<?php


namespace Pars\Mvc\View;


use Pars\Bean\Type\Base\AbstractBaseBean;

class HtmlElementEvent extends AbstractBaseBean
{
    public const TYPE_LINK = 'link';
    public const TYPE_MODAL = 'modal';
    public const TYPE_SUBMIT = 'submit';

    public string $type = self::TYPE_LINK;
    public ?string $path = null;
    public ?string $target = '#components';
    public bool $deleteCache = true;
    public bool $history = true;
    public ?string $form = null;

    /**
     * @param string|null $path
     * @return static
     */
    public static function createLink(string $path = null): self
    {
        return new static([
            'type' => self::TYPE_LINK,
            'path' => $path,
        ]);
    }

    /**
     * @param string|null $path
     * @return static
     */
    public static function createModal(string $path = null): self
    {
        return new static([
            'type' => self::TYPE_MODAL,
            'path' => $path,
        ]);
    }

    /**
     * @param string|null $path
     * @return static
     */
    public static function createSubmit(string $path, string $form): self
    {
        return new static([
            'type' => self::TYPE_SUBMIT,
            'path' => $path,
            'form' => $form,
        ]);
    }
}
