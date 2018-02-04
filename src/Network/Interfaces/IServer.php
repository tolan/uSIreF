<?php

namespace uSIreF\Network\Interfaces;

use \Closure;

/**
 * This file defines interface for server.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IServer {

    /**
     * Starts and runs server listening forever.
     *
     * @param Closure $callback Callback wich is called in each iteration (optional)
     *
     * @return IServer
     */
    public function run(Closure $callback = null): IServer;

}
