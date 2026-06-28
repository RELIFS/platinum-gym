@php
    $isEdit = $mode === 'edit';
    $title = ($isEdit ? 'Edit ' : 'Tambah ').$definition['singular'];
    $action = $isEdit ? route('admin.resources.update', ['resource' => $resource, 'id' => $model->getKey()]) : route('admin.resources.store', $resource);
@endphp

<x-admin-layout :portal="$portal" :navigation="$navigation" :title="$title">
    <x-admin.page-header :eyebrow="$definition['title']" :title="$title" :description="$definition['description']">
        <x-slot:actions>
            <a href="{{ route($definition['index_route']) }}" class="admin-button-secondary shrink-0">Kembali</a>
        </x-slot:actions>
    </x-admin.page-header>

    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" class="admin-card mt-6" x-data="{ saving: false }" x-on:submit="saving = true">
        @csrf
        @if ($isEdit)
            @method('PATCH')
        @endif

        @if ($errors->any())
            <div class="mb-5 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold leading-6 text-red-700 dark:text-red-200" role="alert">
                Periksa kembali data yang ditandai merah.
            </div>
        @endif

        <div class="admin-form-section">
            @foreach ($definition['fields'] as $field)
                @php
                    $name = $field['name'];
                    $type = $field['type'];
                    $fieldId = 'field-'.$name;
                    $helpId = $fieldId.'-help';
                    $errorId = $fieldId.'-error';
                    $hasError = $errors->has($name);
                    $value = old($name, $registry->value($resource, $name, $model));
                    $wide = in_array($type, ['textarea', 'file', 'star-rating'], true);
                    $help = $field['help'] ?? null;
                    $describedBy = trim(($help ? $helpId : '').($hasError ? ' '.$errorId : ''));
                    $placeholder = $field['placeholder'] ?? '';
                @endphp

                @if ($type === 'star-rating')
                    <fieldset class="admin-field {{ $wide ? 'md:col-span-2' : '' }}">
                        <legend class="admin-field-label">
                            {{ $field['label'] }}
                            @if ($field['required'])
                                <span class="admin-required" aria-hidden="true">*</span>
                            @endif
                        </legend>

                        <span class="admin-star-rating" role="radiogroup" aria-label="{{ $field['label'] }}" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                            <span class="admin-star-rating-options">
                                @for ($rating = 5; $rating >= 1; $rating--)
                                    @php
                                        $ratingId = $fieldId.'-'.$rating;
                                    @endphp
                                    <input id="{{ $ratingId }}" type="radio" name="{{ $name }}" value="{{ $rating }}" class="admin-star-rating-input" @checked((int) $value === $rating) @required($field['required']) @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                                    <label for="{{ $ratingId }}" class="admin-star-rating-star">
                                        <span aria-hidden="true">&#9733;</span>
                                        <span class="sr-only">{{ $rating }} dari 5</span>
                                    </label>
                                @endfor
                            </span>
                            <span class="admin-star-rating-caption" aria-hidden="true">Klik bintang untuk memilih rating.</span>
                        </span>

                        @if ($help)
                            <span id="{{ $helpId }}" class="admin-field-help">{{ $help }}</span>
                        @endif

                        @error($name)
                            <span id="{{ $errorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
                        @enderror
                    </fieldset>

                    @continue
                @endif

                <label for="{{ $fieldId }}" class="admin-field {{ $wide ? 'md:col-span-2' : '' }}">
                    <span class="admin-field-label">
                        {{ $field['label'] }}
                        @if ($field['required'])
                            <span class="admin-required" aria-hidden="true">*</span>
                        @endif
                    </span>

                    @if ($type === 'textarea')
                        <textarea id="{{ $fieldId }}" name="{{ $name }}" rows="5" class="admin-form-input" placeholder="{{ $placeholder }}" @required($field['required']) @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>{{ $value }}</textarea>
                    @elseif ($type === 'select')
                        <select id="{{ $fieldId }}" name="{{ $name }}" class="admin-form-input" @required($field['required']) @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                            @if (! $field['required'])
                                <option value="">{{ $placeholder !== '' ? $placeholder : 'Tidak dibatasi' }}</option>
                            @endif
                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'select-model')
                        <select id="{{ $fieldId }}" name="{{ $name }}" class="admin-form-input" @required($field['required']) @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                            <option value="">{{ $placeholder !== '' ? $placeholder : 'Pilih '.str($field['label'])->lower() }}</option>
                            @foreach ($registry->options($field['source']) as $optionValue => $optionLabel)
                                <option value="{{ $optionValue }}" @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'checkbox')
                        <input type="hidden" name="{{ $name }}" value="0">
                        <span class="admin-choice-field">
                            <input id="{{ $fieldId }}" type="checkbox" name="{{ $name }}" value="1" @checked((bool) $value) class="admin-choice-input" @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                            <span class="text-sm font-bold text-zinc-700 dark:text-zinc-200">{{ $field['checkbox_label'] ?? 'Aktif' }}</span>
                        </span>
                    @elseif ($type === 'file')
                        @php
                            $existingFile = null;
                            if ($isEdit && $model) {
                                $candidateAttributes = [$name, str($name)->replaceLast('_file', '_path')->toString(), 'image_path', 'image_url'];
                                foreach ($candidateAttributes as $candidateAttribute) {
                                    $candidateValue = $model->getAttribute($candidateAttribute);
                                    if (filled($candidateValue)) {
                                        $existingFile = (string) $candidateValue;
                                        break;
                                    }
                                }
                            }
                            $existingFileUrl = filled($existingFile)
                                ? (\Illuminate\Support\Str::startsWith($existingFile, ['http://', 'https://']) ? $existingFile : asset($existingFile))
                                : null;
                        @endphp

                        @if (filled($existingFileUrl))
                            <span class="admin-upload-preview">
                                <img src="{{ $existingFileUrl }}" alt="Pratinjau {{ $field['label'] }}" class="h-16 w-16 shrink-0 rounded-md object-cover" loading="lazy">
                                <span class="min-w-0">
                                    <span class="block text-xs font-black uppercase tracking-[0.12em] text-zinc-500 dark:text-zinc-400">File saat ini</span>
                                    <a href="{{ $existingFileUrl }}" target="_blank" rel="noopener" class="mt-1 inline-block break-all text-xs font-bold text-gold-600 underline-offset-2 hover:underline dark:text-gold-400">Lihat file</a>
                                </span>
                            </span>
                        @endif

                        <input id="{{ $fieldId }}" type="file" name="{{ $name }}" accept="image/*" class="admin-form-input admin-upload-field" @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>

                        @if (filled($existingFileUrl))
                            <span class="admin-field-help">Pilih file baru untuk mengganti. Kosongkan untuk mempertahankan file saat ini.</span>
                        @endif
                    @elseif (in_array($type, ['date', 'datetime-local'], true))
                        <x-local-date-input
                            :id="$fieldId"
                            :name="$name"
                            :value="$value"
                            :mode="$type === 'datetime-local' ? 'datetime' : 'date'"
                            class="admin-form-input"
                            :required="$field['required']"
                            :described-by="$describedBy !== '' ? $describedBy : null"
                        />
                    @else
                        <input id="{{ $fieldId }}" type="{{ $type }}" name="{{ $name }}" value="{{ $value }}" class="admin-form-input" placeholder="{{ $placeholder }}" @required($field['required']) @if ($hasError) aria-invalid="true" @endif @if ($describedBy !== '') aria-describedby="{{ $describedBy }}" @endif>
                    @endif

                    @if ($help)
                        <span id="{{ $helpId }}" class="admin-field-help">{{ $help }}</span>
                    @endif

                    @error($name)
                        <span id="{{ $errorId }}" class="admin-field-error" role="alert">{{ $message }}</span>
                    @enderror

                </label>
            @endforeach
        </div>

        <div class="admin-form-actions">
            @if ($isEdit && $resource === 'members' && $model?->user && ! $model->user->hasVerifiedEmail())
                <button type="submit" form="resend-member-invitation-form" class="admin-button-secondary">Kirim ulang undangan</button>
            @endif
            <a href="{{ route($definition['index_route']) }}" class="admin-button-secondary">Batal</a>
            <button type="submit" class="admin-button-primary" x-bind:disabled="saving">
                <span x-show="!saving">Simpan {{ $definition['singular'] }}</span>
                <span x-show="saving" x-cloak>Menyimpan...</span>
            </button>
        </div>
    </form>

    @if ($isEdit && $resource === 'members' && $model?->user && ! $model->user->hasVerifiedEmail())
        <form id="resend-member-invitation-form" method="POST" action="{{ route('admin.members.invitation.send', $model) }}">
            @csrf
        </form>
    @endif
</x-admin-layout>
