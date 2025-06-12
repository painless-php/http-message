<?php

namespace PainlessPHP\Http\Message;

use Psr\Http\Message\ResponseInterface;

class Response extends Message implements ResponseInterface
{
    protected Status $status;

    public function __construct(
        Status|int $status = 200,
        mixed $body = null,
        HeaderCollection|array $headers = []
    ) {
        parent::__construct($body, $headers);
        $this->setStatus($status);
    }

    protected function setStatus(Status|int $status)
    {
        if(is_int($status)) {
            $status = StatusCodes::getStatusForCode($status);
        }
        $this->status = $status;
    }

    public function getStatus() : Status
    {
        return $this->status;
    }

    public function getStatusCode() : int
    {
        return $this->status->getCode();
    }

    public function withStatus(int $code, string $reasonPhrase = '') : static
    {
        $instance = $this->clone();

        $status = StatusCodes::getStatusForCode($code);
        $phrase = $reasonPhrase !== '' ? $reasonPhrase : $status->getReasonPhrase();

        $instance->setStatus(Status::createFromArray([
            ...$status->toArray(),
            'reasonPhrase' => $phrase
        ]));

        return $instance;
    }

    public function getReasonPhrase() : string
    {
        return $this->status->getReasonPhrase();
    }

    public function hasBody() : bool
    {
        return $this->getBody()->getSize() > 0;
    }
}
