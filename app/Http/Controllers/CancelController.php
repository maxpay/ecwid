<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\EcwidUrlHelper;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Controller CancelController class.
 */
class CancelController extends Controller
{
    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     * @throws Exception
     */
    public function index(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'orderId' => 'required|int',
            'storeId' => 'required|int'
        ]);
        $orderId = (int)$request->get('orderId');
        $storeId = (int)$request->get('storeId');

        return redirect(EcwidUrlHelper::getBackUrl($storeId, $orderId));
    }
}
