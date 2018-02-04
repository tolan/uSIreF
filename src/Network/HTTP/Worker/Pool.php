<?php

namespace uSIreF\Network\HTTP\Worker;

use uSIreF\Network\HTTP\Adapter\Factory;

/**
 * This file defines class for collect and provide workers.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Pool {

    /**
     * @var Factory
     */
    private $_factory;

    /**
     * @var [Worker]
     */
    private $_workers = [];

    /**
     * Consturct method for set adapter factory.
     *
     * @param Factory $factory Adapter factory
     */
    public function __construct(Factory $factory) {
        $this->_factory = $factory;
    }

    /**
     * Returns worker wrapper instance.
     *
     * @return Wrapper
     */
    public function getWrapper(): Wrapper {
        return new Wrapper($this);
    }

    /**
     * Returns worker instance.
     *
     * @return Worker|null
     */
    public function getWorker(): ?Worker {
        $result = null;
        $this->_cleanup();

        foreach ($this->_workers as $worker) {
            if ($worker->getStatus() === Worker::STATUS_READY && (!$result || $result->getCalls() > $worker->getCalls())) {
                $result = $worker;
            }
        }

        if (!$result && ($worker = $this->_createWorker())) {
            $this->_workers[] = $worker;
            $result           = $worker;
        }

        return $result;
    }

    /**
     * This method provides cleanup for workers in pool and prepare new instances.
     *
     * @return Pool
     */
    private function _cleanup(): Pool {
        $i = 10;
        while ($i--) {
            $running = 0;
            foreach ($this->_workers as $worker) {
                if ($worker->getStatus() === Worker::STATUS_ERROR) {
                    $worker->reset();
                } elseif ($worker->getStatus() === Worker::STATUS_RUNNING) {
                    $running++;
                }
            }

            if ((empty($this->_workers) || ($running / count($this->_workers)) > 0.8) && ($worker = $this->_createWorker())) {
                $this->_workers[] = $worker;
            } else {
                break;
            }
        }

        return $this;
    }

    /**
     * Creates new worker is possible.
     *
     * @return Worker|null
     */
    private function _createWorker(): ?Worker {
        $result = null;
        if (($adapter = $this->_factory->getAdapter())) {
            $result = new Worker($adapter);
        }

        return $result;
    }
}
