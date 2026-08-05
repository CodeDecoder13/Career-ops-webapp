# career-ops-tracker Webapp — Design Spec

Date: 2026-08-04 (amended 2026-08-05)
Status: Approved — implementation underway

**Repo note:** implementation lives in this repo (`CodeDecoder13/Career-ops-webapp`), Laravel app under `my-app/`.

## Purpose

A read-only web dashboard that mirrors the local [career-ops](../../../../career-ops) job-search pipeline (tracker table + pipeline stats) so it can be viewed from anywhere, not just the terminal/local files. career-ops remains the sole source of truth; the webapp never writes back to it.

## Scope

**In scope (MVP):**
- Read-only dashboard: tracker table (company, role, score, status, date, report link) + stats overview (funnel counts, avg score, pipeline health, scan totals)
- Automatic sync from local career-ops files to the hosted webapp, triggered by file changes (no manual export step)
- Simple password-gated access (single user)
- Local-first verification before deploying to Railway
- Email summary notification when new job(s) land in a sync batch (company + role list, one email per batch)
- Weekly automated scan (`node scan.mjs` every Monday via Windows Task Scheduler) — local OS-level cron, no new code in career-ops

**Explicitly out of scope (MVP):**
- Any write path from the webapp back into career-ops (no status changes, no triggering scans/evaluations from the browser)
- Multi-user accounts / roles
- Editing career-ops core (`.mjs` scripts, `modes/*`) — the sync mechanism must not modify any file covered by career-ops's `DATA_CONTRACT.md` system layer
- Full report content rendering (report detail view) — table + stats only for MVP

**Parked (not built this round):**
- n8n integration (post-scan webhook + downstream automation) — deferred by user decision on 2026-08-05. Revisit later; do not build the webhook or any n8n workflow until explicitly requested again.

## Architecture

Two independently deployable pieces:

1. **sync-watcher** — a small Node script that runs locally, alongside career-ops. Watches `data/applications.md` and relevant `data/*.tsv` files for changes, and pushes fresh data to the webapp. Lives outside the career-ops repo (or as an untracked sibling script) so it never touches files covered by the data contract.
2. **career-ops-tracker webapp** — a Laravel + Inertia.js + Vue 3 application, single deploy, hosted on Railway with a MySQL database.

## Components

### sync-watcher.mjs (local)

- Uses `chokidar` to watch `data/applications.md` + the TSVs that feed `stats.mjs`
- On change, debounced ~2s: parses the tracker table from `applications.md`, and runs `node stats.mjs --json` to get the current pipeline stats snapshot
- POSTs a combined JSON payload to `{WEBAPP_URL}/api/sync` with header `Authorization: Bearer {SYNC_SECRET}`
- On failure: retries up to 3x with backoff, then logs the failure to a local file and continues watching (never crashes, never blocks career-ops usage)

### Laravel webapp (Railway)

- **Auth:** Laravel Breeze, Inertia+Vue preset. One seeded user (email + password = `SITE_PASSWORD`), no self-registration. All dashboard routes sit behind the `auth` middleware.
- **`POST /api/sync`:** outside the `auth` middleware; guarded instead by a custom `sync.token` middleware that checks the bearer token against `config('services.sync.secret')`. Payload validated via a `FormRequest` (rejects malformed shape with 422).
- **`GET /` (behind `auth`):** `DashboardController` loads the current `Job` rows and the latest `StatsSnapshot` from the DB and renders `Inertia::render('Dashboard', [...])`.
- **`Dashboard.vue`:** stat cards (funnel counts, avg score, pipeline health, scan totals) + tracker table (company, role, score, status, date, report link).
- **New-job email:** on `POST /api/sync`, diff incoming `jobs` payload against existing `num`s. If any are new, queue one `Mail` (Laravel queued mailable) listing company + role per new job — one email per sync batch, not per job. Sent to the single seeded user's email.

### Weekly scan automation (local, out-of-band)

- Windows Task Scheduler task, weekly (Monday), runs `node scan.mjs` inside the career-ops directory — same as running it by hand, no new career-ops code, no data-contract changes.
- The existing sync-watcher picks up the resulting file changes normally; no new watcher logic needed for this piece.

### Data model (Eloquent / MySQL)

- `jobs`: `num` (unique), `company`, `role`, `score`, `status`, `date`, `report_link`, `pdf_link`, `notes`, timestamps. Sync upserts by `num`.
- `stats_snapshots`: `payload` (json, raw `stats.mjs --json` output), `synced_at`. Sync inserts a new row each sync; dashboard reads the latest.

No other tables, no other write path. This keeps the webapp a pure mirror — all pipeline logic (scoring, funnel calculation, dedup) stays in career-ops.

## Data flow

```
career-ops pipeline op (evaluate / scan / set-status)
  → applications.md / *.tsv changes on disk
  → sync-watcher detects change (debounced)
  → parses tracker + runs stats.mjs --json
  → POST /api/sync (bearer token)
  → Laravel validates token + payload shape
  → Eloquent upsert (jobs) / insert (stats_snapshots)
  → diff against prior num set → new rows found? queue summary email
  → next dashboard page load (session-gated) reads DB
  → Inertia renders Dashboard.vue

Weekly (separate cadence):
Windows Task Scheduler (Monday) → node scan.mjs → data files change → sync-watcher picks up as normal, flow above continues
```

## Error handling

- **Watcher:** retry + backoff + local log on failure; never crashes; never writes to any career-ops file.
- **API:** 401 on bad/missing sync token; 422 on payload that fails `FormRequest` validation.
- **Webapp:** no mutation route exists back toward career-ops or local files — the read-only scope is enforced structurally, not just by convention.

## Testing

- Pest/PHPUnit feature tests for `POST /api/sync`: valid token, invalid token, malformed payload.
- Unit test for the watcher's tracker-parsing function against a fixture `applications.md`.
- Manual verification: login flow, tracker table renders, stat cards render, sync loop confirmed end-to-end locally before first Railway deploy.

## Deployment

1. **Local first:** `php artisan serve` + local MySQL (Docker) + `npm run dev` (Vite/Inertia) + `sync-watcher.mjs` pointed at `WEBAPP_URL=http://localhost:8000`. Confirm the full loop (career-ops write → dashboard update) before touching Railway.
2. **Railway:** push repo, provision MySQL, set env vars (`APP_KEY`, `DB_*`, `SYNC_SECRET`, `SITE_PASSWORD`), run `artisan migrate` on deploy, then point the local `sync-watcher.mjs`'s `WEBAPP_URL` at the Railway URL.

## Risks / open notes

- Single shared password is an MVP-grade access control, not real multi-factor auth — acceptable for a single-user personal tool, should be revisited if the URL is ever shared.
- `stats_snapshots` grows one row per sync; MVP doesn't prune old snapshots. Fine at personal-project volume; revisit if sync frequency ever gets high enough to matter.
- The watcher is a new standalone script, not part of the career-ops repo's tracked system layer — it must be documented somewhere the user will actually find it again (this project's own README), since career-ops's own `DATA_CONTRACT.md` has no knowledge of it.
