<?php

namespace PainlessPHP\Http\Message;

use PainlessPHP\Http\Message\Exception\StringParsingException;

/**
 * Handles query string related functionality like parameter parsing and
 * encoding.
 *
 * As per psr-7 specification, leading ? character is not considered part of
 * the query string.
 *
 */
class Query
{
    protected $parameters;

    /**
     * Create query object from parameter key-value pairs
     *
     * NOTE: even though construction of query from a nested arrays is
     * supported, it is not standardized and there is no guarantee that every
     * web framework will handle the parameters as expected
     *
     * @param array<string|(string|array)> $parameters
     *
     */
    public function __construct(array $parameters)
    {
        $this->setParameters($parameters);
    }

    /**
     * Set the parameters
     *
     */
    protected function setParameters(array $parameters)
    {
        foreach($parameters as $name => $value) {
            $this->validateParameter($name, $value);
        }
        $this->parameters = $parameters;
    }

    /**
     * Create query object from url string
     *
     * @throws StringParsingException
     *
     */
    public static function createFromUrlString(string $url) : self
    {
        $query = parse_url($url)['query'] ?? '';
        return static::createFromQueryString($query);
    }

    /**
     * Create query object from query string
     *
     * @throws StringParsingException
     *
     */
    public static function createFromQueryString(string $value) : self
    {
        if(trim($value) === '') {
            return new self([]);
        }

        /* Remove leading ? if it exists */
        if(mb_substr($value, 0, 1) === '?') {
            $value = mb_substr($value, 1);
        }

        /* Check if string has other ? characters */
        if(str_contains($value, '?')) {
            $msg = 'Non-leading ? character in query string';
            throw new StringParsingException($msg);
        }

        $value = urldecode($value);
        $params = [];

        foreach(explode('&', $value) as $param) {
            $parts = explode('=', $param);
            if(count($parts) !== 2) {
                $msg = "Key-value pair is missing expected =";
                throw new StringParsingException($msg);
            }
            [$key, $value] = $parts;
            $params[$key] = $value;
        }

        return new self($params);
    }

    /**
     * Get query as a string
     *
     */
    public function __toString()
    {
        $string = '';
        foreach($this->parameters as $key => $value) {
            $parameter = is_array($value)
            ? $this->arrayParameterToString($key, $value)
            : $this->parameterToString($key, $value);

            $leader = $string === '' ? '' : '&';
            $string .= "$leader$parameter";
        }
        return $string;
    }

    /**
     * Validate that a given parameter can be used as query value
     *
     */
    protected function validateParameter(string $path, mixed $value)
    {
        /* Recursively validate every array child */
        if(is_array($value)) {
            foreach($value as $key => $subvalue) {
                $this->validateParameter("$path->$key", $subvalue);
            }
            return;
        }

        if(! is_scalar($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            $msg = "Parameter '$path' could not be converted to string";
            throw new StringParsingException($msg);
        }
    }

    /**
     * Convert a single parameter into string
     *
     */
    protected function parameterToString(string $name, $value, ?string $key = null) : string
    {
        $name = urlencode($name);
        $value = urlencode((string)$value);

        if($key !== null) {
            $key = urlencode($key);
            $name = "{$name}[{$key}]";
        }

        return "$name=$value";

    }

    /**
     * Convert an array parameter into string
     *
     */
    protected function arrayParameterToString(string $name, array $parameter) : string
    {
        $string = '';
        $index = 0;

        foreach($parameter as $key => $value) {

            if($index > 0) {
                /* Add leader before every parameter except the first one */
                $string .= '&';
            }

            if(is_array($value)) {
                $string .= $this->arrayParameterToString($key, $value);
            }
            else {
                $string .= $this->parameterToString($name, $value, $key);
            }

            $index++;
        }

        return $string;
    }

    /**
     * Add and override the given parameters
     *
     */
    public function addParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Remove the given parameters
     *
     */
    public function removeParameters(array $parameters)
    {
        foreach($parameters as $key) {
            unset($this->parameters[$key]);
        }
    }

    /**
     * Get the given parameter value
     *
     */
    public function getParameter(string $key) : ?string
    {
        return $this->parameters[$key] ?? null;
    }

    /**
     * Check if query has the given parameter
     *
     */
    public function hasParameter(string $key) : bool
    {
        return isset($this->parameters[$key]);
    }

    /**
     * Get query as an array with key-value pairs
     *
     */
    public function toArray() : array
    {
        return $this->parameters;
    }
}
