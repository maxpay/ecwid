<?php

declare(strict_types=1);

namespace App\Events;

use Illuminate\Queue\SerializesModels;

/**
 * Abstract class Event.
 */
abstract class Event
{
    use SerializesModels;
}
