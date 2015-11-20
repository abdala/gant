<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Exception;

use Api\Command;
use Api\Exception\ApiException;
use Api\Exception\MultipartUploadException;
use Api\Multipart\UploadState;

/**
 * @covers Api\Exception\MultipartUploadException
 */
class MultipartUploadExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testCanCreateMultipartException($commandName, $status)
    {
        $state = new UploadState([]);
        $prev = new ApiException('WHATEVER', new Command($commandName));
        $exception = new MultipartUploadException($state, $prev);

        $this->assertEquals(
            "An exception occurred while {$status} a multipart upload.",
            $exception->getMessage()
        );
        $this->assertSame($state, $exception->getState());
        $this->assertSame($prev, $exception->getPrevious());
    }

    public function getTestCases()
    {
        return [
            ['CreateMultipartUpload', 'initiating'],
            ['InitiateMultipartUpload', 'initiating'],
            ['CompleteMultipartUpload', 'completing'],
            ['OtherCommands', 'performing'],
        ];
    }

    public function testCanCreateExceptionListingFailedParts()
    {
        $state = new UploadState([]);
        $failed = [
            1 => new ApiException('Bad digest.', new Command('UploadPart')),
            5 => new ApiException('Missing header.', new Command('UploadPart')),
            8 => new ApiException('Needs more love.', new Command('UploadPart')),
        ];

        $exception = new MultipartUploadException($state, $failed);

        $expected = <<<MSG
An exception occurred while uploading parts to a multipart upload. The following parts had errors:
- Part 1: Bad digest.
- Part 5: Missing header.
- Part 8: Needs more love.

MSG;

        $this->assertEquals($expected, $exception->getMessage());
    }
}
