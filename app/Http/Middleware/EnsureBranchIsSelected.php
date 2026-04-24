<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBranchIsSelected
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->routeIs('branches.select') || $request->routeIs('branches.select.store')) {
            return $next($request);
        }

        if (!session()->has('current_branch_id')) {
            return redirect()->route('branches.select');
        }

        return $next($request);
    }
}