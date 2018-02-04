<?php

namespace uSIreF\Common;

/**
 * This file defines class for configuration.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Config {

    /**
     * Configuration data
     *
     * @var array
     */
    private $_data = array();

    /**
     * Construct method for init default values.
     */
    public function __construct() {
        $this->set('root', dirname(__DIR__, 2));
    }

    /**
     * Magic method for basic function (set, get, has).
     *
     * @param string $name      Method name
     * @param array  $arguments Input data
     *
     * @return mixed
     *
     * @throws Exception Throws when called function doesn't exists.
     */
    public function __call($name, $arguments) {
        $method   = substr($name, 0, 3);
        $property = substr($name, 3);

        switch ($method) {
            case 'set':
                return $this->set($property, $arguments[0]);
            case 'get':
                return $this->get($property);
            case 'has':
                return $this->has($property);
            default:
                throw new Exception('Undefined function');
        }
    }

    /**
     * It loads configuration from JSON file.
     *
     * @param string $configFile Path to config file.
     *
     * @return Config
     *
     * @throws Exception Throws when config file doesn't exist.
     */
    public function loadJson(string $configFile): Config {
        if (file_exists($configFile)) {
            $content = file_get_contents($configFile);
            $config  = Utils\JSON::decode($content, JSON_OBJECT_AS_ARRAY);

            $this->fromArray($config);
        } else {
            throw new Exception('Config file doesn\'t exist.');
        }

        return $this;
    }

    /**
     * This method load configuration data from array
     *
     * @param array $array Configuration data
     *
     * @return Config
     */
    public function fromArray(array $array = null): Config {
        foreach ($array as $key => $item) {
            $this->set($key, $item);
        }

        return $this;
    }

    /**
     * This return all configuration data.
     *
     * @return array
     */
    public function toArray(): array {
        return $this->_data;
    }

    /**
     * Sets configuration option
     *
     * @param string $name Name of option
     * @param mixed  $data Configuration data
     *
     * @return Config
     */
    public function set(string $name, $data): Config {
        $key = lcfirst($name);

        if (array_key_exists($key, $this->_data) && is_array($this->_data[$key])) {
            $this->_data[$key] = array_unique(
                array_merge($this->_data[$key], (array)$data),
                \SORT_REGULAR
            );
        } else {
            $this->_data[$key] = $data;
        }

        return $this;
    }

    /**
     * Returns configuration option by name.
     *
     * @param string $name    Name of configuration option
     * @param mixed  $default Default value when config is not defined
     *
     * @return mixed Configuration data
     *
     * @throws Exception Throws when option is not defined.
     */
    public function get(string $name, $default=null) {
        return array_key_exists(lcfirst($name), $this->_data) ? $this->_data[lcfirst($name)] : $default;
    }

    /**
     * This checks that option is defined.
     *
     * @param string $name Name of configuration option
     *
     * @return boolean TRUE when option is defined
     */
    public function has(string $name): bool {
        return key_exists(lcfirst($name), $this->_data);
    }
}