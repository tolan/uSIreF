<?php

namespace uSIreF\Common;

use ReflectionClass;

/**
 * This file defines class for providing instance (include all dependencies) and collect them into one place.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Provider {

    /**
     * Storage for singleton instances.
     *
     * @var array
     */
    private $_instances = [];

    /**
     * Information about dependencies between classes.
     *
     * @var array
     */
    private $_dependencies = [];

    /**
     * Array for prevention cycling dependencies.
     *
     * @var array
     */
    private $_preventCycleDependencies = [];

    /**
     * Maps for resolve short name for class name.
     *
     * @var array
     */
    private $_serviceMap = [];

    /**
     * Construct method which initialize instance.
     *
     * @var Config $config Config instance
     *
     * @return void
     */
    public function __construct(Config $config = null) {
        $services       = [];
        $providerConfig = null;

        if ($config !== null) {
            $this->set($config);
            $providerConfig    = $config->get('provider', []);
            $this->_serviceMap = $providerConfig['serviceMap'] ?? [];
            $services          = $providerConfig['initServices'] ?? [];
        }

        $this->set($this);

        foreach ($services as $name => $service) {
            $instance = $this->get($service);
            $this->set($instance, $name);
        }
    }

    /**
     * Sets instance to stack for singleton usage.
     *
     * @param object $instance Some instance
     * @param string $name     Optional name for instance
     *
     * @return Provider
     *
     * @throws Exception Throws when instance is not object
     */
    public function set($instance, string $name = null): Provider {
        if (is_object($instance) === false) {
            throw new Exception('First parameter must be object.');
        }

        if ($name === null) {
            $name = get_class($instance);
        }

        $this->reset($name);
        $this->reset(get_class($instance));

        $this->_instances[] = [
            'name'      => ltrim($name, '\\'),
            'classname' => ltrim(get_class($instance), '\\'),
            'instance'  => $instance
        ];

        return $this;
    }

    /**
     * Unsets instance from stack for singleton usage.
     *
     * @param string $name Name of class
     *
     * @return Provider
     */
    public function reset(string $name = null): Provider {
        if ($name === null) {
            $this->_instances = [];
        } else {
            foreach($this->_instances as $key => $instanceInfo) {
                if ($instanceInfo['name'] === $name) {
                    unset($this->_instances[$key]);
                    break;
                }

                if ($instanceInfo['classname'] === $name) {
                    unset($this->_instances[$key]);
                    break;
                }
            }
        }

        return $this;
    }

    /**
     * Gets instance from stack. It find instance by name and then by classname.
     * If instance is not in stack then it create instance with dependencies and save it to stack (lazy load principle).
     *
     * @param string $name Name of class
     *
     * @return object ${name} Instance of $name
     */
    public function get(string $name) {
        $name = ltrim($name, '\\');
        if (isset($this->_serviceMap[$name])) {
            $name = $this->_serviceMap[$name];
        }

        $instance = $this->_getInstance($name);

        $this->_preventCycleDependencies = [];

        return $instance;
    }

    /**
     * Returns that provider has created requested instance.
     *
     * @param string $name Name of class
     *
     * @return boolean
     */
    public function has(string $name): bool {
        $has  = false;
        $name = ltrim($name, '\\');
        if (isset($this->_serviceMap[$name])) {
            $name = $this->_serviceMap[$name];
        }

        foreach ($this->_instances as $instance) {
            if ($name === $instance['name']) {
                $has = true;
                break;
            }

            if ($name === $instance['classname']) {
                $has = true;
                break;
            }
        }

        return $has;
    }

    /**
     * Return new instance.
     *
     * @param string  $name Name of class
     * @param boolean $deep Flag for create new dependencies
     *
     * @return object {$name}
     */
    public function prototype(string $name, bool $deep = false) {
        if ($deep === false) {
            $origin = null;
            if ($this->has($name)) {
                $origin = $this->get($name);
            }

            $this->reset($name);
            $prototype = $this->get($name);
            $this->reset($name);

            if ($origin) {
                $this->set($origin, $name);
            }
        } else {
            $instances = $this->_instances;
            $this->reset();
            $prototype        = $this->get($name);
            $this->_instances = $instances;
        }

        return $prototype;
    }

    /**
     * Gets instance from stack. It find instance by name and then by classname.
     *
     * @param string $name Name of class
     *
     * @return object {$name}
     */
    private function _getInstance(string $name) {
        $intance = null;
        foreach ($this->_instances as $item) {
            if ($name === $item['name']) {
                $intance = $item['instance'];
                break;
            }

            if ($name === $item['classname']) {
                $intance = $item['instance'];
                break;
            }
        }

        if (!$intance) {
            $intance = $this->_createInstance($name);
            $this->set($intance, $name);
        }

        return $intance;
    }

    /**
     * It creates instance with dependencies.
     *
     * @param string $className Name of class
     *
     * @return object {$name}
     *
     * @throws Exception Throws when dependecies are cycling or instance was not created.
     */
    private function _createInstance(string $className) {
        if (in_array($className, $this->_preventCycleDependencies)) {
            throw new Exception('Object has cycling dependencies: ['.join(', ', $this->_preventCycleDependencies).']!');
        }

        $this->_preventCycleDependencies[] = $className;

        $dependencies = $this->_getDependencies($className);
        $instances    = [];
        $arguments    = [];

        if (isset($dependencies['dependencies'])) {
            foreach ($dependencies['dependencies'] as $key => $dependency) {
                if (isset($dependency['className'])) {
                    $instances[$key] = $this->_getInstance($dependency['className']);
                }
            }

            foreach ($dependencies['dependencies'] as $key => $dependency) {
                if (isset($dependency['className'])) {
                    $arguments[$dependency['name']] = $instances[$key];
                } elseif (key_exists('defaultValue', $dependency)) {
                    $arguments[$dependency['name']] = $dependency['defaultValue'];
                }
            }
        }

        $instance = null;
        if (array_key_exists('dependencies', $dependencies) === false || empty($dependencies['dependencies'])) {
            if (isset($dependencies['method']) && $dependencies['method'] === 'getInstance') {
                $instance = forward_static_call_array([$className, 'getInstance'], []);
            } else {
                $instance = new $className();
            }
        } else {
            if ($dependencies['method'] === 'getInstance') {
                $instance = forward_static_call_array([$className, 'getInstance'], $arguments);
            } else {
                $class    = new ReflectionClass($className);
                $instance = $class->newInstanceArgs($arguments);
            }
        }

        if (is_object($instance) === false) {
            throw new Exception('Instance '.$className.' was not created!');
        }

        return $instance;
    }

    /**
     * Gets dependencies for create new class. It finds dependecies for standard constructor or singleton method
     * with name getInstance.
     *
     * @param string $className Name of class
     *
     * @return array Array with all dependencies.
     *
     * @throws Exception Throws when class doesn't exists.
     */
    private function _getDependencies($className): array {
        if (!isset($this->_dependencies[$className])) {
            if (class_exists($className)) {
                $reflClass = new ReflectionClass($className);
            } else {
                throw new Exception('Object doesn\'t exists: '.$className);
            }

            $methods      = $reflClass->getMethods();
            $searched     = false;
            $dependencies = [];

            foreach ($methods as $method) {
                if ($method->getName() === 'getInstance' && $method->isPublic()) {
                    $searched = true;
                    break;
                }
            }

            if ($searched === false) {
                foreach ($methods as $method) {
                    if ($method->isConstructor() && $method->isPublic()) {
                        $searched = true;
                        break;
                    }
                }
            }

            if ($searched === false) {
                $this->_dependencies[$className] = $dependencies;
            } else {
                $params = $method->getParameters();
                foreach ($params as $param) {
                    if ($param->getClass() !== null) {
                        $dependencies[$param->getPosition()] = [
                            'className' => $param->getClass()->getName(),
                            'position'  => $param->getPosition(),
                            'name'      => $param->getName()
                        ];
                    } else {
                        $dependencies[$param->getPosition()] = [
                            'defaultValue' => $param->isDefaultValueAvailable() ? $param->getDefaultValue() : null,
                            'position'     => $param->getPosition(),
                            'name'         => $param->getName()
                        ];
                    }
                }

                $path = (new ReflectionClass($className))->getFileName();

                $this->_dependencies[$className] = [
                    'method'       => $method->getName(),
                    'dependencies' => $dependencies,
                    'time'         => $path ? filemtime($path) : false
                ];
            }
        }

        return $this->_dependencies[$className];
    }
}
