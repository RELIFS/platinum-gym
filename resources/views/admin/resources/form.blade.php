@php
    $isEdit = $mode === 'edit';
    $title = ($isEdit ? 'Edit ' : 'Tambah ').$definition['singular'];
    $action = $isEdit ? route('admin.resources.update', ['resource' => $resource, 'id' => $model->getKey()]) : route('admin.resources.store', $resource);
@endphp

<x-admin-layout :portal='$portal' :navigation='$navigation' :title='$title'>
    <section class='admin-page-header'>
        <div class='flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between'>
            <div class='min-w-0 max-w-3xl'>
                <p class='admin-eyebrow'>{{ $definition['title'] }}</p>
                <h2 class='admin-title mt-3'>{{ $title }}</h2>
                <p class='mt-3 admin-copy'>{{ $definition['description'] }}</p>
            </div>
            <a href='{{ route($definition['index_route']) }}' class='admin-button-secondary shrink-0'>Kembali</a>
        </div>
    </section>
    <form method='POST' action='{{ $action }}' enctype='multipart/form-data' class='admin-card mt-6'>
        @csrf
        @if ($isEdit)
            @method('PATCH')
        @endif

        @if ($errors->any())
            <div class='mb-5 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm font-bold text-red-700 dark:text-red-200' role='alert'>Periksa kembali field yang ditandai.</div>
        @endif

        <div class='grid gap-5 md:grid-cols-2'>
            @foreach ($definition['fields'] as $field)
                @php
                    $name = $field['name'];
                    $type = $field['type'];
                    $fieldId = 'field-'.$name;
                    $value = old($name, $registry->value($resource, $name, $model));
                    $wide = in_array($type, ['textarea', 'file'], true);
                @endphp
                <label for='{{ $fieldId }}' class='{{ $wide ? 'md:col-span-2' : '' }} block min-w-0'>
                    <span class='text-sm font-black text-zinc-800 dark:text-zinc-100'>{{ $field['label'] }} @if ($field['required']) <span class='text-red-500' aria-hidden='true'>*</span> @endif</span>

                    @if ($type === 'textarea')
                        <textarea id='{{ $fieldId }}' name='{{ $name }}' rows='5' class='admin-form-input mt-2' @required($field['required'])>{{ $value }}</textarea>
                    @elseif ($type === 'select')
                        <select id='{{ $fieldId }}' name='{{ $name }}' class='admin-form-input mt-2' @required($field['required'])>
                            @if (! $field['required']) <option value=''>Pilih jika perlu</option> @endif
                            @foreach ($field['options'] as $optionValue => $optionLabel)
                                <option value='{{ $optionValue }}' @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'select-model')
                        <select id='{{ $fieldId }}' name='{{ $name }}' class='admin-form-input mt-2' @required($field['required'])>
                            <option value=''>Pilih data</option>
                            @foreach ($registry->options($field['source']) as $optionValue => $optionLabel)
                                <option value='{{ $optionValue }}' @selected((string) $value === (string) $optionValue)>{{ $optionLabel }}</option>
                            @endforeach
                        </select>
                    @elseif ($type === 'checkbox')
                        <input type='hidden' name='{{ $name }}' value='0'>
                        <span class='mt-2 flex min-h-11 items-center gap-3 rounded-lg border border-zinc-200 bg-white px-3 py-2 dark:border-white/10 dark:bg-zinc-950/45'>
                            <input id='{{ $fieldId }}' type='checkbox' name='{{ $name }}' value='1' @checked((bool) $value) class='h-5 w-5 rounded border-zinc-300 text-gold-500 focus:ring-gold-500 dark:border-white/20 dark:bg-zinc-950'>
                            <span class='text-sm font-bold text-zinc-700 dark:text-zinc-200'>Aktif</span>
                        </span>
                    @elseif ($type === 'file')
                        <input id='{{ $fieldId }}' type='file' name='{{ $name }}' accept='image/*' class='admin-form-input mt-2 file:mr-4 file:rounded-md file:border-0 file:bg-gold-500 file:px-3 file:py-2 file:text-sm file:font-black file:text-zinc-950'>
                    @else
                        <input id='{{ $fieldId }}' type='{{ $type }}' name='{{ $name }}' value='{{ $value }}' class='admin-form-input mt-2' @required($field['required'])>
                    @endif

                    @error($name)
                        <span class='mt-1 block text-xs font-bold text-red-600 dark:text-red-300' role='alert'>{{ $message }}</span>
                    @enderror
                </label>
            @endforeach
        </div>

        <div class='mt-6 flex flex-col-reverse gap-3 border-t border-zinc-200 pt-5 dark:border-white/10 sm:flex-row sm:justify-end'>
            <a href='{{ route($definition['index_route']) }}' class='admin-button-secondary'>Batal</a>
            <button type='submit' class='admin-button-primary'>Simpan {{ $definition['singular'] }}</button>
        </div>
    </form>
</x-admin-layout>
