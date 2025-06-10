<?php

namespace PainlessPHP\Http\Message;

use InvalidArgumentException;
use Override;
use PainlessPHP\Http\Message\Exception\StringParsingException;
use PainlessPHP\Http\Message\Internal\Arr;

class BasicAuthorizationHeader extends Header
{
    private string $user;
    private string $password;

    public function __construct(string $user, string $password)
    {
        $this->setUser($user);
        $this->setPassword($password);
        $value = 'Basic ' . base64_encode("$user:$password");
        parent::__construct('Authorization', $value);
    }

    /**
     * Create a new basic auth header from array
     *
     */
    public static function createFromArray(array $auth) : self
    {
        $user = Arr::find($auth, [0, 'user', 'username'], null);

        if(! is_string($user)) {
            $msg = "Could not valid index to use as user";
            throw new InvalidArgumentException($msg);
        }

        $password = Arr::find($auth, [1, 'pass', 'password'], null);

        if(! is_string($password)) {
            $msg = "Could not find valid index to use as password";
            throw new InvalidArgumentException($msg);
        }

        return new self($user, $password);
    }

    /**
     *  Create a basic authorization header from a header line
     *
     */
    #[Override]
    public static function createFromHeaderLine(string $header): BasicAuthorizationHeader
    {
        $parsed = parent::createFromHeaderLine($header);
        return self::createFromHeaderValue($parsed->getValue());
    }

    /**
     * Decode a given header value into basic auth header object
     *
     */
    public static function createFromHeaderValue(string $value) : self
    {
        $value = trim($value);

        if(! str_starts_with($value, 'Basic ')) {
            $msg = "Given header value should start with 'Basic '";
            throw new StringParsingException($msg);
        }

        // Get the part after 'Basic'
        $credentials = explode(' ', $value, 2)[1];

        // Decode credentials
        $credentials = base64_decode($credentials);

        // Split credentials to username and password
        $credentials = explode(':', $credentials, 2);

        return new self($credentials[0], $credentials[1] ?? '');
    }

    /**
     * Set the basic auth user
     *
     */
    private function setUser(string $user)
    {
        if(str_contains($user, ':')) {
            $msg = "Username must not contain the ':' character";
            throw new StringParsingException($msg);
        }

        $this->user = $user;
    }

    /**
     * Set the basic auth password
     *
     */
    private function setPassword(string $password)
    {
        $this->password = $password;
    }

    /**
     * Get the decoded basic auth user
     *
     */
    public function getUser() : string
    {
        return $this->user;
    }

    /**
     * Get the decoded basic auth password
     *
     */
    public function getPassword() : string
    {
        return $this->password;
    }
}
