<?php

namespace App\Features\Gymmi\Actions;

use App\Features\Gymmi\Contracts\GymmiAssistantClient;
use App\Features\Gymmi\Support\GymmiContextBuilder;
use App\Features\MemberPortal\ViewModels\MemberChatbotViewModel;
use App\Features\PublicWebsite\Queries\PublicSettingsQuery;
use App\Features\PublicWebsite\ViewModels\PublicChatbotViewModel;
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
        private readonly GymmiContextBuilder $contextBuilder,
        private readonly PublicSettingsQuery $publicSettingsQuery,
    ) {}

    /**
     * @param  array<int, array{from?: string, text?: string}>  $history
     * @return array{text: string, source: string}
     */
    public function execute(string $message, string $context, ?User $user = null, array $history = []): array
    {
        $context = $context === 'member' && $user ? 'member' : 'public';
        $safeMessage = Str::limit(strip_tags($message), 700, '');
        $promptContext = $this->contextBuilder->build($context, $user);
        $reply = $this->askClient($safeMessage, $promptContext, $context, $user, $history);
        $source = filled($reply) ? 'gemini' : 'fallback';
        $text = $reply ?: $this->fallbackReply($safeMessage, $context);

        $this->storeConversation($safeMessage, $text, $context, $source, $user);

        return [
            'text' => $text,
            'source' => $source,
        ];
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

    private function fallbackReply(string $message, string $context): string
    {
        $config = $context === 'member'
            ? MemberChatbotViewModel::make()
            : PublicChatbotViewModel::make($this->publicSettingsQuery->get());

        $reply = $this->resolveReply($message, $config['replies'] ?? []);

        return is_array($reply) ? (string) ($reply['text'] ?? '') : (string) $reply;
    }

    /**
     * @param  array<string, mixed>  $replies
     * @return string|array<string, string|null>
     */
    private function resolveReply(string $message, array $replies): string|array
    {
        $normalized = Str::lower($message);

        return match (true) {
            str_contains($normalized, 'qr'), str_contains($normalized, 'check-in'), str_contains($normalized, 'check in') => $replies['qr'] ?? $replies['fallback'],
            str_contains($normalized, 'transaksi'), str_contains($normalized, 'pembayaran'), str_contains($normalized, 'invoice') => $replies['transactions'] ?? $replies['fallback'],
            str_contains($normalized, 'jadwal'), str_contains($normalized, 'kelas'), str_contains($normalized, 'zumba'), str_contains($normalized, 'aerobic'), str_contains($normalized, 'muaythai'), str_contains($normalized, 'pound') => $replies['schedule'] ?? $replies['fallback'],
            str_contains($normalized, 'profil'), str_contains($normalized, 'akun'), str_contains($normalized, 'password'), str_contains($normalized, 'sandi') => $replies['account'] ?? $replies['fallback'],
            str_contains($normalized, 'personal trainer'), str_contains($normalized, 'pelatih'), str_contains($normalized, 'coach') => $replies['trainer'] ?? $replies['fallback'],
            str_contains($normalized, 'lokasi'), str_contains($normalized, 'alamat'), str_contains($normalized, 'jam'), str_contains($normalized, 'buka') => $replies['location'] ?? $replies['fallback'],
            str_contains($normalized, 'promo'), str_contains($normalized, 'diskon') => $replies['promo'] ?? $replies['fallback'],
            str_contains($normalized, 'member'), str_contains($normalized, 'paket'), str_contains($normalized, 'gym') => $replies['membership'] ?? $replies['fallback'],
            default => $replies['fallback'],
        };
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
