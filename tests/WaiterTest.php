<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\Api\ApiProvider;
use Api\CommandInterface;
use Api\Exception\ApiException;
use Api\Result;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7;
use Psr\Http\Message\RequestInterface;

/**
 * @covers Api\Waiter
 */
class WaiterTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    public function testErrorOnBadConfig()
    {
        //@todo
    }

    public function testErrorOnBadBeforeCallback()
    {
        //@todo
    }

    public function testContinueWaitingOnHandlerError()
    {
        //@todo
    }

    public function testCanCancel()
    {
        //@todo
    }

    public function testCanWait()
    {
        //@todo
    }

    /**
     * @dataProvider getWaiterWorkflowTestCases
     */
    public function testWaiterWorkflow($results, $expectedException)
    {
        //@todo
    }

    public function getWaiterWorkflowTestCases()
    {
        return [
            [
                [
                    $this->createMockApiException('ResourceNotFoundException'),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'ACTIVE']]),
                ],
                null
            ],
            [
                [
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'DELETING']]),
                ],
                'The TableExists waiter entered a failure state.'
            ],
            [
                [
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                    new Result(['Table' => ['TableStatus' => 'CREATING']]),
                ],
                'The TableExists waiter failed after attempt #5.'
            ],
            [
                [
                    $this->createMockApiException(null, null, 'foo'),
                ],
                'The TableExists waiter entered a failure state. Reason: foo'
            ],
        ];
    }

    private function getApiProvider()
    {
        return function ($type) {
            if ($type == 'api') {
                return [
                    'operations' => ['DescribeTable' => ['input' => []]],
                    'metadata' => [
                        'endpointPrefix' => 'foo',
                        'protocol' => 'json',
                        'signatureVersion' => 'v4'
                    ],
                ];
            } else {
                return ['waiters' => [
                    'TableExists' =>  [
                        'delay' => function ($attempt) { return $attempt; },
                        'maxAttempts' => 5,
                        'operation' => 'DescribeTable',
                        'acceptors' => [
                            [
                                'state' => 'success',
                                'matcher' => 'path',
                                'argument' => 'Table.TableStatus',
                                'expected' => 'ACTIVE',
                            ],
                            [
                                'state' => 'retry',
                                'matcher' => 'error',
                                'expected' => 'ResourceNotFoundException',
                            ],
                            [
                                'state' => 'failed',
                                'matcher' => 'path',
                                'argument' => 'Table.TableStatus',
                                'expected' => 'DELETING',
                            ],
                        ],
                    ]
                ]];
            }
        };
    }

    /**
     * @dataProvider getMatchersTestCases
     */
    public function testMatchers($matcher, $result, $acceptor, $expected)
    {
        $waiter = new \ReflectionClass('Api\Waiter');
        $matcher = $waiter->getMethod($matcher);
        $matcher->setAccessible(true);
        $waiter = $waiter->newInstanceWithoutConstructor();

        $this->assertEquals($expected, $matcher->invoke($waiter, $result, $acceptor));
    }

    public function getMatchersTestCases()
    {
        return [
            [
                'matchesPath',
                null,
                [],
                false
            ],
            [
                'matchesPath',
                $this->getMockResult(['a' => ['b' => 'c']]),
                ['argument' => 'a.b', 'expected' => 'c'],
                true
            ],
            [
                'matchesPath',
                $this->getMockResult(['a' => ['b' => 'c']]),
                ['argument' => 'a', 'expected' => 'z'],
                false
            ],
            [
                'matchesPathAll',
                null,
                [],
                false
            ],
            [
                'matchesPathAll',
                $this->getMockResult([
                    'a' => [
                        ['b' => 'c'],
                        ['b' => 'c'],
                        ['b' => 'c']
                    ]
                ]),
                ['argument' => 'a[].b', 'expected' => 'c'],
                true
            ],
            [
                'matchesPathAll',
                $this->getMockResult(['a' => [
                    ['b' => 'c'],
                    ['b' => 'z'],
                    ['b' => 'c']
                ]]),
                ['argument' => 'a[].b', 'expected' => 'c'],
                false
            ],
            [
                'matchesPathAny',
                null,
                [],
                false
            ],
            [
                'matchesPathAny',
                $this->getMockResult([
                    'a' => [
                        ['b' => 'c'],
                        ['b' => 'd'],
                        ['b' => 'e']
                    ]
                ]),
                ['argument' => 'a[].b', 'expected' => 'c'],
                true
            ],
            [
                'matchesPathAny',
                $this->getMockResult([
                    'a' => [
                        ['b' => 'x'],
                        ['b' => 'y'],
                        ['b' => 'z']
                    ]
                ]),
                ['argument' => 'a[].b', 'expected' => 'c'],
                false
            ],
            [
                'matchesStatus',
                null,
                [],
                false
            ],
            [
                'matchesStatus',
                $this->getMockResult(),
                ['expected' => 200],
                true
            ],
            [
                'matchesStatus',
                $this->getMockResult(),
                ['expected' => 400],
                false
            ],
            [
                'matchesError',
                null,
                [],
                false
            ],
            [
                'matchesError',
                $this->getMockResult('InvalidData'),
                ['expected' => 'InvalidData'],
                true
            ],
            [
                'matchesError',
                $this->getMockResult('InvalidData'),
                ['expected' => 'Foo'],
                false
            ],
        ];
    }

    private function getMockResult($data = [])
    {
        if (is_string($data)) {
            return new ApiException('ERROR', $this->getMock('Api\CommandInterface'), [
                'code'   => $data,
                'result' => new Result(['@metadata' => ['statusCode' => 200]])
            ]);
        } else {
            return new Result($data + ['@metadata' => ['statusCode' => 200]]);
        }
    }
}
