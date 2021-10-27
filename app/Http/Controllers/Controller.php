<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

/**
 * Class Controller.
 */
class Controller extends BaseController
{
    public function __construct()
    {
        $requestUrl =
            (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") .
            PATH_SEPARATOR . DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR .
            $_SERVER['HTTP_HOST'] .
            $_SERVER['REQUEST_URI'];
    }
}
