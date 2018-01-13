<?php

namespace uSIreF\Network\HTTP\Request;

use uSIreF\Common\Abstracts\AEntity;

class Builder extends AEntity {

    public $method;                   // HTTP request method, e.g. "GET" or "POST"
    public $uri;                      // path component of URI, without query string, after decoding %xx entities
    public $httpVersion = 'HTTP/1.1'; // version from the request line, e.g. "HTTP/1.1"
    public $query       = [];         // parsed query string
    public $headers     = [];         // associative array of HTTP headers
    public $data        = null;       // HTTP response messaage

    public function build() {
        return $this->method.' '.$this->_buildUri().' '.$this->httpVersion."\r\n"
            .$this->_renderHeaders()
            .$this->_buildBody();
    }

    private function _buildUri() {
        return !empty($this->query) ? $this->uri.'?'.http_build_query($this->query) : $this->uri;
    }

    private function _renderHeaders(): string {
        $result = '';
        foreach ($this->headers as $name => $value) {
            $result .= "$name: $value\r\n";
        }

        $result .= "\r\n";

        return $result;
    }

    private function _buildBody(): string {
        // TODO build POST data body
        return '';
    }
}
