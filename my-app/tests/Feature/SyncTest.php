<?php

namespace Tests\Feature;

use App\Mail\NewJobsMail;
use App\Models\Application;
use App\Models\StatsSnapshot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SyncTest extends TestCase
{
    use RefreshDatabase;

    private function validPayload(): array
    {
        return [
            'jobs' => [
                [
                    'num' => 1,
                    'company' => 'Acme Corp',
                    'role' => 'Backend Engineer',
                    'score' => 3.5,
                    'status' => 'applied',
                    'date' => '2026-08-01',
                    'report_link' => '/reports/1.md',
                    'pdf_link' => '/output/1.pdf',
                    'notes' => 'strong match',
                ],
            ],
            'stats' => ['total' => 1, 'avg_score' => 85],
        ];
    }

    public function test_rejects_request_without_token(): void
    {
        $response = $this->postJson('/api/sync', $this->validPayload());

        $response->assertStatus(401);
        $this->assertDatabaseCount('applications', 0);
    }

    public function test_rejects_request_with_wrong_token(): void
    {
        $response = $this->withToken('wrong-secret')
            ->postJson('/api/sync', $this->validPayload());

        $response->assertStatus(401);
    }

    public function test_rejects_malformed_payload(): void
    {
        $response = $this->withToken('testing-sync-secret')
            ->postJson('/api/sync', ['jobs' => 'not-an-array']);

        $response->assertStatus(422);
    }

    public function test_accepts_valid_payload_and_upserts_jobs(): void
    {
        User::factory()->create();
        Mail::fake();

        $response = $this->withToken('testing-sync-secret')
            ->postJson('/api/sync', $this->validPayload());

        $response->assertStatus(201);
        $this->assertDatabaseHas('applications', [
            'num' => 1,
            'company' => 'Acme Corp',
            'role' => 'Backend Engineer',
        ]);
        $this->assertDatabaseCount('stats_snapshots', 1);
    }

    public function test_resyncing_same_num_updates_instead_of_duplicating(): void
    {
        User::factory()->create();
        Mail::fake();

        $this->withToken('testing-sync-secret')->postJson('/api/sync', $this->validPayload());

        $payload = $this->validPayload();
        $payload['jobs'][0]['status'] = 'interview';

        $this->withToken('testing-sync-secret')->postJson('/api/sync', $payload);

        $this->assertDatabaseCount('applications', 1);
        $this->assertDatabaseHas('applications', ['num' => 1, 'status' => 'interview']);
    }

    public function test_new_job_triggers_queued_mail_to_seeded_user(): void
    {
        $user = User::factory()->create();
        Mail::fake();

        $this->withToken('testing-sync-secret')->postJson('/api/sync', $this->validPayload());

        Mail::assertQueued(NewJobsMail::class, function (NewJobsMail $mail) use ($user) {
            return $mail->hasTo($user->email) && $mail->newApplications->count() === 1;
        });
    }

    public function test_resync_with_no_new_jobs_does_not_send_mail(): void
    {
        User::factory()->create();
        Mail::fake();

        $this->withToken('testing-sync-secret')->postJson('/api/sync', $this->validPayload());
        Mail::assertQueued(NewJobsMail::class, 1);

        $this->withToken('testing-sync-secret')->postJson('/api/sync', $this->validPayload());
        Mail::assertQueued(NewJobsMail::class, 1);
    }
}
