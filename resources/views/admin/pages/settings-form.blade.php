@php
    $editableSettings = $portal['editableSettings'] ?? [];
    $settingFields = collect($editableSettings['fields'] ?? []);
    $settingGroups = collect($editableSettings['groups'] ?? []);
    $settingValues = $editableSettings['values'] ?? [];
@endphp

<section class="admin-card mt-6" data-admin-settings-form>
    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
        <div class="min-w-0">
            <p class="admin-eyebrow">Pengaturan Website</p>
            <h2 class="mt-2 text-xl font-black text-zinc-950 dark:text-white">Informasi publik website</h2>
            <p class="mt-2 admin-copy">Perbarui informasi yang tampil di website publik. Credential, token, dan API key tidak diedit dari halaman ini.</p>
        </div>
    </div>

    <x-confirm-form
        :action="route('admin.settings.update')"
        method="PATCH"
        message="Simpan perubahan pengaturan website? Perubahan langsung tampil di website publik."
        title="Simpan Pengaturan"
        confirm-label="Simpan"
        variant="primary"
        class="mt-5 grid gap-5"
    >
        @foreach ($settingGroups as $groupKey => $group)
            @php
                $fields = $settingFields->where('group', $groupKey)->values();
            @endphp

            @if ($fields->isNotEmpty())
                <fieldset class="admin-panel">
                    <legend class="sr-only">{{ $group['label'] ?? str($groupKey)->headline() }}</legend>
                    <div class="mb-4 min-w-0">
                        <p class="admin-eyebrow">{{ $group['label'] ?? str($groupKey)->headline() }}</p>
                        <p class="mt-2 admin-copy">{{ $group['description'] ?? 'Lengkapi pengaturan yang dibutuhkan.' }}</p>
                    </div>

                    <div class="grid min-w-0 gap-4 xl:grid-cols-2">
                        @foreach ($fields as $field)
                            @php
                                $value = old($field['name'], $settingValues[$field['name']] ?? '');
                                $fieldId = 'admin-setting-'.$field['name'];
                                $helpId = $fieldId.'-help';
                                $errorId = $fieldId.'-error';
                                $hasError = $errors->has($field['name']);
                                $isFullWidth = ($field['type'] ?? 'text') === 'textarea' || (bool) ($field['fullWidth'] ?? false);
                            @endphp
                            <label for="{{ $fieldId }}" class="admin-field {{ $isFullWidth ? 'xl:col-span-2' : '' }}">
                                <span class="admin-field-label">{{ $field['label'] }}</span>
                                @if (($field['type'] ?? 'text') === 'textarea')
                                    <textarea
                                        id="{{ $fieldId }}"
                                        name="{{ $field['name'] }}"
                                        rows="3"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        class="admin-form-input"
                                        aria-describedby="{{ $helpId }}{{ $hasError ? ' '.$errorId : '' }}"
                                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                                    >{{ $value }}</textarea>
                                @else
                                    <input
                                        id="{{ $fieldId }}"
                                        type="{{ $field['type'] ?? 'text' }}"
                                        name="{{ $field['name'] }}"
                                        value="{{ $value }}"
                                        placeholder="{{ $field['placeholder'] ?? '' }}"
                                        autocomplete="{{ $field['autocomplete'] ?? 'off' }}"
                                        class="admin-form-input"
                                        aria-describedby="{{ $helpId }}{{ $hasError ? ' '.$errorId : '' }}"
                                        aria-invalid="{{ $hasError ? 'true' : 'false' }}"
                                    >
                                @endif
                                <span id="{{ $helpId }}" class="admin-field-help">{{ $field['help'] ?? 'Pengaturan ini dipakai oleh website publik.' }}</span>
                                @error($field['name'])
                                    <span id="{{ $errorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
                                @enderror
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endif
        @endforeach

        <aside class="rounded-lg border border-amber-500/25 bg-amber-500/10 px-4 py-3 text-sm font-bold leading-6 text-amber-800 dark:text-amber-200" role="note">
            Konfigurasi sensitif seperti credential, token, password, private key, dan API key tidak ditampilkan atau diedit dari halaman ini.
        </aside>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <p class="text-sm font-semibold leading-6 text-zinc-500 dark:text-zinc-400">Periksa kembali nomor kontak dan link peta sebelum menyimpan.</p>
            <button type="submit" class="admin-button-primary">Simpan Pengaturan</button>
        </div>
    </x-confirm-form>
</section>
