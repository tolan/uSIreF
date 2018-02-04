<?php

namespace uSIreF\Common\Traits;

/**
 * This file defines trait for representing enumeration.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
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
    public static function getConstants(): array {
        $class = get_called_class();
        if (!isset(self::$_constants[$class])) {
            $refl = new \ReflectionClass($class);
            self::$_constants[$class] = $refl->getConstants();
        }

        return self::$_constants[$class];
    }
}
