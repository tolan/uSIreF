<?php

namespace uSIreF\Network\Interfaces;

use \Closure;

interface IServer {

    public function run(Closure $callback = null): IServer;

}
