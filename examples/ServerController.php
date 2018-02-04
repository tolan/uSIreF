<?php

namespace uSIref\Example;

use uSIreF\Common\Abstracts\AController;
use uSIreF\Network\HTTP\{Request, Response};

/**
 * This file defines class for example controller.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class ServerController extends AController {

    /**
     * Example index method for Hello world message.
     *
     * @param Request  $request  Request message instance
     * @param Response $response Response message instance
     *
     * @return void
     */
    public function index(Request $request, Response $response) {
        $response->message = 'Hello World from controller.';
    }
}
