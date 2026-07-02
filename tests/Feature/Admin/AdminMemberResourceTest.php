<?php

use App\Models\AccountInvitation;
use App\Models\Member;
use App\Models\User;
use App\Notifications\Auth\MemberAccountInvitationNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Feature\Admin\Support\AdminPortalFixtures as AdminFixture;

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('admin can create member resource with member role and profile record', function () {
    Notification::fake();
    $admin = AdminFixture::admin();

    $this->actingAs($admin)
        ->post(route('admin.resources.store', 'members'), [
            'name' => 'Member Resource QA',
            'email' => 'member.resource.qa@gmail.com',
            'phone' => '081299990001',
            'gender' => 'female',
            'birth_date' => '2001-01-01',
            'address' => 'Padang',
            'emergency_contact' => '081299990002',
            'is_student' => '1',
            'student_verification_status' => 'verified',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.members'));

    $user = User::query()->where('email', 'member.resource.qa@gmail.com')->firstOrFail();
    $member = Member::query()->where('user_id', $user->id)->firstOrFail();

    expect($user->hasRole('member'))->toBeTrue()
        ->and($user->hasVerifiedEmail())->toBeFalse()
        ->and($member->gender)->toBe('female')
        ->and($member->student_verification_status)->toBe('verified')
        ->and($member->student_verified_at)->not->toBeNull()
        ->and(AccountInvitation::query()->where('user_id', $user->id)->whereNull('accepted_at')->exists())->toBeTrue();

    Notification::assertSentTo($user, MemberAccountInvitationNotification::class, function (MemberAccountInvitationNotification $notification) use ($user): bool {
        $rendered = $notification->toMail($user)->render();

        return str_contains($notification->acceptUrl, '/undangan-akun/')
            && str_contains($rendered, 'Atur Password &amp; Aktifkan Akun')
            && str_contains($rendered, 'Undangan member')
            && str_contains($rendered, 'hanya bisa dipakai satu kali');
    });
});

test('admin member resource protects unique user email validation', function () {
    $admin = AdminFixture::admin();
    AdminFixture::member(userOverrides: ['email' => 'duplicate.member@gmail.com']);

    $this->actingAs($admin)
        ->from(route('admin.resources.create', 'members'))
        ->post(route('admin.resources.store', 'members'), [
            'name' => 'Duplicate Member',
            'email' => 'duplicate.member@gmail.com',
            'gender' => 'male',
            'status' => 'active',
        ])
        ->assertRedirect(route('admin.resources.create', 'members'))
        ->assertSessionHasErrors('email');
});

test('admin members table shows operational student verification columns', function () {
    $admin = AdminFixture::admin();
    [, $student] = AdminFixture::member('PG-STUDENT-PENDING', [
        'name' => 'Mahasiswa Pending',
        'phone' => '081299990111',
    ], [
        'is_student' => true,
        'student_proof_path' => 'member/student-proofs/pending.jpg',
        'student_proof_uploaded_at' => now(),
        'student_verification_status' => 'pending_review',
    ]);
    AdminFixture::member('PG-MEMBER-UMUM', [
        'name' => 'Member Umum',
        'phone' => '081299990112',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.members'))
        ->assertOk()
        ->assertSee('Kode Member')
        ->assertSee('WhatsApp')
        ->assertSee('Kategori')
        ->assertSee('Verifikasi')
        ->assertSee('Mahasiswa Pending')
        ->assertSee('081299990111')
        ->assertSee('Mahasiswa')
        ->assertSee('Menunggu review')
        ->assertSee('Review Bukti')
        ->assertSee(route('admin.members.student-proof.review', $student), false)
        ->assertSee('Member Umum')
        ->assertSee('Umum');
});

test('admin can securely review approve and reject uploaded student proof', function () {
    Storage::fake('local');
    Storage::disk('local')->put('member/student-proofs/ktm.jpg', 'student-proof-image');

    $admin = AdminFixture::admin();
    [$memberUser, $member] = AdminFixture::member('PG-STUDENT-REVIEW', [
        'name' => 'Mahasiswa Review',
        'email' => 'student.review@example.test',
        'phone' => '081299990221',
    ], [
        'is_student' => true,
        'student_proof_path' => 'member/student-proofs/ktm.jpg',
        'student_proof_uploaded_at' => now(),
        'student_verification_status' => 'pending_review',
        'student_verification_note' => 'Menunggu review admin.',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.members.student-proof.review', $member))
        ->assertOk()
        ->assertSee('Review Bukti Mahasiswa')
        ->assertSee('Mahasiswa Review')
        ->assertSee('student.review@example.test')
        ->assertSee('PG-STUDENT-REVIEW')
        ->assertSee('Menunggu review')
        ->assertSee(route('admin.members.student-proof.show', $member), false);

    $this->actingAs($admin)
        ->get(route('admin.members.student-proof.show', $member))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertSee('student-proof-image', false);

    $this->actingAs($memberUser)
        ->get(route('admin.members.student-proof.show', $member))
        ->assertForbidden();

    $this->actingAs($admin)
        ->post(route('admin.members.student-proof.approve', $member), [
            'note' => 'KTM sesuai.',
        ])
        ->assertRedirect(route('admin.members.student-proof.review', $member));

    expect($member->refresh()->student_verification_status)->toBe('verified')
        ->and($member->student_verified_at)->not->toBeNull()
        ->and($member->student_verification_source)->toBe('admin')
        ->and($member->student_verification_note)->toBe('KTM sesuai.');

    $this->actingAs($admin)
        ->post(route('admin.members.student-proof.reject', $member), [
            'note' => 'Foto tidak terbaca.',
        ])
        ->assertRedirect(route('admin.members.student-proof.review', $member));

    expect($member->refresh()->student_verification_status)->toBe('failed')
        ->and($member->student_verified_at)->toBeNull()
        ->and($member->student_verification_source)->toBe('admin')
        ->and($member->student_verification_note)->toBe('Foto tidak terbaca.');
});
