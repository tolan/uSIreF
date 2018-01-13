<?php

namespace uSIreF\Network\Interfaces;

interface IClient {

    const STATE_NONE       = 'none';
    const STATE_CONNECTING = 'connecting';
    const STATE_READY      = 'ready';
    const STATE_WAITNG     = 'waiting';
    const STATE_DONE       = 'done';
    const STATE_ERROR      = 'error';

    public function getOutput(): IResponse;

    public function getState(): string;

    public function request(IRequest $request): IClient;
}
