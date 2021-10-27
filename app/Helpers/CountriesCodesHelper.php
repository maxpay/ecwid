<?php

declare(strict_types=1);

namespace App\Helpers;

use Exception;

/**
 * Helper CountriesCodesHelper class.
 */
class CountriesCodesHelper
{
    /**
     * Absolute path to json file containing array of iso-2 => iso-3 upper-cased country codes.
     *
     * @see https://www.iban.com/country-codes
     * @link http://country.io/iso3.json
     * @var string
     */
    const DATA_SOURCE_PATH = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'iso3.json';

    /**
     * Cache storage for iso-2 => iso-3 country codes array.
     *
     * @var array
     */
    private static array $iso2Iso3 = [];

    /**
     * Cache storage for iso-3 => iso-2 country codes array.
     *
     * @var array
     */
    private static array $iso3Iso2 = [];

    /**
     * Get ISO-3 country code for ISO-2 country code.
     *
     * @param string|null $iso2 ISO-2 country code
     * @return string|null ISO-3 country code
     */
    public static function getIso3ByIso2(?string $iso2): ?string
    {
        if (empty($iso2)) {
            return $iso2;
        }

        if (empty(self::$iso2Iso3)) {
            self::initCodesCache();
        }

        $iso2 = strtoupper($iso2);

        return self::$iso2Iso3[$iso2] ?? null;
    }

    /**
     * Get ISO-2 country code for ISO-3 country code.
     *
     * @param string|null $iso3 ISO-3 country code
     * @return string|null ISO-2 country code
     */
    public static function getIso2ByIso3(?string $iso3): ?string
    {
        if (empty($iso3)) {
            return $iso3;
        }

        if (empty(self::$iso3Iso2)) {
            self::initCodesCache();
        }

        $iso3 = strtoupper($iso3);

        return self::$iso3Iso2[$iso3] ?? null;
    }

    /**
     * Initializes $iso2iso3 and $iso3iso2 cache arrays.
     * @throws Exception
     */
    private static function initCodesCache(): void
    {
        if (
            is_file(self::DATA_SOURCE_PATH) &&
            is_readable(self::DATA_SOURCE_PATH)
        ) {
            $json = file_get_contents(self::DATA_SOURCE_PATH);
            self::$iso2Iso3 = json_decode($json, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception('Failed to decode: ' . json_last_error_msg());
            }

            self::$iso3Iso2 = array_flip(self::$iso2Iso3);
        } else {
            self::$iso2Iso3 = [];
            self::$iso3Iso2 = [];
        }
    }
}
