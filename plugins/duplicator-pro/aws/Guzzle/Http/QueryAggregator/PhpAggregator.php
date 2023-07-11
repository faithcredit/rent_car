<?php
namespace DuplicatorPro\Guzzle\Http\QueryAggregator;

defined("ABSPATH") or die("");

use DuplicatorPro\Guzzle\Http\QueryString;

/**
 * Aggregates nested query string variables using PHP style []
 */
class PhpAggregator implements QueryAggregatorInterface
{
    public function aggregate($key, $value, QueryString $query)
    {
        $ret = array();

        foreach ($value as $k => $v) {
            $k = "{$key}[{$k}]";
            if (is_array($v)) {
                $ret = array_merge($ret, self::aggregate($k, $v, $query));
            } else {
                $ret[$query->encodeValue($k)] = $query->encodeValue($v);
            }
        }

        return $ret;
    }
}
