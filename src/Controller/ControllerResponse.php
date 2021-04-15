<?php

declare(strict_types=1);

namespace Pars\Mvc\Controller;

use Pars\Mvc\View\HtmlElementEvent;
use Pars\Pattern\Attribute\AttributeAwareInterface;
use Pars\Pattern\Attribute\AttributeAwareTrait;
use Pars\Pattern\Mode\ModeAwareInterface;
use Pars\Pattern\Mode\ModeAwareTrait;
use Pars\Pattern\Option\OptionAwareInterface;
use Pars\Pattern\Option\OptionAwareTrait;
use Pars\Mvc\Exception\MvcException;

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
    public const MODE_DOWNLOAD = 'download';

    public const ATTRIBUTE_REDIRECT_URI = 'redirect_url';
    public const ATTRIBUTE_FILENAME = 'filename';

    public const OPTION_RENDER_RESPONSE = 'render_response';

    public const STATUS_NOT_FOUND = 404;
    public const STATUS_PERMISSION_DENIED = 401;
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
     * @var HtmlElementEvent
     */
    private ?HtmlElementEvent $event = null;

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
    * @return HtmlElementEvent
    */
    public function getEvent(): HtmlElementEvent
    {
        return $this->event;
    }

    /**
    * @param HtmlElementEvent $event
    *
    * @return $this
    */
    public function setEvent(HtmlElementEvent $event): self
    {
        $this->event = $event;
        return $this;
    }

    /**
    * @return bool
    */
    public function hasEvent(): bool
    {
        return isset($this->event);
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
        if (!$this->isMode(self::MODE_JSON)) {
            $this->setMode(self::MODE_REDIRECT);
        }
        $this->setAttribute(self::ATTRIBUTE_REDIRECT_URI, $uri);
        $this->removeOption(self::OPTION_RENDER_RESPONSE);
        return true;
    }

    /**
     * @param string $filename
     * @param string $body
     * @param string $contentType
     * @throws \Pars\Pattern\Exception\AttributeExistsException
     * @throws \Pars\Pattern\Exception\AttributeLockException
     */
    public function setDownload(string $filename, string $body, string $contentType)
    {
        $this->setMode(self::MODE_DOWNLOAD);
        $this->unsetOption(self::OPTION_RENDER_RESPONSE);
        $this->setAttribute(self::ATTRIBUTE_FILENAME, $filename);
        $this->setBody($body);
        $this->setHeaders(['Content-Type' => $contentType]);
    }
}
