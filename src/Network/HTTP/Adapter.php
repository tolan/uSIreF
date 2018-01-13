<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\{IAdapter, ISocket, IRequest};
use uSIreF\Network\Exception;
use uSIreF\Common\Abstracts\AEntity;

class Adapter extends AEntity implements IAdapter {

    public $address;

    public $port;

    private $_server;

    public function __construct(string $address = '0.0.0.0', int $port = 80) {
        $this->address = $address;
        $this->port    = $port;
    }

    public function __destruct() {
        if ($this->_server) {
            @fclose($this->_server);
        }
    }

    public function startServer(): IAdapter {
        set_time_limit(0);

        $uri           = $this->address.':'.$this->port;
        $this->_server = @stream_socket_server('tcp://'.$uri, $errno, $errstr);
        if (!$this->_server) {
            throw new Exception('Could not start a web server on port '.$this->port.': ('.$errno.') '.$errstr);
        }

        stream_set_blocking($this->_server, 0);

        return $this;
    }

    public function select(float $timeout = null): ?ISocket {
        $toRead  = [$this->_server];
        $toWrite = [];
        $except  = null;

        list($sec, $usec) = $this->_getTimeout($timeout);

        @stream_select($toRead, $toWrite, $except, $sec, $usec);

        $socket = null;
        if (in_array($this->_server, $toRead)) { // new client connection
            $client = stream_socket_accept($this->_server);
            $socket = new Socket($client);
        }

        return $socket;
    }

    public function connect(IRequest $request, float $timeout = 0.001): ISocket {
        $uri     = preg_replace('#/+#', '/', $this->address.':'.$this->port.'/'.$request->uri);
        $uri    .= !empty($request->query) ? '?'.http_build_query($request->query) : '';
        $socket  = @stream_socket_client('tcp://'.$uri, $errno, $errstr, $timeout);

        if (!$socket) {
            throw new Exception('Could not connect to a web server "'.$uri.'": ('.$errno.') - '.$errstr);
        }

        stream_set_timeout($socket, 0, 250);

        return new Socket($socket, $request);
    }

    public function read(ISocket $socket): ?string {
        return @fread($socket->getSocket(), 30000);
    }

    public function write(ISocket $socket, string $message): bool {
        return (bool)@fwrite($socket->getSocket(), $message);
    }

    public function close(ISocket $socket): bool {
        return @fclose($socket->getSocket());
    }

    private function _getTimeout(float $timeout = null) {
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
