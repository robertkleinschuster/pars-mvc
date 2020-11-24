<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Mvc\Exception\MvcException;
use Niceshops\Core\Attribute\AttributeAwareInterface;
use Niceshops\Core\Attribute\AttributeAwareTrait;
use Niceshops\Core\Mode\ModeAwareInterface;
use Niceshops\Core\Mode\ModeAwareTrait;
use Niceshops\Core\Option\OptionAwareInterface;
use Niceshops\Core\Option\OptionAwareTrait;

/**
 * Class ControllerResponse
 * @package Pars\Mvc\Controller
 */
class ControllerResponse implements OptionAwareInterface, AttributeAwareInterface, ModeAwareInterface
{
    use OptionAwareTrait;
    use AttributeAwareTrait;
    use ModeAwareTrait;

    public const MODE_HTML = 'html';
    public const MODE_JSON = 'json';
    public const MODE_REDIRECT = 'redirect';

    public const ATTRIBUTE_REDIRECT_URI = 'redirect_url';

    public const OPTION_RENDER_RESPONSE = 'render_response';

    public const STATUS_NOT_FOUND = 404;
    public const STATUS_FOUND = 200;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $headers;

    /**
     * @var int
     */
    private $statusCode;

    /**
     * @var ControllerResponseInjector
     */
    private $injector;

    /**
     * ControllerResponseProperties constructor.
     */
    public function __construct()
    {
        $this->setMode(self::MODE_HTML);
        $this->addOption(self::OPTION_RENDER_RESPONSE);
        $this->setStatusCode(self::STATUS_FOUND);
        $this->setHeaders([]);
        $this->setBody('');
    }

    /**
     * @param string $mode
     * @return bool
     */
    public function isMode(string $mode): bool
    {
        return $this->hasMode() && $this->getMode() === $mode;
    }

    /**
     * @return string
     */
    public function getBody(): string
    {
        return $this->body ?? '';
    }

    /**
     * @param string $body
     */
    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @param int $statusCode
     */
    public function setStatusCode(int $statusCode): void
    {
        $this->statusCode = $statusCode;
    }

    /**
     * @return ControllerResponseInjector
     */
    public function getInjector(): ControllerResponseInjector
    {
        if (null === $this->injector) {
            $this->injector = new ControllerResponseInjector();
        }
        return $this->injector;
    }

    /**
     * @throws MvcException
     */
    protected function validateInject()
    {
        if (!$this->isMode(self::MODE_JSON)) {
            throw new MvcException('Inject only possible in json mode. Mode: ' . $this->getMode());
        }
    }

    /**
     * @param string $script
     * @throws MvcException
     */
    public function injectScript(string $script)
    {
        $this->validateInject();
        $this->getInjector()->addScript($script);
    }

    /**
     * @param string $html
     * @param string $selector
     * @param string $position
     * @throws MvcException
     */
    public function injectHtml(string $html, string $selector, string $position)
    {
        $this->validateInject();
        $this->getInjector()->addHtml($html, $selector, $position);
    }

    /**
     * @param string $template
     * @param string $selector
     * @param string $position
     * @throws MvcException
     */
    public function injectTemplate(string $template, string $selector, string $position)
    {
        $this->validateInject();
        $this->getInjector()->addHtml($template, $selector, $position);
    }

    /**
     * @param string $uri
     * @return bool
     */
    public function setRedirect(string $uri): bool
    {
        $this->setMode(self::MODE_REDIRECT);
        $this->setAttribute(self::ATTRIBUTE_REDIRECT_URI, $uri);
        $this->removeOption(self::OPTION_RENDER_RESPONSE);
        return true;
    }
}
