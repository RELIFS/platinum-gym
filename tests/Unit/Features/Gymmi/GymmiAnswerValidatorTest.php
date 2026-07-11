<?php

use App\Features\Gymmi\Support\GymmiAnswerDraft;
use App\Features\Gymmi\Support\GymmiAnswerValidator;
use App\Features\Gymmi\Support\GymmiEvidence;
use App\Features\Gymmi\Support\GymmiEvidenceSet;

it('rejects malformed unknown and fabricated provider output', function () {
    $validator = app(GymmiAnswerValidator::class);
    $evidence = new GymmiEvidenceSet([
        new GymmiEvidence('price-1', 'membership', 'public', 'public_live', 80, [
            'answer' => 'Harga Gym Umum Rp249.000 untuk 30 hari.',
        ], ['Rp249.000', '30']),
    ]);

    expect($validator->validate(GymmiAnswerDraft::fromJson('not-json'), $evidence)['reason'])->toBe('invalid_json')
        ->and($validator->validate(new GymmiAnswerDraft('Harga Rp249.000.', ['unknown']), $evidence)['reason'])->toBe('unknown_fact_id')
        ->and($validator->validate(new GymmiAnswerDraft('Harga Rp999.000.', ['price-1']), $evidence)['reason'])->toBe('unsupported_literal')
        ->and($validator->validate(new GymmiAnswerDraft('Gemini memakai data lokal.', ['price-1']), $evidence)['reason'])->toBe('internal_term')
        ->and($validator->validate(new GymmiAnswerDraft('Harga Gym Umum Rp249.000 untuk 30 hari.', ['price-1']), $evidence)['valid'])->toBeTrue();
});
