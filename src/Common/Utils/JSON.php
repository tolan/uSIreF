<?php

namespace uSIreF\Common\Utils;

/**
 * This file defines class for JSON utility methods.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class JSON {

    /**
     * Returns decoded data from JSON string.
     *
     * @param string $string JSON string
     * @param int    $assoc  JSON association flag
     *
     * @return mixed
     */
    public static function decode(string $string, $assoc = \JSON_OBJECT_AS_ARRAY) {
        return json_decode($string, $assoc);
    }

    /**
     * Returns encoded JSON string from JSON seriazable data.
     *
     * @param mixed $data JSON seriazable data
     *
     * @return string
     */
    public static function encode($data): string {
        return json_encode($data);
    }
}
