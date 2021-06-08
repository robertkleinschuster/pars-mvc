<?php

declare(strict_types=1);

namespace Pars\Mvc\View;

use Mezzio\Template\TemplateRendererInterface;
use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Helper\Debug\DebugHelper;
/**
 * Class ViewRenderer
 * @package Pars\Mvc\View
 */
class ViewRenderer
{

    public const HTML_START = '<!DOCTYPE html>';

    /**
     * @var TemplateRendererInterface
     */
    private TemplateRendererInterface $templateRenderer;

    /**
     * @var string
     */
    private string $templateFolder;

    /**
     * @var bool
     */
    protected bool $flush = true;

    /**
     * ViewRenderer constructor.
     * @param TemplateRendererInterface $templateRenderer
     * @param string $templateFolder
     */
    public function __construct(TemplateRendererInterface $templateRenderer, string $templateFolder)
    {
        $this->templateFolder = $templateFolder;
        $this->templateRenderer = $templateRenderer;
    }

    /**
     * @return TemplateRendererInterface
     */
    public function getTemplateRenderer(): TemplateRendererInterface
    {
        return $this->templateRenderer;
    }

    /**
     * @return string
     */
    public function getTemplateFolder(): string
    {
        return $this->templateFolder;
    }

    /**
     * @param ViewInterface $view
     * @param string|null $id
     * @throws ViewException
     */
    public function display(ViewInterface $view, ?string $id = null)
    {
        $view->setRenderer($this);
        if (DebugHelper::hasDebug()) {
            echo DebugHelper::getDebug();
        }
        $this->getTemplateRenderer()->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'templateFolder',
            $this->getTemplateFolder()
        );
        $this->getTemplateRenderer()->addDefaultParam(
            TemplateRendererInterface::TEMPLATE_ALL,
            'view',
            $view
        );
        if ($view instanceof BeanConverterAwareInterface && !$view->hasBeanConverter()) {
            $view->setBeanConverter(new ViewBeanConverter());
        }
        if ($view->hasTemplate()) {
            echo $this->getTemplateRenderer()->render($this->getTemplateFolder() . '::' . $view->getTemplate());
        } elseif ($view->hasLayout()) {
            $bean = $view->hasBeanConverter() ? $view->getBeanConverter()->convert($view) : $view;
            if ($id === null) {
                echo self::HTML_START;
                $renderable = $view->getLayout();
            } else {
                $id = str_replace('#', '', $id);
                $renderable = $view->getLayout()->getElementById($id);
            }
            if ($renderable instanceof RenderableInterface) {
                $renderable->setView($view);
                $renderable->display($bean, true);
            }
        } else {
            $class = get_class($view);
            throw new ViewException("Could not render view {$class}, no template or layout set.");
        }
    }

    /**
     * @param ViewInterface $view
     * @param string|null $id
     * @return string
     * @throws ViewException
     */
    public function render(ViewInterface $view, ?string $id = null): string
    {
        $this->flush = false;
        ob_start();
        $this->display($view, $id);
        return ob_get_clean();
    }

    /**
     * @return bool
     */
    public function isFlush(): bool
    {
        return $this->flush;
    }


}
