<?php

namespace uSIreF\Network\Interfaces\Adapter;

use uSIreF\Network\Interfaces\IMessage;

/**
 * This file defines interface for create server resource.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IServer {

    /**
     * Creates and starts server.
     *
     * @return IServer
     *
     * @throws Exception
     */
    public function start(): IServer;

    /**
     * Select Message from server stream.
     *
     * @param float $timeout Timeout for select message in ms
     *
     * @return IMessage|null
     */
    public function select(float $timeout = null): ?IMessage;

}
