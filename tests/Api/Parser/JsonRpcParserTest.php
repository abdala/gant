<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api\Parser;

use Api\Api\Operation;
use Api\Api\Parser\JsonParser;
use Api\Api\Parser\JsonRpcParser;
use Api\Api\Service;
use Api\Api\Shape;
use Api\CommandInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class JsonRpcParserTest extends \PHPUnit_Framework_TestCase
{
    public function testCanHandleNullResponses()
    {
        $operation = $this->getMockBuilder(Operation::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOutput'])
            ->getMock();
        $operation->expects($this->any())
            ->method('getOutput')
            ->withAnyParameters()
            ->willReturn(
                $this->getMockBuilder(Shape::class)
                    ->disableOriginalConstructor()
                    ->setMethods([])
                    ->getMock()
            );

        $service = $this->getMockBuilder(Service::class)
            ->disableOriginalConstructor()
            ->setMethods(['getOperation'])
            ->getMock();
        $service->expects($this->any())
            ->method('getOperation')
            ->withAnyParameters()
            ->willReturn($operation);

        $parser = $this->getMockBuilder(JsonParser::class)
            ->disableOriginalConstructor()
            ->setMethods(['parse'])
            ->getMock();
        $parser->expects($this->any())
            ->method('parse')
            ->withAnyParameters()
            ->willReturn(null);

        $instance = new JsonRpcParser($service, $parser);
        $result = $instance(
            $this->getMock(CommandInterface::class),
            new Response(200, [], json_encode(null))
        );
    }
}
