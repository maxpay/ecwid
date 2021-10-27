<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\EcwidUrlHelper;
use App\Models\Order;
use App\Models\OrderDataMapper;
use App\Models\OrderHandler;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Laravel\Lumen\Http\ResponseFactory;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Scriney;

/**
 * Controller CallbackController class.
 */
class CallbackController extends Controller
{
    /**
     * Controller action to process payment transaction result
     * callback from payment system.
     *
     * @param Request $request
     * @return Response|ResponseFactory
     * @throws ValidationException
     * @throws Exception
     */
    public function callback(Request $request): object
    {
        $this->validate($request, [
            'productList' => 'required|array',
            'productList.0.productId' => 'required|string',
            'status' => 'required|string',
        ]);

        $payloadStr = request()->getContent();
        $payloadArr = request()->json()->all();
        $orderUuid = request()->input('productList.0.productId');

        /** @var Order $order */
        $order = Order::where('uuid', $orderUuid)->first();
        if (!$order) {
            Log::alert('Callback order not found: ' . var_export($orderUuid, true));
            return response('', 500);
        }

        $orderData = new OrderDataMapper($order->ecwid_order_data, $order->uuid);

        try {
            $scriney = new Scriney($orderData->maxpayPublicKey, $orderData->maxpayPrivateKey);

            $headers = [
                'X-Signature' => request()->header('x-signature'),
            ];

            if (!$scriney->validateCallback($payloadStr, $headers)) {
                throw new GeneralMaxpayException('Callback validation error');
            }

        } catch (GeneralMaxpayException $exception) {
            Log::alert('Maxpay exception while validating callback: ' . $exception->getMessage());
            return response('', 500);
        }

        if (stripos($payloadArr['status'], 'refund') !== false) {
            $order->maxpay_refund_callback_data = $payloadArr;
            $order->saveQuietly();

        } elseif (is_null($order->maxpay_payment_data)) {
            $order->maxpay_payment_data_code = $payloadArr['code'];
            $order->maxpay_payment_data = $payloadArr;
            $order->saveQuietly();

            (new OrderHandler())->processPaymentCallback($orderData, $payloadArr);
        }

        return response('OK', 200);
    }

    /**
     * Controller action to process success/error status callback
     * (successful/declined payment) from payment system.
     *
     * @param Request $request
     * @return object
     * @throws ValidationException
     */
    public function return(Request $request): object
    {
        $this->validate($request, [
            'productList' => 'required|array',
            'productList.0.productId' => 'required|string',
        ]);

        $orderUuid = request()->input('productList.0.productId');

        /** @var Order $order */
        $order = Order::where('uuid', $orderUuid)->first();
        if (!$order) {
            Log::alert('Status callback order not found: ' . var_export($orderUuid, true));
            return response('', 500);
        }

        $orderData = new OrderDataMapper($order->ecwid_order_data, $order->uuid);

        try {
            $scriney = new Scriney($orderData->maxpayPublicKey, $orderData->maxpayPrivateKey);
            if (!$scriney->validateApiResult(request()->post())) {
                throw new GeneralMaxpayException('Status callback validation error');
            }

        } catch (GeneralMaxpayException $exception) {
            Log::alert('Maxpay exception while validating status callback: ' . $exception->getMessage());
            return response('', 500);
        }

        if (empty($order->ecwid_update_status_data)) {
            $order = Order::where('id', $order->id)->first();
            if (empty($order->ecwid_update_status_data)) {
                $vars = [
                    'orderId' => $order->id,
                ];

                return view('pay-form.spinner_html', $vars);
            }
        }

        $returnUrl = EcwidUrlHelper::getReturnUrl($orderData);

        return response('<script>window.location="' . $returnUrl . '"</script>');
    }

    /**
     * Controller action to process check callback request from "Processing, please wait" page
     *
     * @param Request $request
     * @return object
     * @throws ValidationException
     */
    public function checkCallback(Request $request): object
    {
        $this->validate($request, [
            'orderId' => 'required|string',
            'fallback' => 'string',
        ]);

        if (!$request->isJson()) {
            return response()->json([
                'error' => 'Invalid request format',
                'redirectUrl' => '',
            ]);
        }

        $orderId = $request->json()->get('orderId');
        $order = Order::where('id', $orderId)->first();

        if (!$order) {
            return response()->json([
                'error' => 'Order not found',
                'redirectUrl' => '',
            ]);
        }

        $orderData = new OrderDataMapper($order->ecwid_order_data, $order->uuid);
        $redirectUrl = '';
        if (!empty($order->ecwid_update_status_data) && $request->json()->get('fallback')) {
            $redirectUrl = EcwidUrlHelper::getReturnUrl($orderData);
        }

        return response()->json([
            'error' => '',
            'redirectUrl' => $redirectUrl,
        ]);
    }
}
