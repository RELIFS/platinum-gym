<?php

namespace App\Features\Gymmi\Actions;

use App\Features\Gymmi\Contracts\GymmiAnswerClient;
use App\Features\Gymmi\Contracts\GymmiInputNormalizerClient;
use App\Features\Gymmi\Support\GymmiActionResolver;
use App\Features\Gymmi\Support\GymmiAnswerDraft;
use App\Features\Gymmi\Support\GymmiAnswerValidator;
use App\Features\Gymmi\Support\GymmiConversationalResponder;
use App\Features\Gymmi\Support\GymmiConversationStore;
use App\Features\Gymmi\Support\GymmiEvidence;
use App\Features\Gymmi\Support\GymmiEvidenceSet;
use App\Features\Gymmi\Support\GymmiFollowUpResolver;
use App\Features\Gymmi\Support\GymmiGroundedResponder;
use App\Features\Gymmi\Support\GymmiInputGuard;
use App\Features\Gymmi\Support\GymmiKnowledgeMatcher;
use App\Features\Gymmi\Support\GymmiKnowledgeRepository;
use App\Features\Gymmi\Support\GymmiLiveDataProvider;
use App\Features\Gymmi\Support\GymmiNormalizedInput;
use App\Features\Gymmi\Support\GymmiQueryPlanner;
use App\Features\Gymmi\Support\GymmiResponseFormatter;
use App\Features\Gymmi\Support\GymmiTextNormalizer;
use App\Features\Gymmi\Support\GymmiTurnPlan;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AskGymmiAction
{
    public function __construct(
        private readonly GymmiInputNormalizerClient $inputNormalizer,
        private readonly GymmiAnswerClient $answerClient,
        private readonly GymmiAnswerValidator $answerValidator,
        private readonly GymmiKnowledgeRepository $knowledge,
        private readonly GymmiKnowledgeMatcher $matcher,
        private readonly GymmiLiveDataProvider $liveData,
        private readonly GymmiInputGuard $guard,
        private readonly GymmiConversationalResponder $conversation,
        private readonly GymmiGroundedResponder $groundedResponder,
        private readonly GymmiResponseFormatter $formatter,
        private readonly GymmiTextNormalizer $textNormalizer,
        private readonly GymmiConversationStore $conversationStore,
        private readonly GymmiFollowUpResolver $followUpResolver,
        private readonly GymmiQueryPlanner $queryPlanner,
        private readonly GymmiActionResolver $actionResolver,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(string $message, string $surface, ?User $user, ?string $conversationId, string $clientMessageId, string $sessionId): array
    {
        $startedAt = hrtime(true);
        $surface = $surface === 'member' && $user ? 'member' : 'public';
        $threadUser = $surface === 'member' ? $user : null;
        $thread = $this->conversationStore->open($conversationId, $surface, $threadUser, $sessionId);
        $cached = $this->conversationStore->responseFor($thread['state'], $clientMessageId);

        if ($cached !== null) {
            return $cached;
        }

        $safeMessage = $this->formatter->userMessage($message);
        $guard = $this->guard->inspect($safeMessage);

        if (! $guard['allowed']) {
            $plan = new GymmiTurnPlan($safeMessage, ['guard'], null);

            return $this->finish(
                thread: $thread,
                clientMessageId: $clientMessageId,
                plan: $plan,
                userMessage: $safeMessage,
                text: $this->formatter->reply((string) $guard['reply']),
                surface: $surface,
                status: 'blocked',
                mode: 'guard',
                user: $threadUser,
                startedAt: $startedAt,
            );
        }

        $conversationReply = $this->conversation->replyFor($safeMessage, $surface);

        if ($conversationReply !== null) {
            $plan = new GymmiTurnPlan($safeMessage, ['conversation'], null);

            return $this->finish(
                thread: $thread,
                clientMessageId: $clientMessageId,
                plan: $plan,
                userMessage: $safeMessage,
                text: $this->formatter->reply($conversationReply),
                surface: $surface,
                status: 'answered',
                mode: 'conversation',
                user: $threadUser,
                startedAt: $startedAt,
            );
        }

        $normalization = $this->normalizeInput($safeMessage, $surface);
        $resolved = $this->followUpResolver->resolve($normalization->message, $thread['state']['focus'] ?? null);
        $entities = array_filter(array_merge(
            $this->localEntities($resolved['message']),
            $normalization->source === 'gemini' ? $normalization->entities : [],
        ), fn (mixed $value): bool => is_string($value) || is_numeric($value));
        $plan = $this->queryPlanner->plan(
            message: $resolved['message'],
            knowledge: $this->knowledge->all(),
            normalizerIntents: $normalization->source === 'gemini' ? $normalization->intents : [],
            entities: $entities,
            followUp: $resolved['follow_up'],
        );

        if ($surface === 'public' && str_starts_with($plan->primaryIntent(), 'member_')) {
            return $this->finish(
                thread: $thread,
                clientMessageId: $clientMessageId,
                plan: $plan,
                userMessage: $safeMessage,
                text: 'Untuk mengecek data akun pribadi, silakan masuk ke portal member lalu tanyakan kembali di sana.',
                surface: $surface,
                status: 'clarify',
                mode: 'fallback',
                user: $threadUser,
                startedAt: $startedAt,
                meta: $normalization->meta() + ['fallback_reason' => 'member_surface_required', 'evidence_count' => 0],
            );
        }

        if ($plan->ambiguous) {
            return $this->finish(
                thread: $thread,
                clientMessageId: $clientMessageId,
                plan: $plan,
                userMessage: $safeMessage,
                text: $this->formatter->reply($this->clarification($surface)),
                surface: $surface,
                status: 'clarify',
                mode: 'fallback',
                user: $threadUser,
                startedAt: $startedAt,
                meta: $normalization->meta() + ['fallback_reason' => 'ambiguous', 'evidence_count' => 0],
            );
        }

        $match = $this->matcher->match($plan->message, $this->knowledge->all());
        $match['intent'] = [
            'intent' => $plan->primaryIntent(),
            'subject' => $plan->subject,
            'normalized' => $plan->message,
        ];
        $liveSnippets = [];

        if ($surface === 'member' && $user) {
            $liveSnippets = $this->liveData->memberSnippets($user, $plan->message, $plan);
        }

        if (! str_starts_with($plan->primaryIntent(), 'member_')) {
            $liveSnippets = array_merge($liveSnippets, $this->liveData->publicSnippets($plan->message, $match));
        }

        if ($liveSnippets !== []) {
            if (str_starts_with($plan->primaryIntent(), 'member_')) {
                $match['answer'] = null;
                $match['snippets'] = [];
            }

            $match = $this->withLiveSnippets($match, $liveSnippets);
        }

        $mode = $liveSnippets !== [] ? 'live' : (($match['type'] ?? 'none') === 'faq' ? 'faq' : (($match['snippets'] ?? []) !== [] ? 'fallback' : 'fallback'));
        $status = (($match['type'] ?? 'none') === 'none' && $liveSnippets === []) ? 'clarify' : 'answered';
        $text = $this->groundedResponder->answer($match, $plan, $surface);
        $composer = $this->composeAnswer(
            plan: $plan,
            match: $match,
            surface: $surface,
            user: $threadUser,
            aiNormalizerAttempted: $normalization->source !== 'local',
        );

        if ($composer['text'] !== null) {
            $text = $composer['text'];
            $mode = 'generated';
        }

        return $this->finish(
            thread: $thread,
            clientMessageId: $clientMessageId,
            plan: $plan,
            userMessage: $safeMessage,
            text: $text,
            surface: $surface,
            status: $status,
            mode: $mode,
            user: $threadUser,
            startedAt: $startedAt,
            meta: $normalization->meta() + ['evidence_count' => count($liveSnippets), 'composer_rejection' => $composer['rejection']],
        );
    }

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}  $match
     * @return array{text: string|null, rejection: string|null}
     */
    private function composeAnswer(GymmiTurnPlan $plan, array $match, string $surface, ?User $user, bool $aiNormalizerAttempted): array
    {
        if (! (bool) config('gymmi.composer_enabled', false) || $aiNormalizerAttempted || $surface !== 'public' || $user || in_array($plan->primaryIntent(), ['membership_price', 'class_price', 'class_schedule', 'class_coach', 'class_capacity', 'product_stock', 'location_contact', 'promotion', 'member_membership', 'member_session', 'member_payment', 'member_booking', 'member_qr'], true)) {
            return ['text' => null, 'rejection' => null];
        }

        $evidence = new GymmiEvidenceSet(collect($match['snippets'] ?? [])
            ->take(4)
            ->map(fn (string $snippet, int $index): GymmiEvidence => new GymmiEvidence(
                id: 'fact-'.($index + 1),
                domain: $plan->primaryIntent(),
                scope: 'public',
                source: 'selected',
                priority: 50 - $index,
                fields: ['text' => $snippet],
                protectedLiterals: [],
            ))
            ->all());

        if ($evidence->items === []) {
            return ['text' => null, 'rejection' => 'no_evidence'];
        }

        $cacheSeconds = (int) config('services.gemini.public_cache_seconds', 0);
        $cacheKey = 'gymmi:validated-draft:'.hash('sha256', implode('|', [
            $plan->message,
            $evidence->promptContext(),
            'v1',
        ]));
        $cachedDraft = $cacheSeconds > 0 ? cache()->get($cacheKey) : null;
        $draft = is_array($cachedDraft)
            ? new GymmiAnswerDraft((string) ($cachedDraft['answer'] ?? ''), (array) ($cachedDraft['used_fact_ids'] ?? []))
            : null;

        if (! $draft instanceof GymmiAnswerDraft) {
            $draft = GymmiAnswerDraft::fromJson((string) $this->answerClient->answer($plan->message, $evidence->promptContext()));
        }

        $validated = $this->answerValidator->validate($draft, $evidence);

        if (! $validated['valid']) {
            return ['text' => null, 'rejection' => $validated['reason']];
        }

        if ($cacheSeconds > 0) {
            cache()->put($cacheKey, [
                'answer' => $draft->answer,
                'used_fact_ids' => $draft->usedFactIds,
            ], now()->addSeconds($cacheSeconds));
        }

        return ['text' => $this->formatter->reply($draft->answer), 'rejection' => null];
    }

    /**
     * @return array<string, string|int>
     */
    private function localEntities(string $message): array
    {
        $entities = [];

        if (preg_match('/\b(gym umum|gym mahasiswa|muaythai|poundfit|zumba|aerobic|personal trainer)\b/u', $message, $matches) === 1) {
            $entities['topic'] = $matches[1];
        }

        if (preg_match('/\b(\d+)\s*(bulan|x|sesi)\b/u', $message, $matches) === 1) {
            $entities['quantity'] = (int) $matches[1];
            $entities['unit'] = $matches[2];
        }

        return $entities;
    }

    private function clarification(string $surface): string
    {
        return $surface === 'member'
            ? 'Boleh diperjelas topiknya? Misalnya status membership, booking, transaksi, QR member, atau profil.'
            : 'Boleh diperjelas topiknya? Misalnya harga Gym Umum, jadwal Muaythai, lokasi, atau metode pembayaran.';
    }

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}  $match
     * @param  array<int, string>  $liveSnippets
     * @return array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int, intent?: array<string, mixed>}
     */
    private function withLiveSnippets(array $match, array $liveSnippets): array
    {
        $match['type'] = 'knowledge';
        $match['answer'] = null;
        $match['topic'] = $match['topic'] ?: 'live_data';
        $match['confidence'] = max((int) ($match['confidence'] ?? 0), 90);
        $match['snippets'] = collect(array_merge($liveSnippets, $match['snippets'] ?? []))
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();

        return $match;
    }

    private function normalizeInput(string $message, string $surface): GymmiNormalizedInput
    {
        $local = new GymmiNormalizedInput(
            message: $this->textNormalizer->normalize($message),
            confidence: 100,
            source: 'local',
        );

        if (! $this->shouldUseAiNormalizer($message, $local->message)) {
            return $local;
        }

        $normalized = $this->inputNormalizer->normalize($message, $surface);

        if ($this->canUseAiNormalization($normalized, $message)) {
            return $normalized;
        }

        return new GymmiNormalizedInput(
            message: $local->message,
            intents: $normalized?->intents ?? [],
            entities: $normalized?->entities ?? [],
            confidence: $normalized?->confidence ?? 100,
            unsafeFlags: $normalized?->unsafeFlags ?? [],
            source: $normalized ? 'local_ai_rejected' : 'local_ai_unavailable',
        );
    }

    private function shouldUseAiNormalizer(string $message, string $localNormalized): bool
    {
        if (! (bool) config('services.gemini.normalizer_enabled', true)) {
            return false;
        }

        $base = Str::of($message)->lower()->ascii()->replaceMatches('/[^a-z0-9]+/', ' ')->squish()->toString();

        return mb_strlen($base) >= 8 && ($base !== $localNormalized || preg_match('/\b(brp|hrg|jdwl|dmn|gmn|gimn|pkt|pktt|pket|gymm|jim|ktm|tdk|ga|gak|nggak|buking)\b/u', $base) === 1);
    }

    private function canUseAiNormalization(?GymmiNormalizedInput $normalization, string $original): bool
    {
        if (! $normalization || $normalization->unsafeFlags !== [] || $normalization->confidence < (int) config('services.gemini.normalizer_min_confidence', 60)) {
            return false;
        }

        if (mb_strlen($normalization->message) < 2 || mb_strlen($normalization->message) > 700 || ! $this->guard->inspect($normalization->message)['allowed']) {
            return false;
        }

        $protected = collect(preg_split('/\s+/', $this->textNormalizer->normalize($original)) ?: [])
            ->filter(fn (string $token): bool => preg_match('/\d|rp|muay|pound|zumba|aerobic|gym|whatsapp|instagram/u', $token) === 1);
        $normalizedMessage = $this->textNormalizer->normalize($normalization->message);

        return $protected->every(fn (string $token): bool => str_contains($normalizedMessage, $token));
    }

    /**
     * @param  array{id: string, reset: bool, state: array<string, mixed>}  $thread
     * @param  array<string, mixed>  $meta
     * @return array<string, mixed>
     */
    private function finish(array $thread, string $clientMessageId, GymmiTurnPlan $plan, string $userMessage, string $text, string $surface, string $status, string $mode, ?User $user, int $startedAt, array $meta = []): array
    {
        $latencyMs = (int) round((hrtime(true) - $startedAt) / 1_000_000);
        $action = in_array($status, ['answered', 'clarify'], true) ? $this->actionResolver->resolve($plan, $surface) : null;
        $conversationDbId = $this->storeConversation(
            existingId: is_numeric($thread['state']['conversation_id'] ?? null) ? (int) $thread['state']['conversation_id'] : null,
            message: $this->formatter->logMessage($userMessage),
            reply: $text,
            surface: $surface,
            mode: $mode,
            user: $user,
            latencyMs: $latencyMs,
            meta: array_merge($plan->meta(), $meta, [
                'action_id' => $action['id'] ?? null,
                'conversation_reset' => $thread['reset'],
            ]),
        );
        $response = [
            'status' => $status,
            'source' => $this->legacySource($mode),
            'request_id' => substr(hash('sha256', $thread['id'].'|'.$clientMessageId), 0, 16),
            'reply' => [
                'text' => $text,
                'action' => $action,
            ],
            'mode' => $mode,
            'conversation' => [
                'id' => $thread['id'],
                'reset' => $thread['reset'],
            ],
            'retryable' => false,
        ];

        $this->conversationStore->remember(
            token: $thread['id'],
            state: $thread['state'],
            clientMessageId: $clientMessageId,
            plan: $plan,
            response: $response,
            conversationId: $conversationDbId,
        );

        return $response;
    }

    private function legacySource(string $mode): string
    {
        return match ($mode) {
            'guard' => 'guard',
            'faq' => 'faq',
            'conversation' => 'fallback',
            default => 'knowledge',
        };
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function storeConversation(?int $existingId, string $message, string $reply, string $surface, string $mode, ?User $user, int $latencyMs, array $meta): ?int
    {
        try {
            return DB::transaction(function () use ($existingId, $message, $reply, $surface, $mode, $user, $latencyMs, $meta): int {
                $conversation = $existingId
                    ? AiConversation::query()
                        ->whereKey($existingId)
                        ->when($user, fn ($query) => $query->where('user_id', $user->id))
                        ->when(! $user, fn ($query) => $query->whereNull('user_id'))
                        ->first()
                    : null;

                if (! $conversation) {
                    $conversation = AiConversation::query()->create([
                        'user_id' => $user?->id,
                        'context' => $surface,
                        'title' => Str::limit($message, 120, ''),
                        'model' => (string) config('services.gemini.model', 'gemini-2.0-flash'),
                        'meta' => array_merge(['mode' => $mode, 'source' => $this->legacySource($mode)], $meta),
                    ]);
                }

                $conversation->messages()->create(['role' => 'user', 'content' => $message]);
                $conversation->messages()->create(['role' => 'assistant', 'content' => $reply, 'latency_ms' => $latencyMs]);
                $conversation->touch();

                return (int) $conversation->id;
            });
        } catch (Throwable $exception) {
            report($exception);

            return $existingId;
        }
    }
}
