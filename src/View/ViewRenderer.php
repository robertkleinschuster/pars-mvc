<?php

declare(strict_types=1);

namespace Pars\Mvc\View;

use Mezzio\Template\TemplateRendererInterface;
use Niceshops\Bean\Converter\BeanConverterAwareInterface;
use Niceshops\Bean\Type\Base\BeanInterface;

/**
 * Class ViewRenderer
 * @package Pars\Mvc\View
 */
class ViewRenderer
{
    /**
     * @var TemplateRendererInterface
     */
    private TemplateRendererInterface $templateRenderer;

    /**
     * @var string
     */
    private string $templateFolder;

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
     * @return string
     */
    public function render(ViewInterface $view): string
    {
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
            return $this->getTemplateRenderer()->render($this->getTemplateFolder() . '::' . $view->getTemplate());
        } elseif ($view->hasLayout()) {
            $layout = $view->getLayout();
            if (
                $view instanceof BeanConverterAwareInterface &&
                $layout instanceof BeanConverterAwareInterface
            ) {
                if ($view->hasBeanConverter()) {
                    if (!$layout->hasBeanConverter()) {
                        $layout->setBeanConverter($view->getBeanConverter());
                    }
                }
            }
            $result = '<!DOCTYPE html>';
            if ($view instanceof BeanInterface) {
                if ($view->hasBeanConverter()) {
                    $bean = $view->getBeanConverter()->convert($view);
                } else {
                    $bean = $view;
                }
                $result .= $view->getLayout()->render($bean);
            } else {
                $result .= $view->getLayout()->render();
            }
            return $result;
        } else {
            $class = get_class($view);
            throw new ViewException("Could not render view {$class}, no template or layout set.");
        }
    }
}
