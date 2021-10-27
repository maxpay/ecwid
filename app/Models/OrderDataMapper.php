<?php

declare(strict_types=1);

namespace App\Models;

/**
 * Model OrderDataMapper class.
 */
class OrderDataMapper
{
    /**
     * @var array
     */
    public array $data;

    /**
     * @var string|null
     */
    public ?string $currency;

    /**
     * @var string|null
     */
    public ?string $maxpayPrivateKey;

    /**
     * @var string|null
     */
    public ?string $maxpayPublicKey;

    /**
     * @var int
     */
    public int $storeId;

    /**
     * @var int
     */
    public int $orderNumber;

    /**
     * @var string
     */
    public string $token;

    /**
     * @var float
     */
    public float $totalAmount;

    /**
     * @var string
     */
    public string $transactionId;

    /**
     * @var string|null
     */
    public ?string $userCity;

    /**
     * @var string|null
     */
    public ?string $userCountryCodeIso2;

    /**
     * @var string|null
     */
    public ?string $userEmail;

    /**
     * @var string|null
     */
    public ?string $userFirstName;

    /**
     * @var string|null
     */
    public ?string $userLastName;

    /**
     * @var string|null
     */
    public ?string $userPhone;

    /**
     * @var string|null
     */
    public ?string $userPostalCode;

    /**
     * @var string|null
     */
    public ?string $userStreet;

    /**
     * @var string|null
     */
    public ?string $uuid;

    /**
     * OrderDataMapper constructor.
     *
     * @param array $data
     * @param string|null $uuid
     */
    public function __construct(array $data, ?string $uuid = null)
    {
        $this->data = $data;
        $this->uuid = $uuid;

        $this->load($data);
    }

    /**
     * Load object properties from Ecwid order data array.
     *
     * @param array $data
     */
    private function load(array $data): void
    {
        $cartData = $data['cart'] ?? [];
        $orderData = $data['cart']['order'] ?? [];
        $personData = $data['cart']['order']['billingPerson'] ?? [];
        $settingsData = $data['merchantAppSettings'] ?? [];

        $this->currency =
            (isset($cartData['currency']) && !empty($cartData['currency']))
                ? $cartData['currency'] : null;

        $this->maxpayPrivateKey =
            (isset($settingsData['privateKey']) && !empty($settingsData['privateKey']))
                ? $settingsData['privateKey'] : null;

        $this->maxpayPublicKey =
            (isset($settingsData['publicKey']) && !empty($settingsData['publicKey']))
                ? $settingsData['publicKey'] : null;

        $this->storeId =
            (isset($data['storeId']) && !empty($data['storeId']))
                ? $data['storeId'] : null;

        $this->orderNumber =
            (isset($orderData['orderNumber']) && !empty($orderData['orderNumber']))
                ? $orderData['orderNumber'] : null;

        $this->token =
            (isset($data['token']) && !empty($data['token']))
                ? $data['token'] : null;

        $this->totalAmount =
            (isset($orderData['total']) && !empty($orderData['total']))
                ? $orderData['total'] : null;

        $this->transactionId =
            (isset($orderData['referenceTransactionId']) && !empty($orderData['referenceTransactionId']))
                ? $orderData['referenceTransactionId'] : null;

        $this->userCity =
            (isset($personData['city']) && !empty($personData['city']))
                ? $personData['city'] : null;

        $this->userCountryCodeIso2 =
            (isset($personData['countryCode']) && !empty($personData['countryCode']))
                ? $personData['countryCode'] : null;

        $this->userEmail =
            (isset($orderData['email']) && !empty($orderData['email']))
                ? $orderData['email'] : null;

        $this->userFirstName =
            (isset($personData['name']) && !empty($personData['name']))
                ? $personData['name'] : null;

        $this->userLastName = null;

        $this->userPhone =
            (isset($personData['phone']) && !empty($personData['phone']))
                ? $personData['phone'] : null;

        $this->userPostalCode =
            (isset($personData['postalCode']) && !empty($personData['postalCode']))
                ? $personData['postalCode'] : null;

        $this->userStreet =
            (isset($personData['street']) && !empty($personData['street']))
                ? $personData['street'] : null;
    }
}
