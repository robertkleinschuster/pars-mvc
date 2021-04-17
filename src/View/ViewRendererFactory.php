<?php


namespace Pars\Mvc\View;


use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

class ViewRendererFactory
{
    public function __invoke(ContainerInterface $container)
    {
        $config = $container->get('config');
        $mvcConfig = $config['mvc'];
        return new ViewRenderer($container->get(TemplateRendererInterface::class), $mvcConfig['template_folder']);
    }

}
