<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test;

use Api;
use Api\MockHandler;
use Api\Result;

class FunctionsTest extends \PHPUnit_Framework_TestCase
{
    public function testCreatesRecursiveDirIterator()
    {
        $iter = Api\recursive_dir_iterator(__DIR__);
        $this->assertInstanceOf('Iterator', $iter);
        $files = iterator_to_array($iter);
        $this->assertContains(__FILE__, $files);
    }

    public function testCreatesNonRecursiveDirIterator()
    {
        $iter = Api\dir_iterator(__DIR__);
        $this->assertInstanceOf('Iterator', $iter);
        $files = iterator_to_array($iter);
        $this->assertContains('FunctionsTest.php', $files);
    }

    public function testComposesOrFunctions()
    {
        $a = function ($a, $b) { return null; };
        $b = function ($a, $b) { return $a . $b; };
        $c = function ($a, $b) { return 'C'; };
        $comp = Api\or_chain($a, $b, $c);
        $this->assertEquals('+-', $comp('+', '-'));
    }

    public function testReturnsNullWhenNonResolve()
    {
        $called = [];
        $a = function () use (&$called) { $called[] = 'a'; };
        $b = function () use (&$called) { $called[] = 'b'; };
        $c = function () use (&$called) { $called[] = 'c'; };
        $comp = Api\or_chain($a, $b, $c);
        $this->assertNull($comp());
        $this->assertEquals(['a', 'b', 'c'], $called);
    }

    public function testCreatesConstantlyFunctions()
    {
        $fn = Api\constantly('foo');
        $this->assertSame('foo', $fn());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUsesJsonCompiler()
    {
        Api\load_compiled_json('/path/to/not/here.json');
    }

    public function testUsesPhpCompilationOfJsonIfPossible()
    {
        $soughtData = ['foo' => 'bar'];
        $jsonPath = sys_get_temp_dir() . '/some-file-name-' . time() . '.json';
        file_put_contents($jsonPath, 'INVALID JSON', LOCK_EX);
        file_put_contents(
            "$jsonPath.php",
            '<?php return ' . var_export($soughtData, true) . ';',
            LOCK_EX
        );

        $this->assertSame($soughtData, Api\load_compiled_json($jsonPath));
    }

    public function filterTest()
    {
        $data = [0, 1, 2, 3, 4];
        $result = \Api\filter($data, function ($v) { return $v % 2; });
        $this->assertEquals([1, 3], iterator_to_array($result));
    }

    public function mapTest()
    {
        $data = [0, 1, 2, 3, 4];
        $result = \Api\map($data, function ($v) { return $v + 1; });
        $this->assertEquals([1, 2, 3, 4, 5], iterator_to_array($result));
    }

    public function flatmapTest()
    {
        $data = [[1, 2], [3], [], [4, 5]];
        $xf = function ($value) { return array_sum($value); };
        $result = \Api\flatmap($data, $xf);
        $this->assertEquals([3, 3, 0, 9], iterator_to_array($result));
    }

    public function partitionTest()
    {
        $data = [0, 1, 2, 3, 4, 5];
        $result = \Api\partition($data, 2);
        $this->assertEquals([[1, 2], [3, 4], [5]], iterator_to_array($result));
    }

    public function testSerializesHttpRequests()
    {
        //@todo
    }
}
