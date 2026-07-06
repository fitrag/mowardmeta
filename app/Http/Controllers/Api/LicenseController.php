<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApiKey;
use App\Models\AppSetting;
use App\Models\License;
use App\Models\LicenseActivation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LicenseController extends Controller
{
    /**
     * Verify license and use it
     * - Duration-based: just verify validity
     * - Credits-based: verify and deduct credit
     */
    public function verify(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'product_name' => 'required|string',
            'domain' => 'nullable|string|max:255',
            'device_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'error' => 'validation_failed',
                'message' => 'Invalid request parameters',
                'errors' => $validator->errors(),
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)
            ->where('product_name', $request->product_name)
            ->first();

        // License not found
        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'license_not_found',
                'message' => 'License key not found for this product',
            ], 404);
        }

        // Check license status
        if ($license->status === 'revoked') {
            return response()->json([
                'valid' => false,
                'error' => 'license_revoked',
                'message' => 'This license has been revoked',
            ], 403);
        }

        if ($license->status === 'pending') {
            return response()->json([
                'valid' => false,
                'error' => 'license_pending',
                'message' => 'This license is pending activation',
            ], 403);
        }

        // Check based on license type
        if ($license->isDurationBased()) {
            // Duration-based: check expiration
            if ($license->expires_at && $license->expires_at->isPast()) {
                if ($license->status !== 'expired') {
                    $license->update(['status' => 'expired']);
                }

                return response()->json([
                    'valid' => false,
                    'error' => 'license_expired',
                    'message' => 'This license has expired',
                    'expired_at' => $license->expires_at->toIso8601String(),
                ], 403);
            }
        } else {
            // Credits-based: check and deduct credit
            if (!$license->hasCredits()) {
                return response()->json([
                    'valid' => false,
                    'error' => 'credits_exhausted',
                    'message' => 'This license has no remaining credits',
                    'credits_total' => $license->credits_total,
                    'credits_used' => $license->credits_used,
                    'credits_remaining' => 0,
                ], 403);
            }

            // Deduct 1 credit
            $license->useCredit(1);
        }

        // Check domain restriction
        if ($license->domain && $request->domain) {
            $licenseDomain = strtolower(preg_replace('/^www\./', '', trim($license->domain)));
            $requestDomain = strtolower(preg_replace('/^www\./', '', trim($request->domain)));

            if ($licenseDomain !== $requestDomain) {
                return response()->json([
                    'valid' => false,
                    'error' => 'domain_mismatch',
                    'message' => 'This license is not valid for this domain',
                ], 403);
            }
        }

        // Track activation
        $deviceId = $request->device_id ?? $request->ip();
        $existingActivation = $license->activations()
            ->where('device_identifier', $deviceId)
            ->where('is_active', true)
            ->first();

        if (!$existingActivation) {
            $activeCount = $license->activations()->where('is_active', true)->count();
            
            if ($activeCount >= $license->max_activations) {
                return response()->json([
                    'valid' => false,
                    'error' => 'max_activations_reached',
                    'message' => 'Maximum number of activations reached',
                    'max_activations' => $license->max_activations,
                    'current_activations' => $activeCount,
                ], 403);
            }

            LicenseActivation::create([
                'license_id' => $license->id,
                'device_identifier' => $deviceId,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_check_at' => now(),
                'is_active' => true,
            ]);

            $license->increment('current_activations');
        } else {
            $existingActivation->update([
                'last_check_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        // Build response
        $response = [
            'valid' => true,
            'message' => 'License is valid',
            'license' => [
                'product_name' => $license->product_name,
                'status' => $license->status,
                'license_type' => $license->license_type,
                'activated_at' => $license->activated_at?->toIso8601String(),
                'max_activations' => $license->max_activations,
                'current_activations' => $license->current_activations,
            ],
        ];

        if ($license->isDurationBased()) {
            $response['license']['expires_at'] = $license->expires_at?->toIso8601String();
            $response['license']['days_remaining'] = $license->days_remaining;
        } else {
            $response['license']['credits_total'] = $license->credits_total;
            $response['license']['credits_used'] = $license->credits_used;
            $response['license']['credits_remaining'] = $license->getCreditsRemaining();
        }

        // Get active AI provider API key
        $activeProvider = AppSetting::get('ai_provider', 'gemini');
        $apiKey = ApiKey::getRandomActive($activeProvider);
        
        if ($apiKey) {
            $apiKey->incrementUsage();
            $response['ai'] = [
                'provider' => $activeProvider,
                'api_key' => $apiKey->api_key,
            ];
        }

        return response()->json($response);
    }

    /**
     * Check license status (without using credit)
     */
    public function check(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'product_name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'valid' => false,
                'error' => 'validation_failed',
                'message' => 'Invalid request parameters',
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)
            ->where('product_name', $request->product_name)
            ->first();

        if (!$license) {
            return response()->json([
                'valid' => false,
                'error' => 'license_not_found',
                'message' => 'License not found',
            ], 404);
        }

        $isValid = $license->isValid();

        $response = [
            'valid' => $isValid,
            'message' => $isValid ? 'License is valid' : 'License is not valid',
            'license' => [
                'product_name' => $license->product_name,
                'status' => $license->status,
                'status_label' => $license->status_label,
                'license_type' => $license->license_type,
            ],
        ];

        if ($license->isDurationBased()) {
            $response['license']['expires_at'] = $license->expires_at?->toIso8601String();
            $response['license']['days_remaining'] = $license->days_remaining;
            $response['license']['is_expired'] = $license->isExpired();
        } else {
            $response['license']['credits_total'] = $license->credits_total;
            $response['license']['credits_used'] = $license->credits_used;
            $response['license']['credits_remaining'] = $license->getCreditsRemaining();
        }

        return response()->json($response);
    }

    /**
     * Deactivate license from a device
     */
    public function deactivate(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'license_key' => 'required|string',
            'product_name' => 'required|string',
            'device_id' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'error' => 'validation_failed',
                'message' => 'Invalid request parameters',
            ], 422);
        }

        $license = License::where('license_key', $request->license_key)
            ->where('product_name', $request->product_name)
            ->first();

        if (!$license) {
            return response()->json([
                'success' => false,
                'error' => 'license_not_found',
                'message' => 'License not found',
            ], 404);
        }

        $deviceId = $request->device_id ?? $request->ip();
        $activation = $license->activations()
            ->where('device_identifier', $deviceId)
            ->where('is_active', true)
            ->first();

        if ($activation) {
            $activation->update(['is_active' => false]);
            $license->decrement('current_activations');

            return response()->json([
                'success' => true,
                'message' => 'License deactivated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'activation_not_found',
            'message' => 'No active activation found for this device',
        ], 404);
    }
}
