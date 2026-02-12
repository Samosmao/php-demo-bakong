<?php
// PayWayApiCheckout.php

// ====== HARD-CODE CONFIG (EDIT HERE) ======
define('ABA_PAYWAY_API_URL', 'https://checkout-sandbox.payway.com.kh/api/payment-gateway/v1/payments/purchase');
define('ABA_PAYWAY_MERCHANT_ID', 'ec463594');
define('ABA_PAYWAY_API_KEY', '017552060dc3c37efffc52c0de03c67db2d114c4');
// ==========================================

final class PayWayApiCheckout
{
    public static function getApiUrl(): string
    {
        return ABA_PAYWAY_API_URL;
    }

    public static function getMerchantId(): string
    {
        return ABA_PAYWAY_MERCHANT_ID;
    }

    public static function getHash(string $data): string
    {
        return base64_encode(hash_hmac('sha512', $data, ABA_PAYWAY_API_KEY, true));
    }

    /**
     * Builds the hash string in EXACT order per PayWay docs.
     * All fields (even empty) must be included if submitted in the form.
     */
    public static function buildHash(array $payload): string
    {
        $order = [
            'req_time',
            'merchant_id',
            'tran_id',
            'amount',
            'items',
            'shipping',
            'ctid',
            'pwt',
            'firstname',
            'lastname',
            'email',
            'phone',
            'type',
            'payment_option',
            'return_url',
            'cancel_url',
            'continue_success_url',
            'return_deeplink',
            'currency',
            'custom_fields',
            'return_params',
        ];

        $s = '';
        foreach ($order as $k) {
            $s .= (string)($payload[$k] ?? '');
        }
        return self::getHash($s);
    }
}
