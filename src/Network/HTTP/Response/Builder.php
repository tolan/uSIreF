<?php

namespace uSIreF\Network\HTTP\Response;

use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Network\HTTP\Response\Code;

/**
 * This file defines class for build response message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Builder extends AEntity {

    /**
     * HTTP response code, e.g. 200 - OK
     *
     * @var int
     */
    public $code;

    /**
     * HTTP response message.
     *
     * @var string
     */
    public $message;

    /**
     * Response headers.
     *
     * @var array
     */
    public $headers = [];

    /**
     * Returns built response message.
     *
     * @return string
     */
    public function build(): string {
        $defaults = [
            'Content-Length' => strlen($this->message),
        ];

        $headers = array_merge($defaults, $this->headers);

        return Code::renderStatus($this->code).
            $this->_renderHeaders($headers).
            $this->message;
    }

    /**
     * Returns rendered header string.
     *
     * @param array $headers Headers data
     *
     * @return string
     */
    private function _renderHeaders(array $headers = []): string {
        $result = '';
        foreach ($headers as $name => $value) {
            $result .= "$name: $value\r\n";
        }

        $result .= "\r\n";

        return $result;
    }
}
