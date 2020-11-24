<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

/**
 * Class ControllerResponseInjector
 * @package Pars\Mvc\Controller
 */
class ControllerResponseInjector
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

    /**
     * @param string $template
     * @param string $selector
     * @param string $mode
     */
    public function addTemplate(string $template, string $selector, string $mode)
    {
        $this->template[] = [
            'template' => $template,
            'selector' => $selector,
            'mode' => $mode
        ];
    }

    /**
     * @param string $script
     */
    public function addScript(string $script)
    {
        $this->script[] = [
            'script' => $script,
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
}
