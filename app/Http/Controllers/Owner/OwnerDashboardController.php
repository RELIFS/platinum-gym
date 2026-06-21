<?php

namespace App\Http\Controllers\Owner;

use App\Features\OwnerPortal\Queries\OwnerDashboardQuery;
use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class OwnerDashboardController extends Controller
{
    public function index(Request $request, OwnerDashboardQuery $query): View
    {
        abort_unless($request->user()?->can('view_owner_dashboard'), 403);

        return view('owner.dashboard', [
            'portal' => $query->forUser($request->user()),
            'navigation' => $query->navigation(),
        ]);
    }
}
