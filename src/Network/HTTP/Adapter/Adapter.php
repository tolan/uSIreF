<?php

namespace uSIreF\Network\HTTP\Adapter;

/**
 * This file defines class for collection of server and client adapters.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Adapter {

    /**
     * @var Server
     */
    private $_server;

    /**
     * @var Client
     */
    private $_client;

    /**
     * Construct method for set server and client adapters.
     *
     * @param Server $server Server adapter
     * @param Client $client Client adapter
     */
    public function __construct(Server $server, Client $client) {
        $this->_server = $server;
        $this->_client = $client;
    }

    /**
     * Returns server adapter.
     *
     * @return Server
     */
    public function getServer(): Server {
        return $this->_server;
    }

    /**
     * Returns client adapter.
     *
     * @return Client
     */
    public function getClient(): Client {
        return $this->_client;
    }
}
