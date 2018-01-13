<?php

namespace uSIreF\Common\Utils;

class JSON {

    public static function decode($string, $assoc = \JSON_OBJECT_AS_ARRAY) {
        return json_decode($string, $assoc);
    }

    public static function encode($data) {
        return json_encode($data);
    }
}
