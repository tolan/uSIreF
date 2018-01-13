<?php

namespace uSIreF\Network\HTTP\Worker;

use uSIreF\Network\HTTP\AdapterFactory;

class Pool {

    /**
     * @var AdapterFactory
     */
    private $_factory;

    private $_workers = [];

    private static $_availableStatus = [
        Worker::STATUS_READY,
    ];

    public function __construct(AdapterFactory $factory) {
        $this->_factory = $factory;
    }

    public function getWrapper(): Wrapper {
        return new Wrapper($this);
    }

    public function getWorker(): ?Worker {
        $result = null;
        $this->_cleanup();

        foreach (self::$_availableStatus as $availableStatus) {
            $workers = [];
            foreach ($this->_workers as $worker) {
                if ($worker->getStatus() === $availableStatus) {
                    $workers[$worker->getCalls()][] = $worker;
                }
            }

            if (!empty($workers)) {
                $min    = min(array_keys($workers));
                $result = current($workers[$min]);
            }

            if (!$result && ($worker = $this->_createWorker())) {
                $this->_workers[] = $worker;
                $result           = $worker;
            }
        }

        return $result;
    }

    private function _cleanup() {
        $i = 10;
        while ($i--) {
            $running = 0;
            foreach ($this->_workers as $worker) {
                if ($worker->getStatus() === Worker::STATUS_ERROR) {
                    $worker->restart();
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

    private function _createWorker(): ?Worker {
        $result = null;
        if (($adapter = $this->_factory->createAdapter())) {
            $result = new Worker($adapter);
        }

        return $result;
    }
}
