<?php

namespace uSIreF\Network\HTTP\Response;

use uSIreF\Common\Abstracts\AEntity;

/**
 * This file defines class for parsing response message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Parser extends AEntity {

    /**
     * These constants define state of reading chunks.
     */
    const READ_CHUNK_HEADER  = 0;
    const READ_CHUNK_DATA    = 1;
    const READ_CHUNK_TRAILER = 2;

    /**
     * These constants define state of reading message.
     */
    const READ_HEADERS  = 0;
    const READ_CONTENT  = 1;
    const READ_COMPLETE = 2;

    /**
     * HTTP response code "200 OK".
     *
     * @var int
     */
    public $code;

    /**
     * Associative array of HTTP headers.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Response message.
     *
     * @var string
     */
    public $message;

    /**
     * Version from the request line, e.g. "HTTP/1.1".
     *
     * @var string
     */
    public $httpVersion;

    /**
     * Associative array of HTTP headers, with header names in lowercase.
     *
     * @var array
     */
    private $_lcHeaders = [];

    /**
     * The HTTP response line exactly as it came from the client.
     *
     * @var string
     */
    private $_requestLine;

    /**
     * Reading state.
     *
     * @var string
     */
    private $_state = self::READ_HEADERS;

    /**
     * Header buffer for keeping data between reading.
     *
     * @var string
     */
    private $_headerBuffer = '';

    /**
     * Whole length of content.
     *
     * @var int
     */
    private $_contentLength = 0;

    /**
     * Already read length of content.
     *
     * @var int
     */
    private $_contentLengthRead = 0;

    /**
     * Flag for chunked message.
     *
     * @var bool
     */
    private $_isChunked = false;

    /**
     * State of chunk.
     *
     * @var string
     */
    private $_chunkState = self::READ_CHUNK_HEADER;

    /**
     * Remaining length of chunk.
     *
     * @var int
     */
    private $_chunkLengthRemaining = 0;

    /**
     * Remaining trail of chunk.
     *
     * @var int
     */
    private $_chunkTrailerRemaining = 0;

    /**
     * Buffer for chunked header.
     *
     * @var string
     */
    private $_chunkHeaderBuffer = '';

    /**
     * Add received data to current state and resolve it.
     *
     * @param string $data Received data
     *
     * @return Parser
     */
    public function addData(string $data): Parser {
        switch ($this->_state) {
            case self::READ_HEADERS:
                $this->_readHeaders($data);
                // fallthrough to READ_CONTENT with leftover data
            case self::READ_CONTENT:
                if ($this->_isChunked) {
                    $this->_readChunkedData($data);
                } else {
                    $this->_contentLengthRead += strlen($data);
                    $this->message            .= $data;
                    if ($this->_contentLength - $this->_contentLengthRead <= 0) {
                        $this->_state  = self::READ_COMPLETE;
                    }
                }

                break;
        }

        return $this;
    }

    /**
     * Returns decoded header or null when it is not defined.
     *
     * @param string $name Header identificator
     *
     * @return string|null
     */
    public function getHeader(string $name): ?string {
        return $this->_lcHeaders[strtolower($name)] ?? null;
    }

    /**
     * Clean up of current state.
     *
     * @return Parser
     */
    public function cleanup(): Parser {
        $this->code    = null;
        $this->headers = [];
        $this->message = null;

        return $this;
    }

    /**
     * Returns that the reading of received data is completed.
     *
     * @return bool
     */
    public function isCompleted(): bool {
        return $this->_state === self::READ_COMPLETE;
    }

    /**
     * Reads header from received data.
     *
     * @param string $data Received data
     *
     * @return Parser
     */
    private function _readHeaders(&$data) {
        $this->_headerBuffer .= $data;
        $endHeaders           = strpos($this->_headerBuffer, "\r\n\r\n", 4);
        if ($endHeaders === false) {
            return $this;
        }

        // parse HTTP request line
        $endReq             = strpos($this->_headerBuffer, "\r\n");
        $this->_requestLine = substr($this->_headerBuffer, 0, $endReq);

        list($this->httpVersion, $code) = explode(' ', $this->_requestLine, 3);
        $this->code = (int)$code;

        // parse HTTP headers
        $startHeaders  = $endReq + 2;
        $headersStr    = substr($this->_headerBuffer, $startHeaders, $endHeaders - $startHeaders);
        $this->headers = $this->_readHeaderString($headersStr);

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

    /**
     * Reads chunked data from received data.
     *
     * @param string $data Received data
     *
     * @return Parser
     */
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

                    $this->_contentLength        += strlen($chunkData);
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

    /**
     * Reads headers from received headers string.
     *
     * @param string $headersStr Headers string
     *
     * @return array
     */
    private function _readHeaderString(string $headersStr): array {
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
