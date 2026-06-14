<?php

namespace App\Features\Admin\Actions;

use App\Features\Admin\Support\AdminEditableSettingRegistry;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class UpdateAdminSettingsAction
{
    public function __construct(private readonly AdminEditableSettingRegistry $registry) {}

    public function handle(array $data): void
    {
        DB::transaction(function () use ($data): void {
            foreach ($this->registry->storagePayload($data) as $key => $value) {
                Setting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => $this->registry->typeFor($key),
                        'group' => $this->registry->groupFor($key),
                    ],
                );
            }

            activity()->event('updated')->log('Pengaturan website diperbarui dari admin.');
        });
    }
}
