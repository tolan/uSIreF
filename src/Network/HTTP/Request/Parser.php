<?php

namespace uSIreF\Network\HTTP\Request;

use uSIreF\Common\Abstracts\AEntity;

class Parser extends AEntity {

    const READ_CHUNK_HEADER  = 0;
    const READ_CHUNK_DATA    = 1;
    const READ_CHUNK_TRAILER = 2;

    const READ_HEADERS  = 0;
    const READ_CONTENT  = 1;
    const READ_COMPLETE = 2;

    public $method;       // HTTP request method, e.g. "GET" or "POST"
    public $requestUri;   // original requested URI, with query string
    public $uri;          // path component of URI, without query string, after decoding %xx entities
    public $httpVersion;  // version from the request line, e.g. "HTTP/1.1"
    public $query   = []; // parsed query string
    public $headers = []; // associative array of HTTP headers
    public $data    = '';

    private $_lcHeaders = []; // associative array of HTTP headers, with header names in lowercase
    private $_contentStream;  // stream containing content of HTTP request (e.g. POST data)
    private $_requestLine;    // The HTTP request line exactly as it came from the client

    // internal fields to track the state of reading the HTTP request
    private $_state             = self::READ_HEADERS;
    private $_headerBuffer      = '';
    private $_contentLength     = 0;
    private $_contentLengthRead = 0;

    private $_isChunked             = false;
    private $_chunkState            = self::READ_CHUNK_HEADER;
    private $_chunkLengthRemaining  = 0;
    private $_chunkTrailerRemaining = 0;
    private $_chunkHeaderBuffer     = '';

    public function __construct() {
        $this->_contentStream = fopen('data://text/plain,', 'r+b');
    }

    public function addData(string $data): Parser {
        switch ($this->_state) {
            case self::READ_HEADERS:
                $this->_readHeaders($data);
                // fallthrough to READ_CONTENT with leftover data
            case self::READ_CONTENT:
                if ($this->_isChunked) {
                    $this->_readChunkedData($data);
                } else {
                    fwrite($this->_contentStream, $data);
                    $this->_contentLengthRead += strlen($data);
                    $this->data               .= $data;
                    if ($this->_contentLength - $this->_contentLengthRead <= 0) {
                        $this->_state  = self::READ_COMPLETE;
                    }
                }

                break;
        }

        if ($this->_state == self::READ_COMPLETE) {
            fseek($this->_contentStream, 0);
        }

        return $this;
    }

    public function getHeader(string $name): ?string {
        return $this->_lcHeaders[strtolower($name)] ?? null;
    }

    public function cleanup(): Parser {
        fclose($this->_contentStream);
        $this->_contentStream = null;
        $this->data           = null;

        return $this;
    }

    public function isCompleted(): bool {
        return $this->_state === self::READ_COMPLETE;
    }

    private function _readHeaders(&$data) {
        $this->_headerBuffer .= $data;
        $endHeaders           = strpos($this->_headerBuffer, "\r\n\r\n", 4);
        if ($endHeaders === false) {
            return $this;
        }

        // parse HTTP request line
        $endReq             = strpos($this->_headerBuffer, "\r\n");
        $this->_requestLine = substr($this->_headerBuffer, 0, $endReq);

        list($this->method, $this->requestUri, $this->httpVersion) = explode(' ', $this->_requestLine, 3);

        $parsedUri = parse_url($this->requestUri);
        $this->uri = urldecode($parsedUri['path']);
        parse_str($parsedUri['query'] ?? '', $this->query);

        // parse HTTP headers
        $startHeaders  = $endReq + 2;
        $headersStr    = substr($this->_headerBuffer, $startHeaders, $endHeaders - $startHeaders);
        $this->headers = $this->_parseHeaderString($headersStr);

        $this->_lcHeaders = [];
        foreach ($this->headers as $key => $value) {
            $this->_lcHeaders[strtolower($key)] = $value;
        }

        if (isset($this->_lcHeaders['transfer-encoding'])) {
            $this->_isChunked     = $this->_lcHeaders['transfer-encoding'] === 'chunked';
            $this->_contentLength = 0;
            unset($this->_lcHeaders['transfer-encoding']);
            unset($this->headers['Transfer-Encoding']);
        } else {
            $this->_contentLength = (int)($this->_lcHeaders['content-length'] ?? 0);
        }

        $startContent        = $endHeaders + 4; // $endHeaders is before last \r\n\r\n
        $data                = substr($this->_headerBuffer, $startContent);
        $this->_headerBuffer = '';

        $this->_state = self::READ_CONTENT;

        return $this;
    }

    private function _readChunkedData(&$data) {
        while (isset($data[0])) { // keep processing chunks until we run out of data
            switch ($this->_chunkState) {
                case self::READ_CHUNK_HEADER:
                    $this->_chunkHeaderBuffer .= $data;
                    $data                      = '';
                    $endChunkHeader            = strpos($this->_chunkHeaderBuffer, "\r\n");
                    if ($endChunkHeader === false) { // still need to read more chunk header
                        break;
                    }

                    // done with chunk header
                    $chunkHeader       = substr($this->_chunkHeaderBuffer, 0, $endChunkHeader);
                    list($chunkLenHex) = explode(';', $chunkHeader, 2);

                    $this->_chunkLengthRemaining = intval($chunkLenHex, 16);
                    $this->_chunkState           = self::READ_CHUNK_DATA;
                    $data                        = substr($this->_chunkHeaderBuffer, $endChunkHeader + 2);
                    $this->_chunkHeaderBuffer    = '';

                    if ($this->_chunkLengthRemaining == 0) {
                        $this->_state = self::READ_COMPLETE;

                        $this->headers['Content-Length']    = $this->_contentLength;
                        $this->_lcHeaders['content-length'] = $this->_contentLength;

                        // TODO: this is where we should process trailers...
                        return;
                    }

                    // fallthrough to READ_CHUNK_DATA with leftover data
                case self::READ_CHUNK_DATA:
                    if (strlen($data) > $this->_chunkLengthRemaining) {
                        $chunkData = substr($data, 0, $this->_chunkLengthRemaining);
                    } else {
                        $chunkData = $data;
                    }

                    $this->_contentLength += strlen($chunkData);
                    fwrite($this->_contentStream, $chunkData);
                    $data                         = substr($data, $this->_chunkLengthRemaining);
                    $this->_chunkLengthRemaining -= strlen($chunkData);

                    if ($this->_chunkLengthRemaining == 0) {
                        $this->_chunkTrailerRemaining = 2;
                        $this->_chunkState = self::READ_CHUNK_TRAILER;
                    }

                    break;
                case self::READ_CHUNK_TRAILER: // each chunk ends in \r\n, which we ignore
                    $lenToRead                     = min(strlen($data), $this->_chunkTrailerRemaining);
                    $data                          = substr($data, $lenToRead);
                    $this->_chunkTrailerRemaining -= $lenToRead;

                    if ($this->_chunkTrailerRemaining == 0) {
                        $this->_chunkState = self::READ_CHUNK_HEADER;
                    }

                    break;
            }
        }

        return $this;
    }

    private function _parseHeaderString(string $headersStr): array {
        $headersArr = explode("\r\n", $headersStr);
        $headers    = [];
        foreach ($headersArr as $headerStr) {
            $headerArr = explode(': ', $headerStr, 2);
            if (sizeof($headerArr) === 2) {
                $headers[$headerArr[0]] = $headerArr[1];
            }
        }

        return $headers;
    }
}
