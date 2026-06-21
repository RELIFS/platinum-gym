<?php

namespace App\Features\MemberPortal\Gateways;

use App\Features\MemberPortal\Contracts\StudentVerificationGateway;
use App\Features\MemberPortal\Support\StudentVerificationResult;
use App\Models\Member;
use Illuminate\Support\Facades\Http;
use Throwable;

class PddiktiStudentVerificationGateway implements StudentVerificationGateway
{
    public function verify(Member $member, string $memberName, string $studentIdNumber): StudentVerificationResult
    {
        if (! config('services.pddikti.enabled') || blank(config('services.pddikti.base_url')) || blank(config('services.pddikti.token'))) {
            return StudentVerificationResult::pending('manual', 'API PDDikti belum dikonfigurasi. Admin perlu review manual.');
        }

        try {
            $response = Http::withToken((string) config('services.pddikti.token'))
                ->acceptJson()
                ->timeout((int) config('services.pddikti.timeout', 10))
                ->connectTimeout(min((int) config('services.pddikti.timeout', 10), 5))
                ->get(rtrim((string) config('services.pddikti.base_url'), '/').'/students/verify', [
                    'name' => $memberName,
                    'nim' => $studentIdNumber,
                    'birth_date' => $member->birth_date?->toDateString(),
                ]);
        } catch (Throwable $exception) {
            report($exception);

            return StudentVerificationResult::pending('pddikti', 'PDDikti belum dapat dihubungi. Admin perlu review manual.');
        }

        if (! $response->successful()) {
            return StudentVerificationResult::pending('pddikti', 'PDDikti belum mengembalikan hasil valid. Admin perlu review manual.');
        }

        $payload = $response->json();
        $matched = (bool) data_get($payload, 'matched', false);
        $name = (string) data_get($payload, 'name', $memberName);

        return $matched
            ? StudentVerificationResult::verified('pddikti', 'Data cocok dengan PDDikti untuk '.$name.'.')
            : StudentVerificationResult::failed('pddikti', 'Nama lengkap dan NIM tidak cocok dengan PDDikti.');
    }
}
