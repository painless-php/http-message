<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\Uri;
use PHPUnit\Framework\TestCase;

class UriTest extends TestCase
{
    private $string;
    private $uri;

    public function setUp() : void
    {
        parent::setUp();
        $this->string = 'http://username:password@hostname:9090/path?arg=value#anchor';
        $this->uri = new Uri($this->string);
    }

    public function testQueryCanBeConstructedFromStringAndRevertedToOriginalString()
    {
        $this->assertSame($this->string, (string)$this->uri);
    }

    public function testGetSchemeWorks()
    {
        $this->assertSame('http', $this->uri->getScheme());
    }

    public function testGetUserWorks()
    {
        $this->assertSame('username', $this->uri->getUser());
    }

    public function testGetPasswordWorks()
    {
        $this->assertSame('password', $this->uri->getPassword());
    }

    public function testGetHostWorks()
    {
        $this->assertSame('hostname', $this->uri->getHost());
    }

    public function testGetPortWorks()
    {
        $this->assertSame(9090, $this->uri->getPort());
    }

    public function testGetPathWorks()
    {
        $this->assertSame('/path', $this->uri->getPath());
    }

    public function testGetQueryWorks()
    {
        $this->assertSame('arg=value', $this->uri->getQuery());
    }

    public function testToStringWorksCorrectlyWithShortUri()
    {
        $string = 'foo.bar';
        $uri = new Uri($string);
        $this->assertSame($string, (string)$uri);
    }

    public function testStringWillBeUsedAsHost()
    {
        $string = 'foo.bar';
        $uri = new Uri($string);
        $this->assertSame($string, $uri->getHost());
    }

    public function testPathIsNotSetWhenStringIsUsedAsHost()
    {
        $string = 'foo.bar';
        $uri = new Uri($string);
        $this->assertSame('', $uri->getPath());
    }

    public function testWithSchemeSetsSchemeOfTheUri()
    {
        $uri = $this->uri->withScheme('ftp');
        $this->assertSame('ftp', $uri->getScheme());
    }

    public function testWithUserInfoSetsUserInfoOfTheUri()
    {
        $uri = $this->uri->withUserInfo('foo', 'bar');
        $this->assertSame('foo:bar', $uri->getUserInfo());
    }

    public function testWithHostSetsHostOfTheUri()
    {
        $uri = $this->uri->withHost('foo.bar');
        $this->assertSame('foo.bar', $uri->getHost());
    }

    public function testWithPortSetsPortOfTheUri()
    {
        $uri = $this->uri->withPort(8080);
        $this->assertSame(8080, $uri->getPort());
    }

    public function testWithPathSetsPathOfTheUri()
    {
        $uri = $this->uri->withPath('foo');
        $this->assertSame('foo', $uri->getPath());
    }

    public function testWithQuerySetsQueryOfTheUri()
    {
        $uri = $this->uri->withQuery('param1=foo&param2=bar');
        $this->assertSame('param1=foo&param2=bar', $uri->getQuery());
    }

    public function testWithQuerySupportsNestedArrays()
    {
        $query = [
            'param1' => [
                'foo',
                'bar',
            ],
            'param2' => 'baz'
        ];

        $uri = $this->uri->withQuery($query);
        $this->assertSame('param1[0]=foo&param1[1]=bar&param2=baz', $uri->getQuery());
    }

    public function testWithFragmentSetsFragmentOfTheUri()
    {
        $uri = $this->uri->withFragment('fragment');
        $this->assertSame('fragment', $uri->getFragment());
    }

    public function testWithAddedQueryParametersAddsTheGivenParameters()
    {
        $uri = new Uri('https://google.com?search=foo');
        $this->assertSame('search=foo&test=bar', $uri->withAddedQueryParameters(['test' => 'bar'])->getQuery());
    }

    public function testWithAddedQueryParametersDoesNotModifyOriginalInstance()
    {
        $uri = new Uri('https://google.com?search=foo');
        $uri->withAddedQueryParameters(['test' => 'bar']);

        $this->assertSame('search=foo', $uri->getQuery());
    }

    public function testWithRemovedQueryParametersRemovesTheGivenParameters()
    {
        $uri = new Uri('https://google.com?foo=1&bar=2&baz=3');
        $this->assertSame('foo=1', $uri->withRemovedQueryParameters(['bar', 'baz'])->getQuery());
    }

    public function testWithRemovedQueryParametersDoesNotModifyOriginalInstance()
    {
        $uri = new Uri('https://google.com?foo=1&bar=2&baz=3');
        $uri->withRemovedQueryParameters(['test' => 'bar']);

        $this->assertSame('foo=1&bar=2&baz=3', $uri->getQuery());
    }
}
