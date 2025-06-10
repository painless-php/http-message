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
