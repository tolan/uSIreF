<?php

/*
 * Example standalone PHP microservice based on HTTP server.
 *
 * Just run it on the command line like "examples/microservice.php".
 */
require __DIR__.'/../vendor/autoload.php';

echo "Start microservice";

use uSIreF\Network\Server;
use uSIreF\Network\HTTP\{AdapterFactory, Adapter, Router, Collector, Request, Response};
use uSIreF\Network\HTTP\Worker\Pool;
use uSIref\Example\ServerController;

try {
    $serverAdapter = new Adapter();
    $clientAdapter = new AdapterFactory('127.0.0.1', range(12001, 12020));

    $router = new Router(function(Collector $collector) {
        $collector->get('/usiref[/]', function(Request $request, Response $response) {
            $response->message = 'Hello to '.$request->remoteAddr;
        });
        $collector->get('/usiref/ping', function(Request $request, Response $response) {
            $response->message = 'pong';
        });
        $collector->get('/usiref/background[/]', [ServerController::class, 'index']);
    }, new Pool($clientAdapter));

    $server = new Server($serverAdapter, $router);
    $server->run();
} catch (\Throwable $e) {
    echo $e->getMessage()."\n".$e->getTraceAsString();
}