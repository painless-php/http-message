<?php

namespace PainlessPHP\Http\Message\Internal;

/**
 * Array related helper functionality.
 * WARNING: Not part of the package public API.
 */
class Arr
{
    public static function find(array $array, array $keys, mixed $default = null)
    {
        foreach($keys as $key) {
            if(array_key_exists($key, $array)) {
                return $array[$key] ?? $default;
            }
        }
        return $default;
    }
}
