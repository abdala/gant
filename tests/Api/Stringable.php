<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Test\Api;

class Stringable
{
    public function __toString()
    {
        return 'Hello world';
    }
}