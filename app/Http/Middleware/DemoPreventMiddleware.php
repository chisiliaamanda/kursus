<?php

namespace App\Http\Middleware;

use Brian2694\Toastr\Facades\Toastr;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class DemoPreventMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (demoCheck()) {
            if ($request->wantsJson()) {
                return response()->json(['message' => trans('common.For the demo version, you cannot change this')], 422);
            } else {
                Toastr::error(trans('common.For the demo version, you cannot change this'), trans('common.Failed'));
            }
            return redirect()->back();
        }

        return $next($request);
    }
}
