<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\View\View;
use Laravel\Lumen\Application;

/**
 * Controller SettingsController class.
 */
class SettingsController extends Controller
{
    /**
     * Controller action to generate plugin settings form.
     *
     * @return View|Application
     */
    public function index(): object
    {
        $vars = [
            'ecwidClientId' => config('ecwid.ecwid_client_id'),
        ];

        return view('settings.index_html', $vars);
    }
}
