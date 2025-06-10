<?php

namespace Test\Unit;

use PainlessPHP\Http\Message\Response;
use PHPUnit\Framework\TestCase;

class ResponseTest extends TestCase
{
    private Response $response;

    public function setUp() : void
    {
        parent::setUp();
        $this->response = new Response(200);
    }

    public function testWithStatusSetsStatusCode()
    {
        $response = $this->response->withStatus(201);
        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testWithStatusSetsReasonPhrase()
    {
        $response = $this->response->withStatus(201, 'FOOBAR');
        $this->assertEquals('FOOBAR', $response->getReasonPhrase());
    }

    public function testWithStatusDoesNotModifyOriginalStatusCode()
    {
        $this->response->withStatus(201);
        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testWithStatusDoesNotModifyOriginalReasonPhrase()
    {
        $this->response->withStatus(201, 'FOOBAR');
        $this->assertEquals('OK', $this->response->getReasonPhrase());
    }
}
