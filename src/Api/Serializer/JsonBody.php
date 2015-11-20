<?php
/**
 * This is a modified version of original AWS SDK PHP file.
 * https://github.com/aws/aws-sdk-php
 */
namespace Api\Api\Serializer;

use Api\Api\Service;
use Api\Api\Shape;
use Api\Api\TimestampShape;

/**
 * Formats the JSON body of a JSON-REST or JSON-RPC operation.
 * @internal
 */
class JsonBody
{
    private $api;

    public function __construct(Service $api)
    {
        $this->api = $api;
    }

    /**
     * Gets the JSON Content-Type header for a service API
     *
     * @param Service $service
     *
     * @return string
     */
    public static function getContentType(Service $service)
    {
        return 'application/json';
    }

    /**
     * Builds the JSON body based on an array of arguments.
     *
     * @param Shape $shape Operation being constructed
     * @param array $args  Associative array of arguments
     *
     * @return string
     */
    public function build(Shape $shape, array $args)
    {
        $result = json_encode($this->format($shape, $args));

        return $result == '[]' ? '{}' : $result;
    }

    private function format(Shape $shape, $value)
    {
        switch ($shape['type']) {
            case 'structure':
                $data = [];
                foreach ($value as $k => $v) {
                    if ($v !== null && $shape->hasMember($k)) {
                        $valueShape = $shape->getMember($k);
                        $data[$valueShape['locationName'] ?: $k]
                            = $this->format($valueShape, $v);
                    }
                }
                return $data;

            case 'list':
                $items = $shape->getMember();
                foreach ($value as &$v) {
                    $v = $this->format($items, $v);
                }
                return $value;

            case 'map':
                if (empty($value)) {
                    return new \stdClass;
                }
                $values = $shape->getValue();
                foreach ($value as &$v) {
                    $v = $this->format($values, $v);
                }
                return $value;

            case 'blob':
                return base64_encode($value);

            case 'timestamp':
                return TimestampShape::format($value, 'unixTimestamp');

            default:
                return $value;
        }
    }
}
