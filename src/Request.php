<?php

namespace PainlessPHP\Http\Message;

use InvalidArgumentException;
use PainlessPHP\Http\Message\Exception\StringParsingException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\UriInterface;

/**
 * Wrapper class for http request information
 *
 */
class Request extends Message implements RequestInterface
{
    protected Method $method;
    protected Uri $uri;
    protected ?string $target = null;

    public function __construct(
        Method|string $method,
        Uri|UriInterface|string|null $uri = null,
        mixed $body = null,
        HeaderCollection|array $headers = []
    ) {
        parent::__construct($body, $headers);
        $this->setMethod($method);
        $this->setUri($uri);

        // Set host header based on target uri
        if($this->getHeaderLine('Host') === '' && $this->uri->getHost() !== '') {
            $this->headers = $this->headers->withHeader(new Header('Host', $this->uri->getHost()));
        }
    }

    /**
     * Instances must be immutable
     *
     */
    protected function setMethod(Method|string $method)
    {
        if(is_string($method)) {
            $method = Method::tryFrom(strtoupper($method));
        }

        if(is_null($method)) {
            $msg = "'$method' is not a valid http method";
            throw new InvalidArgumentException($msg);
        }

        $this->method = $method;
    }

    /**
     * Instances must be immutable
     *
     */
    protected function setUri(Uri|UriInterface|string|null $uri)
    {
        if($uri instanceof UriInterface) {
            $uri = new Uri((string)$uri);
        }

        if(is_string($uri) || $uri === null) {
            $uri = new Uri($uri ?? '');
        }

        $this->uri = $uri;
    }

    /**
     * Set request target, immutable from outside
     *
     */
    protected function setRequestTarget(string $target)
    {
        if(preg_match('#\s#', $target)) {
            $msg = "request target may not contain whitespace";
            throw new StringParsingException($msg);
        }

        $this->target = $target;
    }

    public function getRequestTarget() : string
    {
        if($this->target !== null) {
            return $this->target;
        }

        if((string)$this->uri === '') {
            return '/';
        }

        return $this->uri->getOriginForm();
    }

    public function withRequestTarget(string $requestTarget) : static
    {
        $instance = $this->clone();
        $instance->setRequestTarget($requestTarget);
        return $instance;
    }

    public function getMethod() : string
    {
        return $this->method->value;
    }

    public function withMethod(Method|string $method) : static
    {
        $instance = $this->clone();
        $instance->setMethod($method);
        return $instance;
    }

    public function getUri() : Uri
    {
        return $this->uri;
    }

    public function withUri(UriInterface $uri, bool $preserveHost = false) : static
    {
        $instance = $this->clone();
        $instance->setUri((string)$uri);

        if($preserveHost) {
            if($this->getHeaderLine('Host') === '' && $uri->getHost() !== '') {
                return $instance->withHeader('Host', $this->uri->getHost());
            }
        }

        /* Update host by default if uri contains host */
        if($uri->getHost() !== '') {
            $instance = $instance->withHeader('Host', $uri->getHost());
        }

        return $instance;
    }

    /**
     * Get a new request instance with the given parameters
     *
     * If the request is a GET request parameters will be set to the uri
     * query
     *
     * If the request is a POST request parameters will be set to body and
     * content-type header will be added for x-www-form-urlencoded
     *
     */
    public function withParameters(array $parameters) : static
    {
        $instance = $this->clone();
        $query = new Query($parameters);

        if(in_array($this->getMethod(), ['GET', 'HEAD'])) {
            $uri = $instance->getUri()->withQuery((string)$query);
            return $instance->withUri($uri);
        }

        if(in_array($this->getMethod(), ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return $instance
                ->withHeader('content-type', 'application/x-www-form-urlencoded')
                ->withBody(new Body((string)$query));
        }

        return $instance;
    }
}
