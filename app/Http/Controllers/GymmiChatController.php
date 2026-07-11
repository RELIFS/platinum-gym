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
        $surface = $request->routeIs('member.gymmi.chat') ? 'member' : 'public';

        $response = $askGymmi->execute(
            message: $validated['message'],
            surface: $surface,
            user: $request->user(),
            conversationId: $validated['conversation_id'] ?? null,
            clientMessageId: $validated['client_message_id'],
            sessionId: $request->session()->getId(),
        );

        return response()->json($response);
    }
}
