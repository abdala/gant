<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\Api\ApiProvider;
use Api\CommandInterface;
use Api\Result;
use Psr\Http\Message\RequestInterface;

/**
 * @covers Api\ResultPaginator
 */
class ResultPaginatorTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    /**
     * @dataProvider getPaginatorIterationData
     */
    public function testStandardIterationWorkflow(
        array $config,
        array $results,
        $expectedRequestCount,
        array $expectedTableNames
    ) {
        //@todo
    }

    /**
     * @dataProvider getPaginatorIterationData
     */
    public function testAsyncWorkflow(
        array $config,
        array $results,
        $expectedRequestCount,
        array $expectedTableNames
    ) {
        //@todo
    }

    public function testNonIterator()
    {
        //@todo
    }

    /**
     * @return array Test data
     */
    public function getPaginatorIterationData()
    {
        return [
            // Single field token case
            [
                // Config
                ['input_token' => 'NextToken', 'output_token' => 'LastToken'],
                // Results
                [
                    new Result(['LastToken' => 'test2', 'TableNames' => ['test1', 'test2']]),
                    new Result(['LastToken' => 'test2', 'TableNames' => []]),
                    new Result(['TableNames' => ['test3']]),
                ],
                // Request count
                3,
                // Table names
                ['test1', 'test2', 'test3'],
            ],
            [
                // Config
                ['input_token' => ['NT1', 'NT2'], 'output_token' => ['LT1', 'LT2']],
                // Results
                [
                    new Result(['LT1' => 'foo', 'LT2' => 'bar', 'TableNames' => ['test1', 'test2']]),
                    new Result(['LT1' => 'foo', 'LT2' => 'bar', 'TableNames' => []]),
                    new Result(['TableNames' => ['test3']]),
                ],
                // Request count
                3,
                // Table names
                ['test1', 'test2', 'test3'],
            ],
            [
                // Config
                ['output_token' => null],
                // Results
                [new Result(['TableNames' => ['test1']]),],
                // Request count
                1,
                // Table names
                ['test1'],
            ],
            [
                // Config
                ['more_results' => 'IsTruncated'],
                // Results
                [new Result(['TableNames' => ['test1'], 'IsTruncated' => false]),],
                // Request count
                1,
                // Table names
                ['test1'],
            ]
        ];
    }

    public function testCanSearchOverResultsUsingFlatMap()
    {
        //@todo
    }

    public function testGracefullyHandlesSingleValueResults()
    {
        //@todo
    }

    public function testYieldsReturnedCallbackPromises()
    {
        //@todo
    }
}
