<?php

namespace App\Http\Middleware;

use Closure;

class HttpCaching
{
    /**
     * Implement HTTP Caching
     *
     * @param \Illuminate\Http\Request $request
     *
     * @param \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        $response = $this->setETag($request, $response);
        $response = $this->setCacheControl($request, $response);

        return $response;
    }

    /**
     * ETag support
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     *
     * @return mixed
     */
    public function setETag($request, $response)
    {
        if ($request->isMethod('get')) {
            $etag = md5($response->getContent());
            $requestEtag = str_replace('"', '', $request->getETags());

            if ($requestEtag && $requestEtag[0] == $etag) {
                $response->setNotModified();
            }

            $response->setEtag($etag);
        }

        return $response;
    }

    /**
     * Cache-control support
     *
     * @param \Illuminate\Http\Request $request
     * @param \Illuminate\Http\Response $response
     *
     * @return mixed
     */
    public function setCacheControl($request, $response)
    {
        if ($request->isMethod('get')) {
            $response->header('Cache-Control', 'public, max-age=86400');
        }

        return $response;
    }
}
