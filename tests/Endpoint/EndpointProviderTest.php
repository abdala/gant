<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\Endpoint\EndpointProvider;

/**
 * @covers Api\Endpoint\EndpointProvider
 */
class EndpointProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \Api\Exception\UnresolvedEndpointException
     */
    public function testThrowsWhenUnresolved()
    {
        EndpointProvider::resolve(function() {}, []);
    }

    /**
     * @expectedException \Api\Exception\UnresolvedEndpointException
     */
    public function testThrowsWhenNotArray()
    {
        EndpointProvider::resolve(function() { return 'foo'; }, []);
    }

    public function testCreatesDefaultProvider()
    {
        $p = EndpointProvider::defaultProvider(__DIR__ . '/../Api/api_provider_fixtures');
        $this->assertInstanceOf('Api\Endpoint\PatternEndpointProvider', $p);
    }

    public function testCreatesProviderFromPatterns()
    {
        $p = EndpointProvider::patterns([
            '*/*' => ['endpoint' => 'foo.com']
        ]);
        $this->assertInstanceOf('Api\Endpoint\PatternEndpointProvider', $p);
        $result = EndpointProvider::resolve($p, []);
        $this->assertEquals('https://foo.com', $result['endpoint']);
    }
}
