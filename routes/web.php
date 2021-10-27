<?php

declare(strict_types=1);

/** @var Router $router */

use Laravel\Lumen\Routing\Router;

$router->post('/callback', 'CallbackController@callback');

$router->post('/check-callback', 'CallbackController@checkCallback');

$router->post('/error', 'CallbackController@return');

$router->post('/pay', 'PayController@index');

$router->get('/settings', 'SettingsController@index');

$router->post('/success', 'CallbackController@return');

$router->post('/webhook', 'WebhookController@index');
