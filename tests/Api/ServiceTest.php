<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

use Api\Api\Service;
use Api\Test\UsesServiceTrait;

/**
 * @covers \Api\Api\Service
 */
class ServiceTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    public function testSetsDefaultValues()
    {
        $s = new Service([], function () { return []; });
        $this->assertSame([], $s['operations']);
        $this->assertSame([], $s['shapes']);
    }

    public function testImplementsArrayAccess()
    {
        $s = new Service(['metadata' => ['foo' => 'bar']], function () { return []; });
        $this->assertEquals('bar', $s['metadata']['foo']);
        $this->assertNull($s['missing']);
        $s['abc'] = '123';
        $this->assertEquals('123', $s['abc']);
        $this->assertSame([], $s['shapes']);
    }

    public function testReturnsApiData()
    {
        $s = new Service(
            [
                'metadata' => [
                    'serviceFullName' => 'foo',
                    'endpointPrefix'  => 'bar',
                    'apiVersion'      => 'baz',
                    'signingName'     => 'qux',
                    'protocol'        => 'yak',
                ]
            ],
            function () { return []; }
        );
        $this->assertEquals('foo', $s->getServiceFullName());
        $this->assertEquals('bar', $s->getEndpointPrefix());
        $this->assertEquals('baz', $s->getApiVersion());
        $this->assertEquals('qux', $s->getSigningName());
        $this->assertEquals('yak', $s->getProtocol());
    }

    public function testReturnsMetadata()
    {
        $s = new Service([], function () { return []; });
        $this->assertInternalType('array', $s->getMetadata());
        $s['metadata'] = [
            'serviceFullName' => 'foo',
            'endpointPrefix'  => 'bar',
            'apiVersion'      => 'baz'
        ];
        $this->assertEquals('foo', $s->getMetadata('serviceFullName'));
        $this->assertNull($s->getMetadata('baz'));
    }

    public function testReturnsIfOperationExists()
    {
        $s = new Service(
            ['operations' => ['foo' => ['input' => []]]],
            function () { return []; }
        );
        $this->assertTrue($s->hasOperation('foo'));
        $this->assertInstanceOf('Api\Api\Operation', $s->getOperation('foo'));
        $this->assertArrayHasKey('foo', $s->getOperations());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresOperationExists()
    {
        $s = new Service([], function () { return []; });
        $s->getOperation('foo');
    }

    public function testCanRetrievePaginationConfig()
    {
        $expected = [
            'input_token'  => 'a',
            'output_token' => 'b',
            'limit_key'    => 'c',
            'result_key'   => 'd',
            'more_results' => 'e',
        ];

        // Stub out the API provider
        $service = new Service([], function () use ($expected) {
            return ['pagination' => ['foo' => $expected]];
        });
        $this->assertTrue($service->hasPaginator('foo'));
        $actual = $service->getPaginatorConfig('foo');
        $this->assertSame($expected, $actual);
    }

    public function testLoadWaiterConfigs()
    {
        $api = new Service([], function () {
            return ['waiters' => ['Foo' => ['bar' => 'baz']]];
        });

        $this->assertTrue($api->hasWaiter('Foo'));
        $config = $api->getWaiterConfig('Foo');
        $this->assertEquals(['bar' => 'baz'], $config);

        $this->assertFalse($api->hasWaiter('Fizz'));
        $this->setExpectedException('UnexpectedValueException');
        $api->getWaiterConfig('Fizz');
    }

    public function errorParserProvider()
    {
        return [
            ['json', 'Api\Api\ErrorParser\JsonRpcErrorParser'],
            ['rest-json', 'Api\Api\ErrorParser\RestJsonErrorParser'],
            ['query', 'Api\Api\ErrorParser\XmlErrorParser'],
            ['rest-xml', 'Api\Api\ErrorParser\XmlErrorParser']
        ];
    }

    /**
     * @dataProvider errorParserProvider
     */
    public function testCreatesRelevantErrorParsers($p, $cl)
    {
        $this->assertInstanceOf($cl, Service::createErrorParser($p));
    }

    public function serializerDataProvider()
    {
        return [
            ['json', 'Api\Api\Serializer\JsonRpcSerializer'],
            ['rest-json', 'Api\Api\Serializer\RestJsonSerializer'],
            ['rest-xml', 'Api\Api\Serializer\RestXmlSerializer'],
            ['query', 'Api\Api\Serializer\QuerySerializer'],
            ['ec2', 'Api\Api\Serializer\QuerySerializer'],
        ];
    }

    /**
     * @dataProvider serializerDataProvider
     */
    public function testCreatesSerializer($type, $parser)
    {

    }

    public function parserDataProvider()
    {
        return [
            ['json', 'Api\Api\Parser\JsonRpcParser'],
            ['rest-json', 'Api\Api\Parser\RestJsonParser'],
            ['rest-xml', 'Api\Api\Parser\RestXmlParser'],
            ['query', 'Api\Api\Parser\XmlParser'],
            ['ec2', 'Api\Api\Parser\XmlParser'],
        ];
    }

    /**
     * @dataProvider parserDataProvider
     */
    public function testCreatesParsers($type, $parser)
    {

    }
}
