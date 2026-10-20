<?php

namespace App\Service;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected ?string $apiKey;
    protected ?int $templateId;
    protected string $parameterName;

    public function __construct()
    {
        $this->apiKey = config('services.smsir.api_key');
        $this->templateId = config('services.smsir.template_id') ? (int) config('services.smsir.template_id') : null;
        $this->parameterName = (string) (config('services.smsir.parameter_name') ?: 'OTP');
    }

    /**
     * Send OTP verification code via SMS.ir Fast Send (Verify) API.
     *
     * @param string $phone
     * @param string|int $code
     * @return bool
     */
    public function sendOtp($phone, $code): bool
    {
        if (empty($this->apiKey) || empty($this->templateId)) {
            Log::error('SMS.ir configuration error: SMSIR_API_KEY or SMSIR_TEMPLATE_ID is missing in .env');
            return false;
        }

        $phone = trim((string)$phone);

        try {
            $response = Http::withoutVerifying()
                ->withHeaders([
                    'x-api-key' => $this->apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->timeout(10)
                ->post('https://api.sms.ir/v1/send/verify', [
                    'mobile' => $phone,
                    'templateId' => $this->templateId,
                    'parameters' => [
                        [
                            'name' => $this->parameterName,
                            'value' => (string) $code,
                        ],
                    ],
                ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData['status']) && $responseData['status'] === 1) {
                Log::info('SMS.ir OTP sent successfully', [
                    'mobile' => $phone,
                    'messageId' => $responseData['data']['messageId'] ?? null,
                ]);
                return true;
            }

            Log::error('SMS.ir OTP send failed', [
                'mobile' => $phone,
                'status_code' => $response->status(),
                'response' => $responseData ?: $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('SMS.ir Exception during OTP send', [
                'mobile' => $phone,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}