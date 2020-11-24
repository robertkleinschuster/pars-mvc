<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Pars\Helper\Path\PathHelper;
use Pars\Mvc\Controller\AbstractController;
use Pars\Mvc\Controller\ControllerInterface;
use Pars\Mvc\Controller\ControllerRequest;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Exception\ControllerNotFoundException;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Class ControllerFactory
 * @package Pars\Mvc\Factory
 */
class ControllerFactory
{
    /**
     * @var ModelFactory
     */
    private $modelFactory;

    /**
     * @var PathHelper
     */
    private $pathHelper;

    /**
     * ControllerFactory constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->modelFactory = $container->get(ModelFactory::class);
        $this->pathHelper = $container->get(PathHelper::class);
    }

    /**
     * @param string $code
     * @param ServerRequestInterface $request
     * @param array $config
     * @return ControllerInterface
     * @throws ControllerNotFoundException
     */
    public function __invoke(string $code, ServerRequestInterface $request, array $config): ControllerInterface
    {
        $class = $this->getControllerClass($config, $code);
        /**
         * @var AbstractController $controller
         */
        return new $class(
            new ControllerRequest($request),
            new ControllerResponse(),
            ($this->modelFactory)($code, $config),
            $this->pathHelper
        );
    }

    /**
     * @param array $config
     * @param string $code
     * @return string
     * @throws ControllerNotFoundException
     */
    protected function getControllerClass(array $config, string $code): string
    {
        if (!isset($config['controllers'][$code])) {
            throw new ControllerNotFoundException(
                "No controller class found for code '$code'. Check your mvc configuration."
            );
        }
        return $config['controllers'][$code];
    }
}
