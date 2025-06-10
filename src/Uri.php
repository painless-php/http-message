<?php

namespace PainlessPHP\Http\Message;

use PainlessPHP\Http\Message\Concern\DeepCloneTrait;
use PainlessPHP\Http\Message\Internal\ParsedUri;
use Psr\Http\Message\UriInterface;

class Uri implements UriInterface
{
    use DeepCloneTrait;

    private const DEFAULT_PORTS = [
        'http'  => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    private ParsedUri $parsed;

    public function __construct(ParsedUri|string $uri)
    {
        $this->parsed = $uri instanceof ParsedUri ? $uri : ParsedUri::createFromUriString($uri);
    }

    public function __toString() : string
    {
        return (string)$this->parsed;
    }

    public function getScheme() : string
    {
        return $this->parsed->scheme;
    }

    public function getUser() : string
    {
        return $this->parsed->user;
    }

    public function getPassword() : string
    {
        return $this->parsed->pass;
    }

    public function getHost() : string
    {
        return $this->parsed->host;
    }

    public function getPort() : int
    {
        return $this->parsed->port;
    }

    public function getPath() : string
    {
        return $this->parsed->path;
    }

    public function getQuery() : string
    {
        return $this->parsed->query;
    }

    public function getAuthority() : string
    {
        $authority = [
            'host' => $this->parsed->host,
            'user' => $this->parsed->user,
            'pass' => $this->parsed->pass
        ];

        if(! $this->usesDefaultPort()) {
            $authority['port'] = $this->parsed->port;
        }

        return http_build_url($authority);
    }

    public function getDefaultPort() : ?int
    {
        return self::DEFAULT_PORTS[$this->parsed->scheme] ?? null;
    }

    public function usesDefaultPort() : bool
    {
        return $this->getDefaultPort() === $this->parsed->port;
    }

    public function getUserInfo() : string
    {
        $info = $this->parsed->user;

        if($this->parsed->pass !== '') {
            $info .= ":{$this->parsed->pass}";
        }

        return $info;
    }

    public function getFragment() : string
    {
        return $this->parsed->fragment;
    }

    public function withScheme(string $scheme) : self
    {
        $instance = $this->clone();
        $instance->parsed->scheme = $scheme;
        return $instance;
    }

    public function withUserInfo(string $user, ?string $password = null) : self
    {
        $instance = $this->clone();
        $instance->parsed->user = $user;
        $instance->parsed->pass = $password ?? '';
        return $instance;
    }

    public function withHost(string $host) : self
    {
        $instance = $this->clone();
        $instance->parsed->host = $host;
        return $instance;
    }

    public function withPort($port) : self
    {
        $instance = $this->clone();
        $instance->parsed->port = $port;
        return $instance;
    }

    public function withPath(string $path) : self
    {
        $instance = $this->clone();
        $instance->parsed->path = $path;
        return $instance;
    }

    public function withQuery(Query|array|string $query) : self
    {
        $instance = $this->clone();

        /* Convert query object to string */
        if($query instanceof Query) {
            $query = (string)$query;
        }

        /* Convert query array to string */
        if(is_array($query)) {
            $query = (string)(new Query($query));
        }

        $instance->parsed->query = $query;
        return $instance;
    }

    public function withFragment(string $fragment) : self
    {
        $instance = $this->clone();
        $instance->parsed->fragment = $fragment;
        return $instance;
    }

    /**
     * rfc 7230 section 5.3.1
     *
     */
    public function getOriginForm() : string
    {
        $form = '';

        /* Append path if not empty */
        if($this->parsed->path !== '') {
            $form .= $this->parsed->path;
        }

        /* Append query if not empty */
        if($this->parsed->query !== '') {
            $form .= "?{$this->parsed->query}";
        }

        return $form;
    }

    /**
     * rfc 7230 section 5.3.2
     *
     */
    public function getAbsoluteForm() : string
    {
        return (string)$this;
    }

    /**
     * rfc 7230 section 5.3.3
     *
     */
    public function getAuthorityForm() : string
    {
        return http_build_url([
            'host' => $this->parsed->host,
            'port' => $this->parsed->port
        ]);
    }

    /**
     * rfc 7230 section 5.3.4
     *
     */
    public function getAsteriskForm() : string
    {
        return '*';
    }

    /**
     * Create a new Uri with the desired parameters added to the query.
     *
     * @param Query|array<string,string> $query Query object or array with query key value pairs
     *
     */
    public function withAddedQueryParameters(Query|array $query) : self
    {
        $instance = $this->clone();

        if($query instanceof Query) {
            $query = $query->toArray();
        }

        // Create query object using parsed query string
        $originalQuery = Query::createFromQueryString($this->parsed->query);

        // Add new query parameters to created object
        $originalQuery->addParameters($query);

        // Override the parsed query of copied instance
        $instance->parsed->query = $originalQuery->__toString();

        return $instance;
    }

    /**
     * Create a new Uri with the desired parameters removed from the query.
     *
     * @param array<string> $removedParameters Array containing strings representing
     * names of the parameters that should be removed from the query.
     *
     */
    public function withRemovedQueryParameters(array $removedParameters) : self
    {
        $instance = $this->clone();

        // Create query object using parsed query string
        $originalQuery = Query::createFromQueryString($this->parsed->query);

        // Remove new query parameters from created object
        $originalQuery->removeParameters($removedParameters);

        // Override the parsed query of copied instance
        $instance->parsed->query = $originalQuery->__toString();

        return $instance;
    }
}
