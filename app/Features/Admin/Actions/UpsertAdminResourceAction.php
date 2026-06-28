<?php

namespace App\Features\Admin\Actions;

use App\Features\Admin\Support\AdminResourceRegistry;
use App\Features\Auth\Actions\SendAccountInvitationAction;
use App\Models\Member;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class UpsertAdminResourceAction
{
    public function __construct(
        private readonly AdminResourceRegistry $registry,
        private readonly SendAccountInvitationAction $sendAccountInvitation,
    ) {}

    public function handle(string $resource, array $data, ?Model $model = null, ?User $actor = null): Model
    {
        return DB::transaction(function () use ($resource, $data, $model, $actor): Model {
            if ($resource === 'members') {
                return $this->upsertMember($data, $model instanceof Member ? $model : null, $actor);
            }

            $definition = $this->registry->definition($resource);
            $class = $definition['model'];
            $model ??= new $class;

            $payload = $this->normalizePayload($resource, $data, $model);

            if (array_key_exists('image_file', $data) && $data['image_file'] instanceof UploadedFile) {
                $payload['image_path'] = $this->storeImage($resource, $data['image_file']);
            }

            $model->fill(Arr::except($payload, ['image_file']));
            $model->save();

            activity()
                ->performedOn($model)
                ->event($model->wasRecentlyCreated ? 'created' : 'updated')
                ->log($definition['title'].' diperbarui dari admin.');

            return $model->refresh();
        });
    }

    private function upsertMember(array $data, ?Member $member, ?User $actor): Member
    {
        $user = $member?->user;
        $isNew = ! $user;

        if (! $user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make(Str::random(48)),
                'status' => $data['status'] ?? 'active',
            ]);

            $user->assignRole(Role::findOrCreate('member', 'web'));

            $member = new Member([
                'member_code' => Member::generateMemberCode(),
                'joined_at' => now()->toDateString(),
            ]);
            $member->user()->associate($user);
        } else {
            $user->forceFill([
                'name' => $data['name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'status' => $data['status'] ?? 'active',
            ])->save();
        }

        $member->fill(Arr::only($data, [
            'gender',
            'birth_date',
            'address',
            'emergency_contact',
            'is_student',
            'student_id_number',
            'student_verification_status',
            'student_verification_note',
            'status',
        ]));
        if (($data['student_verification_status'] ?? null) === 'verified') {
            $member->student_verified_at ??= now();
            $member->student_verification_source ??= 'admin';
        } elseif (array_key_exists('student_verification_status', $data)) {
            $member->student_verified_at = null;
            $member->student_verification_source = 'admin';
        }
        $member->save();

        activity()->performedOn($member)->event($member->wasRecentlyCreated ? 'created' : 'updated')->log('Anggota diperbarui dari admin.');

        if ($isNew) {
            $this->sendAccountInvitation->handle($user, $actor);
        }

        return $member->refresh();
    }

    private function normalizePayload(string $resource, array $data, ?Model $model = null): array
    {
        if ($resource === 'packages') {
            $data = $this->normalizePackageDurations($data);
        }

        if ($this->hasField($resource, 'slug') && blank($data['slug'] ?? null)) {
            $source = $data['name'] ?? $data['title'] ?? null;

            if (filled($source)) {
                $data['slug'] = $this->uniqueSlug($resource, Str::slug((string) $source), $model);
            }
        }

        foreach (['benefits', 'certifications'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = collect(preg_split('/\R/', (string) $data[$field]))
                    ->map(fn (string $line): string => trim($line))
                    ->filter()
                    ->values()
                    ->all();
            }
        }

        foreach ($this->registry->booleanFields($resource) as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        return $data;
    }

    private function normalizePackageDurations(array $data): array
    {
        $packageKind = (string) ($data['package_kind'] ?? '');

        if ($packageKind === 'membership') {
            $baseDuration = max((int) ($data['base_duration_days'] ?? $data['duration_days'] ?? 30), 1);
            $bonusDuration = max((int) ($data['bonus_duration_days'] ?? 0), 0);

            $data['base_duration_days'] = $baseDuration;
            $data['bonus_duration_days'] = $bonusDuration;
            $data['duration_days'] = $baseDuration + $bonusDuration;

            if ($bonusDuration < 1) {
                $data['bonus_label'] = null;
            }

            return $data;
        }

        $data['base_duration_days'] = blank($data['base_duration_days'] ?? null) ? null : max((int) $data['base_duration_days'], 1);
        $data['bonus_duration_days'] = 0;
        $data['bonus_label'] = null;

        return $data;
    }

    private function hasField(string $resource, string $field): bool
    {
        return collect($this->registry->definition($resource)['fields'])->contains('name', $field);
    }

    private function uniqueSlug(string $resource, string $base, ?Model $model): string
    {
        $definition = $this->registry->definition($resource);
        $class = $definition['model'];
        $base = trim($base, '-') ?: $resource;
        $base = Str::substr($base, 0, 160);
        $candidate = $base;
        $suffix = 2;

        while ($this->slugExists($class, $candidate, $model)) {
            $ending = '-'.$suffix;
            $candidate = Str::substr($base, 0, 180 - strlen($ending)).$ending;
            $suffix++;
        }

        return $candidate;
    }

    private function slugExists(string $class, string $candidate, ?Model $model): bool
    {
        return $class::query()
            ->where('slug', $candidate)
            ->when($model?->exists, fn ($query) => $query->whereKeyNot($model->getKey()))
            ->exists();
    }

    private function storeImage(string $resource, UploadedFile $file): string
    {
        $folder = match ($resource) {
            'products' => 'admin/products',
            'gallery' => 'admin/gallery',
            default => 'admin/uploads',
        };

        return 'storage/'.Storage::disk('public')->putFile($folder, $file);
    }
}
