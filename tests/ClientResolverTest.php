<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\ClientResolver;
use Api\CommandInterface;
use Api\LruArrayCache;
use Api\HandlerList;
use Api\Sdk;
use Api\WrappedHttpHandler;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\RequestInterface;

/**
 * @covers Api\ClientResolver
 */
class ClientResolverTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Missing required client configuration options
     */
    public function testEnsuresRequiredArgumentsAreProvided()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([], new HandlerList());
    }

    public function testAddsValidationSubscriber()
    {
        //@todo
    }

    
    public function testCanDisableValidation()
    {
        //@todo
    }

    public function testAppliesApiProvider()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $provider = function () {
            return ['metadata' => ['protocol' => 'query']];
        };
        $conf = $r->resolve([
            'service'      => 'dynamodb',
            'api_provider' => $provider,
            'version'      => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures',
            ''
        ], new HandlerList());
        $this->assertArrayHasKey('api', $conf);
        $this->assertArrayHasKey('error_parser', $conf);
        $this->assertArrayHasKey('serializer', $conf);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid configuration value provided for "foo". Expected string, but got int(-1)
     */
    public function testValidatesInput()
    {
        $r = new ClientResolver([
            'foo' => [
                'type'  => 'value',
                'valid' => ['string']
            ]
        ]);
        $r->resolve(['foo' => -1], new HandlerList());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid configuration value provided for "foo". Expected callable, but got string(1) "c"
     */
    public function testValidatesCallables()
    {
        $r = new ClientResolver([
            'foo' => [
                'type'   => 'value',
                'valid'  => ['callable']
            ]
        ]);
        $r->resolve(['foo' => 'c'], new HandlerList());
    }

    public function testCanDisableRetries()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'      => 'dynamodb',
            'version'      => 'latest',
            'retries'      => 0,
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], new HandlerList());
    }

    public function testCanEnableRetries()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'      => 'dynamodb',
            'version'      => 'latest',
            'retries'      => 2,
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], new HandlerList());
    }

    public function testAddsLoggerWithDebugSettings()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'      => 'dynamodb',
            'retry_logger' => 'debug',
            'endpoint'     => 'http://us-east-1.foo.amazonaws.com',
            'version'      => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], new HandlerList());
    }

    public function testAddsDebugListener()
    {
        $em = new HandlerList();
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'  => 'dynamodb',
            'debug'    => true,
            'endpoint' => 'http://us-east-1.foo.amazonaws.com',
            'version'  => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], $em);
    }

    public function canSetDebugToFalse()
    {
        $em = new HandlerList();
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'  => 'dynamodb',
            'debug'    => false,
            'endpoint' => 'http://us-east-1.foo.amazonaws.com',
            'version'  => 'latest',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], $em);
    }

    public function testCanAddHttpClientDefaultOptions()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'dynamodb',
            'version' => 'latest',
            'http'    => ['foo' => 'bar'],
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], new HandlerList());
        $this->assertEquals('bar', $conf['http']['foo']);
    }

    public function testCanAddConfigOptions()
    {
        //@todo
    }

    public function testSkipsNonRequiredKeys()
    {
        $r = new ClientResolver([
            'foo' => [
                'valid' => ['int'],
                'type'  => 'value'
            ]
        ]);
        $r->resolve([], new HandlerList());
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage A "version" configuration value is required
     */
    public function testHasSpecificMessageForMissingVersion()
    {
        $dir = __DIR__ . '/Api/api_provider_fixtures';
        $args = ClientResolver::getDefaultArguments()['version'];
        $r = new ClientResolver(['version' => $args]);
        $r->resolve(['service' => 'foo', 'modelsDir' => $dir], new HandlerList());
    }

    public function testAddsTraceMiddleware()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $list = new HandlerList();
        $r->resolve([
            'service'     => 'dynamodb',
            'version'     => 'latest',
            'debug'       => ['logfn' => function ($value) use (&$str) { $str .= $value; }],
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], $list);
        $value = $this->readAttribute($list, 'interposeFn');
        $this->assertTrue(is_callable($value));
    }

    public function testAppliesUserAgent()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $list = new HandlerList();
        $conf = $r->resolve([
            'service'     => 'dynamodb',
            'version'     => 'latest',
            'ua_append' => 'PHPUnit/Unit',
            'modelsDir'    => __DIR__ . '/Api/api_provider_fixtures'
        ], $list);
        $this->assertArrayHasKey('ua_append', $conf);
        $this->assertInternalType('array', $conf['ua_append']);
        $this->assertContains('PHPUnit/Unit', $conf['ua_append']);
        $this->assertContains('generic-api-php/' . Sdk::VERSION, $conf['ua_append']);
    }

    public function testUserAgentAlwaysStartsWithSdkAgentString()
    {
        $command = $this->getMockBuilder(CommandInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $request = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $request->expects($this->once())
            ->method('getHeader')
            ->with('User-Agent')
            ->willReturn(['MockBuilder']);

        $request->expects($this->once())
            ->method('withHeader')
            ->with('User-Agent', 'generic-api-php/' . Sdk::VERSION . ' MockBuilder');

        $args = [];
        $list = new HandlerList(function () {});
        ClientResolver::_apply_user_agent([], $args, $list);
        call_user_func($list->resolve(), $command, $request);
    }

    public function malformedEndpointProvider()
    {
        return [
            ['www.amazon.com'], // missing protocol
            ['https://'], // missing host
        ];
    }

    /**
     * @dataProvider malformedEndpointProvider
     * @param $endpoint
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Endpoints must be full URIs and include a scheme and host
     */
    public function testRejectsMalformedEndpoints($endpoint)
    {
        $list = new HandlerList();
        $args = [];
        ClientResolver::_apply_endpoint($endpoint, $args, $list);
    }
}
