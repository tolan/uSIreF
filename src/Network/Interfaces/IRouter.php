<?php

namespace uSIreF\Network\Interfaces;

/**
 * This file defines interface for routing message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
interface IRouter {

    /**
     * It resolves request message.
     *
     * @param IRequest  $request  Request message instance
     * @param IResponse $response Response message instance
     *
     * @return IRouter
     */
    public function resolve(IRequest $request, IResponse $response): IRouter;

}
