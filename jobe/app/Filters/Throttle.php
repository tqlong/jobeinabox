<?php
/**
 * If API keys are enabled, and the key used in a RUN submission has a rate-limit,
 * this class enforces the rate limit on a per-IP basis to the per-hour limit set in the
 * Config file jobe.php.
 */

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;

class Throttle implements FilterInterface
{
    /**
     * Called before processing of POST ("run") requests to throttle rate if necessary.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $api_keys_in_use = config('Jobe')->require_api_keys;
        if ($api_keys_in_use) {
            $keys = config('Jobe')->api_keys;
            if (!$request->hasHeader('X-API-KEY')) {
                $response = Services::response()->setStatusCode(403)->setBody("Missing API key");
                return addCorsHeaders($response);
            }
            
            $api_key_hdr = $request->header('X-API-KEY');
            $api_key = explode(': ', $api_key_hdr)[1];
            if (!array_key_exists($api_key, $keys)) {
                $response = Services::response()->setStatusCode(403)->setBody("Unknown API key");
                return addCorsHeaders($response);
            }
            $rate_limit = $keys[$api_key];

            if ($rate_limit) {
                $throttler = Services::throttler();
                // Restrict an IP address to no more than the configured hourly rate limit for RUN requests only.
                $ip = $request->getIPAddress();
                if ($throttler->check(md5($ip), $rate_limit, HOUR) === false) {
                    log_message('debug', "Throttled IP $ip");
                    $response = Services::response()->setStatusCode(429)->setBody("Max RUN rate for this server exceeded");
                    return addCorsHeaders($response);
                }
            }
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // ...
    }
}
