<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

use Api\Api\ApiProvider;
use Api\Api\Parser\Crc32ValidatingParser;
use Api\Api\Parser\JsonRpcParser;
use Api\Api\Service;
use Api\Command;
use Api\Exception\ApiException;
use GuzzleHttp\Psr7\Response;

/**
 * @covers Api\Api\Parser\Crc32ValidatingParser
 */
class Crc32ValidatingParserTest extends \PHPUnit_Framework_TestCase
{
    private function getWrapped()
    {
        $provider = ApiProvider::defaultProvider();
        $data = $provider('api', 'dynamodb', 'latest');
        $parser = new JsonRpcParser(new Service($data, $provider));
        return new Crc32ValidatingParser($parser);
    }

    public function testSkipsIfNoCrcHeader()
    {
        $wrapped = $this->getWrapped();
        $command = new Command('GetItem');
        $response = new Response(200, [], '{"foo": "bar"}');
        $this->assertInstanceOf('Api\ResultInterface', $wrapped($command, $response));
    }

    public function testThrowsWhenMismatch()
    {
        $wrapped = $this->getWrapped();
        $command = new Command('GetItem');
        $response = new Response(200, ['x-amz-crc32' => '123'], '{"foo": "bar"}');
        try {
            $wrapped($command, $response);
            $this->fail();
        } catch (ApiException $e) {
            $this->assertContains('crc32 mismatch. Expected 123, found 11124959', $e->getMessage());
            $this->assertTrue($e->isConnectionError());
        }
    }

    public function testNothingWhenValidChecksum()
    {
        $wrapped = $this->getWrapped();
        $command = new Command('GetItem');
        $response = new Response(200, ['x-amz-crc32' => '11124959'], '{"foo": "bar"}');
        $this->assertInstanceOf('Api\ResultInterface', $wrapped($command, $response));
    }
}
