<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Exception;

use Api\Exception\CouldNotCreateChecksumException;

/**
 * @covers Api\Exception\CouldNotCreateChecksumException
 */
class CouldNotCreateChecksumExceptionTest extends \PHPUnit_Framework_TestCase
{
    public function testUsesCorrectWords()
    {
        $e = new CouldNotCreateChecksumException('md5');
        $this->assertStringStartsWith('An md5', $e->getMessage());

        $e = new CouldNotCreateChecksumException('sha256');
        $this->assertStringStartsWith('A sha256', $e->getMessage());
    }
}
