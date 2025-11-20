<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;

class CheckGenderMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $gender = strtolower((string) $request->input('gender', ''));

        if ($gender === '') {
            return $next($request);
        }

        if (!in_array($gender, ['male', 'female'], true)) {
            return $next($request);
        }

        $users = User::where('gender', $gender)->get(['id', 'name', 'email', 'gender', 'phone']);

        return response()->json([
            'middleware' => 'CheckGenderMiddleware',
            'users' => $users,
        ]);
    }
}
