<?php

namespace uSIreF\Network\Interfaces;

/**
 * This file defines interface for client.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IClient {

    const STATE_NONE       = 'none';
    const STATE_CONNECTING = 'connecting';
    const STATE_READY      = 'ready';
    const STATE_WAITNG     = 'waiting';
    const STATE_DONE       = 'done';
    const STATE_ERROR      = 'error';

    /**
     * It returns output of the client.
     *
     * @return IResponse
     */
    public function getOutput(): IResponse;

    /**
     * It returns state of client (one of ONE_*).
     *
     * @return string
     */
    public function getState(): string;

    /**
     * It sends request to client.
     *
     * @param IRequest $request Request message instance
     *
     * @return IClient
     */
    public function request(IRequest $request): IClient;

    /**
     * It resets client.
     *
     * @return IClient
     */
    public function reset(): IClient;
}
