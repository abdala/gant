<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Api;

/**
 * Base class representing a modeled shape.
 */
class Shape extends AbstractModel
{
    /**
     * Get a concrete shape for the given definition.
     *
     * @param array    $definition
     * @param ShapeMap $shapeMap
     *
     * @return mixed
     * @throws \RuntimeException if the type is invalid
     */
    public static function create(array $definition, ShapeMap $shapeMap)
    {
        static $map = [
            'structure' => 'Api\Api\StructureShape',
            'map'       => 'Api\Api\MapShape',
            'list'      => 'Api\Api\ListShape',
            'timestamp' => 'Api\Api\TimestampShape',
            'integer'   => 'Api\Api\Shape',
            'double'    => 'Api\Api\Shape',
            'float'     => 'Api\Api\Shape',
            'long'      => 'Api\Api\Shape',
            'string'    => 'Api\Api\Shape',
            'byte'      => 'Api\Api\Shape',
            'character' => 'Api\Api\Shape',
            'blob'      => 'Api\Api\Shape',
            'boolean'   => 'Api\Api\Shape'
        ];

        if (isset($definition['shape'])) {
            return $shapeMap->resolve($definition);
        }

        if (!isset($map[$definition['type']])) {
            throw new \RuntimeException('Invalid type: '
                . print_r($definition, true));
        }

        $type = $map[$definition['type']];

        return new $type($definition, $shapeMap);
    }

    /**
     * Get the type of the shape
     *
     * @return string
     */
    public function getType()
    {
        return $this->definition['type'];
    }

    /**
     * Get the name of the shape
     *
     * @return string
     */
    public function getName()
    {
        return $this->definition['name'];
    }
}
