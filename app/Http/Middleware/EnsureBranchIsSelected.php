<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureBranchIsSelected
{
    public function handle(Request $request, Closure $next)
    {
        if (!session()->has('current_branch_id')) {
            return redirect()->route('branches.select');
        }

        return $next($request);
    }
}