<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\IRequest;
use uSIreF\Common\Abstracts\AEntity;

use uSIreF\Network\HTTP\Request\{Parser, Builder};

class Request extends AEntity implements IRequest {

    public $remoteAddr;   // IP address of client, as string
    public $method;       // HTTP method, e.g. "GET" or "POST"
    public $requestUri;   // original requested URI, with query string
    public $uri;          // path component of URI, without query string, after decoding %xx entities
    public $httpVersion;  // version from the request line, e.g. "HTTP/1.1"
    public $query   = []; // parsed query string
    public $headers = []; // associative array of HTTP headers
    public $data;

    /**
     * @var Parser
     */
    private $_parser;

    /**
     * @var Builder
     */
    private $_builder;

    public function __construct() {
        $this->_parser  = new Parser();
        $this->_builder = new Builder();
    }

    public function readSocketInfo($socket): Request {
        $remoteName = stream_socket_get_name($socket, true);
        if ($remoteName) {
            $portPos          = strrpos($remoteName, ':');
            $this->remoteAddr = $portPos ? substr($remoteName, 0, $portPos): $remoteName;
        }

        return $this;
    }

    /**
     * Provides cleanup content stream of HTTP request.
     *
     * @return Request
     */
    public function cleanup(): IRequest {
        $this->_parser->cleanup();

        return $this;
    }

    public function build(): string {
        return $this->_builder->from($this->to())->build();
    }

    /**
     * Returns the value of a HTTP header from this request (case-insensitive).
     *
     * @param string $name Name of HTTP value
     *
     * @return string
     */
    public function getHeader(string $name): ?string {
        return $this->_parser->getHeader($name);
    }

    /**
     * Returns true if a full HTTP request has been read by addData().
     *
     * @return bool
     */
    public function isReadCompleted(): bool {
        $result = $this->_parser->isCompleted();
        if ($result) {
            $this->from($this->_parser->to());
        }

        return $result;
    }

    /*
     * Reads a chunk of a HTTP request from a client socket.
     */
    public function addData(string $data): IRequest {
        $this->_parser->addData($data);
        $this->isReadCompleted();

        return $this;
    }
}
