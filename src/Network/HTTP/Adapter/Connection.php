<?php

namespace uSIreF\Network\HTTP\Adapter;

use uSIreF\Network\Interfaces\Adapter\IConnection;

/**
 * This file defines class for adapter connection, that is represented by tcp connection resource.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Connection implements IConnection {

    /**
     * @var Connection
     */
    private $_resource;

    /**
     * Construct method for inject resource pointer.
     *
     * @param resource $resource A resource pointer
     */
    public function __construct($resource) {
        $this->_resource = $resource;
    }

    /**
     * Read data from resource.
     *
     * @return string|null
     */
    public function read(): ?string {
        return @fread($this->_resource, 30000);
    }

    /**
     * Writes text message to connection.
     *
     * @param string $message Text message
     *
     * @return int
     */
    public function write(string $message): int {
        return @fwrite($this->_resource, $message);
    }

    /**
     * Closes resource.
     *
     * @return bool
     */
    public function close(): bool {
        return @fclose($this->_resource);
    }

    /**
     * Returns string name.
     *
     * @return string
     */
    public function getName(): string {
        return stream_socket_get_name($this->_resource, true);
    }
}
