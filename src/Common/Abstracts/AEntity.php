<?php

namespace uSIreF\Common\Abstracts;

use uSIreF\Common\Utils\JSON;
use uSIreF\Common\Exception;
use ReflectionObject;
use ReflectionProperty;

abstract class AEntity implements \JsonSerializable {

    const OBJECT_ID = '_objectId';

    private $_objectId;

    public function __clone() {
        $this->_objectId = null;
    }

    public function from(array $data = []): AEntity {
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->_checkProperty($name)->$name = $value;
            }
        }

        return $this;
    }

    public function to(): array {
        $result = [];
        foreach ($this->_getProperties(ReflectionProperty::IS_PUBLIC) as $property) { /* @var $property ReflectionProperty */
            $name = $property->getName();
            if ($name !== self::OBJECT_ID) {
                $result[$name] = $this->$name;
            }
        }

        return $result;
    }

    public function getObjectId(): string {
        if (!$this->_objectId) {
            $this->_objectId = uniqid('objectId:');
        }

        return $this->_objectId;
    }

    public function __get(string $name) {
        return $this->_checkProperty($name)->$name;
    }

    public function __set(string $name, $value = null): AEntity {
        $this->_checkProperty($name)->$name = $value;

        return $this;
    }

    public function __isset(string $name): bool {
        return (bool)($this->$name);
    }

    public function __unset(string $name): AEntity {
        $this->_checkProperty($name)->$name = null;

        return $this;
    }

    public function jsonSerialize(): string {
        return JSON::encode($this->to());
    }

    private function _getProperties($type = null): array {
        $reflection = new ReflectionObject($this);
        return $reflection->getProperties($type);
    }

    private function _checkProperty(string $name): AEntity {
        if ($name === '_objectId') {
            throw new Exception('You can\'t change obejct ID in object "'.get_class($this).'".');
        }

        if (!property_exists($this, $name)) {
            throw new Exception('Property "'.$name.'" doesn\'t exist in object "'.get_class($this).'".');
        }

        return $this;
    }
}
