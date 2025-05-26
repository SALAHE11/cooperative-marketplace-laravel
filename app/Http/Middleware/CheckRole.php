<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Non authentifié'], 401);
            }
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->role !== $role) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'error' => 'Accès non autorisé'], 403);
            }
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
