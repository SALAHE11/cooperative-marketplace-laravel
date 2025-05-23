<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function adminDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSystemAdmin()) {
            abort(403);
        }

        return view('dashboards.admin');
    }

    public function coopDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isCooperativeAdmin()) {
            abort(403);
        }

        return view('dashboards.coop');
    }

    public function clientDashboard()
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user->isClient()) {
            abort(403);
        }

        return view('dashboards.client');
    }
}
