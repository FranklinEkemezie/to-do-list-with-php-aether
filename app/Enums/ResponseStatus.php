<?php

namespace PHPAether\Enums;

enum ResponseStatus: int
{

    // 2xx HTTP Status Code - Successful response
    case OK = 200;
    case CREATED = 201;
    case ACCEPTED = 202;
    case NO_CONTENT = 204;

    // 3xx HTTP Status Code - Redirection messages
    case MOVED_PERMANENTLY = 301;
    case FOUND = 302;
    case NOT_MODIFIED = 304;
    case TEMPORARY_REDIRECT = 307;
    case PERMANENT_REDIRECT = 308;

    // 4xx HTTP Status - Client Error responses
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case FORBIDDEN = 403;
    case NOT_FOUND = 404;
    case METHOD_NOT_ALLOWED = 405;
    case NOT_ACCEPTABLE = 406;
    case PROXY_AUTHENTICATION_REQUIRED = 407;
    case REQUEST_TIMEOUT = 408;
    case CONFLICT = 409;
    case CONTENT_TOO_LARGE = 413;
    case TOO_MANY_REQUEST = 429;

    // 5xx HTTP Status Code - Server error responses
    case NOT_IMPLEMENTED = 501;
    case BAD_GATEWAY = 502;
    case SERVICE_UNAVAILABLE = 503;
    case GATEWAY_TIMEOUT = 504;
    case HTTP_VERSION_NOT_SUPPORTED = 505;
    case NETWORK_AUTHENTICATION_REQUIRED = 511;

    public function getMessage(): string
    {
        return $this->name;
    }


}
