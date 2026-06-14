<?php

namespace App\Http\Controllers\Webhook;

use App\Features\Payments\Actions\HandleMidtransWebhookAction;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InvalidArgumentException;

class MidtransWebhookController extends Controller
{
    public function __invoke(Request $request, HandleMidtransWebhookAction $handleMidtransWebhook): JsonResponse
    {
        try {
            $payment = $handleMidtransWebhook->handle($request->all());
        } catch (InvalidArgumentException) {
            return response()->json(['message' => 'invalid signature'], 403);
        }

        return response()->json([
            'message' => 'ok',
            'payment_id' => $payment->id,
            'status' => $payment->status,
        ]);
    }
}
