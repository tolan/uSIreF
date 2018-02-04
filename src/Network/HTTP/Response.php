<?php

namespace uSIreF\Network\HTTP;

use uSIreF\Network\Interfaces\IResponse;
use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Network\HTTP\Response\{Builder, Parser};

/**
 * This file defines class for HTTP Response message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Response extends AEntity implements IResponse {

    /**
     * Response message code e.g. 200
     *
     * @var int
     */
    public $code;

    /**
     * Body message.
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
     * @var Worker\Wrapper|Worker\Worker
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

    /**
     * Construct metrhod.
     */
    public function __construct() {
        $this->_parser = new Parser();
    }

    /**
     * It returns that the reading of response is completed.
     *
     * @return bool
     */
    public function isReadCompleted(): bool {
        return $this->worker ? $this->worker->getStatus() === Worker\Worker::STATUS_DONE : !empty($this->message);
    }

    /**
     * It returns that the writing of response is completed.
     *
     * @return bool
     */
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

    /**
     * It cleans response.
     *
     * @return bool
     */
    public function cleanup(): bool {
        $this->code      = null;
        $this->message   = null;
        $this->_rendered = null;
        $this->_parser->cleanup();
        if ($this->worker) {
            $this->worker->reset();
        }

        return true;
    }

    /**
     * It renders response message string.
     *
     * @return string|null
     */
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

    /**
     * It adds received data for parsing message.
     *
     * @param string $data Received data
     *
     * @return Response
     */
    public function addData(string $data): IResponse {
        $this->_parser->addData($data);
        if ($this->_parser->isCompleted()) {
            $this->from($this->_parser->to());
        }

        return $this;
    }
}
