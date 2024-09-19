<?php

namespace App\Http\Controllers;

use Closure;
use Inertia\Inertia;
use App\Services\PromiseService;
use App\Traits\CanTrowException;
use App\Services\NotificationService;

abstract class Controller
{
    use CanTrowException;

    protected function notifier(): NotificationService
    {
        return app(NotificationService::class);
    }

    protected function promise(Closure $callback, int $times = 0, bool $commit = false, int $sleep = 5)
    {
        return app(PromiseService::class)
            ->then($callback, $times, $commit, $sleep);
    }
}
