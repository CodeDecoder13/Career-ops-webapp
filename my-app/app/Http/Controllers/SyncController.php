<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Mail\NewJobsMail;
use App\Models\Application;
use App\Models\StatsSnapshot;
use App\Models\User;
use App\Http\Requests\SyncRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

final class SyncController extends Controller
{
    public function store(SyncRequest $request): JsonResponse
    {
        $jobs = $request->validated('jobs');
        $stats = $request->validated('stats');

        $incomingNums = collect($jobs)->pluck('num')->all();

        $newApplications = DB::transaction(function () use ($jobs, $stats, $incomingNums): Collection {
            $existingNums = Application::query()
                ->whereIn('num', $incomingNums)
                ->pluck('num')
                ->all();

            foreach ($jobs as $job) {
                Application::query()->updateOrCreate(
                    ['num' => $job['num']],
                    [
                        'company' => $job['company'],
                        'role' => $job['role'],
                        'score' => $job['score'] ?? null,
                        'status' => $job['status'] ?? null,
                        'date' => $job['date'] ?? null,
                        'report_link' => $job['report_link'] ?? null,
                        'pdf_link' => $job['pdf_link'] ?? null,
                        'notes' => $job['notes'] ?? null,
                    ],
                );
            }

            StatsSnapshot::query()->create([
                'payload' => $stats,
                'synced_at' => now(),
            ]);

            $newNums = array_values(array_diff($incomingNums, $existingNums));

            return Application::query()->whereIn('num', $newNums)->get();
        });

        if ($newApplications->isNotEmpty()) {
            $user = User::query()->first();

            if ($user !== null) {
                Mail::to($user)->queue(new NewJobsMail($newApplications));
            }
        }

        return response()->json(['status' => 'ok'], 201);
    }
}
