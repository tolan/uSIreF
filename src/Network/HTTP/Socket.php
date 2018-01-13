<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\{ISocket, IRequest, IResponse};
use uSIreF\Common\Abstracts\AEntity;

class Socket extends AEntity implements ISocket {

    private $_socket;

    /**
     * @var Request
     */
    private $_request;

    /**
     * @var Response
     */
    private $_response;

    private $_start;

    public function __construct($socket, Request $request = null, Response $response = null) {
        $this->_start    = microtime(true);
        $this->_socket   = $socket;
        $this->_request  = $request ?? new Request();
        $this->_response = $response ?? new Response();

        $this->_request->readSocketInfo($socket);
    }

    public function getTimeout(): float {
        return microtime(true) - $this->_start;
    }

    public function getSocket() {
        return $this->_socket;
    }

    public function getRequest(): IRequest {
        return $this->_request;
    }

    public function getResponse(): IResponse {
        return $this->_response;
    }
}
