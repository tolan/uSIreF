<?php

namespace uSIreF\Common\Traits;

trait TEnum {

    /**
     * Storage for enums
     *
     * @var array
     */
    private static $_constants = array();

    /**
     * Returns all constants from enum.
     *
     * @return array
     */
    public static function getConstants() {
        $class = get_called_class();
        if (!isset(self::$_constants[$class])) {
            $refl = new \ReflectionClass($class);
            self::$_constants[$class] = $refl->getConstants();
        }

        return self::$_constants[$class];
    }
}
