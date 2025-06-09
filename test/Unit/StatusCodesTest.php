<?php

namespace Test\Unit\Http;

use PainlessPHP\Http\Message\StatusCodes;
use PHPUnit\Framework\TestCase;

class StatusCodesTest extends TestCase
{
    public function testCode200CanBeFound()
    {
        $expected = [
            'code' => 200,
            'name' => 'OK',
            'description' => 'The request has succeeded',
            'standard' => null,
            'should_retry' => false
        ];
        $this->assertSame($expected, StatusCodes::getStatusForCode(200)->toArray());
    }

    public function testImaginaryCodeReturnsUnknownStatus()
    {
        $expected = [
            'code' => 123,
            'name' => 'Unknown',
            'description' => 'No description could be found for this status code',
            'standard' => null,
            'should_retry' => false
        ];

        $this->assertSame($expected, StatusCodes::getStatusForCode(123)->toArray());
    }
}
