<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\CountriesCodesHelper;
use App\Helpers\EcwidUrlHelper;
use Exception;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Lib\Model\FixedProduct;
use Maxpay\Lib\Model\UserInfo;
use Illuminate\Support\Facades\Log;

/**
 * Model OrderHandler class.
 */
class OrderHandler
{
    const ECWID_PAYMENT_STATUS_UNDEFINED = '';
    const ECWID_PAYMENT_STATUS_AWAITING_PAYMENT = 'AWAITING_PAYMENT';
    const ECWID_PAYMENT_STATUS_PAID = 'PAID';
    const ECWID_PAYMENT_STATUS_INCOMPLETE = 'INCOMPLETE';
    const ECWID_PAYMENT_STATUS_REFUNDED = 'REFUNDED';
    const ECWID_PAYMENT_STATUS_PARTIALLY_REFUNDED = 'PARTIALLY_REFUNDED';

    /** @see https://maxpay.com/docs/#behavior-on-response-codes */
    const CODES_SUCCESS = [0];
    const CODES_UNKNOWN = [2, 3, 10, 11, 12, 1003, 6000];

    /**
     * Cipher for encrypting/decrypting Ecwid payload.
     *
     * @var string
     */
    const CIPHER = 'AES-128-CBC';

    /**
     * Process new order that came from Ecwid.
     *
     * @return OrderDataMapper|null
     */
    public function processNewOrder(): ?OrderDataMapper
    {
        $payload = request()->input('data') ?? '';
        $payload = $this->decryptNewOrderPayload($payload);

        $orderData = new OrderDataMapper($payload);

        $order = new Order();
        $order->createNew($orderData);
        $order->saveQuietly();

        Log::info('Ecwid order saved', [
            'id' => $order->id,
            'uuid' => $order->uuid,
        ]);

        $orderData->uuid = $order->uuid;

        return $orderData;
    }

    /**
     * Decrypt new order payload from Ecwid.
     *
     * @param string $payload
     * @return array
     * @throws Exception
     */
    private function decryptNewOrderPayload(string $payload): array
    {
        // Ecwid sends data in url-safe base64. Convert the raw data to the original base64 first
        $payload = str_replace(['-', '_'], ['+', '/'], $payload);
        $payload = base64_decode($payload);
        // Initialization vector is the first 16 bytes of the received data
        if ($payload === false) {
            throw new Exception('Payload does not exist.');
        }
        $iv = substr($payload, 0, 16);
        $payload = substr($payload, 16);
        $payload = openssl_decrypt(
            $payload, self::CIPHER, config('ecwid.ecwid_client_secret'),
            OPENSSL_RAW_DATA, $iv
        );
        $result = json_decode($payload, true);
        if (is_array($result)) {
            return $result;
        } else {
            throw new Exception('Payload does not exist.');
        }
    }

    /**
     * Process payment system callback as for transaction status.
     *
     * @param OrderDataMapper $orderData
     * @param array $payload
     * @throws Exception
     */
    public function processPaymentCallback(OrderDataMapper $orderData, array $payload): void
    {
        $code = null;
        if (isset($payload['code'])) {
            $code = $payload['code'];
        }

        if (in_array($code, self::CODES_SUCCESS, true)) {
            // success
            $status = self::ECWID_PAYMENT_STATUS_PAID;
        } elseif (in_array($payload['code'], self::CODES_UNKNOWN, true)) {
            // unknown
            $status = self::ECWID_PAYMENT_STATUS_UNDEFINED;
        } else {
            // decline
            $status = self::ECWID_PAYMENT_STATUS_INCOMPLETE;
        }

        $this->updateOrderStatus($orderData, $status);
    }

    /**
     * Update order status on Ecwid side.
     *
     * @param OrderDataMapper $orderData
     * @param string $status
     * @throws Exception
     */
    private function updateOrderStatus(OrderDataMapper $orderData, string $status): void
    {
        if ($status === self::ECWID_PAYMENT_STATUS_UNDEFINED) {
            return;
        }

        /** @var Order $order */
        $order = Order::where('uuid', $orderData->uuid)->first();
        if (!$order) {
            throw new Exception('Order not found');
        }

        $url = EcwidUrlHelper::getUpdateOrderUrl($orderData);

        $data = [
            'paymentStatus' => $status,
            'externalTransactionId' => $orderData->transactionId,
        ];
        $json = json_encode($data);

        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json),
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_POSTFIELDS => $json,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 0,
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        $order->ecwid_update_status_http_code = $httpCode;
        $order->ecwid_update_status_data =
            json_decode($response, true)
            ?? ['invalid response' => var_export($response, true)];
        $order->saveQuietly();
    }

    /**
     * @param OrderDataMapper $orderData
     * @return UserInfo
     * @throws GeneralMaxpayException
     */
    public function getUserInfo(OrderDataMapper $orderData): UserInfo
    {
        return new UserInfo(
            $orderData->userEmail,
            $orderData->userFirstName,
            $orderData->userLastName,
            CountriesCodesHelper::getIso3ByIso2($orderData->userCountryCodeIso2),
            $orderData->userCity,
            $orderData->userPostalCode,
            $orderData->userStreet,
            $orderData->userPhone
        );
    }

    /**
     * @param OrderDataMapper $orderData
     * @return FixedProduct
     * @throws GeneralMaxpayException
     */
    public function getProduct(OrderDataMapper $orderData): FixedProduct
    {
        $product_name = 'Order #' . $orderData->transactionId;

        return new FixedProduct(
            $orderData->uuid,
            $product_name,
            $orderData->totalAmount,
            $orderData->currency
        );
    }
}
