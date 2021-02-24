<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Pars\Mvc\Exception\ControllerNotFoundException;

/**
 * Class ModelFactory
 * @package Pars\Mvc\Factory
 */
class ModelFactory
{

    /**
     * @param string $code
     * @param array $config
     * @return mixed
     * @throws ControllerNotFoundException
     */
    public function __invoke(string $code, array $config, array $appConfig)
    {
        $model = $this->getModelClass($config, $code);
        return new $model($appConfig);
    }

    /**
     * @param array $config
     * @param string $code
     * @return string
     * @throws ControllerNotFoundException
     */
    protected function getModelClass(array $config, string $code): string
    {
        if (!isset($config['models'][$code])) {
            throw new ControllerNotFoundException(
                "No model class found for code '$code'. Check your mvc configuration."
            );
        }
        return $config['models'][$code];
    }
}
