<?php

namespace uSIreF\Network\HTTP\Worker;

/**
 * This file defines class for wrap Worker instance.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
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

    /**
     * Construct method for set Pool instance.
     *
     * @param Pool $pool Pool instance
     */
    public function __construct(Pool $pool) {
        $this->_pool = $pool;
    }

    /**
     * It decorates methods of Worker (if it is possible).
     *
     * @param string $name      Name method
     * @param array  $arguments Arguments
     *
     * @return Wrapper
     */
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

    /**
     * It updates current status (It gets worker from pool if it is possible).
     *
     * @return Wrapper
     */
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
