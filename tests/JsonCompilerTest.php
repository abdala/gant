<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api\JsonCompiler;

/**
 * @covers Api\JsonCompiler
 */
class JsonCompilerTest extends \PHPUnit_Framework_TestCase
{
    private $models;

    public function setup()
    {
        $this->models = realpath(__DIR__ . '/Api/api_provider_fixtures');
    }

    public function testDecodesJsonToArray()
    {
        $c = new JsonCompiler();
        $data = $c->load($this->models . '/endpoints.json');
        $this->assertInternalType('array', $data);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testEnsuresFileExists()
    {
        $c = new JsonCompiler();
        $c->load($this->models . '/not_there.json');
    }
}
