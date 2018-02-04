<?php

namespace uSIreF\Network\Interfaces;

/**
 * This file defines interface for response message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IResponse {

    /**
     * It returns that the reading of the message is completed.
     *
     * @return bool
     */
    public function isReadCompleted(): bool;

    /**
     * It returns that the writing of the message is completed.
     *
     * @return bool
     */
    public function isWriteCompleted(): bool;

    /**
     * It renders response message string (if it is possible).
     *
     * @return string|null
     */
    public function render(): ?string;

    /**
     * It cleans request message.
     *
     * @return IRequest
     */
    public function cleanup(): bool;

    /**
     * It adds received data for parsing response message.
     *
     * @param string $data Received data
     *
     * @return IResponse
     */
    public function addData(string $message): IResponse;

}
