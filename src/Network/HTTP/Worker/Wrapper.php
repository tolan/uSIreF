<?php

namespace uSIreF\Network\HTTP\Worker;

class Wrapper {

    /**
     * @var Pool
     */
    private $_pool;

    /**
     * @var Worker
     */
    private $_worker;

    /**
     * @var null|array
     */
    private $_callBuffer;

    public function __construct(Pool $pool) {
        $this->_pool = $pool;
    }

    public function __call($name, $arguments) {
        $this->_update();
        $result = null;
        if ($this->_worker) {
            $result = call_user_func_array([$this->_worker, $name], $arguments);
        } else {
            switch ($name) {
                case 'getStatus':
                    $result = Worker::STATUS_READY;
                    break;
                case 'call':
                    $this->_callBuffer = $arguments;
                case 'restart':
                    $result = $this;
                    break;
            }
        }

        return $result;
    }

    private function _update(): Wrapper {
        if (!$this->_worker && ($worker = $this->_pool->getWorker())) {
            $this->_worker = $worker;
        }

        if ($this->_worker && $this->_callBuffer) {
            list($controller, $method, $request) = $this->_callBuffer;
            $this->_worker->call($controller, $method, $request);
            $this->_callBuffer = null;
        }

        return $this;
    }
}
