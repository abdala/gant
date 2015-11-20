<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\ClientInterface;
use Api\Exception\ApiException;
use Api\MockHandler;
use Api\Result;
use Api\Sdk;
use Api\Api\Service;

/**
 * @internal
 */
trait UsesServiceTrait
{
    private $_mock_handler;

    /**
     * Creates an instance of the AWS SDK for a test
     *
     * @param array $args
     *
     * @return Sdk
     */
    private function getTestSdk(array $args = [])
    {
        return new Sdk($args + [
            'version'     => 'latest',
            'retries'     => 0
        ]);
    }

    /**
     * Creates an instance of a service client for a test
     *
     * @param string $service
     * @param array  $args
     *
     * @return ClientInterface
     */
    private function getTestClient($service, array $args = [])
    {
        // Disable network access. If the INTEGRATION envvar is set, then this
        // disabling is not done.
        if (!isset($_SERVER['INTEGRATION'])
            && !isset($args['handler'])
            && !isset($args['http_handler'])
        ) {
            $this->_mock_handler = $args['handler'] = new MockHandler([]);
        }

        return $this->getTestSdk($args)->createClient($service);
    }

    /**
     * Queues up mock Result objects for a client
     *
     * @param ClientInterface $client
     * @param Result[]|array[]   $results
     * @param callable $onFulfilled Callback to invoke when the return value is fulfilled.
     * @param callable $onRejected  Callback to invoke when the return value is rejected.
     *
     * @return ClientInterface
     */
    private function addMockResults(
        ClientInterface $client,
        array $results,
        callable $onFulfilled = null,
        callable $onRejected = null
    ) {
        foreach ($results as &$res) {
            if (is_array($res)) {
                $res = new Result($res);
            }
        }

        $this->_mock_handler = new MockHandler($results, $onFulfilled, $onRejected);
        $client->getHandlerList()->setHandler($this->_mock_handler);

        return $client;
    }

    private function mockQueueEmpty()
    {
        return 0 === count($this->_mock_handler);
    }

    /**
     * Creates a mock CommandException with a given error code
     *
     * @param string $code
     * @param string $type
     * @param string|null $message
     *
     * @return ApiException
     */
    private function createMockApiException(
        $code = null,
        $type = null,
        $message = null
    ) {
        $code = $code ?: 'ERROR';
        $type = $type ?: 'Api\Exception\ApiException';

        $client = $this->getMockBuilder('Api\ClientInterface')
            ->setMethods(['getApi'])
            ->getMockForAbstractClass();

        $client->expects($this->any())
            ->method('getApi')
            ->will($this->returnValue(
                new Service(
                    [
                        'metadata' => [
                            'endpointPrefix' => 'foo',
                            'apiVersion' => 'version'
                        ]
                    ],
                    function () { return []; }
                )));

        return new $type(
            $message ?: 'Test error',
            $this->getMock('Api\CommandInterface'),
            [
                'message' => $message ?: 'Test error',
                'code'    => $code
            ]
        );
    }
}
