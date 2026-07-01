<?php

namespace App\Features\Gymmi\Actions;

use App\Features\Gymmi\Contracts\GymmiAssistantClient;
use App\Features\Gymmi\Support\GymmiConversationalResponder;
use App\Features\Gymmi\Support\GymmiFallbackResponder;
use App\Features\Gymmi\Support\GymmiInputGuard;
use App\Features\Gymmi\Support\GymmiIntentDetector;
use App\Features\Gymmi\Support\GymmiKnowledgeMatcher;
use App\Features\Gymmi\Support\GymmiKnowledgeRepository;
use App\Features\Gymmi\Support\GymmiLiveDataProvider;
use App\Features\Gymmi\Support\GymmiPromptBuilder;
use App\Features\Gymmi\Support\GymmiResponseFormatter;
use App\Models\AiConversation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class AskGymmiAction
{
    public function __construct(
        private readonly GymmiAssistantClient $client,
        private readonly GymmiKnowledgeRepository $knowledge,
        private readonly GymmiKnowledgeMatcher $matcher,
        private readonly GymmiLiveDataProvider $liveData,
        private readonly GymmiInputGuard $guard,
        private readonly GymmiConversationalResponder $conversation,
        private readonly GymmiIntentDetector $intentDetector,
        private readonly GymmiPromptBuilder $promptBuilder,
        private readonly GymmiFallbackResponder $fallback,
        private readonly GymmiResponseFormatter $formatter,
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

        $intent = $this->intentDetector->detect($safeMessage);
        $match = $this->matcher->match($safeMessage, $this->knowledge->all());
        $match['intent'] = $intent;

        if ($match['type'] === 'ambiguous') {
            $text = $this->formatter->reply($this->fallback->ambiguous($context));
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'fallback', $user);

            return ['text' => $text, 'source' => 'fallback'];
        }

        $liveSnippets = [];

        if ($context === 'member' && $user) {
            $liveSnippets = array_merge($liveSnippets, $this->liveData->memberSnippets($user, $safeMessage));
        }

        $liveSnippets = array_merge($liveSnippets, $this->liveData->publicSnippets($safeMessage, $match));

        if ($liveSnippets !== []) {
            $match = $this->withLiveSnippets($match, $liveSnippets);
        } elseif ($match['type'] === 'faq' && filled($match['answer'])) {
            $text = $this->formatter->reply((string) $match['answer']);
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'faq', $user);

            return ['text' => $text, 'source' => 'faq'];
        }
        if ($match['type'] === 'none' || ($match['snippets'] ?? []) === []) {
            $text = $this->formatter->reply($this->fallback->outOfScope($context));
            $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, 'fallback', $user);

            return ['text' => $text, 'source' => 'fallback'];
        }

        $promptContext = $this->promptBuilder->build($match, $context, $user);
        $reply = $this->askClient($safeMessage, $promptContext, $context, $user, $history);
        $source = filled($reply) ? 'gemini' : 'knowledge';
        $text = $reply ? $this->formatter->reply($reply) : $this->fallback->fromMatch($match);

        $this->storeConversation($this->formatter->logMessage($safeMessage), $text, $context, $source, $user);

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
            return $this->client->ask($message, $promptContext, $history);
        }

        $cacheSeconds = (int) config('services.gemini.public_cache_seconds', 900);

        if ($cacheSeconds <= 0) {
            return $this->client->ask($message, $promptContext, $history);
        }

        return Cache::remember(
            'gymmi:public-reply:'.sha1($message.'|'.$promptContext),
            now()->addSeconds($cacheSeconds),
            fn (): ?string => $this->client->ask($message, $promptContext, $history),
        );
    }

    private function storeConversation(string $message, string $reply, string $context, string $source, ?User $user): void
    {
        try {
            DB::transaction(function () use ($message, $reply, $context, $source, $user): void {
                $conversation = AiConversation::query()->create([
                    'user_id' => $user?->id,
                    'context' => $context,
                    'title' => Str::limit($message, 120, ''),
                    'model' => (string) config('services.gemini.model', 'gemini-2.0-flash'),
                    'meta' => ['source' => $source],
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
