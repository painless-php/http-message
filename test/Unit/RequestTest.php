<?php

namespace Test\Unit;

use InvalidArgumentException;
use PainlessPHP\Http\Message\Request;
use PainlessPHP\Http\Message\Uri;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UriInterface;

class RequestTest extends TestCase
{
    private Request $request;

    public function setUp() : void
    {
        $this->request = new Request('GET', 'https://google.com/search?q=foo');
    }

    public function testExceptionIsThrownWhenTryingToSetInvalidMethod()
    {
        $this->expectException(InvalidArgumentException::class);
        new Request('foo', 'bar');
    }

    public function testGetUriReturnsUriInterface()
    {
        $this->assertinstanceOf(UriInterface::class, $this->request->getUri());
    }

    public function testGetRequestTargetReturnsUriOriginForm()
    {
        $this->assertSame('/search?q=foo', $this->request->getRequestTarget());
    }

    public function testGetRequestTargetReturnsSlashIfNoUriIsSet()
    {
        $this->assertSame('/', (new Request('GET'))->getRequestTarget());
    }

    public function testWithRequestTargetSetsRequestTarget()
    {
        $request = $this->request->withRequestTarget('foo');
        $this->assertSame('foo', $request->getRequestTarget());
    }

    public function testWithRequestTargetDoesNotModifyOriginalRequestTarget()
    {
        $this->request->withRequestTarget('foo');
        $this->assertSame('/search?q=foo', $this->request->getRequestTarget());
    }

    public function testGetMethodReturnsHttpMethod()
    {
        $this->assertSame('GET', $this->request->getMethod());
    }

    public function testWithMethodSetsHttpMethod()
    {
        $request = $this->request->withMethod('POST');
        $this->assertSame('POST', $request->getMethod());
    }

    public function testWithMethodDoesNotModifyOriginalRequestMethod()
    {
        $this->request->withMethod('POST');
        $this->assertSame('GET', $this->request->getMethod());
    }

    public function testWithUriSetsNewUri()
    {
        $uri = new Uri('https://foo.bar');
        $request = $this->request->withUri($uri);
        $this->assertEquals($uri, $request->getUri());
    }

    public function testWithUriDoesNotModifyOriginalUri()
    {
        $originalUri = $this->request->getUri();
        $uri = new Uri('https://foo.bar');
        $this->request->withUri($uri);

        $this->assertSame($originalUri, $this->request->getUri());
    }

    public function testHostHeaderIsSetFromGivenUriDuringConstruction()
    {
        $this->assertSame('google.com', $this->request->getHeaderLine('host'));
    }

    public function testWithUriUpdatesHostHeaderByDefault()
    {
        $uri = new Uri('https://foo.bar');
        $request = $this->request->withUri($uri);
        $this->assertSame('foo.bar', $request->getHeaderLine('host'));
    }

    public function testWithParametersAddsParametersToUriForGetRequest()
    {
        $original = new Request('GET', 'https://foo.bar?param1=baz');

        $request = $original->withParameters([
            'param1' => 'foo',
            'param2' => 'bar'
        ]);

        $this->assertSame('https://foo.bar?param1=foo&param2=bar', (string)$request->getUri());
    }

    public function testWithParametersSetsContentTypeHeaderForPostRequest()
    {
        $original = new Request('POST', 'https://foo.bar?param1=baz');

        $request = $original->withParameters([
            'param1' => 'foo',
            'param2' => 'bar'
        ]);

        $this->assertSame('application/x-www-form-urlencoded', $request->getHeaderLine('content-type'));
    }

    public function testWithParametersAddsParametersToBodyForPostRequest()
    {
        $original = new Request('POST', 'https://foo.bar?param1=baz');

        $request = $original->withParameters([
            'param1' => 'foo',
            'param2' => 'bar'
        ]);

        $this->assertSame('param1=foo&param2=bar', (string)$request->getBody());
    }

    public function testWithParametersDoesNotModifyOriginalUri()
    {
        $originalUri = 'https://foo.bar?param1=baz';
        $original = new Request('GET', $originalUri);

        $original->withParameters([
            'param1' => 'foo',
            'param2' => 'bar'
        ]);

        $this->assertSame($originalUri, (string)$original->getUri());
    }
}
