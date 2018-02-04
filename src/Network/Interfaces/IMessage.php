<?php

namespace uSIreF\Network\Interfaces;

/**
 * This file defines interface for message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IMessage {

    /**
     * It returns unique object id.
     *
     * @return string
     */
    public function getObjectId();

    /**
     * It returns time from start of the message.
     *
     * @return float
     */
    public function getTimeout(): float;

    /**
     * It returns connection with client.
     *
     * @return Adapter\IConnection
     */
    public function getConnection(): Adapter\IConnection;

    /**
     * It returns request message instance.
     *
     * @return IRequest
     */
    public function getRequest(): IRequest;

    /**
     * It returns response message instance.
     *
     * @return IResponse
     */
    public function getResponse(): IResponse;

    /**
     * It cleans the message.
     *
     * @return bool
     */
    public function cleanup(): bool;
}
