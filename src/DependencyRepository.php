<?php
namespace Yaoi;

class DependencyRepository {
    public static $items = array();
    private static $sequenceId = 0;

    public static function add($dependency) {
        self::$items[++self::$sequenceId] = $dependency;
        return self::$sequenceId;
    }
}