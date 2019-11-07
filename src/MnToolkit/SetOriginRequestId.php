<?php

declare(strict_types=1);

namespace MnToolkit;

use Closure;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

class SetOriginRequestId
{
    private $logger;

    public function __construct(LoggerInterface $logger = null)
    {
        if (is_null($logger)) {
            $logger = new Logger(get_class($this));
            $logger->pushHandler(new ErrorLogHandler());
        }

        $this->logger = $logger;
    }

    /**
     * Get Origin request Id and log it - set it as request Id for every request
     *
     *
     */
    public function setOriginRequestId($request, Closure $next)
    {
        if ($_SERVER['HTTP_X_REQUEST_ID']) {
            $request_id = $_SERVER['HTTP_X_REQUEST_ID'];
            $this->logger->error("Found HTTP_X_REQUEST_ID: $request_id");
        } elseif ($_SERVER['HTTP_X_AMZN_TRACE_ID']) {
            preg_match('/^.*Root=([^;]*).*$/', $_SERVER['HTTP_X_AMZN_TRACE_ID'], $matches);
            $request_id = $matches[0];
            $this->logger->error("Found HTTP_X_AMZN_TRACE_ID: $request_id");
        }
        if (!$request_id) {
            $request_id = uniqid();
            $this->logger->error("Set request_id: $request_id");
        }

        $response = $next($request);

        $response->header('X-Request-Id', $request_id, false);

        return $response;
    }

}
