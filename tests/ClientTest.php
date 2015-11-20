<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\Api\ErrorParser\JsonRpcErrorParser;
use Api\Client;
use Api\MockHandler;
use Api\Result;
use Api\WrappedHttpHandler;
use GuzzleHttp\Promise\RejectedPromise;

/**
 * @covers Api\Client
 */
class ClientTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    private function getApiProvider()
    {
        return function () {
            return [
                'metadata' => [
                    'protocol'       => 'query',
                    'endpointPrefix' => 'foo'
                ],
                'shapes' => []
            ];
        };
    }

    public function testHasGetters()
    {
        $config = [
            'handler'      => function () {},
            'endpoint'     => 'http://example.com',
            'serializer'   => function () {},
            'api_provider' => $this->getApiProvider(),
            'service'      => 'foo',
            'error_parser' => function () {},
            'version'      => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ];

        $client = new Client($config);
        $this->assertSame($config['handler'], $this->readAttribute($client->getHandlerList(), 'handler'));
        $this->assertEquals('foo', $client->getApi()->getEndpointPrefix());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Operation not found: Foo
     */
    public function testEnsuresOperationIsFoundWhenCreatingCommands()
    {
        $this->createClient()->getCommand('foo');
    }

    public function testReturnsCommandForOperation()
    {
        $client = $this->createClient([
            'operations' => [
                'foo' => [
                    'http' => ['method' => 'POST']
                ]
            ]
        ]);

        $this->assertInstanceOf(
            'Api\CommandInterface',
            $client->getCommand('foo')
        );
    }

    /**
     * @expectedException \Api\Exception\ApiException
     * @expectedExceptionMessage Error executing "foo" on "http://example.com"; AWS HTTP error: Baz Bar!
     */
    public function testWrapsExceptions()
    {
        $parser = function () {};
        $errorParser = new JsonRpcErrorParser();
        $h = new WrappedHttpHandler(
            function () {
                return new RejectedPromise([
                    'exception'        => new \Exception('Baz Bar!'),
                    'connection_error' => true,
                    'response'         => null
                ]);
            },
            $parser,
            $errorParser,
            'Api\Exception\ApiException'
        );

        $client = $this->createClient(
            ['operations' => ['foo' => ['http' => ['method' => 'POST']]]],
            ['handler' => $h]
        );

        $command = $client->getCommand('foo');
        $client->execute($command);
    }

    public function testChecksBothLowercaseAndUppercaseOperationNames()
    {
        $client = $this->createClient(['operations' => ['Foo' => [
            'http' => ['method' => 'POST']
        ]]]);

        $this->assertInstanceOf(
            'Api\CommandInterface',
            $client->getCommand('foo')
        );
    }

    public function testReturnsAsyncResultsUsingMagicCall()
    {
        $client = $this->createClient(['operations' => ['Foo' => [
            'http' => ['method' => 'POST']
        ]]]);
        $client->getHandlerList()->setHandler(new MockHandler([new Result()]));
        $result = $client->fooAsync();
        $this->assertInstanceOf('GuzzleHttp\Promise\PromiseInterface', $result);
    }

    public function testCanGetIterator()
    {
        //@todo
    }

    public function testCanGetIteratorWithoutDefinedPaginator()
    {
        //@todo
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetIteratorFailsForMissingConfig()
    {
        $client = $this->createClient();
        $client->getIterator('ListObjects');
    }

    public function testCanGetPaginator()
    {
        $client = $this->createClient(['pagination' => [
            'ListObjects' => [
                'input_token' => 'foo',
                'output_token' => 'foo',
            ]
        ]]);

        $this->assertInstanceOf(
            'Api\ResultPaginator',
            $client->getPaginator('ListObjects', ['Bucket' => 'foobar'])
        );
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetPaginatorFailsForMissingConfig()
    {
        $client = $this->createClient();
        $client->getPaginator('ListObjects');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Operation not found
     */
    public function testCanWaitSynchronously()
    {
        $client = $this->createClient(['waiters' => ['PigsFly' => [
            'acceptors'   => [],
            'delay'       => 1,
            'maxAttempts' => 1,
            'operation'   => 'DescribePigs',
        ]]]);

        $client->waitUntil('PigsFly');
    }

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testGetWaiterFailsForMissingConfig()
    {
        $client = $this->createClient();
        $client->waitUntil('PigsFly');
    }

    public function testGetWaiterPromisor()
    {
        //@todo
    }

    public function testCanGetEndpoint()
    {
        $client = $this->createClient();
        $this->assertEquals(
            'http://example.com',
            $client->getEndpoint()
        );
    }

    public function testSignsRequestsUsingSigner()
    {
        //@todo
    }

    private function createClient(array $service = [], array $config = [])
    {
        $apiProvider = function ($type) use ($service, $config) {
            if ($type == 'paginator') {
                return isset($service['pagination'])
                    ? ['pagination' => $service['pagination']]
                    : ['pagination' => []];
            } elseif ($type == 'waiter') {
                return isset($service['waiters'])
                    ? ['waiters' => $service['waiters'], 'version' => 2]
                    : ['waiters' => [], 'version' => 2];
            } else {
                if (!isset($service['metadata'])) {
                    $service['metadata'] = [];
                }
                $service['metadata']['protocol'] = 'query';
                return $service;
            }
        };

        return new Client($config + [
            'handler'      => new MockHandler(),
            'endpoint'     => 'http://example.com',
            'service'      => 'foo',
            'api_provider' => $apiProvider,
            'serializer'   => function () {},
            'error_parser' => function () {},
            'version'      => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ]);
    }
}
