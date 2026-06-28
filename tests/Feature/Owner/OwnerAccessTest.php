<?php

use Database\Seeders\RolePermissionSeeder;
use Tests\Feature\Owner\Support\OwnerPortalFixtures as OwnerFixtures;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('owner get routes require authentication', function (string $routeName, array $parameters = []) {
    $model = null;

    if (in_array($routeName, ['owner.invoices.show', 'owner.invoices.receipt', 'owner.invoices.download'], true)) {
        [, $member] = OwnerFixtures::member();
        $invoice = OwnerFixtures::invoice(OwnerFixtures::payment($member));
        $parameters = [$invoice];
    }

    $this->get(route($routeName, $parameters))->assertRedirect(route('login'));
})->with([
    ['owner.dashboard'],
    ['owner.reports.index'],
    ['owner.reports.finance'],
    ['owner.reports.members'],
    ['owner.reports.classes'],
    ['owner.reports.export'],
    ['owner.invoices.show'],
    ['owner.invoices.receipt'],
    ['owner.invoices.download'],
]);

test('owner photo upload route requires authentication', function () {
    $this->patch(route('owner.profile-photo.update'))->assertRedirect(route('login'));
});

test('member and admin cannot access owner routes', function (string $role, string $routeName) {
    $user = OwnerFixtures::roleUser($role);
    $parameters = [];

    if (str_starts_with($routeName, 'owner.invoices.')) {
        [, $member] = OwnerFixtures::member();
        $parameters = [OwnerFixtures::invoice(OwnerFixtures::payment($member))];
    }

    $this->actingAs($user)->get(route($routeName, $parameters))->assertForbidden();
})->with(['member', 'admin'])->with([
    'dashboard' => ['owner.dashboard'],
    'reports index' => ['owner.reports.index'],
    'finance report' => ['owner.reports.finance'],
    'member report' => ['owner.reports.members'],
    'class report' => ['owner.reports.classes'],
    'export' => ['owner.reports.export'],
    'invoice show' => ['owner.invoices.show'],
    'invoice receipt' => ['owner.invoices.receipt'],
    'invoice download' => ['owner.invoices.download'],
]);

test('owner permission gates dashboard reports invoices and downloads', function (string $permission, string $routeName, int $expectedStatus) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member();
    $invoice = OwnerFixtures::invoice(OwnerFixtures::payment($member));

    OwnerFixtures::revokeOwnerPermission($permission);

    $parameters = str_starts_with($routeName, 'owner.invoices.') ? [$invoice] : [];

    $this->actingAs($owner)->get(route($routeName, $parameters))->assertStatus($expectedStatus);
})->with([
    'dashboard permission' => ['view_owner_dashboard', 'owner.dashboard', 403],
    'report view permission' => ['view_financial_reports', 'owner.reports.finance', 403],
    'invoice view permission' => ['view_financial_reports', 'owner.invoices.show', 403],
    'invoice receipt permission' => ['view_financial_reports', 'owner.invoices.receipt', 403],
    'report export permission' => ['export_financial_reports', 'owner.reports.export', 403],
    'invoice download permission' => ['export_financial_reports', 'owner.invoices.download', 403],
]);

test('owner can open every owner page with default permissions', function (string $routeName) {
    $owner = OwnerFixtures::owner();
    [, $member] = OwnerFixtures::member();
    $invoice = OwnerFixtures::invoice(OwnerFixtures::payment($member));
    $parameters = str_starts_with($routeName, 'owner.invoices.') ? [$invoice] : [];

    $this->actingAs($owner)->get(route($routeName, $parameters))->assertOk();
})->with([
    'owner.dashboard',
    'owner.reports.index',
    'owner.reports.finance',
    'owner.reports.members',
    'owner.reports.classes',
    'owner.invoices.show',
    'owner.invoices.receipt',
]);
