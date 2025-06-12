<?php

namespace PainlessPHP\Http\Message;

use InvalidArgumentException;
use PainlessPHP\Http\Message\Concern\DeepCloneTrait;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\StreamInterface;

/**
 * Message superclass used by both request and response
 *
 */
class Message implements MessageInterface
{
    use DeepCloneTrait;

    protected Body $body;
    protected HeaderCollection $headers;

    /**
     * @param Body|StreamInterface|resource|string|null $body Source of the body.
     *
     * Note that a given StreamInterface that is not an instance of Body will
     * be converted into a body to make sure that the underlying resource can
     * be cloned correctly without modifying state of other objects
     *
     * @param HeaderCollection|array|null $headers Message headers
     *
     */
    public function __construct(
        mixed $body = null,
        HeaderCollection|array $headers = [],
        private string $version = '1.1'
    )
    {
        $this->setBody($body);
        $this->setHeaders($headers);
    }

    protected function setBody(mixed $body)
    {
        if(! ($body instanceof Body)) {
            $body = new Body($body);
        }

        $this->body = $body;
    }

    protected function setHeaders(HeaderCollection|array $headers)
    {
        if(is_array($headers)) {
            $headers = HeaderCollection::createFromArray($headers);
        }
        $this->headers = $headers;
    }

    public function getProtocolVersion() : string
    {
        return $this->version;
    }

    public function withProtocolVersion(string $version) : static
    {
        $instance = $this->clone();
        $instance->version = $version;

        return $instance;
    }

    public function getHeaders() : array
    {
        return $this->headers->toArray();
    }

    public function hasHeader(string $name) : bool
    {
        return $this->headers->hasHeader($name);
    }

    public function getHeader(string $name) : array
    {
        $header = $this->headers->getHeader($name);

        if($header === null) {
            return [];
        }

        return $header->getValues();
    }

    public function getHeaderLine(string $name) : string
    {
        return $this->headers->getHeaderLine($name);
    }

    public function withHeader(string $name, mixed $value) : static
    {
        $instance = $this->clone();
        $instance->headers = $this->headers->withHeader(new Header($name, $value));
        return $instance;
    }

    public function withAddedHeader(string $name, mixed $value) : static
    {
        $instance = $this->clone();
        $instance->headers = $this->headers->withAddedHeader(new Header($name, $value));
        return $instance;
    }

    public function withoutHeader(string $name) : static
    {
        $instance = $this->clone();
        $instance->headers = $this->headers->withoutHeader($name);
        return $instance;
    }

    public function getBody() : Body
    {
        return $this->body;
    }

    public function withBody(StreamInterface $body) : static
    {
        $instance = $this->clone();
        $instance->setBody($body);

        return $instance;
    }

    /**
     * Create a message instance with json body content and content-type header
     *
     */
    public function withJson(mixed $data) : static
    {
        $encoded = json_encode($data);

        if($encoded === false) {
            $error = json_last_error();
            $msg = "The given data could not be encoded into valid json: $error";
            throw new InvalidArgumentException($msg);
        }

        return $this->withHeader('content-type', 'application/json')
            ->withBody(new Body($encoded));
    }

    /**
     * Create a message instance with basic authorization header
     *
     */
    public function withBasicAuth(string $user, string $password) : static
    {
        $instance = $this->clone();
        $instance->headers = $this->headers->withHeader(
            new BasicAuthorizationHeader($user, $password)
        );
        return $instance;
    }
}
