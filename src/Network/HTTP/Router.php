<?php

namespace uSIreF\Network\HTTP;

use Closure;
use FastRoute\Dispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use uSIreF\Network\HTTP\Worker\Pool;
use uSIreF\Network\HTTP\Response\Code;
use uSIreF\Network\Interfaces\{IRouter, IRequest, IResponse};

/**
 * This file defines class for routing message.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Router implements IRouter {

    const NOT_FOUND          = Dispatcher::NOT_FOUND;
    const FOUND              = Dispatcher::FOUND;
    const METHOD_NOT_ALLOWED = Dispatcher::METHOD_NOT_ALLOWED;

    /**
     * @var GroupCountBased
     */
    private $_dispatcher;

    /**
     * @var Pool
     */
    private $_pool;

    /**
     * Construct method for set route collector and pool.
     *
     * @param Closure $collectorFunction Route collector instance
     * @param Pool    $pool              Worker pool instance
     */
    public function __construct(Closure $collectorFunction, Pool $pool = null) {
        $options = [
            'routeCollector' => Collector::class
        ];
        $this->_dispatcher = \FastRoute\simpleDispatcher($collectorFunction, $options);
        $this->_pool       = $pool;
    }

    /**
     * It resolves request message.
     *
     * @param IRequest  $request  Request message instance
     * @param IResponse $response Response message instance
     *
     * @return Router
     */
    public function resolve(IRequest $request, IResponse $response): IRouter {
        try {
            $routeInfo = $this->_dispatcher->dispatch($request->method, $request->uri);
            switch ($routeInfo[0]) {
                case self::NOT_FOUND:
                    $response->code = Code::NOT_FOUND_404;
                    break;
                case self::METHOD_NOT_ALLOWED:
                    $response->code = Code::METHOD_NOT_ALLOWED_405;
                    break;
                case self::FOUND:
                    $this->_resolveFound($request, $response, $routeInfo);
                    break;
                default:
                    $response->code = Code::INTERNAL_SERVER_ERROR_500;
            }
        } catch (Throwable $error) {
            $response->code    = Code::INTERNAL_SERVER_ERROR_500;
            $response->message = $error->getMessage();
        }

        if ($response->code && $response->code !== Code::OK_200 && empty($response->message)) {
            $response->message = Code::getMessage($response->code);
        }

        return $this;
    }

    /**
     * It runs resolved callback.
     *
     * @param IRequest  $request   Request message instance
     * @param IResponse $response  Response message instance
     * @param Closure   $routeInfo Resolved route info
     *
     * @return Router
     */
    private function _resolveFound(IRequest $request, IResponse $response, $routeInfo): Router {
        if ($routeInfo[1] instanceof Closure) {
            call_user_func_array($routeInfo[1], [$request, $response, $routeInfo]);
        } else {
            list($controller, $method) = $routeInfo[1];
            $response->worker          = $this->_pool->getWrapper()->call($controller, $method, $request);
        }

        if (!$response->code) {
            $response->code = Code::OK_200;
        }

        return $this;
    }
}
