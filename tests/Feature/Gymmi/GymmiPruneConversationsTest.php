<?php

use App\Models\AiConversation;

function gymmiPruneConversation(string $title, int $daysOld): AiConversation
{
    $conversation = AiConversation::query()->create([
        'context' => 'public',
        'title' => $title,
        'model' => 'deterministic',
    ]);
    $conversation->timestamps = false;
    $conversation->created_at = now()->subDays($daysOld);
    $conversation->updated_at = now()->subDays($daysOld);
    $conversation->save();

    return $conversation;
}

test('gymmi prune supports dry run and retention deletion', function () {
    $expired = gymmiPruneConversation('expired', 31);
    $current = gymmiPruneConversation('current', 2);

    $this->artisan('gymmi:prune-conversations', ['--dry-run' => true])
        ->expectsOutputToContain('1 percakapan Gymmi')
        ->assertSuccessful();

    expect(AiConversation::query()->whereKey($expired->id)->exists())->toBeTrue();

    $this->artisan('gymmi:prune-conversations')
        ->expectsOutputToContain('1 percakapan Gymmi dihapus')
        ->assertSuccessful();

    expect(AiConversation::query()->whereKey($expired->id)->exists())->toBeFalse()
        ->and(AiConversation::query()->whereKey($current->id)->exists())->toBeTrue();
});
