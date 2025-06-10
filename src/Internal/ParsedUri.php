<?php

namespace PainlessPHP\Http\Message\Internal;

use InvalidArgumentException;
use PainlessPHP\Http\Message\Exception\StringParsingException;

class ParsedUri
{
    public int|string|null $port;

    /**
     * Parse a new instance from a given uri string
     *
     */
    public function __construct(
        public string $scheme = '',
        public string $host = '',
        int|string|null $port = null,
        public string $user = '',
        public string $pass = '',
        public string $path = '',
        public string $query = '',
        public string $fragment = '',
    )
    {
        $this->setPort($port);
    }

    /**
     * Parse a given uri string
     *
     */
    public static function createFromUriString(string $uri) : self
    {
        $components = parse_url($uri);

        if($components === false) {
            $msg = "Could not parse malformed uri string '$uri'";
            throw new StringParsingException($msg);
        }

        /* For some reason, simple domain names like test.com will be interpreted as path
           and hostname will be left empty by parse_url, if this happens, use path as hostname
         */
        if(! isset($components['host'])) {
            $components['host'] = $components['path'] ?? null;
            if($components['host'] === null) {
                $msg = "Could not parse hostname from string '$uri'";
                throw new StringParsingException($msg);
            }
            unset($components['path']);
        }

        return new self(...$components);
    }

    /**
     * Create a new instance while overriding some components
     *
     * @param array<string,string> $components
     *
     */
    public function withComponents(array $components) : self
    {
        return new self(...[...$this->toArray(), ...$components]);
    }

    /**
     * Get full uri string
     *
     */
    public function __toString() : string
    {
        return http_build_url($this->toArray());
    }

    /**
     * Set the value of port component
     */
    private function setPort(int|string|null $port) : void
    {
        if(is_string($port)) {
            $port = $this->parsePortFromString($port);
        }
        if($port !== null && ($port < 1 || $port > 65535)) {
            $msg = "Invalid port number $port - outside of range";
            throw new InvalidArgumentException($msg);
        }
        $this->port = $port;
    }

    /**
     * Convert a string represeting port to integer
     *
     */
    private function parsePortFromString(string $string) : int
    {
        $port = intval($string);

        if($port === 0) {
            $msg = "Failed to parse given port string '$port' to an integer value";
            throw new StringParsingException($msg);
        }

        return $port;
    }

    /**
     * Get an array containing all the uri components.
     *
     * @return array<string,mixed>
     */
    public function toArray() : array
    {
        return [
            'scheme' => $this->scheme,
            'host' => $this->host,
            'port' => $this->port,
            'user' => $this->user,
            'pass' => $this->pass,
            'path' => $this->path,
            'query' => $this->query,
            'fragment' => $this->fragment
        ];
    }
}
