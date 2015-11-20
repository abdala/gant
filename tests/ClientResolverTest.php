<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\ClientResolver;
use Api\CommandInterface;
use Api\Credentials\CredentialProvider;
use Api\Credentials\Credentials;
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
            'version'      => 'latest'
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

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Credentials must be an
     */
    public function testValidatesCredentials()
    {
        $r = new ClientResolver([
            'credentials' => ClientResolver::getDefaultArguments()['credentials']
        ]);
        $r->resolve(['credentials' => []], new HandlerList());
    }

    public function testLoadsFromDefaultChainIfNeeded()
    {
        $key = getenv(CredentialProvider::ENV_KEY);
        $secret = getenv(CredentialProvider::ENV_SECRET);
        putenv(CredentialProvider::ENV_KEY . '=foo');
        putenv(CredentialProvider::ENV_SECRET . '=bar');
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'sqs',
            'version' => 'latest'
        ], new HandlerList());
        $c = call_user_func($conf['credentials'])->wait();
        $this->assertInstanceOf('Api\Credentials\CredentialsInterface', $c);
        $this->assertEquals('foo', $c->getAccessKeyId());
        $this->assertEquals('bar', $c->getSecretKey());
        putenv(CredentialProvider::ENV_KEY . "=$key");
        putenv(CredentialProvider::ENV_SECRET . "=$secret");
    }

    public function testCreatesFromArray()
    {
        $exp = time() + 500;
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'     => 'sqs',
            'version'     => 'latest',
            'credentials' => [
                'key'     => 'foo',
                'secret'  => 'baz',
                'token'   => 'tok',
                'expires' => $exp
            ]
        ], new HandlerList());
        $creds = call_user_func($conf['credentials'])->wait();
        $this->assertEquals('foo', $creds->getAccessKeyId());
        $this->assertEquals('baz', $creds->getSecretKey());
        $this->assertEquals('tok', $creds->getSecurityToken());
        $this->assertEquals($exp, $creds->getExpiration());
    }

    public function testCanDisableRetries()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'      => 's3',
            'version'      => 'latest',
            'retries'      => 0,
        ], new HandlerList());
    }

    public function testCanEnableRetries()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'      => 's3',
            'version'      => 'latest',
            'retries'      => 2,
        ], new HandlerList());
    }

    public function testCanCreateNullCredentials()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'sqs',
            'credentials' => false,
            'version' => 'latest'
        ], new HandlerList());
        $creds = call_user_func($conf['credentials'])->wait();
        $this->assertInstanceOf('Api\Credentials\Credentials', $creds);
        $this->assertEquals('anonymous', $conf['config']['signature_version']);
    }

    public function testCanCreateCredentialsFromProvider()
    {
        $c = new Credentials('foo', 'bar');
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'     => 'sqs',
            'credentials' => function () use ($c) {
                return \GuzzleHttp\Promise\promise_for($c);
            },
            'version'     => 'latest'
        ], new HandlerList());
        $this->assertSame($c, call_user_func($conf['credentials'])->wait());
    }

    public function testCanCreateCredentialsFromProfile()
    {
        $dir = sys_get_temp_dir() . '/.aws';
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $ini = <<<EOT
[foo]
aws_access_key_id = foo
aws_secret_access_key = baz
aws_session_token = tok
EOT;
        file_put_contents($dir . '/credentials', $ini);
        $home = getenv('HOME');
        putenv('HOME=' . dirname($dir));
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'sqs',
            'profile' => 'foo',
            'version' => 'latest'
        ], new HandlerList());
        $creds = call_user_func($conf['credentials'])->wait();
        $this->assertEquals('foo', $creds->getAccessKeyId());
        $this->assertEquals('baz', $creds->getSecretKey());
        $this->assertEquals('tok', $creds->getSecurityToken());
        unlink($dir . '/credentials');
        putenv("HOME=$home");
    }

    public function testCanUseCredentialsObject()
    {
        $c = new Credentials('foo', 'bar');
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'     => 'sqs',
            'credentials' => $c,
            'version'     => 'latest'
        ], new HandlerList());
        $this->assertSame($c, call_user_func($conf['credentials'])->wait());
    }

    public function testCanUseCredentialsCache()
    {
        $credentialsEnvironment = [
            'home' => 'HOME',
            'key' => CredentialProvider::ENV_KEY,
            'secret' => CredentialProvider::ENV_SECRET,
            'session' => CredentialProvider::ENV_SESSION,
            'profile' => CredentialProvider::ENV_PROFILE,
        ];
        $envState = [];
        foreach ($credentialsEnvironment as $key => $envVariable) {
            $envState[$key] = getenv($envVariable);
            putenv("$envVariable=");
        }

        $c = new Credentials('foo', 'bar');
        $cache = new LruArrayCache;
        $cache->set('aws_cached_credentials', $c);
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'     => 'sqs',
            'credentials' => $cache,
            'version'     => 'latest'
        ], new HandlerList());

        $cached = call_user_func($conf['credentials'])->wait();

        foreach ($credentialsEnvironment as $key => $envVariable) {
            putenv("$envVariable={$envState[$key]}");
        }

        $this->assertSame($c, $cached);
    }

    public function testCanUseCustomEndpointProviderWithExtraData()
    {
        $p = function () {
            return [
                'endpoint' => 'http://foo.com',
                'signatureVersion' => 'v4'
            ];
        };
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'sqs',
            'endpoint_provider' => $p,
            'version' => 'latest'
        ], new HandlerList());
        $this->assertEquals('v4', $conf['config']['signature_version']);
    }

    public function testAddsLoggerWithDebugSettings()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service'      => 'sqs',
            'retry_logger' => 'debug',
            'endpoint'     => 'http://us-east-1.foo.amazonaws.com',
            'version'      => 'latest'
        ], new HandlerList());
    }

    public function testAddsDebugListener()
    {
        $em = new HandlerList();
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'  => 'sqs',
            'debug'    => true,
            'endpoint' => 'http://us-east-1.foo.amazonaws.com',
            'version'  => 'latest'
        ], $em);
    }

    public function canSetDebugToFalse()
    {
        $em = new HandlerList();
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $r->resolve([
            'service'  => 'sqs',
            'debug'    => false,
            'endpoint' => 'http://us-east-1.foo.amazonaws.com',
            'version'  => 'latest'
        ], $em);
    }

    public function testCanAddHttpClientDefaultOptions()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $conf = $r->resolve([
            'service' => 'sqs',
            'version' => 'latest',
            'http'    => ['foo' => 'bar']
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
        $args = ClientResolver::getDefaultArguments()['version'];
        $r = new ClientResolver(['version' => $args]);
        $r->resolve(['service' => 'foo'], new HandlerList());
    }

    public function testAddsTraceMiddleware()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $list = new HandlerList();
        $r->resolve([
            'service'     => 'sqs',
            'credentials' => ['key' => 'a', 'secret' => 'b'],
            'version'     => 'latest',
            'debug'       => ['logfn' => function ($value) use (&$str) { $str .= $value; }]
        ], $list);
        $value = $this->readAttribute($list, 'interposeFn');
        $this->assertTrue(is_callable($value));
    }

    public function testAppliesUserAgent()
    {
        $r = new ClientResolver(ClientResolver::getDefaultArguments());
        $list = new HandlerList();
        $conf = $r->resolve([
            'service'     => 'sqs',
            'credentials' => ['key' => 'a', 'secret' => 'b'],
            'version'     => 'latest',
            'ua_append' => 'PHPUnit/Unit',
        ], $list);
        $this->assertArrayHasKey('ua_append', $conf);
        $this->assertInternalType('array', $conf['ua_append']);
        $this->assertContains('PHPUnit/Unit', $conf['ua_append']);
        $this->assertContains('aws-sdk-php/' . Sdk::VERSION, $conf['ua_append']);
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
            ->with('User-Agent', 'aws-sdk-php/' . Sdk::VERSION . ' MockBuilder');

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
