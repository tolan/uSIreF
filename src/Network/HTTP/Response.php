<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\IResponse;
use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Network\HTTP\Response\{Builder, Parser};

class Response extends AEntity implements IResponse {

    public $code;
    public $message;
    public $headers = [];

    /**
     * @var Worker\Wrapper
     */
    public $worker;

    /**
     * @var Parser
     */
    private $_parser;

    /**
     * @var string
     */
    private $_rendered;

    public function __construct() {
        $this->_parser = new Parser();
    }

    public function isReadCompleted(): bool {
        return $this->worker ? $this->worker->getStatus() === Worker\Worker::STATUS_DONE : !empty($this->message);
    }

    public function isWriteCompleted(): bool {
        $result = false;
        if ($this->worker) {
            $output = $this->worker->getOutput();
            $result = !empty($output) && strlen($output) === strlen($this->_rendered);
        } else {
            $builder = new Builder();
            $message = $builder->from($this->to())->build();
            $result  = !empty($this->message) && strlen($message) === strlen($this->_rendered);
        }

        return $result;
    }

    public function cleanup(): bool {
        $this->code      = null;
        $this->message   = null;
        $this->_rendered = null;
        $this->_parser->cleanup();
        if ($this->worker) {
            $this->worker->restart();
        }

        return true;
    }

    public function render(): ?string {
        $result = null;
        if ($this->isReadCompleted()) {
            if ($this->worker) {
                $result = $this->worker->getOutput();
            } else {
                $builder = new Builder();
                $result  = $builder->from($this->to())->build();
            }

            $this->_rendered = $result;
        }

        return $result;
    }

    public function addData(string $data): IResponse {
        $this->_parser->addData($data);
        if ($this->_parser->isCompleted()) {
            $this->from($this->_parser->to());
        }

        return $this;
    }
}
