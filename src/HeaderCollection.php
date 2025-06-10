<?php

namespace PainlessPHP\Http\Message;

use InvalidArgumentException;

class HeaderCollection
{
    /**
     * @param array<Header> $headers
     */
    public function __construct(private array $headers)
    {
    }

    /**
     * Create header collection from an array
     *
     * @param array<string,string|array<string>|Header> $array
     *
     */
    public static function createFromArray(array $array) : self
    {
        $headers = [];

        foreach($array as $name => $values) {
            if($values instanceof Header) {
                $headers[strtolower($values->getName())] = $values;
                continue;
            }
            if(is_string($values) || is_array($values)) {
                $headers[strtolower($name)] = new Header($name, $values);
                continue;
            }
            $type = is_object($values) ? get_class($values) : gettype($values);
            $msg = "Invalid header value type '$type', header value should be either a string or an array";
            throw new InvalidArgumentException($msg);
        }

        return new self($headers);
    }

    /**
     * Get header
     *
     * @param string $name case-insensitive header name
     *
     */
    public function getHeader(string $name) : ? Header
    {
        return $this->headers[strtolower($name)] ?? null;
    }

    /**
     * Get comma-separated string of the values for a single header
     * based on psr-7 getHeaderLine() specification
     *
     * @param string $name case-insensitive header name
     *
     */
    public function getHeaderLine(string $name) : string
    {
        return $this->getHeader($name)?->getValue() ?? '';
    }

    /**
     * Check if a header with the given name exists in this collection
     *
     * @param string $name case-insensitive header name
     *
     */
    public function hasHeader(string $name) : bool
    {
        return isset($this->headers[strtolower($name)]);
    }

    /**
     * Create a new header collection that contains the given header in
     * addition to the old headers. Will override existing header.
     *
     */
    public function withHeader(Header $header) : self
    {
        return new self([...$this->headers, strtolower($header->getName()) => $header]);
    }

    /**
     * Create a new header collection that contains the given header in
     * addition to the old headers. Values of the given header will be
     * added to the existing header.
     *
     */
    public function withAddedHeader(Header $header)
    {
        $oldHeader = $this->getHeader($header->getName());

        if($oldHeader === null) {
            return $this->withHeader($header);
        }

        // Merge the values from existing header
        $newHeader = new Header($header->getName(), [
            ...$oldHeader->getValues(),
            ...$header->getValues()
        ]);

        return $this->withHeader($newHeader);
    }

    /**
     * Create a new header collection based on the old headers that does
     * not contain the header with the given name.
     *
     */
    public function withoutHeader(string $name) : self
    {
        $headers = $this->headers;
        unset($headers[$name]);
        return new self($headers);
    }

    /**
     * Get array with all key-value pairs of headers as specified by
     * psr-7 MessageInterface specification getHeaders()
     *
     * Array keys are header names and array values are arrays containing string values
     *
     * @return array<string,array<string>>
     *
     */
    public function toArray() : array
    {
        $result = [];

        foreach($this->headers as $header) {
            $result[$header->getName()] = $header->getValues();
        }

        return $result;
    }
}
