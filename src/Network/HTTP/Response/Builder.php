<?php

namespace uSIreF\Network\HTTP\Response;

use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Network\HTTP\Response\Code;

class Builder extends AEntity {

    public $code;
    public $message;
    public $headers = [];

    public function build(): string {
        $defaults = [
            'Content-Length' => strlen($this->message),
        ];

        $headers = array_merge($defaults, $this->headers);

        return Code::renderStatus($this->code).
            $this->_renderHeaders($headers).
            $this->message;
    }

    private function _renderHeaders(array $headers = []): string {
        $result = '';
        foreach ($headers as $name => $value) {
            $result .= "$name: $value\r\n";
        }

        $result .= "\r\n";

        return $result;
    }
}
