<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Mvc\View\ViewInjector;

/**
 * Class ControllerResponseInjector
 * @package Pars\Mvc\Controller
 */
class ControllerResponseInjector implements \JsonSerializable
{

    public const MODE_PREPEND = 'prepend';
    public const MODE_REPLACE = 'replace';
    public const MODE_APPEND = 'append';

    /**
     * @var array
     */
    private $html;

    /**
     * @var array
     */
    private $template;

    /**
     * @var array
     */
    private $script;

    /**
     * Injector constructor.
     */
    public function __construct()
    {
        $this->html = [];
        $this->template = [];
        $this->script = [];
    }

    /**
     * @param string $html
     * @param string $selector
     * @param string $mode
     */
    public function addHtml(string $html, string $selector, string $mode)
    {
        $this->html[] = [
            'html' => $html,
            'selector' => $selector,
            'mode' => $mode
        ];
    }

   /* public function addTemplate(string $template, string $selector, string $mode)
    {
        $this->template[] = [
            'template' => $template,
            'selector' => $selector,
            'mode' => $mode
        ];
    }*/

    /**
     * @param string $script
     * @param bool $unique
     */
    public function addScript(string $script, bool $unique = true)
    {
        $this->script[] = [
            'src' => $script,
            'unique' => $unique
        ];
    }

    public function toArray()
    {
        return [
            'script' => $this->script,
            'html' => $this->html,
            'template' => $this->template,
        ];
    }

    public function jsonSerialize()
    {
        return $this->toArray();
    }


}
