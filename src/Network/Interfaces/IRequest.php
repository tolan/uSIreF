<?php

namespace uSIreF\Network\Interfaces;

/**
 * This file defines interface for request message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IRequest {

    /**
     * It returns that reading of the message is completed.
     *
     * @return bool
     */
    public function isReadCompleted(): bool;

    /**
     * It adds received data for parsing request message.
     *
     * @param string $data Received data
     *
     * @return IRequest
     */
    public function addData(string $data): IRequest;

    /**
     * It cleans request message.
     *
     * @return IRequest
     */
    public function cleanup(): IRequest;

    /**
     * It builds request message string.
     *
     * @return string
     */
    public function build(): string;

}
