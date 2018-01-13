<?php

namespace uSIreF\Network\Utils;

use uSIreF\Network\Client;

class ClientCollector {

    /**
     * @var [Client]
     */
    private $_clients = [];

    public function add($name, Client $client) {
        $this->_clients[$name] = $client;
    }

    public function wait() {
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
