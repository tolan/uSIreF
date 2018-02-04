<?php

namespace uSIreF\Network\HTTP\Adapter;

use uSIreF\Network\HTTP\{Exception, Message, Request, Response};
use uSIreF\Network\Interfaces\IMessage;
use uSIreF\Network\Interfaces\Adapter\IServer;

/**
 * This file defines class for create server resource.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Server implements IServer {

    /**
     * @var string
     */
    private $_host;

    /**
     * @var int
     */
    private $_port;

    /**
     * @var Connection
     */
    private $_server;

    /**
     * Construct method for set host and port.
     *
     * @param string $host Hostname where server will listen to.
     * @param int    $port Port where server will listen to.
     */
    public function __construct(string $host = 'localhost', int $port = 80) {
        $this->_host = $host;
        $this->_port = $port;
    }

    /**
     * Creates and starts server.
     *
     * @return IServer
     *
     * @throws Exception
     */
    public function start(): IServer {
        set_time_limit(0);

        if ($this->_server) {
            throw new Exception('Could not start server again.');
        }

        $uri           = $this->_host.':'.$this->_port;
        $this->_server = @stream_socket_server('tcp://'.$uri, $errno, $errstr);
        if (!$this->_server) {
            throw new Exception('Could not start a web server on port '.$this->port.': ('.$errno.') '.$errstr);
        }

        stream_set_blocking($this->_server, 0);

        return $this;
    }

    /**
     * Select Message from server stream.
     *
     * @param float $timeout Timeout for select message in ms
     *
     * @return IMessage|null
     */
    public function select(float $timeout = null): ?IMessage {
        if (!$this->_server) {
            throw new Exception('Could not select because server is not started.');
        }

        $toRead  = [$this->_server];
        $toWrite = [];
        $except  = null;

        list($sec, $usec) = $this->_getTimeout($timeout);

        @stream_select($toRead, $toWrite, $except, $sec, $usec);

        $resource = null;
        if (in_array($this->_server, $toRead)) { // new client connection
            $socket   = stream_socket_accept($this->_server);
            $resource = new Message(new Connection($socket), new Request(), new Response());
        }

        return $resource;
    }

    /**
     * Returns computed timeout in seconds and microsecond from miliseconds.
     *
     * @param float $timeout Timeout in ms
     *
     * @return array
     */
    private function _getTimeout(float $timeout = null): array {
        $sec  = null;
        $usec = null;

        if ($timeout) {
            $sec     = floor($timeout / 1000);
            $timeout = $timeout - ($sec * 1000);
            $usec    = ($timeout * 1000);
        }

        return [$sec, $usec];
    }

}
