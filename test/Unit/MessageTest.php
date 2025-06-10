<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\BasicAuthorizationHeader;
use PainlessPHP\Http\Message\Body;
use PainlessPHP\Http\Message\HeaderCollection;
use PainlessPHP\Http\Message\Message;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class MessageTest extends TestCase
{
    private $message;

    public function setUp() : void
    {
        $this->message = new Message(
            body: new Body(fopen(__DIR__ . '/../input/body.txt', 'r')),
            headers: HeaderCollection::createFromArray([
                'header1' => 'foo, bar',
                'header2' => 'baz'
            ])
        );
    }

    public function testMessageCanBeInitialized()
    {
        $this->assertInstanceOf(Message::class, $this->message);
    }

    public function testGetHeadersReturnsHeadersInPsr7Format()
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

        $this->assertEquals($expected, $this->message->getHeaders());
    }

    public function testHasHeaderReturnsTrueForExistingHeader()
    {
        $this->assertTrue($this->message->hasHeader('header1'));
    }

    public function testHasHeaderReturnsFalseForNonExistingHeader()
    {
        $this->assertFalse($this->message->hasHeader('header3'));
    }

    public function testHasHeaderIsCaseInsensitive()
    {
        $this->assertTrue($this->message->hasHeader('Header1'));
    }

    public function testGetHeaderReturnsHeaderValuesArray()
    {
        $this->assertEquals(['foo', 'bar'], $this->message->getHeader('header1'));
    }

    public function testGetHeaderIsCaseInsensitive()
    {
        $this->assertEquals(['foo', 'bar'], $this->message->getHeader('Header1'));
    }

    public function getHeaderLineReturnsHeaderValuesAsConcatenatedString()
    {
        $this->assertEquals('foo, bar', $this->message->getHeaderLine('header1'));
    }

    public function testGetHeaderLinesIsCaseInsensitive()
    {
        $this->assertEquals('foo, bar', $this->message->getHeaderLine('Header1'));
    }

    public function testWithHeaderReturnsInstanceWithReplacedHeader()
    {
        $message = $this->message->withHeader('header1', 'test');
        $this->assertEquals('test', $message->getHeaderLine('header1'));
    }

    public function testWithHeaderDoesNotModifyOriginalMessageHeaders()
    {
        $this->message->withHeader('header1', 'test');
        $this->assertEquals('foo, bar', $this->message->getHeaderLine('header1'));
    }

    public function testWithAddedHeaderReturnsInstanceWithAppendedHeaderValue()
    {
        $message = $this->message->withAddedHeader('header1', 'baz');
        $this->assertEquals(['foo', 'bar', 'baz'], $message->getHeader('header1'));
    }

    public function testWithAddedHeaderDoesNotModifyOriginalMessageHeaders()
    {
        $this->message->withAddedHeader('header1', 'baz');
        $this->assertEquals('foo, bar', $this->message->getHeaderLine('header1'));
    }

    public function testWithoutHeaderRemovesHeader()
    {
        $message = $this->message->withoutHeader('header2');
        $this->assertEquals(['header1'], array_keys($message->getHeaders()));
    }

    public function testWithoutHeaderDoesNotModifyOriginalMessageHeaders()
    {
        $this->message->withoutHeader('header2');
        $this->assertEquals(['header1', 'header2'], array_keys($this->message->getHeaders()));
    }

    public function testGetBodyReturnsStreamInterface()
    {
        $this->assertInstanceOf(StreamInterface::class, $this->message->getBody());
    }

    public function testWithBodySetsMessageBody()
    {
        $body = new Body();
        $message = $this->message->withBody($body);
        $this->assertTrue($body === $message->getBody());
    }

    public function testWithBodyDoesNotModifyOriginalMessageBody()
    {
        $body = new Body();
        $this->message->withBody($body);
        $this->assertFalse($body === $this->message->getBody());
    }

    public function testWithJsonSetsContentTypeHeader()
    {
        $message = $this->message->withJson(['foo' => 'bar']);
        $this->assertEquals('application/json', $message->getHeaderLine('content-type'));
    }

    public function testWithJsonSetsEncodedBodyContent()
    {
        $content = ['foo' => 'bar'];
        $message = $this->message->withJson($content);
        $this->assertEquals(json_encode($content), (string)$message->getBody());
    }

    public function testWithJsonDoesNotModifyOriginalMessageBody()
    {
        $content = ['foo' => 'bar'];
        $this->message->withJson($content);
        $this->assertEquals("test\n", (string)$this->message->getBody());
    }

    public function testWithJsonDoesNotModifyOriginalMessageHeaders()
    {
        $content = ['foo' => 'bar'];
        $this->message->withJson($content);
        $this->assertEmpty($this->message->getHeaderLine('content-type'));
    }

    public function testWithBasicAuthSetsAuthorizationHeader()
    {
        $message = $this->message->withBasicAuth('foo', 'bar');
        $header = BasicAuthorizationHeader::createFromHeaderValue($message->getHeaderLine('Authorization'));

        $this->assertEquals('foo', $header->getUser());
        $this->assertEquals('bar', $header->getPassword());
    }

    public function testWithBasicAuthDoesNotModifyOriginalMessageHeaders()
    {
        $this->message->withBasicAuth('foo', 'bar');
        $this->assertFalse($this->message->hasHeader('Authorization'));
    }
}
