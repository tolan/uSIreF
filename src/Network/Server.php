<?php

namespace uSIreF\Network;

use uSIreF\Network\Interfaces\{IServer, IMessage, IRouter, Adapter};
use \Closure;

/**
 * This file defines class for server.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Server implements IServer {

    const CONNECTION_TIMEOUT = 30 * 1000;

    /**
     * @var Adapter\IServer
     */
    private $_adapter;

    /**
     * @var IRouter
     */
    private $_router;

    /**
     * @var [IMessage]
     */
    private $_messages = [];

    /**
     * Construct method for set server adapter and router.
     *
     * @param Adapter\IServer $adapter Server adapter instance
     * @param IRouter         $router  Router instance
     */
    public function __construct(Adapter\IServer $adapter, IRouter $router) {
        $this->_adapter = $adapter;
        $this->_router  = $router;
    }

    /**
     * Destruct method disconnect all messages.
     *
     * @return void
     */
    public function __destruct() {
        foreach ($this->_messages as $message) { /* @var $message IMessage */
            $message->getConnection()->close();
        }
    }

    /**
     * Starts and runs server listening forever.
     *
     * @param Closure $callback Callback wich is called in each iteration (optional)
     *
     * @return IServer
     */
    public function run(Closure $callback = null): IServer {
        $this->_adapter->start();
        $condition = $callback ?? function() {
            return true;
        };

        while ($condition($this)) {
            $timeout = empty($this->_messages) ? 100 : 1;
            if (($message = $this->_adapter->select($timeout))) {
                $this->_messages[$message->getObjectId()] = $message;
            }

            foreach ($this->_messages as $message) {
                if ($message->getRequest()->isReadCompleted() === false) {
                    $this->_read($message);
                }
            }

            foreach ($this->_messages as $message) {
                if ($message->getResponse()->isReadCompleted() === true) {
                    $this->_write($message);
                }
            }

            foreach ($this->_messages as $message) {
                if ($message->getTimeout() > self::CONNECTION_TIMEOUT) {
                    $this->_close($message);
                }
            }
        }

        return $this;
    }

    /**
     * It reads request message.
     *
     * @param IMessage $message Message instance
     *
     * @return Server
     */
    private function _read(IMessage $message): Server {
        $data = $message->getConnection()->read();
        if (empty($data)) {
            $this->_close($message);
        } elseif ($message->getRequest()->addData($data)->isReadCompleted()) {
            $this->_router->resolve($message->getRequest(), $message->getResponse());
        }

        return $this;
    }

    /**
     * It writes response message.
     *
     * @param IMessage $message Message instance
     *
     * @return Server
     */
    private function _write(IMessage $message): Server {
        $response = $message->getResponse();
        if ($message->getConnection()->write($response->render()) && $response->isWriteCompleted()) {
            $this->_close($message);
        }

        return $this;
    }

    /**
     * It closes message.
     *
     * @param IMessage $message Message instance
     *
     * @return Server
     */
    private function _close(IMessage $message): Server {
        unset($this->_messages[$message->getObjectId()]);
        $message->cleanup();

        return $this;
    }
}
