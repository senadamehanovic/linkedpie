<?php

namespace linkinbio\Base\Helpers;

class IterableUtil
{
    public static function iterable_map(iterable $items, \Closure $function, bool $return_item_on_null = true) : \Generator
    {
        foreach ($items as $item) {
            $new = $function($item);
            if ($new == null) {
                if ($return_item_on_null) {
                    (yield $item);
                }
            } else {
                (yield $new);
            }
        }
    }
    public static function in_iterable(iterable $items, \Closure $function)
    {
        foreach ($items as $item) {
            if ($function($item)) {
                return true;
            }
        }
        return false;
    }
    /**
     * @param iterable $items items to  join
     * @param \Closure $function function that takes value as first and key
     *                      as second parameter for each item and return string
     * @return string the concatenated string
     */
    public static function join(iterable $items, \Closure $function, string $seperator = "")
    {
        $r = [];
        foreach ($items as $k => $v) {
            $r[] = $function($v, $k);
        }
        return implode($seperator, $r);
    }
    /**
     * @param iterable $items
     * @param \Closure $function if the key needs to be changed use $key by reference,
     *          value is passed as  first argument
     * @return array
     */
    public static function map_to_kv_array(iterable $items, \Closure $function)
    {
        $r = [];
        foreach ($items as $k => $v) {
            $v = $function($v, $k);
            // change by reference
            $r[$k] = $v;
        }
        return $r;
    }
}