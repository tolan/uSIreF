<?php

namespace uSIreF\Network\Interfaces;

interface IRouter {

    public function resolve(IRequest $request, IResponse $response): IRouter;

}
