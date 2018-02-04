<?php

namespace uSIreF\Network\Utils;

use uSIreF\Network\Client;
use uSIreF\Network\Exception;

/**
 * This file defines class for collect clients.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ClientCollector {

    /**
     * @var [Client]
     */
    private $_clients = [];

    /**
     * It adds client into collection.
     *
     * @param string $name   Identification name
     * @param Client $client
     */
    public function add(string $name, Client $client) {
        if (array_key_exists($name, $this->_clients)) {
            throw new Exception('Client with name "'.$name.'" is already set.');
        }

        $this->_clients[$name] = $client;
    }

    /**
     * It waits until all clients are completed.
     *
     * @return array
     */
    public function wait(): array {
        $clients = $this->_clients;
        $result  = [];

        while(count($clients)) {
            foreach ($clients as $name => $client) {
                if (($output = $client->getOutput())) {
                    $result[$name] = $output;
                    unset($clients[$name]);
                }
            }
        }

        return $result;
    }
}
