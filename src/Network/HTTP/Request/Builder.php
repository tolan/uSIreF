<?php

namespace uSIreF\Network\HTTP\Request;

use uSIreF\Common\Abstracts\AEntity;

/**
 * This file defines class for build request message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Builder extends AEntity {

    /**
     * HTTP request method, e.g. "GET" or "POST".
     *
     * @var string
     */
    public $method;

    /**
     * path component of URI, without query string, after decoding %xx entities.
     *
     * @var string
     */
    public $uri;

    /**
     * version from the request line, e.g. "HTTP/1.1".
     *
     * @var string
     */
    public $httpVersion = 'HTTP/1.1';

    /**
     * Parsed query string.
     *
     * @var array
     */
    public $query       = [];

    /**
     * Associative array of HTTP headers.
     *
     * @var array
     */
    public $headers     = [];

    /**
     * HTTP request body.
     *
     * @var string
     */
    public $data;

    /**
     * Returns built request message.
     *
     * @return string
     */
    public function build(): string {
        return $this->method.' '.$this->_buildUri().' '.$this->httpVersion."\r\n"
            .$this->_buildHeaders()
            .$this->_buildBody();
    }

    /**
     * Build uri string with query.
     *
     * @return string
     */
    private function _buildUri(): string {
        return !empty($this->query) ? $this->uri.'?'.http_build_query($this->query) : $this->uri;
    }

    /**
     * Build request headers.
     *
     * @return string
     */
    private function _buildHeaders(): string {
        $result = '';
        foreach ($this->headers as $name => $value) {
            $result .= "$name: $value\r\n";
        }

        $result .= "\r\n";

        return $result;
    }

    /**
     * Build request body.
     *
     * @return string
     */
    private function _buildBody(): string {
        // TODO build POST data body
        return '';
    }
}
