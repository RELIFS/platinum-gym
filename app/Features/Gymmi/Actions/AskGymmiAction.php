<?php

namespace App\Features\Gymmi\Actions;

use App\Features\Gymmi\Contracts\GymmiAnswerClient;
use App\Features\Gymmi\Contracts\GymmiInputNormalizerClient;
use App\Features\Gymmi\Support\GymmiConversationalResponder;
use App\Features\Gymmi\Support\GymmiFallbackResponder;
use App\Features\Gymmi\Support\GymmiInputGuard;
use App\Features\Gymmi\Support\GymmiIntentDetector;
use App\Features\Gymmi\Support\GymmiKnowledgeMatcher;
use App\Features\Gymmi\Support\GymmiKnowledgeRepository;
use App\Features\Gymmi\Support\GymmiLiveDataProvider;
use App\Features\Gymmi\Support\GymmiNormalizedInput;
use App\Features\Gymmi\Support\GymmiPromptBuilder;
use App\Features\Gymmi\Support\GymmiResponseFormatter;
use App\Features\Gymmi\Support\GymmiTextNormalizer;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AskGymmiAction
{
    public function __construct(
        private readonly GymmiAnswerClient $answerClient,
        private readonly GymmiInputNormalizerClient $inputNormalizer,
        private readonly GymmiKnowledgeRepository $knowledge,
        private readonly GymmiKnowledgeMatcher $matcher,
        private readonly GymmiLiveDataProvider $liveData,
        private readonly GymmiInputGuard $guard,
        private readonly GymmiConversationalResponder $conversation,
        private readonly GymmiIntentDetector $intentDetector,
        private readonly GymmiPromptBuilder $promptBuilder,
        private readonly GymmiFallbackResponder $fallback,
        private readonly GymmiResponseFormatter $formatter,
        private readonly GymmiTextNormalizer $textNormalizer,
    ) {}

    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     * @return array{text: string, source: string}
     */
    public function execute(string $message, string $context, ?User $user = null, array $history = []): array
    {
        $context = $context === 'member' && $user ? 'member' : 'public';
        $safeMessage = $this->formatter->userMessage($message);
        $guard = $this->guard->inspect($safeMessage);

        if (! $guard['allowed']) {
            $text = $this->formatter->reply((string) $guard['reply']);
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'guard', $user);

            return ['text' => $text, 'source' => 'guard'];
        }

        $conversationReply = $this->conversation->replyFor($safeMessage, $context);

        if ($conversationReply !== null) {
            $text = $this->formatter->reply($conversationReply);
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'fallback', $user);

            return ['text' => $text, 'source' => 'fallback'];
        }

        $normalization = $this->normalizeInput($safeMessage, $context);
        $lookupMessage = $normalization->message;
        $intent = $this->intentDetector->detect($lookupMessage);
        $match = $this->matcher->match($lookupMessage, $this->knowledge->all());
        $match['intent'] = $intent;
        $meta = $this->metaFor($normalization, $intent);

        if ($match['type'] === 'ambiguous') {
            $text = $this->formatter->reply($this->fallback->ambiguous($context));
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'fallback', $user, $meta + ['fallback_reason' => 'ambiguous']);

            return ['text' => $text, 'source' => 'fallback'];
        }

        $liveSnippets = [];

        if ($context === 'member' && $user) {
            $liveSnippets = array_merge($liveSnippets, $this->liveData->memberSnippets($user, $lookupMessage));
        }

        $liveSnippets = array_merge($liveSnippets, $this->liveData->publicSnippets($lookupMessage, $match));

        if ($liveSnippets !== []) {
            $match = $this->withLiveSnippets($match, $liveSnippets);
        } elseif ($match['type'] === 'faq' && filled($match['answer'])) {
            $text = $this->formatter->reply((string) $match['answer']);
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'faq', $user, $meta);

            return ['text' => $text, 'source' => 'faq'];
        }
        if ($match['type'] === 'none' || ($match['snippets'] ?? []) === []) {
            $text = $this->formatter->reply($this->fallback->outOfScope($context));
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'fallback', $user, $meta + ['fallback_reason' => 'no_match']);

            return ['text' => $text, 'source' => 'fallback'];
        }

        $promptContext = $this->promptBuilder->build($match, $context, $user);
        $reply = $this->askClient($lookupMessage, $promptContext, $context, $user, $history);
        $source = filled($reply) ? 'gemini' : 'knowledge';
        $text = $reply ? $this->formatter->reply($reply) : $this->fallback->fromMatch($match);

        $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, $source, $user, $meta);

        return [
            'text' => $text,
            'source' => $source,
        ];
    }

    /**
     * @param  array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}  $match
     * @param  array<int, string>  $liveSnippets
     * @return array{type: string, answer: string|null, snippets: array<int, string>, topic: string|null, confidence: int}
     */
    private function withLiveSnippets(array $match, array $liveSnippets): array
    {
        $match['type'] = 'knowledge';
        $match['answer'] = null;
        $match['topic'] = $match['topic'] ?: 'live_data';
        $match['confidence'] = max((int) ($match['confidence'] ?? 0), 70);
        $match['snippets'] = collect(array_merge($liveSnippets, $match['snippets'] ?? []))
            ->filter()
            ->unique()
            ->take(8)
            ->values()
            ->all();

        return $match;
    }

    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     */
    private function askClient(string $message, string $promptContext, string $context, ?User $user, array $history): ?string
    {
        if ($context !== 'public' || $user || $history !== []) {
            return $this->answerClient->answer($message, $promptContext, $history);
        }

        $cacheSeconds = (int) config('services.gemini.public_cache_seconds', 900);

        if ($cacheSeconds <= 0) {
            return $this->answerClient->answer($message, $promptContext, $history);
        }

        return Cache::remember(
            'gymmi:public-reply:'.sha1($message.'|'.$promptContext),
            now()->addSeconds($cacheSeconds),
            fn (): ?string => $this->answerClient->answer($message, $promptContext, $history),
        );
    }

    private function normalizeInput(string $message, string $context): GymmiNormalizedInput
    {
        $local = new GymmiNormalizedInput(
            message: $this->textNormalizer->normalize($message),
            confidence: 100,
            source: 'local',
        );

        if (! $this->shouldUseAiNormalizer($message, $local->message)) {
            return $local;
        }

        $normalized = $this->inputNormalizer->normalize($message, $context);

        if ($this->canUseAiNormalization($normalized)) {
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

        $base = Str::of($message)
            ->lower()
            ->ascii()
            ->replaceMatches('/[^a-z0-9]+/', ' ')
            ->squish()
            ->toString();

        if ($base === '' || mb_strlen($base) < 8) {
            return false;
        }

        if ($base !== $localNormalized) {
            return true;
        }

        return preg_match('/\b(brp|hrg|jdwl|dmn|gmn|gimn|pkt|pktt|pket|gymm|jim|ktm|tdk|ga|gak|nggak|buking)\b/u', $base) === 1;
    }

    private function canUseAiNormalization(?GymmiNormalizedInput $normalization): bool
    {
        if (! $normalization) {
            return false;
        }

        if ($normalization->unsafeFlags !== []) {
            return false;
        }

        if ($normalization->confidence < (int) config('services.gemini.normalizer_min_confidence', 60)) {
            return false;
        }

        if (mb_strlen($normalization->message) < 2 || mb_strlen($normalization->message) > 700) {
            return false;
        }

        if (! $this->allIntentsAllowed($normalization->intents)) {
            return false;
        }

        return $this->guard->inspect($normalization->message)['allowed'];
    }

    /**
     * @param  array<int, string>  $intents
     */
    private function allIntentsAllowed(array $intents): bool
    {
        $allowed = [
            'general',
            'membership_price',
            'student_package_requirement',
            'class_schedule',
            'class_price',
            'class_coach',
            'class_capacity',
            'private_or_group',
            'location_contact',
            'product_stock',
            'member_membership',
            'member_payment',
            'member_booking',
            'member_qr',
            'account_help',
            'promotion',
            'facility',
            'policy',
        ];

        return collect($intents)
            ->every(fn (string $intent): bool => in_array($intent, $allowed, true));
    }

    /**
     * @param  array{intent: string, subject: string|null, normalized: string}  $intent
     * @return array<string, mixed>
     */
    private function metaFor(GymmiNormalizedInput $normalization, array $intent): array
    {
        return array_merge($normalization->meta(), [
            'intent' => $intent['intent'],
            'subject' => $intent['subject'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $meta
     */
    private function storeConversation(string $message, string $reply, string $context, string $source, ?User $user, array $meta = []): void
    {
        try {
            DB::transaction(function () use ($message, $reply, $context, $source, $user, $meta): void {
                $conversation = AiConversation::query()->create([
                    'user_id' => $user?->id,
                    'context' => $context,
                    'title' => Str::limit($message, 120, ''),
                    'model' => (string) config('services.gemini.model', 'gemini-2.0-flash'),
                    'meta' => array_merge(['source' => $source], $meta),
                ]);

                $conversation->messages()->create([
                    'role' => 'user',
                    'content' => $message,
                ]);

                $conversation->messages()->create([
                    'role' => 'assistant',
                    'content' => $reply,
                ]);
            });
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
