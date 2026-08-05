<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\StatsSnapshot;
use Inertia\Inertia;
use Inertia\Response;

final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        $applications = Application::query()->orderedByDate()->get();
        $latestStats = StatsSnapshot::query()->latest('synced_at')->first();

        return Inertia::render('Dashboard', [
            'applications' => $applications,
            'stats' => $latestStats?->payload ?? [],
        ]);
    }
}
