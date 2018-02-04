<?php

namespace uSIreF\Common\Abstracts;

use uSIreF\Common\Utils\JSON;
use uSIreF\Common\Exception;
use ReflectionObject;
use ReflectionProperty;

/**
 * This file defines abstract class for representing entity.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
abstract class AEntity implements \JsonSerializable {

    /**
     * Object ID prefix.
     */
    const OBJECT_ID = '_objectId';

    /**
     * @var string
     */
    private $_objectId;

    /**
     * Clone magic method for set initial values.
     */
    public function __clone() {
        $this->_objectId = null;
    }

    /**
     * Transform input array data into entity properties.
     *
     * @param array $data Input data
     *
     * @return AEntity
     */
    public function from(array $data = []): AEntity {
        foreach ($data as $name => $value) {
            if (property_exists($this, $name)) {
                $this->_checkProperty($name)->$name = $value;
            }
        }

        return $this;
    }

    /**
     * Returns all public properties in one associative array.
     *
     * @return array
     */
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

    /**
     * Returns unique object id.
     *
     * @return string
     */
    public function getObjectId(): string {
        if (!$this->_objectId) {
            $this->_objectId = uniqid('objectId:');
        }

        return $this->_objectId;
    }

    /**
     * Getter magic method.
     *
     * @param string $name Name of property
     *
     * @return mixed
     */
    public function __get(string $name) {
        return $this->_checkProperty($name)->$name;
    }

    /**
     * Setter magic method.
     *
     * @param string $name  Name of property
     * @param mixed  $value Valud of property (optional)
     *
     * @return AEntity
     */
    public function __set(string $name, $value = null): AEntity {
        $this->_checkProperty($name)->$name = $value;

        return $this;
    }

    /**
     * Isset magic method.
     *
     * @param string $name Name of property
     *
     * @return bool
     */
    public function __isset(string $name): bool {
        return (bool)($this->$name);
    }

    /**
     * Unset magic method.
     *
     * @param string $name Name of property
     *
     * @return AEntity
     */
    public function __unset(string $name): AEntity {
        $this->_checkProperty($name)->$name = null;

        return $this;
    }

    /**
     * JSON serialize method implemented from JsonSerializable. It works with only public properties.
     *
     * @return string
     */
    public function jsonSerialize(): string {
        return JSON::encode($this->to());
    }

    /**
     * Returns array of properties defined by ReflectionProperty
     *
     * @param string $type Type of properties (one of ReflectionProperty::IS_*)
     *
     * @return [ReflectionProperty]
     */
    private function _getProperties($type = null): array {
        $reflection = new ReflectionObject($this);
        return $reflection->getProperties($type);
    }

    /**
     * Checks that property is defined.
     *
     * @param string $name Name of property
     *
     * @return AEntity
     *
     * @throws Exception
     */
    private function _checkProperty(string $name): AEntity {
        if ($name === '_objectId') {
            throw new Exception('You can\'t access to obejct ID in object "'.get_class($this).'". Use method getObjectId.');
        }

        if (!property_exists($this, $name)) {
            throw new Exception('Property "'.$name.'" doesn\'t exist in object "'.get_class($this).'".');
        }

        return $this;
    }
}
