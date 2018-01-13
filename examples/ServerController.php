<?php

namespace uSIref\Example;

use uSIreF\Common\Abstracts\AController;
use uSIreF\Network\HTTP\{Request, Response};

class ServerController extends AController {

    public function index(Request $request, Response $response) {
        $response->message = 'Hello World from controller.';
    }
}
