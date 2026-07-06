<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function pakasir(Request $request)
    {
        $data = $request->all();

        Log::info('Pakasir webhook received', ['data' => $data]);

        if (($data['status'] ?? '') !== 'completed') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $orderId = $data['order_id'] ?? null;

        if (!$orderId) {
            return response()->json(['status' => 'error', 'message' => 'Missing order_id'], 400);
        }

        $order = SubscriptionOrder::where('pakasir_order_id', $orderId)->first();

        if (!$order) {
            Log::warning('Pakasir webhook: order not found', ['order_id' => $orderId]);

            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        if ($order->status === 'approved') {
            Log::info('Pakasir webhook: order already approved', ['order_id' => $orderId]);

            return response()->json(['status' => 'already_approved'], 200);
        }

        $amount = intval($data['amount'] ?? 0);

        $plan = $order->subscriptionPlan;

        if ($amount !== intval($plan?->price ?? 0)) {
            Log::warning('Pakasir webhook: amount mismatch', [
                'order_id' => $orderId,
                'expected' => $plan?->price,
                'received' => $amount,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Amount mismatch'], 400);
        }

        if (!$plan) {
            Log::error('Pakasir webhook: subscription plan not found', ['order_id' => $orderId]);

            return response()->json(['status' => 'error', 'message' => 'Plan not found'], 400);
        }

        $this->processPayment($order, $plan);

        return response()->json(['status' => 'ok'], 200);
    }

    protected function processPayment(SubscriptionOrder $order, $plan): void
    {
        $user = $order->user;

        $order->status = 'approved';
        $order->processed_at = now();
        $order->save();

        $user->is_subscribed = true;
        $user->subscription_expires_at = now()->addDays($plan->duration_days);
        $user->save();
    }
}
