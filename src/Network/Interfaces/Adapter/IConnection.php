<?php

namespace uSIreF\Network\Interfaces\Adapter;

/**
 * This file defines interface for adapter connection.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IConnection {

    /**
     * Read data from resource.
     *
     * @return string|null
     */
    public function read(): ?string;

    /**
     * Writes text message to connection.
     *
     * @param string $message Text message
     *
     * @return int
     */
    public function write(string $message): int;

    /**
     * Closes resource.
     *
     * @return bool
     */
    public function close(): bool;
}
