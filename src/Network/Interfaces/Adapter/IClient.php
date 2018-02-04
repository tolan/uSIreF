<?php

namespace uSIreF\Network\Interfaces\Adapter;

use uSIreF\Network\Interfaces\{IRequest, IMessage};

/**
 * This file defines interface for create client resource.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IClient {

    /**
     * Connect by request.
     *
     * @param IRequest $request Request instance with connection params
     * @param float    $timeout Timeout for socket connection in ms
     *
     * @return IMessage
     *
     * @throws Exception
     */
    public function connect(IRequest $request, float $timeout = null): IMessage;

}
