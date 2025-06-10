<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\BasicAuthorizationHeader;
use PHPUnit\Framework\TestCase;

class BasicAuthorizationHeaderTest extends TestCase
{
    public function testHeaderValueIsSetCorrectly()
    {
        $header = new BasicAuthorizationHeader('foo', 'bar');
        $auth = explode(' ', (string)$header, 2)[1];
        $this->assertSame('foo:bar', base64_decode($auth));
    }

    public function testHeaderValueIsSetCorrectlyWhenHeaderIsCreatedFromArray()
    {
        $header = BasicAuthorizationHeader::createFromArray([ 'foo', 'bar' ]);
        $auth = explode(' ', (string)$header, 2)[1];
        $this->assertSame('foo:bar', base64_decode($auth));
    }

    public function testGetUserReturnsUser()
    {
        $header = new BasicAuthorizationHeader('foo', 'bar');
        $this->assertSame('foo', $header->getUser());
    }

    public function testGetPasswordReturnsPassword()
    {
        $header = new BasicAuthorizationHeader('foo', 'bar');
        $this->assertSame('bar', $header->getPassword());
    }

    public function testExceptionIsThrownIfUserWithColonCharacterIsSupplied()
    {
        $this->expectExceptionMessage("Username must not contain the ':' character");
        new BasicAuthorizationHeader('foo:bar', 'baz');
    }

    public function testPasswordWithColonCharacterIsValid()
    {
        $header = new BasicAuthorizationHeader('foo', 'bar:baz');
        $this->assertSame('bar:baz', $header->getPassword());
    }

    public function testCreateFromHeaderLineDecodesHeaderCorrectly()
    {
        $value = 'Authorization: Basic ' . base64_encode('foo:bar');
        $header = BasicAuthorizationHeader::createFromHeaderString($value);

        $this->assertSame('foo', $header->getUser());
        $this->assertSame('bar', $header->getPassword());
    }

    public function testCreateFromHeaderLineThrowsExceptionForNonBasicAuthHeader()
    {
        $value = 'foo';
        $this->expectExceptionMessage("Given header value should start with 'Basic '");
         BasicAuthorizationHeader::createFromHeaderString($value);
    }
}
