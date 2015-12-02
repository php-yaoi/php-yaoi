<?php
namespace Yaoi;

class DependencyRepository {
    private static $items = array();
    private static $sequenceId = 0;

    public static function add($dependency) {
        self::$items[++self::$sequenceId] = $dependency;
        return self::$sequenceId;
    }

    public static function get($refId)
    {
        return isset(self::$items[$refId]) ? self::$items[$refId] : null;
    }

    public static function delete($refId)
    {
        if (isset(self::$items[$refId])) {
            unset(self::$items[$refId]);
        }
    }
}