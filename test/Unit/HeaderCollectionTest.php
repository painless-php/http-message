<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\Header;
use PainlessPHP\Http\Message\HeaderCollection;
use PHPUnit\Framework\TestCase;

class HeaderCollectionTest extends TestCase
{
    private HeaderCollection $headers;

    public function setUp() : void
    {
        $this->headers = HeaderCollection::createFromArray([
            'header1' => ['foo', 'bar'],
            'header2' => 'baz'
        ]);
    }

    public function testToArrayReturnsHeadersInPsr7GetHeadersFormat()
    {
        $expected = [
            'header1' => [
                'foo',
                'bar'
            ],
            'header2' => [
                'baz'
            ]
        ];
        $this->assertEquals($expected, $this->headers->toArray());
    }

    public function testHasHeaderReturnsFalseForMissingHeader()
    {
        $this->assertFalse($this->headers->hasHeader('header3'));
    }

    public function testHasHeaderReturnsTrueForExistingHeader()
    {
        $this->assertTrue($this->headers->hasHeader('header1'));
    }

    public function testGetHeaderReturnsHeaderObject()
    {
        $this->assertInstanceOf(Header::class, $this->headers->getHeader('header1'));
    }

    public function testGetHeaderLineReturnsCommaSeparatedStringOfHeaderValues()
    {
        $this->assertEquals('foo, bar', $this->headers->getHeaderLine('header1'));
    }
}
