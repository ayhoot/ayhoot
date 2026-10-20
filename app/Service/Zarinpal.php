<?php

namespace App\Service;

class Zarinpal
{
    protected string $merchantId;
    protected string $callbackUrl;
    protected bool $sandbox;

    protected string $apiBaseUrl;
    protected string $startPayBaseUrl;

    public function __construct()
    {
        $this->merchantId = env('ZARINPAL_MERCHANT_ID');
        $this->callbackUrl = env('ZARINPAL_CALLBACK_URL');
        $this->sandbox = env('ZARINPAL_SANDBOX', false);

        $this->apiBaseUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/v4/payment'
            : 'https://api.zarinpal.com/pg/v4/payment';

        $this->startPayBaseUrl = $this->sandbox
            ? 'https://sandbox.zarinpal.com/pg/StartPay/'
            : 'https://www.zarinpal.com/pg/StartPay/';
    }

    public function request(int $amount, string $description, string $email = '', string $mobile = '',string $callbackUrl=''): array
    {


        $metadata = [
            "email" => $email,
        ];
        if (!empty($mobile)) {
            $metadata['mobile'] = $mobile;
        }

        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "callback_url" => $callbackUrl ?: $this->callbackUrl,
            "description" => $description,
            "metadata" => $metadata
        ];

        $response = $this->callApi("/request.json", $data);

        if (isset($response['data']['code']) && $response['data']['code'] == 100) {
            return [
                'success' => true,
                'authority' => $response['data']['authority'],
                'start_pay_url' => $this->startPayBaseUrl . $response['data']['authority']
            ];
        }

        return [
            'success' => false,
            'message' => $response['errors']['message'] ?? 'Unexpected error',
            'code' => $response['errors']['code'] ?? null,
        ];
    }

    public function verify(string $authority, int $amount): array
    {
        $data = [
            "merchant_id" => $this->merchantId,
            "amount" => $amount,
            "authority" => $authority
        ];

        $response = $this->callApi("/verify.json", $data);

        if (isset($response['data']['code']) && in_array($response['data']['code'], [100, 101])) {
            return [
                'success' => true,
                'ref_id' => $response['data']['ref_id'],
                'message' => 'Payment was successful',
                'code' => $response['data']['code']
            ];
        }

        return [
            'success' => false,
            'message' => $response['errors']['message'] ?? 'Verification failed',
            'code' => $response['errors']['code'] ?? null
        ];
    }

    private function callApi(string $endpoint, array $payload): array
    {
        $jsonData = json_encode($payload);
        $ch = curl_init($this->apiBaseUrl . $endpoint);

        curl_setopt_array($ch, [
            CURLOPT_USERAGENT => 'ZarinPal Rest Api v1',
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $jsonData,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($jsonData)
            ]
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['errors' => ['message' => $error]];
        }

        return json_decode($response, true);
    }
}

