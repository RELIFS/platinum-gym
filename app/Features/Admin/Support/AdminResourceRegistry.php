<?php

namespace App\Features\Admin\Support;

use App\Models\ClassSchedule;
use App\Models\Gallery;
use App\Models\GymClass;
use App\Models\Member;
use App\Models\Package;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promo;
use App\Models\Testimonial;
use App\Models\Trainer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class AdminResourceRegistry
{
    public function definition(string $resource): array
    {
        $definition = $this->definitions()[$resource] ?? null;

        abort_unless($definition, 404);

        return $definition;
    }

    public function model(string $resource, int|string $id): Model
    {
        $model = $this->definition($resource)['model'];

        return $model::query()->findOrFail($id);
    }

    public function definitions(): array
    {
        return [
            'members' => $this->resource('members', 'Anggota', Member::class, 'manage_members', 'admin.members', [
                ['name', 'Nama Lengkap', 'text', true], ['email', 'Email Login', 'email', true], ['phone', 'No. WhatsApp', 'text'],
                ['gender', 'Gender', 'select', true, ['male' => 'Laki-laki', 'female' => 'Perempuan']], ['birth_date', 'Tanggal Lahir', 'date'],
                ['address', 'Alamat', 'textarea'], ['emergency_contact', 'Kontak Darurat', 'text'], ['is_student', 'Kategori Mahasiswa', 'checkbox'],
                ['status', 'Status Member', 'select', true, ['active' => 'Aktif', 'inactive' => 'Nonaktif']],
            ], 'Kelola akun member, profil, dan status akses.'),
            'packages' => $this->resource('packages', 'Paket', Package::class, 'manage_packages', 'admin.packages', [
                ['name', 'Nama Paket', 'text', true], ['slug', 'Slug URL', 'text'], ['package_kind', 'Jenis Paket', 'select', true, ['membership' => 'Membership', 'session' => 'Paket Sesi']],
                ['type', 'Tipe Akses', 'select', true, ['gym' => 'Gym', 'aerobic' => 'Aerobic', 'zumba' => 'Zumba', 'muaythai' => 'Muaythai', 'pt' => 'Personal Trainer', 'include' => 'Include']],
                ['category', 'Kategori', 'text'], ['gender_restriction', 'Batas Jenis Kelamin', 'select', false, ['all' => 'Semua', 'male' => 'Laki-laki', 'female' => 'Perempuan']],
                ['max_age', 'Usia Maksimal', 'number'], ['price', 'Harga', 'number', true], ['promo_price', 'Harga Promo', 'number'],
                ['promo_starts_at', 'Mulai Promo', 'datetime-local'], ['promo_ends_at', 'Selesai Promo', 'datetime-local'], ['base_duration_days', 'Durasi Dasar Hari', 'number'],
                ['bonus_duration_days', 'Bonus Durasi Hari', 'number'], ['bonus_label', 'Label Bonus', 'text'], ['duration_days', 'Durasi Total Hari', 'number'],
                ['session_count', 'Jumlah Sesi', 'number'], ['requires_active_membership', 'Wajib Membership Aktif', 'checkbox'], ['description', 'Deskripsi', 'textarea'],
                ['benefits', 'Manfaat Paket', 'textarea'], ['is_active', 'Paket Aktif', 'checkbox'],
            ], 'Kelola paket membership dan paket sesi.'),
            'classes' => $this->resource('classes', 'Kelas', GymClass::class, 'manage_classes', 'admin.classes', [
                ['name', 'Nama Kelas', 'text', true], ['slug', 'Slug URL', 'text'], ['class_type', 'Jenis Kelas', 'select', true, ['gym' => 'Gym', 'aerobic' => 'Aerobic', 'zumba' => 'Zumba', 'muaythai' => 'Muaythai', 'pt' => 'Personal Trainer']],
                ['access_type', 'Tipe Akses', 'select', true, ['included' => 'Termasuk Membership', 'session_based' => 'Paket Sesi', 'paid' => 'Berbayar']],
                ['required_package_type', 'Paket Dibutuhkan', 'text'], ['capacity', 'Kapasitas', 'number', true], ['member_price', 'Harga Member', 'number'],
                ['non_member_price', 'Harga Non-member', 'number'], ['promo_price', 'Harga Promo', 'number'], ['description', 'Deskripsi', 'textarea'], ['is_active', 'Kelas Aktif', 'checkbox'],
            ], 'Kelola kelas dan aturan aksesnya.'),
            'class-schedules' => $this->resource('class-schedules', 'Jadwal Kelas', ClassSchedule::class, 'manage_classes', 'admin.classes', [
                ['gym_class_id', 'Kelas', 'select-model', true, 'classes'], ['trainer_id', 'Trainer', 'select-model', false, 'trainers'],
                ['day_of_week', 'Hari', 'select', true, [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu']],
                ['start_time', 'Jam Mulai', 'time', true], ['end_time', 'Jam Selesai', 'time', true], ['room', 'Ruangan', 'text'], ['capacity', 'Kapasitas', 'number'], ['is_active', 'Jadwal Aktif', 'checkbox'],
            ], 'Kelola hari, jam, trainer, dan kapasitas.'),
            'product-categories' => $this->resource('product-categories', 'Kategori Produk', ProductCategory::class, 'manage_products', 'admin.products', [
                ['name', 'Nama Kategori', 'text', true], ['slug', 'Slug URL', 'text'], ['description', 'Deskripsi', 'textarea'], ['sort_order', 'Urutan', 'number'], ['is_active', 'Kategori Aktif', 'checkbox'],
            ], 'Kelola kategori katalog produk.'),
            'products' => $this->resource('products', 'Produk', Product::class, 'manage_products', 'admin.products', [
                ['category_id', 'Kategori', 'select-model', false, 'product-categories'], ['name', 'Nama Produk', 'text', true], ['slug', 'Slug URL', 'text'],
                ['price', 'Harga', 'number', true], ['stock', 'Stok', 'number', true], ['description', 'Deskripsi', 'textarea'], ['image_file', 'Foto Produk', 'file'],
                ['image_alt', 'Deskripsi Foto', 'text'], ['is_active', 'Produk Aktif', 'checkbox'],
            ], 'Kelola katalog, stok, dan foto produk.'),
            'gallery' => $this->resource('gallery', 'Galeri', Gallery::class, 'manage_content', 'admin.gallery', [
                ['title', 'Judul', 'text'], ['caption', 'Caption', 'textarea'], ['image_file', 'Foto Galeri', 'file'], ['image_alt', 'Deskripsi Foto', 'text'], ['sort_order', 'Urutan', 'number'], ['is_published', 'Tayang di Website', 'checkbox'],
            ], 'Kelola foto fasilitas dan aktivitas.'),
            'testimonials' => $this->resource('testimonials', 'Testimoni', Testimonial::class, 'manage_content', 'admin.testimonials', [
                ['member_id', 'Member Terkait', 'select-model', false, 'members'], ['name', 'Nama Penampil', 'text', true], ['role', 'Label Tampilan', 'text'],
                ['content', 'Isi Testimoni', 'textarea', true], ['rating', 'Rating', 'star-rating', true], ['sort_order', 'Urutan', 'number'], ['is_published', 'Tayang di Website', 'checkbox'],
            ], 'Kelola testimoni publik.'),
            'promos' => $this->resource('promos', 'Promo', Promo::class, 'manage_content', 'admin.promos', [
                ['package_id', 'Paket Terkait', 'select-model', false, 'packages'], ['title', 'Judul Promo', 'text', true], ['slug', 'Slug URL', 'text'], ['description', 'Deskripsi', 'textarea'], ['starts_at', 'Mulai', 'datetime-local'], ['ends_at', 'Selesai', 'datetime-local'],
                ['discount_type', 'Tipe Diskon', 'select', false, ['none' => 'Tidak Ada', 'percentage' => 'Persen', 'fixed' => 'Nominal']], ['discount_value', 'Nilai Diskon', 'number'], ['sort_order', 'Urutan', 'number'], ['is_published', 'Tayang di Website', 'checkbox'],
            ], 'Kelola promo resmi website.'),
            'trainers' => $this->resource('trainers', 'Trainer', Trainer::class, 'manage_trainers', 'admin.trainers', [
                ['name', 'Nama Trainer', 'text', true], ['specialization', 'Spesialisasi', 'text'], ['experience_years', 'Pengalaman (tahun)', 'number'],
                ['certifications', 'Sertifikasi', 'textarea'], ['bio', 'Profil Singkat', 'textarea'], ['is_active', 'Trainer Aktif', 'checkbox'],
            ], 'Kelola trainer dan status aktif.'),
        ];
    }

    public function rules(string $resource, ?Model $model = null): array
    {
        return match ($resource) {
            'members' => $this->memberRules($model),
            'packages' => $this->slugRules('packages', $model) + ['name' => ['required', 'string', 'max:120'], 'package_kind' => ['required', Rule::in(['membership', 'session'])], 'type' => ['required', 'string', 'max:40'], 'category' => ['nullable', 'string', 'max:30'], 'gender_restriction' => ['nullable', Rule::in(['all', 'male', 'female'])], 'max_age' => ['nullable', 'integer', 'min:1', 'max:120'], 'price' => ['required', 'numeric', 'min:0'], 'promo_price' => ['nullable', 'numeric', 'min:0'], 'promo_starts_at' => ['nullable', 'date'], 'promo_ends_at' => ['nullable', 'date', 'after_or_equal:promo_starts_at'], 'base_duration_days' => ['required_if:package_kind,membership', 'nullable', 'integer', 'min:1'], 'bonus_duration_days' => ['nullable', 'integer', 'min:0'], 'bonus_label' => ['nullable', 'string', 'max:80'], 'duration_days' => ['nullable', 'integer', 'min:1'], 'session_count' => ['nullable', 'integer', 'min:1'], 'requires_active_membership' => ['boolean'], 'description' => ['nullable', 'string', 'max:2000'], 'benefits' => ['nullable', 'string', 'max:3000'], 'is_active' => ['boolean']],
            'classes' => $this->slugRules('gym_classes', $model) + ['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:2500'], 'class_type' => ['required', 'string', 'max:40'], 'access_type' => ['required', Rule::in(['included', 'session_based', 'paid'])], 'required_package_type' => ['nullable', 'string', 'max:40'], 'capacity' => ['required', 'integer', 'min:1', 'max:1000'], 'member_price' => ['nullable', 'numeric', 'min:0'], 'non_member_price' => ['nullable', 'numeric', 'min:0'], 'promo_price' => ['nullable', 'numeric', 'min:0'], 'is_active' => ['boolean']],
            'class-schedules' => ['gym_class_id' => ['required', 'exists:gym_classes,id'], 'trainer_id' => ['nullable', 'exists:trainers,id'], 'day_of_week' => ['required', 'integer', 'between:1,7'], 'start_time' => ['required', 'date_format:H:i'], 'end_time' => ['required', 'date_format:H:i', 'after:start_time'], 'room' => ['nullable', 'string', 'max:50'], 'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'], 'is_active' => ['boolean']],
            'product-categories' => $this->slugRules('product_categories', $model) + ['name' => ['required', 'string', 'max:120'], 'description' => ['nullable', 'string', 'max:2000'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_active' => ['boolean']],
            'products' => $this->slugRules('products', $model) + ['category_id' => ['nullable', 'exists:product_categories,id'], 'name' => ['required', 'string', 'max:150'], 'price' => ['required', 'numeric', 'min:0'], 'stock' => ['required', 'integer', 'min:0'], 'description' => ['nullable', 'string', 'max:2500'], 'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'], 'image_alt' => ['nullable', 'string', 'max:180'], 'is_active' => ['boolean']],
            'gallery' => ['title' => ['nullable', 'string', 'max:150'], 'caption' => ['nullable', 'string', 'max:255'], 'image_file' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'], 'image_alt' => ['nullable', 'string', 'max:180'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_published' => ['boolean']],
            'testimonials' => ['member_id' => ['nullable', 'exists:members,id'], 'name' => ['required', 'string', 'max:120'], 'role' => ['nullable', 'string', 'max:120'], 'content' => ['required', 'string', 'max:2000'], 'rating' => ['required', 'integer', 'between:1,5'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_published' => ['boolean']],
            'promos' => $this->slugRules('promos', $model) + ['package_id' => ['nullable', 'exists:packages,id'], 'title' => ['required', 'string', 'max:150'], 'description' => ['nullable', 'string', 'max:2500'], 'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'discount_type' => ['nullable', Rule::in(['none', 'percentage', 'fixed'])], 'discount_value' => ['nullable', 'numeric', 'min:0'], 'sort_order' => ['nullable', 'integer', 'min:0'], 'is_published' => ['boolean']],
            'trainers' => ['name' => ['required', 'string', 'max:150'], 'specialization' => ['nullable', 'string', 'max:150'], 'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'], 'certifications' => ['nullable', 'string', 'max:3000'], 'bio' => ['nullable', 'string', 'max:2500'], 'is_active' => ['boolean']],
            default => [],
        };
    }

    public function value(string $resource, string $field, ?Model $model): mixed
    {
        if (! $model) {
            return in_array($field, $this->booleanFields($resource), true);
        }

        if ($resource === 'members') {
            $model->loadMissing('user');

            return match ($field) {
                'name' => $model->user?->name,
                'email' => $model->user?->email,
                'phone' => $model->user?->phone,
                default => $model->getAttribute($field),
            };
        }

        $value = $model->getAttribute($field);
        if (is_array($value)) {
            return implode(PHP_EOL, $value);
        }
        if ($value instanceof \DateTimeInterface) {
            return str_contains($field, '_at') ? $value->format('Y-m-d\TH:i') : $value->format('Y-m-d');
        }

        return $value;
    }

    public function options(string $source): array
    {
        return match ($source) {
            'classes' => GymClass::query()->orderBy('name')->pluck('name', 'id')->all(),
            'trainers' => Trainer::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id')->all(),
            'packages' => Package::query()->where('is_active', true)->orderBy('package_kind')->orderBy('name')->pluck('name', 'id')->all(),
            'product-categories' => ProductCategory::query()->orderBy('sort_order')->orderBy('name')->pluck('name', 'id')->all(),
            'members' => Member::query()->with('user')->latest()->limit(200)->get()->mapWithKeys(fn (Member $member): array => [$member->id => ($member->user?->name ?? $member->member_code).' - '.$member->member_code])->all(),
            default => [],
        };
    }

    public function booleanFields(string $resource): array
    {
        return collect($this->definition($resource)['fields'])->where('type', 'checkbox')->pluck('name')->all();
    }

    private function resource(string $resource, string $title, string $model, string $permission, string $indexRoute, array $fields, string $description): array
    {
        return ['title' => $title, 'singular' => $title, 'model' => $model, 'permission' => $permission, 'index_route' => $indexRoute, 'fields' => array_map(fn (array $field): array => $this->field($resource, ...$field), $fields), 'description' => $description];
    }

    private function field(string $resource, string $name, string $label, string $type, bool $required = false, array|string|null $options = null): array
    {
        return array_merge(
            ['name' => $name, 'label' => $label, 'type' => $type, 'required' => $required, 'options' => is_array($options) ? $options : [], 'source' => is_string($options) ? $options : null],
            $this->fieldMicrocopy($resource, $name),
        );
    }

    private function fieldMicrocopy(string $resource, string $name): array
    {
        $defaults = [
            'slug' => ['help' => 'Kosongkan agar sistem membuat slug otomatis dari nama.'],
            'email' => ['placeholder' => 'nama@email.com'],
            'phone' => ['placeholder' => '08xxxxxxxxxx'],
            'address' => ['placeholder' => 'Alamat lengkap member'],
            'emergency_contact' => ['placeholder' => '08xxxxxxxxxx'],
            'price' => ['placeholder' => 'Contoh: 250000', 'help' => 'Masukkan nominal tanpa titik atau simbol rupiah.'],
            'promo_price' => ['placeholder' => 'Contoh: 200000', 'help' => 'Isi hanya jika harga promo sedang berlaku.'],
            'promo_starts_at' => ['help' => 'Kosongkan bila promo langsung aktif setelah disimpan.'],
            'promo_ends_at' => ['help' => 'Kosongkan bila promo tidak memiliki tanggal selesai.'],
            'base_duration_days' => ['placeholder' => 'Contoh: 90', 'help' => 'Durasi utama paket membership sebelum bonus.'],
            'bonus_duration_days' => ['placeholder' => 'Contoh: 30', 'help' => 'Isi 0 jika paket tidak memiliki bonus durasi.'],
            'bonus_label' => ['placeholder' => 'Contoh: Gratis 1 bulan'],
            'duration_days' => ['placeholder' => 'Contoh: 120', 'help' => 'Total durasi efektif yang dipakai sistem.'],
            'session_count' => ['placeholder' => 'Contoh: 8', 'help' => 'Jumlah sesi untuk paket sesi.'],
            'category' => ['placeholder' => 'Contoh: Reguler'],
            'description' => ['placeholder' => 'Tulis deskripsi singkat dan jelas.'],
            'benefits' => ['placeholder' => 'Tulis manfaat utama, satu per baris bila perlu.'],
            'required_package_type' => ['placeholder' => 'Contoh: gym'],
            'room' => ['placeholder' => 'Contoh: Studio 1'],
            'capacity' => ['placeholder' => 'Contoh: 20'],
            'member_price' => ['placeholder' => 'Contoh: 50000'],
            'non_member_price' => ['placeholder' => 'Contoh: 75000'],
            'stock' => ['placeholder' => 'Contoh: 12'],
            'caption' => ['placeholder' => 'Caption singkat untuk galeri.'],
            'role' => ['placeholder' => 'Contoh: Member aktif'],
            'content' => ['placeholder' => 'Tulis testimoni singkat.'],
            'specialization' => ['placeholder' => 'Contoh: Muaythai'],
            'experience_years' => ['placeholder' => 'Contoh: 5'],
            'certifications' => ['placeholder' => 'Tulis sertifikasi yang relevan.'],
            'bio' => ['placeholder' => 'Profil singkat trainer.'],
            'image_file' => ['help' => 'Gunakan JPG, PNG, atau WebP sesuai batas ukuran validasi.'],
            'image_alt' => ['placeholder' => 'Deskripsi singkat isi foto', 'help' => 'Tulis deskripsi singkat agar gambar mudah dipahami.'],
            'sort_order' => ['placeholder' => 'Contoh: 10', 'help' => 'Angka kecil tampil lebih dulu.'],
            'discount_value' => ['placeholder' => 'Contoh: 10', 'help' => 'Sesuaikan dengan tipe diskon yang dipilih.'],
            'starts_at' => ['help' => 'Kosongkan jika promo langsung tayang.'],
            'ends_at' => ['help' => 'Kosongkan jika promo tidak punya tanggal selesai.'],
            'rating' => ['help' => 'Pilih rating 1 sampai 5 untuk testimoni.'],
            'is_active' => ['help' => 'Nonaktifkan untuk menyembunyikan dari alur operasional.'],
            'is_published' => ['help' => 'Nonaktifkan untuk menyimpan sebagai draft.'],
        ];

        $resourceSpecific = [
            'members.name' => ['placeholder' => 'Contoh: Muhammad Luthfi'],
            'packages.name' => ['placeholder' => 'Contoh: Gym Umum 1 Bulan'],
            'classes.name' => ['placeholder' => 'Contoh: Zumba Sore'],
            'class-schedules.gym_class_id' => ['placeholder' => 'Pilih kelas'],
            'class-schedules.trainer_id' => ['placeholder' => 'Pilih trainer jika ada'],
            'product-categories.name' => ['placeholder' => 'Contoh: Suplemen'],
            'products.name' => ['placeholder' => 'Contoh: Whey Protein'],
            'products.category_id' => ['placeholder' => 'Pilih kategori produk'],
            'gallery.title' => ['placeholder' => 'Contoh: Area Latihan Beban'],
            'testimonials.member_id' => ['placeholder' => 'Pilih member jika ada'],
            'testimonials.name' => ['placeholder' => 'Contoh: Rina'],
            'promos.package_id' => ['placeholder' => 'Pilih paket terkait jika ada'],
            'promos.title' => ['placeholder' => 'Contoh: Beli Gym Umum 3 Bulan Gratis 1 Bulan'],
            'trainers.name' => ['placeholder' => 'Contoh: Coach Riko'],
        ];

        return array_merge($defaults[$name] ?? [], $resourceSpecific[$resource.'.'.$name] ?? []);
    }

    private function memberRules(?Model $model): array
    {
        $userId = $model instanceof Member ? $model->user_id : null;

        return ['name' => ['required', 'string', 'max:150'], 'email' => ['required', 'email:rfc,dns', 'max:150', Rule::unique('users', 'email')->ignore($userId)], 'phone' => ['nullable', 'string', 'max:20', Rule::unique('users', 'phone')->ignore($userId)], 'gender' => ['required', Rule::in(['male', 'female'])], 'birth_date' => ['nullable', 'date', 'before:today'], 'address' => ['nullable', 'string', 'max:2000'], 'emergency_contact' => ['nullable', 'string', 'max:20'], 'is_student' => ['boolean'], 'student_verification_status' => ['nullable', Rule::in(['unverified', 'pending_review', 'verified', 'failed'])], 'student_verification_note' => ['nullable', 'string', 'max:2000'], 'status' => ['required', Rule::in(['active', 'inactive'])]];
    }

    private function slugRules(string $table, ?Model $model): array
    {
        return ['slug' => ['nullable', 'string', 'max:180', Rule::unique($table, 'slug')->ignore($model?->getKey())]];
    }
}
