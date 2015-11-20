<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

use Api\Api\ShapeMap;
use Api\Api\StructureShape;

/**
 * @covers \Api\Api\StructureShape
 */
class StructureShapeTest extends \PHPUnit_Framework_TestCase
{
    public function testReturnsWhenMembersAreEmpty()
    {
        $s = new StructureShape([], new ShapeMap([]));
        $this->assertFalse($s->hasMember('foo'));
        $this->assertSame([], $s->getMembers());
    }

    public function testReturnsMember()
    {
        $s = new StructureShape([
            'members' => ['foo' => ['type' => 'string']]
        ], new ShapeMap([]));
        $this->assertTrue($s->hasMember('foo'));
        $this->assertInstanceOf('Api\Api\Shape', $s->getMember('foo'));
        $this->assertEquals('string', $s->getMember('foo')->getType());
    }

    public function testReturnsAllMembers()
    {
        $s = new StructureShape([
            'members' => [
                'foo' => ['type' => 'string'],
                'baz' => ['type' => 'integer'],
            ]
        ], new ShapeMap([]));
        $members = $s->getMembers();
        $this->assertInternalType('array', $members);
        $this->assertInstanceOf('Api\Api\Shape', $members['foo']);
        $this->assertInstanceOf('Api\Api\Shape', $members['baz']);
        $this->assertEquals('string', $members['foo']->getType());
        $this->assertEquals('integer', $members['baz']->getType());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresMemberExists()
    {
        (new StructureShape([], new ShapeMap([])))->getMember('foo');
    }
}
