<?php

namespace Tale\Net\Http;

use Tale\System\Enum;

class HttpStatusCode extends Enum {    

    // [Informational 1xx]
    const HTTP_CONTINUE = 100;
    const SWITCHING_PROTOCOLS = 101;

    // [Successful 2xx]
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NONAUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;

    // [Redirection 3xx]
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const UNUSED = 306;
    const TEMPORARY_REDIRECT = 307;

    // [Client Error 4xx]
    const BAD_REQUEST = 400;
    const UNAUTHORIZED  = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;

    // [Server Error 5xx]
    const INTERNAL_SERVER_ERROR = 500;
    const NOT_IMPLEMENTED = 501;
    const BAD_GATEWAY = 502;
    const SERVICE_UNAVAILABLE = 503;
    const GATEWAY_TIMEOUT = 504;
    const VERSION_NOT_SUPPORTED = 505;

    private static $_reasonPhrases = [

        // [Informational 1xx]
        self::HTTP_CONTINUE                     => 'Continue',
        self::SWITCHING_PROTOCOLS               => 'Switching Protocols',

        // [Successful 2xx]
        self::OK                                => 'OK',
        self::CREATED                           => 'Created',
        self::ACCEPTED                          => 'Accepted',
        self::NONAUTHORITATIVE_INFORMATION      => 'Non-Authoritative Information',
        self::NO_CONTENT                        => 'No Content',
        self::RESET_CONTENT                     => 'Reset Content',
        self::PARTIAL_CONTENT                   => 'Partial Content',

        // [Redirection 3xx]
        self::MULTIPLE_CHOICES                  => 'Multiple Choices',
        self::MOVED_PERMANENTLY                 => 'Moved Permanently',
        self::FOUND                             => 'Found',
        self::SEE_OTHER                         => 'See Other',
        self::NOT_MODIFIED                      => 'Not Modified',
        self::USE_PROXY                         => 'Use Proxy',
        self::UNUSED                            => '(Unused)',
        self::TEMPORARY_REDIRECT                => 'Temporary Redirect',

        // [Client Error 4xx]
        self::BAD_REQUEST                       => 'Bad Request',
        self::UNAUTHORIZED                      => 'Unauthorized',
        self::PAYMENT_REQUIRED                  => 'Payment Required',
        self::FORBIDDEN                         => 'Forbidden',
        self::NOT_FOUND                         => 'Not Found',
        self::METHOD_NOT_ALLOWED                => 'Method Not Allowed',
        self::NOT_ACCEPTABLE                    => 'Not Acceptable',
        self::PROXY_AUTHENTICATION_REQUIRED     => 'Proxy Authentication Required',
        self::REQUEST_TIMEOUT                   => 'Request Timeout',
        self::CONFLICT                          => 'Conflict',
        self::GONE                              => 'Gone',
        self::LENGTH_REQUIRED                   => 'Length Required',
        self::PRECONDITION_FAILED               => 'Precondition Failed',
        self::REQUEST_ENTITY_TOO_LARGE          => 'Request Entity Too Large',
        self::REQUEST_URI_TOO_LONG              => 'Request-URI Too Long',
        self::UNSUPPORTED_MEDIA_TYPE            => 'Unsupported Media Type',
        self::REQUESTED_RANGE_NOT_SATISFIABLE   => 'Requested Range Not Satisfiable',
        self::EXPECTATION_FAILED                => 'Expectation Failed',

        // [Server Error 5xx]
        self::INTERNAL_SERVER_ERROR             => 'Internal Server Error',
        self::NOT_IMPLEMENTED                   => 'Not Implemented',
        self::BAD_GATEWAY                       => 'Bad Gateway',
        self::SERVICE_UNAVAILABLE               => 'Service Unavailable',
        self::GATEWAY_TIMEOUT                   => 'Gateway Timeout',
        self::VERSION_NOT_SUPPORTED             => 'HTTP Version Not Supported'
    ];

    public static function getReasonPhrase( $statusCode ) {

        return self::$_reasonPhrases[ $statusCode ];
    }
}