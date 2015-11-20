<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

use Api\Api\DateTimeResult;

/**
 * @covers \Api\Api\DateTimeResult
 */
class DateTimeResultTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesFromEpoch()
    {
        $t = time();
        $d = DateTimeResult::fromEpoch($t);
        $this->assertEquals($t, $d->format('U'));
    }

    public function testCastToIso8601String()
    {
        $t = time();
        $d = DateTimeResult::fromEpoch($t);
        $this->assertSame(gmdate('c', $t), (string) $d);
    }

    public function testJsonSerialzesAsIso8601()
    {
        $t = time();
        $d = DateTimeResult::fromEpoch($t);
        $this->assertSame('"' . gmdate('c', $t). '"', json_encode($d));
    }
}
