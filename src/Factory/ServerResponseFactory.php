<?php

declare(strict_types=1);

namespace Pars\Mvc\Factory;

use Laminas\Diactoros\Response;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Diactoros\UriFactory;
use Pars\Helper\Debug\DebugHelper;
use Pars\Mvc\Controller\ControllerResponse;
use Pars\Mvc\Exception\MvcException;

/**
 * Class ServerResponseFactory
 * @package Pars\Mvc\Factory
 */
class ServerResponseFactory
{
    /**
     * @param ControllerResponse $controllerResponse
     * @return HtmlResponse|JsonResponse|RedirectResponse|Response
     * @throws MvcException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    public function __invoke(ControllerResponse $controllerResponse)
    {
        if (DebugHelper::hasDebug()) {
            $controllerResponse->setBody(DebugHelper::getDebug());
        }
        switch ($controllerResponse->getMode()) {
            case ControllerResponse::MODE_HTML:
                return new HtmlResponse(
                    $controllerResponse->getBody(),
                    $controllerResponse->getStatusCode(),
                    $controllerResponse->getHeaders()
                );
            case ControllerResponse::MODE_JSON:
                $data = [
                    'html' => $controllerResponse->getBody(),
                    'attributes' => $controllerResponse->getAttributes(),
                    'inject' => $controllerResponse->getInjector()->toArray(),
                ];
                if ($controllerResponse->hasEvent()) {
                    $data['event'] = $controllerResponse->getEvent()->toArray(true);
                }
                if (DebugHelper::hasDebug()) {
                    $data['debug'] = DebugHelper::getDebug();
                }
                return new JsonResponse($data, $controllerResponse->getStatusCode(), $controllerResponse->getHeaders());
            case ControllerResponse::MODE_REDIRECT:
                return new RedirectResponse(
                    (new UriFactory())->createUri(
                        $controllerResponse->getAttribute(ControllerResponse::ATTRIBUTE_REDIRECT_URI, true, '')
                    )
                );
            case ControllerResponse::MODE_DOWNLOAD:
                $filename = $controllerResponse->getAttribute(ControllerResponse::ATTRIBUTE_FILENAME);
                return (new Response\TextResponse(
                    $controllerResponse->getBody(),
                    $controllerResponse->getStatusCode(),
                    $controllerResponse->getHeaders()
                ))->withAddedHeader('Content-disposition', 'attachment; filename="' . $filename . '"');
        }
        throw new MvcException("Invalid Mode '{$controllerResponse->getMode()}' set in ControlerResponse.");
    }
}
