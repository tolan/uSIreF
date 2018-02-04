<?php

namespace uSIreF\Network\HTTP\Worker;

use uSIreF\Common\Abstracts\AEntity;
use uSIreF\Common\Utils\JSON;
use uSIreF\Network\Client;
use uSIreF\Network\HTTP\{Request, Response, Adapter\Adapter, Method};
use Symfony\Component\Process\PhpProcess;

/**
 * This file defines class for Worker. It provides sending message to sub-process via client.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Worker extends AEntity {

    const STATUS_READY   = 'ready';
    const STATUS_RUNNING = 'running';
    const STATUS_DONE    = 'done';
    const STATUS_ERROR   = 'error';

    /**
     * @var int
     */
    private $_calls = 0;

    /**
     * @var string
     */
    private $_status;

    /**
     * @var null|array
     */
    private $_callBuffer = null;

    /**
     * @var null|Response
     */
    private $_output = null;

    /**
     * @var PhpProcess
     */
    private $_process;

    /**
     * @var Client
     */
    private $_client;

    /**
     * Construct method for set adapter.
     *
     * @param Adapter $adapter Adapter combine instance
     */
    public function __construct(Adapter $adapter) {
        $this->_status  = self::STATUS_READY;
        $this->_client  = new Client($adapter->getClient());
        $this->_process = new PhpProcess(file_get_contents(__DIR__.'/Template.php'), dirname(__DIR__, 4));
        $this->_process
            ->setEnv(['adapter' => JSON::encode(serialize($adapter->getServer()))])
            ->start();
    }

    /**
     * Returns count of calls.
     *
     * @return int
     */
    public function getCalls(): int {
        return $this->_calls;
    }

    /**
     * Returns current state of worker.
     *
     * @return string
     */
    public function getStatus(): string {
        $this->_update();
        return !empty($this->_callBuffer) ? self::STATUS_RUNNING : $this->_status;
    }

    /**
     * It resets worker.
     *
     * @return Worker
     */
    public function reset(): Worker {
        if ($this->_status === self::STATUS_DONE) {
            $this->_status     = self::STATUS_READY;
            $this->_callBuffer = null;
            $this->_output     = null;
        } else if ($this->_status === self::STATUS_ERROR) {
            $this->_status = self::STATUS_READY;
            if ($this->_process->isRunning()) {
                $this->_process->stop();
            }

            $this->_process->start();
        }

        $this->_client->reset();

        return $this;
    }

    /**
     * It sends request message to controller and method in client.
     *
     * @param string  $controller Controller classname
     * @param string  $method     Controller method
     * @param Request $request    Request message
     *
     * @return Worker
     *
     * @throws Exception
     */
    public function call(string $controller, string $method, Request $request): Worker {
        if ($this->_status === self::STATUS_RUNNING) {
            throw new Exception('Worker is still running.');
        }

        $this->_calls++;
        $this->_callBuffer = [
            'controller' => JSON::encode(serialize($controller)),
            'method'     => JSON::encode(serialize($method)),
            'request'    => JSON::encode(serialize($request)),
        ];

        $this->_update();

        return $this;
    }

    /**
     * It returns output string from worker or null if message is not completed.
     *
     * @return string|null
     */
    public function getOutput(): ?string {
        $this->_update();
        return $this->_output ? $this->_output->render() : null;
    }

    /**
     * It updates current status and calls corresponding method.
     *
     * @return Worker
     */
    private function _update(): Worker {
        if ($this->_status === self::STATUS_READY && $this->_callBuffer) {
            $this->_status = self::STATUS_RUNNING;
            $this->_send($this->_callBuffer);
        }

        if ($this->_status === self::STATUS_RUNNING && ($this->_output = $this->_read())) {
            $this->_status     = self::STATUS_DONE;
            $this->_callBuffer = null;
        }

        if ($this->_client->getState() === Client::STATE_ERROR || $this->_process->isTerminated()) {
            $this->_status = self::STATUS_ERROR;
            $this->reset();
        }

        return $this;
    }

    /**
     * It returns response message from client.
     *
     * @return Response|null
     */
    private function _read(): ?Response {
        return $this->_client->getState() === Client::STATE_DONE ? $this->_client->getOutput() : null;
    }

    /**
     * It sends data to client.
     *
     * @param array $params Message parameters
     *
     * @return Worker
     */
    private function _send(array $params = []): Worker {
        $request         = new Request();
        $request->method = Method::GET;
        $request->uri    = '/run';
        $request->query  = $params;

        $this->_client->request($request);

        return $this;
    }
}
