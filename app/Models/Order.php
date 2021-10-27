<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

/**
 * Model Order class.
 *
 * @property int $id
 * @property string $uuid
 * @property int $store_id
 * @property int $order_number
 * @property string $token
 * @property array $ecwid_order_data
 * @property ?int $maxpay_payment_data_code
 * @property ?array $maxpay_payment_data
 * @property ?int $ecwid_update_status_http_code
 * @property ?array $ecwid_update_status_data
 * @property ?array $maxpay_refund_data
 * @property ?array $maxpay_refund_callback_data
 * @property ?int $created_at
 * @property ?int $updated_at
 */
class Order extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ecwid_order_data' => 'array',
        'maxpay_payment_data' => 'array',
        'ecwid_update_status_data' => 'array',
        'maxpay_refund_data' => 'array',
        'maxpay_refund_callback_data' => 'array',
    ];

    /**
     * Initialize new Order from OrderDataMapper object.
     *
     * @param OrderDataMapper $orderData
     */
    public function createNew(OrderDataMapper $orderData): void
    {
        $this->uuid = str_replace('-', '', Uuid::uuid4()->toString());
        $this->store_id = $orderData->storeId;
        $this->order_number = $orderData->orderNumber;
        $this->token = $orderData->token;
        $this->ecwid_order_data = $orderData->data;
    }
}
