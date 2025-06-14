<?php

namespace PainlessPHP\Http\Message;

class Header
{
    public function __construct(
        private string $name,
        private string|array $values
    )
    {
    }

    /**
     * Parse a header line into a header object
     *
     */
    public static function createFromHeaderString(string $header) : self
    {
        $parts = explode(':', $header, 2);
        return new self(trim($parts[0]), trim($parts[1] ?? ''));
    }

    public function __toString()
    {
        return "$this->name:{$this->getValue()}";
    }

    /**
     * Get the name of the header
     *
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get the value as a singular string without parsing commas
     *
     */
    public function getValue() : string
    {
        return is_array($this->values)
            ? implode(', ', $this->values)
            : $this->values;
    }

    /**
     * Returns array of string values for this header
     *
     * @return array<string>
     *
     */
    public function getValues() : array
    {
        return is_array($this->values)
            ? $this->values
            : array_map('trim', explode(',', $this->values));
    }
}
