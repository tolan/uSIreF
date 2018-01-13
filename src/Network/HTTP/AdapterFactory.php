<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Network\Exception;
use ArrayIterator;

class AdapterFactory extends AEntity {

    public $address = '0.0.0.0';

    /**
     * @var int|array
     */
    public $port = 80;

    private $_generator;

    public function __construct(string $address = '0.0.0.0', $port = 80) {
        if (!is_int($port) && !is_array($port)) {
            throw new Exception('Port must be integer or array range');
        }

        $this->address = $address;
        $this->port    = $port;
    }

    public function createAdapter(): ?Adapter {
        $result  = null;
        $address = $this->address;
        $port    = $this->_getPort();

        if ($address && $port) {
            $result = new Adapter($address, $port);
        }

        return $result;
    }

    private function _getPort() {
        $port = null;
        if (is_int($this->port)) {
            $port = $this->port;
        } elseif (is_array($this->port)) {
            $generator = $this->_getGenerator();
            $port      = current($generator);
            next($generator);
        }

        return $port;
    }

    private function _getGenerator() {
        if (!$this->_generator) {
            $this->_generator = new ArrayIterator($this->port);
        }

        return $this->_generator;
    }
}
