<?php

/**
 * This file defines sub-process template for worker.
 *
 * It creates new server instance with listening on given adapter (serialized and passed by env).
 */

require getcwd().'/vendor/autoload.php';

use uSIreF\Common\Provider;
use uSIreF\Common\Utils\JSON;
use uSIreF\Network\Server;
use uSIreF\Network\HTTP\{Router, Collector, Request, Response};

try {
    function getCpuUsage() {
        $dat = getrusage();
        $dat["ru_utime.tv_usec"] = ($dat["ru_utime.tv_sec"]*1e6 + $dat["ru_utime.tv_usec"]) - PHP_RUSAGE;
        $time = (microtime(true) - PHP_TUSAGE) / 1000;

        return $dat["ru_utime.tv_usec"]/$time;
    }

    $provider = new Provider();
    $adapter  = unserialize(JSON::decode(getenv('adapter')));
    $router   = new Router(function(Collector $collector) use ($provider) {
        $collector->get('/run', function(Request $request, Response $response) use ($provider) {
            $params = [
                'controller' => unserialize(JSON::decode($request->query['controller'])),
                'method'     => unserialize(JSON::decode($request->query['method'])),
                'request'    => unserialize(JSON::decode($request->query['request'])),
            ];

            $controller = $provider->get($params['controller']); /* @var $controller uSIreF\Common\Abstracts\AController */
            $method     = $params['method'];
            call_user_func_array([$controller, $method], [$params['request'], $response]);

            if (empty($response->message)) {
                $response->message = Response\Code::getMessage($response->code ?? Response\Code::OK_200);
            }
        });
    });

    $server = new Server($adapter, $router);
    $i        = 1;
    $overload = 0;
    $server->run(function () use (&$i, &$overload) {
        if (($i++) % 100 === 0) {
            $i        = 1;
            $overload = getCpuUsage() > 1 ? $overload + 1 : max($overload - 1, 0);
            if ($overload >= 5) {
                return false;
            }
        }

        return true;
    });

} catch (\Throwable $e) {
    echo $e->getMessage()."\n".$e->getTraceAsString();
    exit(1);
}
