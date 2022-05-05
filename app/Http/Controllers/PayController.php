<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\EcwidUrlHelper;
use App\Models\OrderHandler;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Laravel\Lumen\Http\Redirector;
use Laravel\Lumen\Http\ResponseFactory;
use Maxpay\Lib\Exception\GeneralMaxpayException;
use Maxpay\Scriney;

/**
 * Controller PayController class.
 */
class PayController extends Controller
{
    /**
     * OrderHandler model.
     *
     * @var OrderHandler
     */
    private OrderHandler $orderHandler;

    /**
     * PayController constructor.
     */
    public function __construct(OrderHandler $orderHandler)
    {
        parent::__construct();

        $this->orderHandler = $orderHandler;
    }

    /**
     * Controller action to process new order and generate payment form.
     *
     * @return Response|ResponseFactory|RedirectResponse|Redirector
     */
    public function index(): object
    {
        $orderData = $this->orderHandler->processNewOrder();

        if (is_null($orderData)) {
            return response('', 500);
        }

        try {
            $userInfo = $this->orderHandler->getUserInfo($orderData);
            $customProducts = [$this->orderHandler->getProduct($orderData)];

            $scriney = new Scriney($orderData->maxpayPublicKey, $orderData->maxpayPrivateKey);

            $vars = [
                'iframe' => $scriney
                    ->buildButton($orderData->userEmail)
                    ->setUserInfo($userInfo)
                    ->setCustomProducts($customProducts)
                    ->setBackUrl(EcwidUrlHelper::getCancelUrl($orderData))
                    ->buildFrame(),
            ];

            return view('pay-form.wrapper_html', $vars);

        } catch (GeneralMaxpayException $exception) {
            Log::alert('Maxpay exception while building iframe: ' . $exception->getMessage());
            return redirect(
                EcwidUrlHelper::getReturnUrl($orderData)
            );
        }
    }
}
