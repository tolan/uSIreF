<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\IRequest;
use uSIreF\Common\Abstracts\AEntity;

use uSIreF\Network\HTTP\Adapter\Connection;
use uSIreF\Network\HTTP\Request\{Parser, Builder};

/**
 * This file defines class for HTTP Request message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Request extends AEntity implements IRequest {

    /**
     * IP address of client, as string.
     *
     * @var string
     */
    public $remoteAddr;

    /**
     * HTTP method, e.g. "GET" or "POST".
     *
     * @var string
     */
    public $method;

    /**
     * Original requested URI, with query string.
     *
     * @var string
     */
    public $requestUri;

    /**
     * Path component of URI, without query string, after decoding %xx entities.
     *
     * @var string
     */
    public $uri;

    /**
     * Version from the request line, e.g. "HTTP/1.1".
     *
     * @var string
     */
    public $httpVersion;

    /**
     * Parsed query string.
     *
     * @var array
     */
    public $query   = [];

    /**
     * Associative array of HTTP headers.
     *
     * @var array
     */
    public $headers = [];

    /**
     * @var Parser
     */
    private $_parser;

    /**
     * @var Builder
     */
    private $_builder;

    /**
     * Construct method.
     */
    public function __construct() {
        $this->_parser  = new Parser();
        $this->_builder = new Builder();
    }

    /**
     * It reads basic information about socket.
     *
     * @param Connection $connection Connection instance
     *
     * @return Request
     */
    public function readSocketInfo(Connection $connection): Request {
        $remoteName = $connection->getName();
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

    /**
     * It builds request message string.
     *
     * @return string
     */
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
     *
     * @return Request
     */
    public function addData(string $data): IRequest {
        $this->_parser->addData($data);
        $this->isReadCompleted();

        return $this;
    }
}