<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\PhpHash;

/**
 * @covers Api\PhpHash
 */
class PhpHashTest extends \PHPUnit_Framework_TestCase
{
    public function testHashesData()
    {
        $hash = new PhpHash('md5');
        $hash->update('foo');
        $hash->update('bar');
        $result = $hash->complete();
        $this->assertEquals(md5('foobar', true), $result);
    }

    public function testHashesDataAndBase64Encodes()
    {
        $hash = new PhpHash('md5', ['base64' => true]);
        $hash->update('foo');
        $hash->update('bar');
        $result = $hash->complete();
        $this->assertEquals(base64_encode(md5('foobar', true)), $result);
    }

    public function testCreatesNewHash()
    {
        $hash = new PhpHash('md5', ['base64' => true]);
        $hash->update('foo');
        $hash->complete();
        $hash->update('foo');
        $hash->update('bar');
        $result = $hash->complete();
        $this->assertEquals(base64_encode(md5('foobar', true)), $result);
        $this->assertSame($result, $hash->complete());
    }

    public function testCanResetHash()
    {
        $hash = new PhpHash('md5');
        $hash->update('foo');
        $hash->reset();
        $hash->update('bar');
        $this->assertEquals(md5('bar'), bin2hex($hash->complete()));
    }
}
