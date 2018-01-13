<?php

namespace uSIreF\Network;

use uSIreF\Network\Interfaces\{IClient, IResponse, IRequest, IAdapter, ISocket};
use uSIreF\Network\Exception;

class Client implements IClient {

    /**
     * @var IAdapter
     */
    private $_adapter;

    /**
     * @var ISocket
     */
    private $_socket;

    private $_state = self::STATE_NONE;

    private $_response = null;

    private $_request = null;

    private $_attempts = 20;

    private $_restarts = 0;

    public function __construct(IAdapter $adapter) {
        $this->_adapter = $adapter;
    }

    public function getOutput(): IResponse {
        $this->_update();
        return $this->_response;
    }

    public function getState(): string {
        $this->_update();
        return $this->_state;
    }

    public function request(IRequest $request): IClient {
        if ($this->_state === self::STATE_WAITNG) {
            throw new Exception('Client can\'t send new request. It is busy.');
        }

        $this->_request = $request;
        $this->_state   = self::STATE_CONNECTING;

        $this->_update();

        return $this;
    }

    public function reset() {
        $this->_restarts++;
        $this->_socket   = null;
        $this->_state    = self::STATE_NONE;
        $this->_response = null;
        $this->_request  = null;
        $this->_attempts = 20 * $this->_restarts;

        return $this;
    }

    private function _update(): IClient {
        if (in_array($this->_state, [self::STATE_CONNECTING, self::STATE_ERROR]) && $this->_request) {
            try {
                $this->_socket  = $this->_adapter->connect($this->_request, 1);
                $this->_state   = IClient::STATE_READY;
                $this->_request = null;
            } catch (Exception $e) {
                // TODO remove usleep and solve this by another way - this blocks the other connections
                usleep(1000);
                $this->_attempts--;
                if (!$this->_attempts) {
                    $this->_state = IClient::STATE_ERROR;
                }
            }
        }

        if ($this->_socket) {
            if ($this->_state === self::STATE_READY) {
                $this->_state = self::STATE_WAITNG;
                $this->_adapter->write($this->_socket, $this->_socket->getRequest()->build());
            }

            if ($this->_state === self::STATE_WAITNG && ($message = $this->_adapter->read($this->_socket))) {
                if ($this->_socket->getResponse()->addData($message)->isReadCompleted()) {
                    $this->_state    = self::STATE_DONE;
                    $this->_response = $this->_socket->getResponse();
                    $this->_adapter->close($this->_socket);
                }
            }
        }

        return $this;
    }
}
