<?php

namespace App\Http\Controllers;

use App\Features\Admin\Queries\AdminDashboardQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminPortalController extends Controller
{
    public function dashboard(Request $request, AdminDashboardQuery $query): View
    {
        return view('admin.dashboard', [
            'portal' => $query->forUser($request->user(), $this->filters($request)),
            'navigation' => $query->navigation(),
        ]);
    }

    public function checkIn(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'check-in');
    }

    public function booking(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'booking');
    }

    public function notifications(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'notifications');
    }

    public function members(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'members');
    }

    public function packages(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'packages');
    }

    public function classes(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'classes');
    }

    public function payments(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'payments');
    }

    public function products(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'products');
    }

    public function gallery(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'gallery');
    }

    public function testimonials(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'testimonials');
    }

    public function promos(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'promos');
    }

    public function trainers(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'trainers');
    }

    public function reports(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'reports');
    }

    public function auditLog(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'audit-log');
    }

    public function settings(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'settings');
    }

    public function profile(Request $request, AdminDashboardQuery $query): View
    {
        return $this->page($request, $query, 'profile');
    }

    private function page(Request $request, AdminDashboardQuery $query, string $key): View
    {
        $definitions = $query->pageDefinitions();

        abort_unless(isset($definitions[$key]), 404);

        return view('admin.page', [
            'portal' => $query->forUser($request->user(), $this->filters($request), $definitions[$key]['moduleKey']),
            'navigation' => $query->navigation(),
            'page' => $definitions[$key],
        ]);
    }

    private function filters(Request $request): array
    {
        return $request->only(['q', 'status', 'date_from', 'date_to', 'event', 'causer_id', 'page']);
    }
}
