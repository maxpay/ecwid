<?php

declare(strict_types=1);

namespace App\Helpers;

use App\Models\OrderDataMapper;

/**
 * Helper EcwidCallbackUrlHelper class.
 */
class EcwidUrlHelper
{
    /**
     * Returns URL to get back to Ecwid store.
     *
     * @param OrderDataMapper $orderData
     * @return string
     */
    public static function getReturnUrl(OrderDataMapper $orderData): string
    {
        return
            config('ecwid.ecwid_custom_payment_apps_url') . $orderData->storeId .
            '?' . http_build_query([
                'orderId' => $orderData->orderNumber,
                'clientId' => config('ecwid.ecwid_client_id'),
            ]);
    }

    /**
     * Returns URL to update Ecwid order status.
     *
     * @param OrderDataMapper $orderData
     * @return string
     */
    public static function getUpdateOrderUrl(OrderDataMapper $orderData): string
    {
        return
            config('ecwid.ecwid_api_V3_url') . $orderData->storeId .
            '/orders/' . $orderData->transactionId .
            '?' . http_build_query([
                'token' => $orderData->token,
            ]);
    }
}
