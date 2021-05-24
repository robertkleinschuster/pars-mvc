<?php

declare(strict_types=1);

namespace Pars\Mvc\View;

use Mezzio\Template\TemplateRendererInterface;
use Pars\Bean\Converter\BeanConverterAwareInterface;
use Pars\Pattern\Exception\AttributeExistsException;
use Pars\Pattern\Exception\AttributeLockException;
use Pars\Pattern\Exception\AttributeNotFoundException;

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
     * @return string
     * @throws ViewException
     * @throws AttributeExistsException
     * @throws AttributeLockException
     * @throws AttributeNotFoundException
     */
    public function render(ViewInterface $view, ?string $id = null): string
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
            $result = self::HTML_START;
            $bean = $view->hasBeanConverter() ? $view->getBeanConverter()->convert($view) : $view;
            if ($id !== null) {
                $id = str_replace('#', '', $id);
                $result = '';
                $renderable = $view->getLayout()->getElementById($id);
            } else {
                $renderable = $view->getLayout();
            }
            if ($renderable instanceof RenderableInterface) {
                $renderable->setRenderer($this);
                $renderable->setView($view);
                $result .= $renderable->render($bean, true);
            }
            return $result;
        } else {
            $class = get_class($view);
            throw new ViewException("Could not render view {$class}, no template or layout set.");
        }
    }
}
