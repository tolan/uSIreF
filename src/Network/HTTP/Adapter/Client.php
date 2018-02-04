<?php

namespace uSIreF\Network\HTTP\Adapter;

use uSIreF\Network\HTTP\{Response, Message, Exception};
use uSIreF\Network\Interfaces\{IRequest, IMessage, Adapter\IClient};

/**
 * This file defines class for create client resource.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Client implements IClient {

    /**
     * @var string
     */
    private $_host;

    /**
     * @var int
     */
    private $_port;

    /**
     * Construct method for set host and port.
     *
     * @param string $host Hostname where client will connect to.
     * @param int    $port Port where server will connect to.
     */
    public function __construct(string $host = 'localhost', int $port = 80) {
        $this->_host = $host;
        $this->_port = $port;
    }

    /**
     * Connect to request uri.
     *
     * @param IRequest $request Request instance with uri and query params
     * @param float    $timeout Timeout for socket connection in ms
     *
     * @return IConnection
     *
     * @throws Exception
     */
    public function connect(IRequest $request, float $timeout = null): IMessage {
        $uri     = preg_replace('#/+#', '/', $this->_host.':'.$this->_port.'/'.$request->uri);
        $uri    .= !empty($request->query) ? '?'.http_build_query($request->query) : '';
        $socket  = @stream_socket_client('tcp://'.$uri, $errno, $errstr, $timeout);
        if (!$socket) {
            throw new Exception('Could not connect to a web server "'.$uri.'": ('.$errno.') - '.$errstr);
        }

        stream_set_timeout($socket, 0, 250);

        return new Message(new Connection($socket), $request, new Response());
    }
}