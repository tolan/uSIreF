<?php

namespace uSIreF\Network;

use uSIreF\Network\Interfaces\{IClient, IResponse, IRequest, IMessage, Adapter};
use uSIreF\Network\Exception;

/**
 * This file defines class for client.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Client implements IClient {

    /**
     * @var Adapter\IClient
     */
    private $_adapter;

    /**
     * @var IMessage
     */
    private $_message;

    /**
     * @var string
     */
    private $_state = self::STATE_NONE;

    /**
     * @var IResponse
     */
    private $_response = null;

    /**
     * @var IRequest
     */
    private $_request = null;

    /**
     * @var int
     */
    private $_attempts = 20;

    /**
     * @var int
     */
    private $_restarts = 0;

    /**
     * Construct method for set client adapter.
     *
     * @param Adapter\IClient $adapter
     */
    public function __construct(Adapter\IClient $adapter) {
        $this->_adapter = $adapter;
    }

    /**
     * It returns response message from the client.
     *
     * @return IResponse
     */
    public function getOutput(): IResponse {
        $this->_update();
        return $this->_response;
    }

    /**
     * It returns current state of the client.
     *
     * @return string
     */
    public function getState(): string {
        $this->_update();
        return $this->_state;
    }

    /**
     * It sends request to client.
     *
     * @param IRequest $request Request message instance
     *
     * @return Client
     */
    public function request(IRequest $request): IClient {
        if ($this->_state === self::STATE_WAITNG) {
            throw new Exception('Client can\'t send new request. It is busy.');
        }

        $this->_request = $request;
        $this->_state   = self::STATE_CONNECTING;

        $this->_update();

        return $this;
    }

    /**
     * It resets client.
     *
     * @return Client
     */
    public function reset(): IClient {
        $this->_restarts++;
        $this->_message   = null;
        $this->_state    = self::STATE_NONE;
        $this->_response = null;
        $this->_request  = null;
        $this->_attempts = 20 * $this->_restarts;

        return $this;
    }

    /**
     * It updates client status and calls corresponding actions.
     *
     * @return Client
     */
    private function _update(): IClient {
        if (in_array($this->_state, [self::STATE_CONNECTING, self::STATE_ERROR]) && $this->_request) {
            try {
                $this->_message  = $this->_adapter->connect($this->_request, 1);
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

        if ($this->_message) {
            if ($this->_state === self::STATE_READY) {
                $this->_state = self::STATE_WAITNG;
                $this->_message->getConnection()->write($this->_message->getRequest()->build());
            }

            if ($this->_state === self::STATE_WAITNG && ($message = $this->_message->getConnection()->read())) {
                if ($this->_message->getResponse()->addData($message)->isReadCompleted()) {
                    $this->_state    = self::STATE_DONE;
                    $this->_response = $this->_message->getResponse();
                    $this->_message->getConnection()->close();
                }
            }
        }

        return $this;
    }
}
