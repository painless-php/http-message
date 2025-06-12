<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\Body;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;

class BodyTest extends TestCase
{
    private $body;
    private $path;

    public function setUp() : void
    {
        parent::setUp();
        $this->path = __DIR__ . '/../input/body.txt';
        $this->body = new Body(fopen($this->path, 'r'));
    }

    public function testImplementsStreamInterface()
    {
        $this->assertInstanceOf(StreamInterface::class, new Body());
    }

    public function testToStringReturnsFileContents()
    {
        /* NOTE on unix systems, text files end with a newline */
        $this->assertEquals('test' . PHP_EOL, (string)$this->body);
    }

    public function testDetachReturnsResource()
    {
        $this->assertEquals('resource', gettype($this->body->detach()));
    }

    public function testCloseClosesResource()
    {
        $this->body->close();
        $this->assertNull($this->body->detach());
    }

    public function testCanBeConstructedFromString()
    {
        $this->assertEquals('foo', (string)(new Body('foo')));
    }

    public function testGetSizeReturnsStreamSizeInBytes()
    {
        $this->assertEquals(filesize($this->path), $this->body->getSize());
    }

    public function testTellReturnsStreamPosition()
    {
        $this->assertEquals(0, $this->body->tell());
    }

    public function testEofReturnsFalseForRecentStream()
    {
        $this->assertFalse($this->body->eof());
    }

    public function testIsSeekableReturnsTrueForValidStream()
    {
        $this->assertTrue($this->body->isSeekable());
    }

    public function testSeekSetsStreamPosition()
    {
        $this->body->seek(1);
        $this->assertEquals(1, $this->body->tell());
    }

    public function testRewindSetsStreamAtPositionZero()
    {
        $this->body->seek(1);
        $this->body->rewind();
        $this->assertEquals(0, $this->body->tell());
    }

    public function testIsWritableReturnsFalseForReadOnlyStream()
    {
        $this->assertFalse($this->body->isWritable());
    }

    public function testIsReadableReturnsTrueForReadOnlyStream()
    {
        $this->assertTrue($this->body->isReadable());
    }

    public function testReadReturnsContentWithGivenLength()
    {
        $this->assertEquals('te', $this->body->read(2));
    }

    public function testGetContentsReturnsRemainingContent()
    {
        $this->body->read(2);
        $this->assertEquals('st' . PHP_EOL, $this->body->getContents());
    }

    public function testGetMetadataReturnsAssociativeArrayEqualsToStreamGetMetaData()
    {
        $stream = fopen($this->path, 'r');
        $this->assertEquals(stream_get_meta_data($stream), $this->body->getMetadata());
    }

    public function testDetachedStreamResourceIsNotAffectedByCloseOfOriginalBody()
    {
        $stream = $this->body->detach();
        $this->body->close();
        $this->assertEquals('resource', gettype($stream));
    }

    public function testUnderlyingStreamResourceIsPreservedWhenBodyIsCloned()
    {
        $body = clone $this->body;
        unset($this->body);

        $this->assertEquals('resource', gettype($body->detach()));
    }

    public function testIsReadableWorksWhenByteModifierIsUsed()
    {
        $body = new Body(fopen('php://temp', 'rb'));
        $this->assertTrue($body->isReadable());
    }

    public function testIsWritableWorksWhenByteModifierIsUsed()
    {
        $body = new Body(fopen('php://temp', 'wb'));
        $this->assertTrue($body->isWritable());
    }

    public function testBodyWithTempSourceCanBeClonedSuccessfully()
    {
        $body = new Body(fopen('php://temp', 'w+'));
        $body->write('foo');
        $body = clone $body;

        $this->assertEquals('foo', (string)$body);
    }

    public function testBodyCanBeCreatedFromString()
    {
        $body = new Body('foo');
        $this->assertSame('foo', $body->getContents());
    }
}
