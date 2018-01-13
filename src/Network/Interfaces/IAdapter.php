<?php

namespace uSIreF\Network\Interfaces;

interface IAdapter {

    public function startServer(): IAdapter;

    public function connect(IRequest $request, float $timeout = 0.001): ISocket;

    public function select(float $timeout = null): ?ISocket;

    public function read(ISocket $socket): ?string;

    public function write(ISocket $socket, string $message): bool;

    public function close(ISocket $socket): bool;
}
