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
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Class ServerResponseFactory
 * @package Pars\Mvc\Factory
 */
class ServerResponseFactory implements ResponseFactoryInterface
{

    protected ?ControllerResponse $controllerResponse = null;


    /**
    * @return ControllerResponse
    */
    public function getControllerResponse(): ControllerResponse
    {
        return $this->controllerResponse;
    }

    /**
    * @param ControllerResponse $controllerResponse
    *
    * @return $this
    */
    public function setControllerResponse(ControllerResponse $controllerResponse): self
    {
        $this->controllerResponse = $controllerResponse;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasControllerResponse(): bool
    {
        return isset($this->controllerResponse);
    }

    /**
     * @param int $code
     * @param string $reasonPhrase
     * @return ResponseInterface
     * @throws MvcException
     */
    public function createResponse(int $code = 200, string $reasonPhrase = ''): ResponseInterface
    {
        if ($this->hasControllerResponse()) {
            return $this->createServerResponse($this->controllerResponse);
        }
        return (new Response())->withStatus($code, $reasonPhrase);
    }

    /**
     * @param ControllerResponse $controllerResponse
     * @return HtmlResponse|JsonResponse|RedirectResponse|Response\TextResponse|\Psr\Http\Message\MessageInterface
     * @throws MvcException
     * @throws \Pars\Pattern\Exception\AttributeNotFoundException
     */
    protected function createServerResponse(ControllerResponse $controllerResponse)
    {
        switch ($controllerResponse->getMode()) {
            case ControllerResponse::MODE_HTML:
                if (DebugHelper::hasDebug()) {
                    $controllerResponse->setBody(DebugHelper::getDebug() . $controllerResponse->getBody());
                }
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
                    $data['debug'] = [
                      'data' => DebugHelper::getDebugList(),
                      'trace' => DebugHelper::getDebug(),
                    ];
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
