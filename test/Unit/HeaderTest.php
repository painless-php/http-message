<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\Header;
use PHPUnit\Framework\TestCase;

class HeaderTest extends TestCase
{
    public function testGetValueReturnsUnparsedValueString()
    {
        $header = new Header('name', ['foo', 'bar', 'baz']);
        $this->assertSame('foo, bar, baz', $header->getValue());
    }

    public function testGetValuesReturnsArrayOfValues()
    {
        $values = ['foo', 'bar', 'baz'];
        $header = new Header('name', $values);
        $this->assertSame($values, $header->getValues());
    }

    public function testToStringReturnsCommaDelimitedHeaderString()
    {
        $header = new Header('name', ['foo', 'bar', 'baz']);
        $this->assertSame('name:foo, bar, baz', $header->__toString());
    }

    public function testGetHeadersTrimsSpaceBetweenValues()
    {
        $header = new Header('name', 'foo, bar, baz');
        $this->assertSame(['foo', 'bar', 'baz'], $header->getValues());
    }
}
