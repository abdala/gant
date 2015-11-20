<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use GuzzleHttp\Psr7;
use Api\PhpHash;
use Api\HashingStream;

/**
 * @covers Api\HashingStream
 */
class HashingStreamTest extends \PHPUnit_Framework_TestCase
{
    public function testCanCreateRollingMd5()
    {
        $source = Psr7\stream_for('foobar');
        $hash = new PhpHash('md5');
        (new HashingStream($source, $hash))->getContents();
        $this->assertEquals(md5('foobar'), bin2hex($hash->complete()));
    }

    public function testCallbackTriggeredWhenComplete()
    {
        $source = Psr7\stream_for('foobar');
        $hash = new PhpHash('md5');
        $called = false;
        $stream = new HashingStream($source, $hash, function () use (&$called) {
            $called = true;
        });
        $stream->getContents();
        $this->assertTrue($called);
    }

    public function testCanOnlySeekToTheBeginning()
    {
        $source = Psr7\stream_for('foobar');
        $hash = new PhpHash('md5');
        $stream = new HashingStream($source, $hash);

        // Reading works fine
        $bytes = $stream->read(3);
        $this->assertEquals('foo', $bytes);

        // Seeking to 0 is fine
        $stream->seek(0);
        $stream->getContents();
        $this->assertEquals(md5('foobar'), bin2hex($hash->complete()));

        // Seeking arbitrarily is not fine
        $stream->seek(3);
    }
}
