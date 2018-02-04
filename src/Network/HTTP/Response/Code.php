<?php

namespace uSIreF\Network\HTTP\Response;

use uSIreF\Common\Traits\TEnum;

/**
 * This file defines class for HTTP status and codes.
 *
 * @author Martin Kovar <mkovar86@gmail.com>
 */
class Code {
    use TEnum;

    const CONTINUE_100                        = 100;
    const SWITCHING_PROTOCOLS_101             = 101;
    const OK_200                              = 200;
    const CREATED_201                         = 201;
    const ACCEPTED_202                        = 202;
    const NON_AUTHORITATIVE_INFORMATION_203   = 203;
    const NO_CONTENT_204                      = 204;
    const RESET_CONTENT_205                   = 205;
    const PARTIAL_CONTENT_206                 = 206;
    const MULTIPLE_CHOICES_300                = 300;
    const MOVED_PERMANENTLY_301               = 301;
    const FOUND_302                           = 302;
    const SEE_OTHER_303                       = 303;
    const NOT_MODIFIED_304                    = 304;
    const USE_PROXY_305                       = 305;
    const TEMPORARY_REDIRECT_307              = 307;
    const BAD_REQUEST_400                     = 400;
    const UNAUTHORIZED_401                    = 401;
    const PAYMENT_REQUIRED_402                = 402;
    const FORBIDDEN_403                       = 403;
    const NOT_FOUND_404                       = 404;
    const METHOD_NOT_ALLOWED_405              = 405;
    const NOT_ACCEPTABLE_406                  = 406;
    const PROXY_AUTHENTICATION_REQUIRED_407   = 407;
    const REQUEST_TIMEOUT_408                 = 408;
    const CONFLICT_409                        = 409;
    const GONE_410                            = 410;
    const LENGTH_REQUIRED_411                 = 411;
    const PRECONDITION_FAILED_412             = 412;
    const REQUEST_ENTITY_TOO_LARGE_413        = 413;
    const REQUEST_URI_TOO_LONG_414            = 414;
    const UNSUPPORTED_MEDIA_TYPE_415          = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE_416 = 416;
    const EXPECTATION_FAILED_417              = 417;
    const INTERNAL_SERVER_ERROR_500           = 500;
    const NOT_IMPLEMENTED_501                 = 501;
    const BAD_GATEWAY_502                     = 502;
    const SERVICE_UNAVAILABLE_503             = 503;
    const GATEWAY_TIMEOUT_504                 = 504;
    const HTTP_VERSION_NOT_SUPPORTED_505      = 505;

    /**
     * Map for status code and message.
     *
     * @var array
     */
    private static $_messages = [
        self::CONTINUE_100                        => 'Continue',
        self::SWITCHING_PROTOCOLS_101             => 'Switching Protocols',
        self::OK_200                              => 'OK',
        self::CREATED_201                         => 'Created',
        self::ACCEPTED_202                        => 'Accepted',
        self::NON_AUTHORITATIVE_INFORMATION_203   => 'Non-Authoritative Information',
        self::NO_CONTENT_204                      => 'No Content',
        self::RESET_CONTENT_205                   => 'Reset Content',
        self::PARTIAL_CONTENT_206                 => 'Partial Content',
        self::MULTIPLE_CHOICES_300                => 'Multiple Choices',
        self::MOVED_PERMANENTLY_301               => 'Moved Permanently',
        self::FOUND_302                           => 'Found',
        self::SEE_OTHER_303                       => 'See Other',
        self::NOT_MODIFIED_304                    => 'Not Modified',
        self::USE_PROXY_305                       => 'Use Proxy',
        self::TEMPORARY_REDIRECT_307              => 'Temporary Redirect',
        self::BAD_REQUEST_400                     => 'Bad Request',
        self::UNAUTHORIZED_401                    => 'Unauthorized',
        self::PAYMENT_REQUIRED_402                => 'Payment Required',
        self::FORBIDDEN_403                       => 'Forbidden',
        self::NOT_FOUND_404                       => 'Not Found',
        self::METHOD_NOT_ALLOWED_405              => 'Method Not Allowed',
        self::NOT_ACCEPTABLE_406                  => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED_407   => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT_408                 => 'Request Timeout',
        self::CONFLICT_409                        => 'Conflict',
        self::GONE_410                            => 'Gone',
        self::LENGTH_REQUIRED_411                 => 'Length Required',
        self::PRECONDITION_FAILED_412             => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE_413        => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG_414            => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE_415          => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE_416 => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED_417              => 'Expectation Failed',
        self::INTERNAL_SERVER_ERROR_500           => 'Internal Server Error',
        self::NOT_IMPLEMENTED_501                 => 'Not Implemented',
        self::BAD_GATEWAY_502                     => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE_503             => 'Service Unavailable',
        self::GATEWAY_TIMEOUT_504                 => 'Gateway Timeout',
        self::HTTP_VERSION_NOT_SUPPORTED_505      => 'HTTP Version Not Supported',
    ];

    /**
     * Returns message by given code.
     *
     * @param int $code Response code
     *
     * @return string
     *
     * @throws Exception
     */
    public static function getMessage(int $code): string {
        if (!array_key_exists($code, self::$_messages)) {
            throw new Exception('Code "'.$code.'" is not supported.');
        }

        return self::$_messages[$code];
    }

    /**
     * Returns status message and HTTP version by given response code if status message is not defined.
     *
     * @param int    $code      Response code
     * @param string $statusMsg Status message
     *
     * @return string
     */
    public static function renderStatus(int $code, string $statusMsg = null): string {
        // Per RFC2616 6.1.1 we pass on a status message from the provider if
        // provided, otherwise we use the standard message for that code.
        if (empty($statusMsg)) {
            $statusMsg = self::getMessage($code);
        }

        return "HTTP/1.1 $code $statusMsg\r\n";
    }
}
