<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\{IMessage, IRequest, IResponse, Adapter};
use uSIreF\Common\Abstracts\AEntity;

/**
 * This file defines class for Message which facade connection, request and response.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Message extends AEntity implements IMessage {

    /**
     * @var Adapter\Connection
     */
    private $_connection;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var Response
     */
    private $_response;

    /**
     * @var float
     */
    private $_start;

    /**
     * Construct method for set connection, request and response.
     *
     * @param Adapter\IConnection $connection Connection instance
     * @param Request             $request    Request instance
     * @param Response            $response   Response instance
     */
    public function __construct(Adapter\IConnection $connection, Request $request, Response $response) {
        $this->_start      = microtime(true);
        $this->_connection = $connection;
        $this->_request    = $request;
        $this->_response   = $response;

        $this->_request->readSocketInfo($connection);
    }

    /**
     * Returns time from start time of the message.
     *
     * @return float
     */
    public function getTimeout(): float {
        return microtime(true) - $this->_start;
    }

    /**
     * Returns connection instance.
     *
     * @return Adapter\IConnection
     */
    public function getConnection(): Adapter\IConnection {
        return $this->_connection;
    }

    /**
     * Returns request message.
     *
     * @return IRequest
     */
    public function getRequest(): IRequest {
        return $this->_request;
    }

    /**
     * Returns response message.
     *
     * @return IResponse
     */
    public function getResponse(): IResponse {
        return $this->_response;
    }

    /**
     * It cleans the message.
     *
     * @return bool
     */
    public function cleanup(): bool {
        $this->_request->cleanup();
        $this->_response->cleanup();
        $this->_connection->close();

        return true;
    }
}
