<?php

namespace PainlessPHP\Http\Message;

class StatusCodes
{
    private CONST array CODES = [
        '200' => [
            'name' => 'OK',
            'description' => 'The request has succeeded',
            'standard' => null,
            'shouldRetry' => false
        ],
        '201' => [
            "name" => "Created",
            "description" => "The request has been fulfilled and has resulted in one or more new resources being created",
            "standard" => "rfc 7231, section 6.3.2",
            "shouldRetry" => false
        ],
        '300' => [
            "name" => "Multiple Choices",
            "description" => "The requested resource corresponds to any one of a set of representations, each with its own specific location",
            "standard" => null,
            "shouldRetry" => false
        ],
        '301' => [
            "name" => "Moved Permanently",
            "description" => "The requested resource has been assigned a new permanent URI",
            "standard" => null,
            "shouldRetry" => false
        ],
        '302' => [
            "name" => "Found",
            "description" => "The requested resource resides temporarily under a different URI",
            "standard" => null,
            "shouldRetry" => false
        ],
        '304' => [
            "name" => "Not Modified",
            "description" => "The document has not been modified",
            "standard" => null,
            "shouldRetry" => false
        ],
        '307' => [
            "name" => "Temporary Redirect",
            "description" => "The requested resource resides temporarily under a different URI",
            "standard" => null,
            "shouldRetry" => false
        ],
        '400' => [
            "name" => "Bad Request",
            "description" => "The request could not be understood by the server due to malformed syntax",
            "standard" => null,
            "shouldRetry" => false
        ],
        '401' => [
            "name" => "Unauthorized",
            "description" => "The request requires user authentication",
            "standard" => null,
            "shouldRetry" => false
        ],
        '403' => [
            "name" => "Forbidden",
            "description" => "The server understood the request, but is refusing to fulfill it",
            "standard" => null,
            "shouldRetry" => false
        ],
        '404' => [
            "name" => "Not Found",
            "description" => "The server has not found anything matching the Request-URI",
            "standard" => null,
            "shouldRetry" => false
        ],
        '405' => [
            "name" => "Method Not Allowed",
            "description" => "The method specified in the Request-Line is not allowed for the resource identified by the Request-URI",
            "standard" => null,
            "shouldRetry" => false
        ],
        '408' => [
            "name" => "Request Timeout",
            "description" => "The client did not produce a request within the time that the server was prepared to wait",
            "standard" => null,
            "shouldRetry" => false
        ],
        '419' => [
            "name" => "Authentication Timeout",
            "description" => "Previously valid authentication has expired",
            "standard" => null,
            "shouldRetry" => false
        ],
        '422' => [
            "name" => "Unprocessable Content",
            "description" => "The request was well-formed but was unable to be followed due to semantic errors",
            "standard" => null,
            "shouldRetry" => false
        ],
        '429' => [
            "name" => "Too Many Requests",
            "description" => "The user has sent too many requests in a given amount of time (rate limiting)",
            "standard" => "RFC6585",
            "shouldRetry" => true
        ],
        '500' => [
            "name" => "Internal Server Error",
            "description" => "The server encountered an unexpected condition which prevented it from fulfilling the request",
            "standard" => null,
            "shouldRetry" => false
        ],
        '502' => [
            'name' => "Bad Gateway",
            'description' => 'This server got an error response while working as a gateway to handle the current request',
            'standard' => null,
            'shouldRetry' => true
        ],
        '503' => [
            "name" => "Service Unavailable",
            "description" => "The server is currently unable to handle the request due to a temporary overloading or maintenance of the server",
            "standard" => null,
            "shouldRetry" => true
        ],
    ];

    public static function getStatusForCode(int $code) : Status
    {
        $status = self::CODES[$code] ?? null;

        if($status === null) {
            return new Status(
                code: $code,
                name: 'Unknown',
                description: 'No description could be found for this status code',
                shouldRetry: false
            );
        }

        return Status::createFromArray([
            ...$status,
            'code' => $code
        ]);
    }
}
