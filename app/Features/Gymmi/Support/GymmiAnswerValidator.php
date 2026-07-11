<?php

namespace App\Features\Gymmi\Support;

class GymmiAnswerValidator
{
    /**
     * @return array{valid: bool, reason: string|null}
     */
    public function validate(?GymmiAnswerDraft $draft, GymmiEvidenceSet $evidence): array
    {
        if (! $draft) {
            return ['valid' => false, 'reason' => 'invalid_json'];
        }

        if (array_diff($draft->usedFactIds, $evidence->ids()) !== []) {
            return ['valid' => false, 'reason' => 'unknown_fact_id'];
        }

        if (preg_match('/\b(Gemini|fallback|provider|rate limit|snippet|prompt|data lokal|system instruction)\b/i', $draft->answer) === 1) {
            return ['valid' => false, 'reason' => 'internal_term'];
        }

        $allowedText = $evidence->promptContext();
        preg_match_all('/(?:https?:\/\/\S+|Rp[\d.]+|\b\d{1,2}[:.]\d{2}\b|\b\d+[\d.,]*\b)/u', $draft->answer, $matches);

        foreach ($matches[0] ?? [] as $literal) {
            if (! str_contains($allowedText, $literal)) {
                return ['valid' => false, 'reason' => 'unsupported_literal'];
            }
        }

        return ['valid' => true, 'reason' => null];
    }
}
