<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ParseMultipartPut
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->isMethod("put") || $request->isMethod("patch")) {
            $contentType = $request->header("Content-Type");
            
            if ($contentType && str_starts_with($contentType, "multipart/form-data")) {
                // Force Laravel to parse the multipart form data
                $request->merge($request->all());
            }
        }

        return $next($request);
    }
}
