# Bellator

A closed social fitness app for small friend groups. Members log workouts, earn points via group-defined scoring rubrics, and compete on group-scoped leaderboards.

See [`PRD.md`](./PRD.md) for the full product spec and [`Project-Summary.md`](./Project-Summary.md) for the pre-MVP code inventory.

---

## Stack

- **Backend:** Laravel 11, PHP 8.2, PostgreSQL 17
- **Frontend:** Inertia.js + React 18 + TypeScript 5
- **Styling:** Tailwind 3, MUI 6
- **Build:** Vite 5
- **Auth:** Laravel Breeze (session-based; email verification required)
- **Local dev:** Laravel Sail (Docker)

---

## Local setup

Prerequisites: Docker, Composer, Node 20+.

```bash
# 1. Install PHP deps
composer install

# 2. Boot Sail (PostgreSQL, mailpit, redis)
./vendor/bin/sail up -d

# 3. Set up environment
cp .env.example .env
./vendor/bin/sail artisan key:generate

# 4. Install + build frontend
npm install
npm run dev          # watch mode for development
# or:
npm run build        # one-shot production build

# 5. Run migrations + seed reference data
./vendor/bin/sail artisan migrate --seed
```

The app will be at <http://localhost> (or whatever `APP_PORT` you set).

`migrate --seed` seeds metric units, a dev `admin@bellator.com` user (password: `password`), and the default workout types. **Exercises are no longer globally seeded** — each group gets its own copy of the default exercise set when it's created.

---

## Running tests

```bash
./vendor/bin/sail test                       # full suite
./vendor/bin/sail test --filter=LeaderboardFeedTest
./vendor/bin/sail test --filter=PointsCalculationServiceTest
```

The test database is the `testing` database, created automatically by Sail's PostgreSQL service. Tests use `RefreshDatabase` so the schema is rebuilt for each test class.

CI runs the same `php artisan test` against PostgreSQL 17 in GitHub Actions — see [`.github/workflows/ci.yml`](./.github/workflows/ci.yml). Merge protection should require this workflow to pass.

---

## Domain model overview

| Model                      | Purpose                                                                                                  |
|----------------------------|----------------------------------------------------------------------------------------------------------|
| `User`                     | UUID-keyed; soft-deletes cascade to workouts/logs (FR-AUTH-6).                                           |
| `Group`                    | A friend-group. Owns its exercise library and point rubric. `invite_code` is `BELL-XXXXXXXX`.            |
| `GroupMember`              | `(group, user, role)` pivot with role ∈ {`owner`, `admin`, `member`}.                                    |
| `Exercise`                 | Per-group; has a `measurement_type` ∈ {`reps_only`, `weighted_reps`, `distance`, `duration`}.            |
| `GroupExercisePoints`      | Rate history per (group, exercise). At most one active row (`end_date IS NULL`).                         |
| `Workout`                  | A logged session, tagged to ≥1 group via `workout_groups`.                                               |
| `WorkoutExercise`          | A workout↔exercise pivot.                                                                                |
| `WorkoutExerciseLog`       | A single set/log row; shape depends on the exercise's measurement type.                                  |
| `WorkoutExerciseLogPoints` | Per-(log, group) snapshot of points earned. Computed at log time; backfilled on first-time rubric set.   |

---

## Key flows

- **First-time user:** register → verify email → land on `/onboarding` (Group Gate). Either join an existing group via invite code or create a new one with a preset rubric (Strength / Endurance / Balanced).
- **Logging a workout:** from a group home, tap "Log workout" → pick date + notes + tagged groups → add exercises from any of those groups → log sets. Each saved set writes per-group point snapshots into `workout_exercise_log_points`.
- **Leaderboard:** group home shows `This Week | This Month | All Time`. Week starts Monday 00:00 **in the group's timezone**.
- **Activity feed:** reverse-chronological list of workouts tagged to the group, 20 per page, with the exercise rows consolidated (`5 × 5 @ 185 lbs`, `5 sets, 135–225 lbs`, etc.).
- **Group settings:** owner/admin can rename, regenerate invite code, manage members, manage exercises, and edit the rubric (anchor-based input).

---

## Project layout

```
app/
  Http/Controllers/        # Group, GroupExercise, GroupExercisePoints,
                           # GroupMember, GroupMemberProfile, Workout,
                           # WorkoutExercise, WorkoutExerciseLog, Dashboard,
                           # Onboarding, Profile, Auth/*
  Http/Middleware/         # EnsureUserHasGroup, HandleInertiaRequests
  Http/Requests/           # Store/Update form requests for every action
  Jobs/                    # BackfillGroupExerciseLogPointsJob
  Models/                  # See table above
  Policies/                # GroupPolicy, ExercisePolicy, WorkoutPolicy,
                           # WorkoutExerciseLogPolicy, GroupExercisePointsPolicy
  Services/                # GroupService, GroupExercisePointsService,
                           # WorkoutService, WorkoutExerciseService,
                           # WorkoutExerciseLogService, PointsCalculationService,
                           # LeaderboardService, ActivityFeedService
  Support/                 # DefaultExercises, PresetRubrics
database/
  factories/               # Every domain model has a factory
  migrations/              # 2024-* baseline + 2026-* MVP work
  seeders/                 # MetricUnitsSeeder + dev User/WorkoutTypes
resources/js/
  Pages/                   # Onboarding, Groups (Create/Show/Edit/Members/
                           # Exercises/Rubric), Workouts (Index/Create/Show/
                           # Edit, Exercises/Show), Profile, Auth, Welcome
  Components/Groups/       # GroupSwitcher, FlashMessages, Leaderboard, FeedCard
  Contexts/GroupContext.tsx # last-viewed group via localStorage
  Layouts/                 # AuthenticatedLayout (with GroupContextProvider)
routes/
  web.php                  # all app routes (auth/verified/group.member guards)
  auth.php                 # Breeze auth routes
tests/
  Feature/                 # Per-controller feature tests
  Unit/                    # Policy + service tests
```

---

## Things worth knowing

- All timestamps stored in UTC. Display formatting is the viewer's responsibility (`dayjs` on the frontend). Leaderboard week/month cutoffs are computed in the **group's** timezone.
- Job queue: `sync` driver in tests + CI. Production starts on `database` and can be switched later without code changes.
- Policies are auto-discovered by Laravel 11's class-name convention; no explicit registration in `AppServiceProvider`.
- Workout edits that change the tagged-group set rebuild `workout_exercise_log_points` for every log on the workout — see `WorkoutExerciseLogService::rebuildPointsForWorkout`.

---

## Future considerations

Tracked in [`PRD.md` §14](./PRD.md). Highest-likelihood next-iteration items: reactions/comments, notifications, group ownership transfer, streaks, PWA.
