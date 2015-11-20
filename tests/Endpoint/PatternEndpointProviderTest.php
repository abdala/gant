<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\Endpoint\EndpointProvider;
use Api\Endpoint\PatternEndpointProvider;

/**
 * @covers Api\Endpoint\PatternEndpointProvider
 */
class PatternEndpointProviderTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsNullWhenUnresolved()
    {
        $e = new PatternEndpointProvider(['foo' => ['rules' => []]]);
        $this->assertNull($e(['service' => 'foo', 'region' => 'bar']));
    }

    public function endpointProvider()
    {
        return [
            [
                ['region' => 'us-east-1', 'service' => 's3'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-east-1', 'service' => 's3', 'scheme' => 'http'],
                ['endpoint' => 'http://api.domain.com']
            ],
            [
                ['region' => 'us-east-1', 'service' => 'sdb'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-west-2', 'service' => 's3'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-east-1', 'service' => 'iam'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'bar', 'service' => 'foo'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-gov-west-1', 'service' => 'iam'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-gov-west-1', 'service' => 's3'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'us-gov-baz', 'service' => 'foo'],
                ['endpoint' => 'https://api.domain.com']
            ],
            [
                ['region' => 'cn-north-1', 'service' => 's3'],
                [
                    'endpoint' => 'https://api.domain.com'
                ]
            ],
            [
                ['region' => 'cn-north-1', 'service' => 'ec2'],
                [
                    'endpoint' => 'https://api.domain.com'
                ]
            ]
        ];
    }

    /**
     * @dataProvider endpointProvider
     */
    public function testResolvesEndpoints($input, $output)
    {
        // Use the default endpoints file
        $p = EndpointProvider::defaultProvider(__DIR__ . '/../Api/api_provider_fixtures');
        $this->assertEquals($output, call_user_func($p, $input));
    }
}
