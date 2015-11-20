<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Multipart;

use Api\Command;
use Api\Exception\ApiException;
use Api\Exception\MultipartUploadException;
use Api\Multipart\UploadState;
use Api\Result;
use Api\Test\UsesServiceTrait;
use GuzzleHttp\Psr7;

/**
 * @covers Api\Multipart\AbstractUploader
 */
class AbstractUploaderTest extends \PHPUnit_Framework_TestCase
{
    use UsesServiceTrait;

    public function testTodo()
    {
        //@todo
    }
}
