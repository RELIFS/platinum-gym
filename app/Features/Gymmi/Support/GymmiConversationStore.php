<?php

namespace App\Features\Gymmi\Support;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class GymmiConversationStore
{
    /**
     * @return array{id: string, reset: bool, state: array<string, mixed>}
     */
    public function open(?string $token, string $surface, ?User $user, string $sessionId): array
    {
        $reset = filled($token);

        if (filled($token)) {
            $state = Cache::get($this->cacheKey((string) $token));

            if (is_array($state) && hash_equals((string) ($state['binding'] ?? ''), $this->binding($surface, $user, $sessionId))) {
                $this->touch((string) $token, $state);

                return ['id' => (string) $token, 'reset' => false, 'state' => $state];
            }
        }

        $token = Str::random(64);
        $state = [
            'binding' => $this->binding($surface, $user, $sessionId),
            'token_hash' => hash('sha256', $token),
            'surface' => $surface,
            'user_id' => $user?->id,
            'focus' => null,
            'turns' => [],
            'responses' => [],
            'conversation_id' => null,
        ];
        $this->touch($token, $state);

        return ['id' => $token, 'reset' => $reset, 'state' => $state];
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, mixed>|null
     */
    public function responseFor(array $state, string $clientMessageId): ?array
    {
        $response = $state['responses'][$clientMessageId] ?? null;

        return is_array($response) ? $response : null;
    }

    /**
     * @param  array<string, mixed>  $state
     * @param  array<string, mixed>  $response
     * @return array<string, mixed>
     */
    public function remember(string $token, array $state, string $clientMessageId, GymmiTurnPlan $plan, array $response, ?int $conversationId): array
    {
        $turns = collect($state['turns'] ?? [])
            ->push([
                'intent' => $plan->primaryIntent(),
                'subject' => $plan->subject,
                'entities' => $plan->entities,
            ])
            ->take(-6)
            ->values()
            ->all();

        $responses = collect($state['responses'] ?? [])
            ->put($clientMessageId, $response);
        $responses = $responses
            ->slice(max(0, $responses->count() - 8))
            ->all();

        $focus = $this->focusFor($plan, $state['focus'] ?? null);
        $state = array_merge($state, [
            'focus' => $focus,
            'turns' => $turns,
            'responses' => $responses,
            'conversation_id' => $conversationId,
        ]);
        $this->touch($token, $state);

        return $state;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    private function touch(string $token, array $state): void
    {
        Cache::put(
            $this->cacheKey($token),
            $state,
            now()->addSeconds((int) config('gymmi.conversation.ttl_seconds', 7200)),
        );
    }

    private function cacheKey(string $token): string
    {
        return 'gymmi:conversation:'.hash('sha256', $token);
    }

    private function binding(string $surface, ?User $user, string $sessionId): string
    {
        return hash_hmac(
            'sha256',
            implode('|', [$surface, (string) ($user?->id ?? 'guest'), hash('sha256', $sessionId)]),
            (string) config('app.key'),
        );
    }

    private function focusFor(GymmiTurnPlan $plan, mixed $current): ?array
    {
        if ($plan->primaryIntent() === 'guard') {
            return null;
        }

        if ($plan->primaryIntent() === 'conversation') {
            return is_array($current) ? $current : null;
        }

        if ($plan->ambiguous) {
            return is_array($current) ? $current : null;
        }

        return [
            'intent' => $plan->primaryIntent(),
            'subject' => $plan->subject,
            'entities' => $plan->entities,
        ];
    }
}
