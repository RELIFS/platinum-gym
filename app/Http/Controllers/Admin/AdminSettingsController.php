<?php

namespace App\Http\Controllers\Admin;

use App\Features\Admin\Actions\UpdateAdminSettingsAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateAdminSettingsRequest;
use Illuminate\Http\RedirectResponse;

class AdminSettingsController extends Controller
{
    public function update(UpdateAdminSettingsRequest $request, UpdateAdminSettingsAction $updateSettings): RedirectResponse
    {
        $updateSettings->handle($request->validated());

        return back()->with('status', 'Pengaturan website berhasil diperbarui.');
    }
}
