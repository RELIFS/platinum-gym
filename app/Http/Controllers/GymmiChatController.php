<?php

namespace App\Http\Controllers;

use App\Features\Gymmi\Actions\AskGymmiAction;
use App\Http\Requests\GymmiChatRequest;
use Illuminate\Http\JsonResponse;

class GymmiChatController extends Controller
{
    public function __invoke(GymmiChatRequest $request, AskGymmiAction $askGymmi): JsonResponse
    {
        $validated = $request->validated();

        $reply = $askGymmi->execute(
            message: $validated['message'],
            context: $validated['context'] ?? 'public',
            user: $request->user(),
            history: $validated['history'] ?? [],
        );

        return response()->json([
            'reply' => [
                'text' => $reply['text'],
            ],
            'source' => $reply['source'],
        ]);
    }
}
