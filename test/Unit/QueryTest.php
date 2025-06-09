<?php

namespace Test\Unit;

use PHPUnit\Framework\TestCase;
use PainlessPHP\Http\Message\Query;

class QueryTest extends TestCase
{
    private $parameters;
    private $query;

    public function setUp() : void
    {
        parent::setUp();

        $this->parameters = [
            'param1' => 'foo',
            'param2' => 'bar',
            'param3' => 'baz',
        ];

        $this->query = new Query($this->parameters);
    }

    public function testQueryCanBeConstructedFromParametersArray()
    {
        $this->assertInstanceOf(Query::class, $this->query);
    }

    public function testToArrayReturnsParameters()
    {
        $this->assertSame($this->parameters, $this->query->toArray());
    }

    public function testParametersCanBeAdded()
    {
        $appended = ['param3' => 'baz', 'param4' => 'foobar'];
        $this->query->addParameters($appended);
        $this->assertSame(array_merge($this->parameters, $appended), $this->query->toArray());
    }

    public function testParametersCanBeRemoved()
    {
        $removed = ['param2', 'param3'];
        $this->query->removeParameters($removed);
        $this->assertSame(['param1' => 'foo'], $this->query->toArray());
    }

    public function testHasParameterReturnsTrueForExistingParameter()
    {
        $this->assertTrue($this->query->hasParameter('param1'));
    }

    public function testHasParameterReturnsFalseForMissingParameter()
    {
        $this->assertFalse($this->query->hasParameter('missing'));
    }

    public function testGetParameterReturnsValueOfExistingParameter()
    {
        $this->assertSame('foo', $this->query->getParameter('param1'));
    }

    public function testGetParameterReturnsNullForMissingParameter()
    {
        $this->assertNull($this->query->getParameter('missing'));
    }

    public function testToStringReturnsQueryString()
    {
        $this->assertSame('param1=foo&param2=bar&param3=baz', (string)$this->query);
    }

    public function testFromQueryStringThrowsExceptionIfEqualsSignIsMissingForValuePair()
    {
        $this->expectExceptionMessage('Key-value pair is missing expected =');
        Query::createFromQueryString('param1foo&param2=bar&param3=baz');
    }

    public function testFromQueryStringCreatesQuery()
    {
        $query = Query::createFromQueryString('param1=foo&param2=bar&param3=baz');
        $this->assertSame($this->parameters, $query->toArray());
    }

    public function testFromQueryStringRemovesLeadingQuestionmarkCharacter()
    {
        $query = Query::createFromQueryString('?param1=foo&param2=bar&param3=baz');
        $this->assertSame($this->parameters, $query->toArray());
    }

    public function testFromQueryStringThrowsExceptionIfStringHasNonLeadingQuestionMarkCharacters()
    {
        $this->expectExceptionMessage('Non-leading ? character in query');
        Query::createFromQueryString('?param1=foo&param2=bar&param3?=baz');
    }

    public function testFromQueryStringAutomaticallyDecodesQuery()
    {
        $query = Query::createFromQueryString(urlencode('param1=https://foo.com'));
        $this->assertSame(['param1' => 'https://foo.com'], $query->toArray());
    }

    public function testFromUrlStringCreatesQuery()
    {
        $query = Query::createFromUrlString('https://google.com?param1=foo&param2=bar');
        $this->assertSame(['param1' => 'foo', 'param2' => 'bar'], $query->toArray());
    }

    public function testNestedArraysCanBeConvertedIntoString()
    {
        $query = new Query([
            'param1' => [
                'foo',
                'bar',
            ],
            'param2' => 'baz'
        ]);

        $this->assertSame('param1[0]=foo&param1[1]=bar&param2=baz', (string)$query);
    }

    public function testNestedArraysThrowExceptionIfNonStrConvertableValuesAreUsed()
    {
        $this->expectExceptionMessage("Parameter 'param1->1' could not be converted to string");

        $query = new Query([
            'param1' => [
                'foo',
                $this,
            ],
            'param2' => 'baz'
        ]);

        (string)$query;
    }

    public function testNestedArraysCanBeConvertedToArray()
    {
        $data = [
            'param1' => [
                'foo',
                'bar',
            ],
            'param2' => 'baz'
        ];

        $query = new Query($data);
        $this->assertSame($data, $query->toArray());
    }

    public function testQueryCanBeConstructedFromEmptyString()
    {
        $query = Query::createFromQueryString('');
        $this->assertInstanceOf(Query::class, $query);
    }
}
