<?php

namespace uSIreF\Network;

use uSIreF\Network\Interfaces\{IServer, ISocket, IAdapter, IRouter};
use \Closure;

class Server implements IServer {

    const CONNECTION_TIMEOUT = 30 * 1000;

    /**
     * @var IAdapter
     */
    private $_adapter;

    /**
     * @var IRouter
     */
    private $_router;

    /**
     * @var [ISocket]
     */
    private $_sockets = [];

    public function __construct(IAdapter $adapter, IRouter $router) {
        $this->_adapter = $adapter;
        $this->_router  = $router;
    }

    public function __destruct() {
        foreach ($this->_sockets as $socket) {
            $this->_adapter->close($socket);
        }
    }

    public function run(Closure $callback = null): IServer {
        $callback = $callback ?? function() { return true; };
        $this->_adapter->startServer();

        while ($callback($this)) {
            $timeout = empty($this->_sockets) ? 100 : 1;
            if (($socket = $this->_adapter->select($timeout))) {
                $this->_sockets[$socket->getObjectId()] = $socket;
            }

            foreach ($this->_sockets as $socket) {
                if ($socket->getRequest()->isReadCompleted() === false) {
                    $this->_read($socket);
                }
            }

            foreach ($this->_sockets as $socket) {
                if ($socket->getResponse()->isReadCompleted() === true) {
                    $this->_write($socket);
                }
            }

            foreach ($this->_sockets as $socket) {
                if ($socket->getTimeout() > self::CONNECTION_TIMEOUT) {
                    $this->_close($socket);
                }
            }
        }

        return $this;
    }

    private function _read(ISocket $socket): Server {
        $data = $this->_adapter->read($socket);
        if (empty($data)) {
            $this->_close($socket);
        } elseif ($socket->getRequest()->addData($data)->isReadCompleted()) {
            $this->_router->resolve($socket->getRequest(), $socket->getResponse());
        }

        return $this;
    }

    private function _write(ISocket $socket): Server {
        $response = $socket->getResponse();
        if ($this->_adapter->write($socket, $response->render()) && $response->isWriteCompleted()) {
            $this->_close($socket);
        }

        return $this;
    }

    private function _close(ISocket $socket): Server {
        $this->_adapter->close($socket);
        unset($this->_sockets[$socket->getObjectId()]);
        $socket->getRequest()->cleanup();
        $socket->getResponse()->cleanup();

        return $this;
    }
}
