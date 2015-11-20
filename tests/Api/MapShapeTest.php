<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

use Api\Api\ShapeMap;
use Api\Api\MapShape;

/**
 * @covers \Api\Api\MapShape
 */
class MapShapeTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsValue()
    {
        $s = new MapShape(['value' => ['type' => 'string']], new ShapeMap([]));
        $v = $s->getValue();
        $this->assertInstanceOf('Api\Api\Shape', $v);
        $this->assertEquals('string', $v->getType());
        $this->assertSame($v, $s->getValue());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testFailsWhenValueIsMissing()
    {
        (new MapShape([], new ShapeMap([])))->getValue();
    }

    public function testReturnsKey()
    {
        $s = new MapShape(['key' => ['type' => 'string']], new ShapeMap([]));
        $k = $s->getKey();
        $this->assertInstanceOf('Api\Api\Shape', $k);
        $this->assertEquals('string', $k->getType());
    }

    public function testReturnsEmptyKey()
    {
        $s = new MapShape([], new ShapeMap([]));
        $this->assertInstanceOf('Api\Api\Shape', $s->getKey());
    }
}
