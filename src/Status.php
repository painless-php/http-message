<?php

namespace PainlessPHP\Http\Message;

use PainlessPHP\Http\Message\Concern\CreatableFromArray;

/**
 * A representation of http response status.
 */
class Status
{
    use CreatableFromArray;

    public function __construct(
        private int $code,
        private string $name,
        private string $description,
        private bool $shouldRetry = false,
        private ?string $standard = null
    )
    {
    }

    public function __toString() : string
    {
        return "$this->code $this->name";
    }

    public function getCode() : int
    {
        return $this->code;
    }

    public function getName() : string
    {
        return $this->name;
    }

    public function getReasonPhrase() : string
    {
        return $this->name;
    }

    public function getDescription() : string
    {
        return $this->description;
    }

    public function shouldRetry() : ?bool
    {
        return $this->shouldRetry;
    }

    public function getStandard() : ?string
    {
        return $this->standard;
    }

    public function toArray() : array
    {
        return [
            'code' => $this->code,
            'name' => $this->name,
            'description' => $this->description,
            'standard' => $this->standard,
            'shouldRetry' => $this->shouldRetry,
        ];
    }
}
