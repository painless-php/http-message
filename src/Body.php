<?php

namespace PainlessPHP\Http\Message;

use InvalidArgumentException;
use PainlessPHP\Http\Message\Internal\Stream;
use Psr\Http\Message\StreamInterface;
use RuntimeException;

/**
 * A representation of a message body.
 *
 */
class Body implements StreamInterface
{
    protected mixed $stream;
    protected ?array $meta = null;

    /**
     *
     * @param StreamInterface|resource|string|null $source Source of the body
     *
     */
    public function __construct(mixed $source = null)
    {
        $this->setSource($source);
    }

    /**
     * Automatically close underlying stream resource when object is destroyed
     *
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Open a new resource stream for the cloned instance to avoid stream state
     * of the original object from affecting internal state
     *
     */
    public function __clone()
    {
        if($this->stream === null) {
            return;
        }

        // Reset stream if seekable
        if($this->isSeekable()) {
            $this->seek(0);
        }

        $original = $this->stream;
        $this->stream = fopen('php://temp', 'w+');
        stream_copy_to_stream($original, $this->stream);
    }

    /**
     * Set the source of the body
     *
     */
    protected function setSource(mixed $source)
    {
        if($source instanceof StreamInterface) {

            $resource = $source->detach();

            /* Try to use underlying resource if possible */
            if(is_resource($resource)) {
                $this->attach($resource);
                return;
            }

            /* Use source content if no resource could be accessed */
            $source = (string)$source;
        }

        if(is_resource($source) || is_null($source)) {
            $this->attach($source);
            return;
        }

        if(is_string($source)) {
            $this->attach(null);
            $this->write($source);
            $this->rewind();
            return;
        }

        $class = StreamInterface::class;
        $type = is_object($source) ? get_class($source) : gettype($source);
        $msg = "Source of the body must be one of the following: resource, string, null or instance of $class, '$type' given";
        throw new InvalidArgumentException($msg);
    }

    /**
     * Not part of the actual psr-7 spec, thus only used privately
     *
     */
    protected function attach(mixed $stream)
    {
        if($stream === null) {
            $this->stream = fopen('php://temp', 'r+');
            return;
        }

        if(is_resource($stream)) {
            $this->stream = $stream;
            return;
        }

        $type = is_object($stream) ? get_class($stream) : gettype($stream);
        $msg = "Body resource must be null or a stream, $type given";
        throw new InvalidArgumentException($msg);
    }

    public function __toString() : string
    {
        $this->seek(0);
        return $this->getContents();
    }

    public function close() : void
    {
        if(is_resource($this->stream)) {
            fclose($this->stream);
            $this->detach();
        }
    }

    /**
     * Provides a way to manipulate stream with methods not included in the
     * psr-7 spec. Invalidates the state of the body object to make sure that
     * the stream state is not mutated externally.
     *
     */
    public function detach()
    {
        $stream = $this->stream;
        $this->stream = null;
        return $stream;
    }

    public function getSize() : ?int
    {
        try {
            return strlen((string)$this);
        }
        catch(RuntimeException $e) {
            return null;
        }
    }

    public function tell() : int
    {
        $this->validateStream();

        $result = ftell($this->stream);

        if($result === false) {
            $msg = "Failure finding file pointer for body stream";
            throw new RuntimeException($msg);
        }

        return $result;
    }

    public function eof() : bool
    {
        $this->validateStream();
        return feof($this->stream);
    }

    public function isSeekable() : bool
    {
        return $this->getMetadata('seekable');
    }

    public function seek($offset, $whence = SEEK_SET) : void
    {
        $this->validateStream();

        if(fseek($this->stream, $offset, $whence) === -1) {
            $msg = "Failure seeking body stream";
            throw new RuntimeException($msg);
        }
    }

    public function rewind() : void
    {
        if(! $this->isSeekable()) {
            $msg = "Body stream is not seekable";
            throw new RuntimeException($msg);
        }
        $this->seek(0);
    }

    public function isWritable() : bool
    {
        foreach(Stream::WRITE_MODES as $writeMode) {
            /* Check if mode starts with read mode to account for trailing b or t */
            if(str_starts_with($this->getMetadata('mode'), $writeMode)) {
                return true;
            }
        }
        return false;
    }

    public function write($string) : int
    {
        $this->validateStream();
        $result = fwrite($this->stream, $string);
        if($result === false) {
            $msg = "Failure writing to body stream";
            throw new RuntimeException($msg);
        }
        return $result;
    }

    public function isReadable() : bool
    {
        foreach(Stream::READ_MODES as $readMode) {
            /* Check if mode starts with read mode to account for trailing b or t */
            if(str_starts_with($this->getMetadata('mode'), $readMode)) {
                return true;
            }
        }

        return false;
    }

    public function read($length) : string
    {
        $this->validateStream();
        $content = fread($this->stream, $length);

        if($content === false) {
            $msg = "Failure reading body stream";
            throw new RuntimeException($msg);
        }

        return $content;
    }

    public function getContents() : string
    {
        $this->validateStream();
        $content = stream_get_contents($this->stream);

        if($content === false) {
            $msg = "Failure reading body stream";
            throw new RuntimeException($msg);
        }

        return $content;
    }

    public function getMetadata(?string $key = null) : mixed
    {
        if($this->stream === null) {
            return null;
        }

        if($this->meta === null) {
            $this->meta = stream_get_meta_data($this->stream);
        }

        $data = $this->meta;

        if(is_string($key)) {
            $data = $data[$key] ?? null;
        }

        return $data;
    }

    /**
     * Validate that the underlying stream is usable before trying to use
     * stream manipulation functions
     *
     */
    protected function validateStream()
    {
        if($this->stream === null) {
            $msg = "Stream is detached";
            throw new RuntimeException($msg);
        }
    }
}
