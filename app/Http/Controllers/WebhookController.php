<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDataMapper;
use App\Models\OrderHandler;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Scriney;

/**
 * Controller WebhookController class.
 */
class WebhookController extends Controller
{
    const EVENT_TYPE_ORDER_UPDATED = 'order.updated';
    const PAYMENT_STATUS_REFUNDED = 'REFUNDED';

    /**
     * Controller action to process webhook call from Ecwid.
     *
     * @param Request $request
     * @return object|null
     * @throws ValidationException
     */
    public function index(Request $request): ?object
    {
        $this->validate($request, [
            'eventType' => 'required|string',
            'storeId' => 'required|int',
            'entityId' => 'required|int',
            'data.newPaymentStatus' => 'required|string',
        ]);

        $eventType = request()->post('eventType');

        if ($eventType === self::EVENT_TYPE_ORDER_UPDATED) {
            $storeId = request()->post('storeId');
            $orderNumber = request()->post('entityId');
            $requestPost = request()->post();
            $newPaymentStatus = $requestPost['data']['newPaymentStatus'];
            if ($newPaymentStatus === self::PAYMENT_STATUS_REFUNDED) {
                $this->refundOrder((int)$storeId, (int)$orderNumber);
            }
        }

        return response('', 200);
    }

    /**
     * Action method to proceed with order refund.
     *
     * @param int $storeId
     * @param int $orderNumber
     */
    protected function refundOrder(int $storeId, int $orderNumber): void
    {
        /** @var Builder $query */
        $query = Order::where('store_id', $storeId);
        $query = $query
            ->where('order_number', $orderNumber)
            ->whereIn('maxpay_payment_data_code', OrderHandler::CODES_SUCCESS);

        /** @var Order $order */
        $order = $query->first();

        if (!$order) {
            Log::alert(
                'Refund order not found: ' .
                'storeId: ' . var_export($storeId, true) .
                'orderNumber: ' . var_export($orderNumber, true)
            );
            return;
        }

        $orderData = new OrderDataMapper($order->ecwid_order_data, $order->uuid);
        $maxpayOrderId = $order->maxpay_payment_data['uniqueTransactionId'];

        try {
            $scriney = new Scriney($orderData->maxpayPublicKey, $orderData->maxpayPrivateKey);
            $response = $scriney->refund($maxpayOrderId, $orderData->totalAmount, $orderData->currency);
            $order->maxpay_refund_data = $response;
            $order->saveQuietly();
        } catch (GeneralMaxpayException $exception) {
            Log::alert('Maxpay exception while refunding order: ' . $exception->getMessage());
            return;
        }

    }
}
