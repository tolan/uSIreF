<?php

namespace uSIreF\Network\HTTP\Adapter;

use uSIreF\Network\HTTP\Adapter\Adapter;

/**
 * This file defines class for create adapter with client and server adapters.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Factory {

    /**
     * @var string
     */
    private $_host;

    /**
     * @var array
     */
    private $_port;

    /**
     * Construct method for set host address and port.
     *
     * @param string $host Host address
     * @param array  $port Port
     */
    public function __construct(string $host = '0.0.0.0', array $port = [80]) {
        $this->_host = $host;
        $this->_port = $port;
        reset($this->_port);
    }

    /**
     * Returns adapter with client and server adapters if possible.
     *
     * @return Adapter|null
     */
    public function getAdapter(): ?Adapter {
        $result  = null;
        $address = $this->_host;
        $port    = current($this->_port);
        next($this->_port);

        if ($address && $port) {
            $server = new Server($address, $port);
            $client = new Client($address, $port);
            $result = new Adapter($server, $client);
        }

        return $result;
    }
}
