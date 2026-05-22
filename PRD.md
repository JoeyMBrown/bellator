# Bellator MVP — Product Requirements Document

> **Status:** Draft v1
> **Companion document:** [Project-Summary.md](./Project-Summary.md) — current-state inventory
> **Owner:** JoeyMBrown

---

## 1. Vision & Context

### 1.1 Product vision
Bellator is a **closed social fitness app for small friend groups**. Members log their workouts, earn points based on group-defined scoring rubrics, and compete on group-scoped leaderboards. The core value proposition is **accountability through visibility** and **friendly competition** between people who already know each other.

Unlike general-purpose fitness trackers (Strong, Hevy, Strava), Bellator does not try to be the best logging tool, the best programming tool, or the best public social network. It is the **shared scoreboard** for a friend group's training — the thing that gets you to log today because you know your buddies will see it.

### 1.2 Target user
- Closed friend groups of **5–10 people** (typical group), with the app scaling to **~100 total users** across many groups.
- Hybrid/varied training styles: strength + cardio + bodyweight + time-based work. Exercises vary substantially person to person.
- Users who are already in active communication with each other (text, Slack, etc.) — the app is a complement to that channel, not a replacement.

### 1.3 Differentiation
- **Group-scoped scoring rubrics**: each group defines what activities are worth what, so the leaderboard is meaningful for the activities that group actually does.
- **Multi-group membership with selective tagging**: a user can belong to several groups; each workout is tagged to the groups it should count toward.
- **Closed by design**: invite-code-only access. No discovery, no public profiles, no algorithmic feed.

---

## 2. Success Criteria

### 2.1 Ship criteria (feature completeness)
MVP ships when **all in-scope features below pass their acceptance criteria** (see §12) and the full PHPUnit suite is green in CI.

### 2.2 Adoption criteria (30-day post-launch validation)
Measured over the 30 days following launch:
- ≥1 active group with ≥5 members
- ≥10 unique users with ≥1 workout logged
- ≥50% weekly active rate among joined users across weeks 2–4
- ≥3 average workouts/user/week among weekly-actives

Adoption misses do not invalidate the MVP ship; they inform the next iteration's PRD.

---

## 3. MVP Scope

### 3.1 In scope
1. Email/password auth (Breeze baseline) with email verification
2. Groups: create, join via invite code, member roles (owner / admin / member)
3. Group exercise libraries (per-group)
4. Workouts: create, attach exercises, log sets/reps/metrics
5. Workout group-tagging (≥1 group required per workout)
6. Points system: per-group rubric, four measurement types, anchor-based input UX, preset rubrics, snapshot-at-log-time + first-time-set backfill
7. Group leaderboard with `This Week | This Month | All Time` tabs (Monday-start week)
8. Group activity feed (chronological, consolidated by exercise)
9. User profile within a group (their workouts in that group)
10. Mobile-first responsive web UI
11. Test suite (feature + unit + policy) and CI

### 3.2 Out of scope (deferred to post-MVP)
- Social interactions: reactions/kudos, comments, @-mentions
- Notifications (email, in-app, push)
- PWA / offline / installable
- Native mobile apps
- Streaks, badges, achievements
- Custom leaderboard date ranges / seasons / challenges
- Workout templates and programs
- Coaching / multi-tier user roles
- Public discovery of groups
- Transfer of group ownership
- Per-invite tokens / email-targeted invites
- Workout types surfaced in UI (schema retained, hidden)
- Tiered/threshold scoring formulas
- Time-window streaks and consecutive-day tracking
- Frontend component tests, E2E tests, visual regression

---

## 4. Personas & Roles

### 4.1 User personas
- **Active member**: logs workouts regularly, competes on the leaderboard, may belong to multiple groups.
- **Casual member**: logs sporadically, primarily lurks and watches the feed.
- **Group organizer (owner)**: created the group, invites friends, configures the rubric.
- **Group admin**: appointed by the owner; can manage members and rubric/exercises.

### 4.2 App-level vs group-level roles
- **App-level**: standard user (no app-wide admin role in MVP).
- **Group-level**: `owner` (one per group; the creator), `admin` (zero or more; appointed by owner), `member` (default).

### 4.3 Permission summary

| Action | Member | Admin | Owner |
|---|---|---|---|
| View group leaderboard, feed, profiles | ✅ | ✅ | ✅ |
| Log own workout tagged to group | ✅ | ✅ | ✅ |
| Edit/delete own workout & logs | ✅ | ✅ | ✅ |
| View invite code | ✅ | ✅ | ✅ |
| Regenerate invite code | ❌ | ✅ | ✅ |
| Invite users (share code) | ✅ | ✅ | ✅ |
| Remove members | ❌ | ✅ | ✅ |
| Promote/demote admin | ❌ | ❌ | ✅ |
| Create/edit/soft-delete group exercises | ❌ | ✅ | ✅ |
| Set/edit group point rubric | ❌ | ✅ | ✅ |
| Rename group / edit description | ❌ | ✅ | ✅ |
| Soft-delete group | ❌ | ❌ | ✅ |
| Leave group | ✅ | ✅ | ❌ (must transfer or delete — deferred; for MVP owner cannot leave) |

---

## 5. Core User Journeys

### 5.1 First-time user (no group yet)
1. Register → verify email → land on **group gate** screen
2. Two paths:
   - **Join existing group**: enter invite code → confirm → land on group home
   - **Create new group**: enter group name → pick preset rubric (Strength / Endurance / Balanced) → land on group home as owner
3. On group home (empty state): prompt to log first workout
4. Tap "Log Workout" → date defaults to today → pick group(s) to tag → add exercises → log sets → save → see workout reflected on leaderboard and feed

### 5.2 Returning user
1. Login → land on default group's home (last-viewed group)
2. See leaderboard (default tab: This Week) and feed
3. Tap FAB to log a workout

### 5.3 Group owner: setup
1. Create group → name → preset rubric → invite code displayed
2. Share invite code with friends (out-of-band: text, Slack, etc.)
3. Open "Group Settings" to:
   - Adjust point values per exercise (anchor-based input)
   - Add custom exercises for activities the group does
   - Promote a member to admin

### 5.4 Existing user joins additional group
1. Receive invite code from friend
2. From any screen, "Join Group" → enter code → land on new group home
3. Workouts already logged elsewhere do not retroactively appear in the new group; only future workouts tagged to it count

---

## 6. Functional Requirements

### 6.1 Authentication & Onboarding
**FR-AUTH-1** — Email/password registration, login, logout (Breeze defaults retained).
**FR-AUTH-2** — Email verification required before any application route is accessible.
**FR-AUTH-3** — Password reset via email link (Breeze default).
**FR-AUTH-4** — After registration & verification, user is routed to the **Group Gate** screen and cannot access workout/leaderboard routes until they are a member of ≥1 group.
**FR-AUTH-5** — User profile (name, email, timezone) editable from Settings. Timezone defaults to browser-detected at signup; editable thereafter.
**FR-AUTH-6** — Account deletion (Breeze default) soft-deletes the user and cascades to their workouts/logs (also soft-deleted). No anonymization in MVP.

### 6.2 Groups
**FR-GRP-1** — Any authenticated user may create a group. The creator becomes `owner`.
**FR-GRP-2** — A group has: `name` (required, ≤60), `description` (optional, ≤140), `invite_code` (auto-generated, displayed to all members, regeneratable by admin/owner), `timezone` (defaults to creator's, used for week boundary on leaderboard), soft-delete.
**FR-GRP-3** — Any authenticated user may join a group by entering its invite code. Joining is immediate (no approval flow in MVP).
**FR-GRP-4** — A user may belong to multiple groups. There is no cap on group count per user in MVP.
**FR-GRP-5** — Members may leave a group at any time (except owner; owner must delete the group to exit — transfer is deferred).
**FR-GRP-6** — Admin/owner may remove a member from a group. Removed members' historical workouts/logs in the group remain visible to other members (preserves leaderboard integrity). Removed users display as "former member" on past records.
**FR-GRP-7** — Owner may soft-delete the group. Members lose access; underlying workouts/logs are retained on user records but no longer counted for any group.
**FR-GRP-8** — Owner may promote/demote admins. There is exactly one owner per group at any time.
**FR-GRP-9** — Group settings UI (admin-only): rename, edit description, regenerate invite code, manage members, edit exercises, edit rubric.

### 6.3 Exercises
**FR-EX-1** — Exercise libraries are **per-group**. An exercise belongs to exactly one group.
**FR-EX-2** — Each exercise has: `name` (required, ≤60), `description` (optional, ≤140), `measurement_type` (enum, required), `created_by_user_id`, `group_id`, soft-delete.
**FR-EX-3** — `measurement_type` enum values:
   - `reps_only` — logs capture reps only (e.g., push-ups)
   - `weighted_reps` — logs capture reps + weight + weight unit (e.g., bench press)
   - `distance` — logs capture distance + distance unit (e.g., run)
   - `duration` — logs capture seconds (e.g., plank)
**FR-EX-4** — On group creation, the group is seeded with a copy of the **app-wide default exercise set** (the existing seeded core: Bench Press, Squat, Run, Sprint, Swim) with appropriate `measurement_type` set per exercise. The chosen preset rubric also seeds initial point values for these.
**FR-EX-5** — Admin/owner may create new exercises within a group, edit existing exercises (name, description; not measurement_type once logs reference it), and soft-delete exercises.
**FR-EX-6** — Members can create exercises in MVP, they may also edit and delete exercises they themselves created, but cannot assign, edit, or remove point values for an exercise.
**FR-EX-7** — Soft-deleted exercises no longer appear in workout/exercise pickers but historical logs continue to display the exercise name.

### 6.4 Workouts & Logs
**FR-WO-1** — A workout has: `user_id` (owner), `workout_date` (required, default today), `notes` (optional, ≤500), `workout_type_id` (nullable, schema-retained, hidden from UI), soft-delete.
**FR-WO-2** — A workout must be tagged to **at least one group** the user is a member of (`workout_groups` pivot, many-to-many). Tagging is selected at workout creation via multi-select.
**FR-WO-3** — A workout has zero or more `WorkoutExercise` rows linking it to exercises from the tagged group(s). Picker shows exercises available in any of the tagged groups.
**FR-WO-4** — A `WorkoutExercise` has zero or more `WorkoutExerciseLog` rows (sets).
**FR-WO-5** — `WorkoutExerciseLog` fields depend on the parent exercise's `measurement_type`:
   - `reps_only`: `repetitions` (required, int ≥1)
   - `weighted_reps`: `repetitions` + `exercise_metric` (weight) + `metric_unit_id` (lbs/kg)
   - `distance`: `exercise_metric` (distance) + `metric_unit_id` (mi/km/yd/m); `repetitions` defaults to 1
   - `duration`: `exercise_metric` (seconds) + `metric_unit_id` (seconds); `repetitions` defaults to 1
   - `points_earned` (decimal, nullable) — snapshotted at log time
**FR-WO-6** — Workout owner may edit/delete the workout and its logs. Admin/owner of a tagged group may NOT edit another user's workout (logs are personal).
**FR-WO-7** — A user with zero group memberships cannot reach the workout-create flow; they are routed back to the Group Gate.
**FR-WO-8** — If a user is removed from a group after tagging a workout to it, the workout remains tagged historically; their access to that group's surfaces is gone but their data persists for remaining members.

### 6.5 Points System
**FR-PT-1** — Point values are defined **per group per exercise**. Stored on a `group_exercise_points` table (`group_id`, `exercise_id`, `points_per_unit`, `start_date`, `end_date`).
**FR-PT-2** — A group may have zero or one currently-active point row per exercise (`end_date IS NULL`). Edits "close" the prior row (`end_date = now()`) and insert a new one. (MVP UI shows only the current row; history table supports future "rate history" features.)
**FR-PT-3** — Point calculation formula by `measurement_type`:
   - `reps_only`: `points = repetitions × points_per_unit`
   - `weighted_reps`: `points = repetitions × exercise_metric × points_per_unit`
   - `distance`: `points = exercise_metric × points_per_unit`
   - `duration`: `points = exercise_metric × points_per_unit` (where `exercise_metric` is in seconds)
**FR-PT-4** — **Anchor-based input UX** for setting point values. The admin enters a concrete reference example; the system derives `points_per_unit` invisibly. Example forms:
   - Weighted reps: "[ X ] points for [ R ] reps at [ W ] [unit]" → `points_per_unit = X / (R × W)`
   - Distance: "[ X ] points for [ D ] [unit]" → `points_per_unit = X / D`
   - Reps only: "[ X ] points for [ R ] reps" → `points_per_unit = X / R`
   - Duration: "[ X ] points for [ T ] seconds" → `points_per_unit = X / T`
**FR-PT-5** — **Preset rubrics** (Strength-focused / Endurance-focused / Balanced) available at group creation. Each preset pre-populates point values for the seeded core exercises in proportions appropriate to its theme. After creation, the rubric is fully editable.
**FR-PT-6** — **Snapshot-at-log-time**: when a `WorkoutExerciseLog` is saved, the current `points_per_unit` for the exercise in each tagged group is fetched and `points_earned` is computed and stored on the log row. The stored value is **per-log**, not per-group — see FR-PT-9 for multi-group attribution.
**FR-PT-7** — Subsequent edits to an exercise's point value do **not** retroactively change `points_earned` on existing logs.
**FR-PT-8** — **NULL-log backfill**: when a `points_per_unit` is set for an exercise for the first time (no prior row exists for that group/exercise), a background job (`BackfillGroupExerciseLogPoints`) computes `points_earned` for all logs in that group on that exercise where `points_earned IS NULL` and updates them. UI shows "N pending logs will receive points when you save."
**FR-PT-9** — **Multi-group attribution**: since a workout may be tagged to multiple groups with different rubrics, `points_earned` on a log is computed and stored **per (log, group)** in a `workout_exercise_log_points` join table (`workout_exercise_log_id`, `group_id`, `points_earned`). Leaderboard queries filter by `group_id`. The log row itself does not store points directly.
**FR-PT-10** — If a log's exercise has no active `points_per_unit` for a tagged group, the join row is inserted with `points_earned = NULL` and is eligible for backfill.

### 6.6 Leaderboard & Visibility

**Leaderboard**
**FR-LB-1** — Each group has a leaderboard scoped to its members.
**FR-LB-2** — Time-window tabs: `This Week` (default), `This Month`, `All Time`.
**FR-LB-3** — Week starts Monday 00:00 in the **group's timezone**. Month and "All Time" follow the same timezone for cutoffs.
**FR-LB-4** — Ranking: descending by total `points_earned` summed from `workout_exercise_log_points` rows for the group, filtered to the time window via the parent workout's `workout_date`.
**FR-LB-5** — Per row displays: rank, avatar (initials placeholder in MVP), display name, total points, count of workouts in window, last workout date.
**FR-LB-6** — Tap a row → user's group-scoped profile (FR-VIS-1).
**FR-LB-7** — Pagination: leaderboards in MVP are scoped to ≤100 members; render all rows. No pagination needed.

**Activity feed**
**FR-VIS-2** — The group home page shows a reverse-chronological feed of workouts tagged to the group.
**FR-VIS-3** — Each feed card: user avatar + name, time ago (relative, with absolute on hover), total points for this workout in this group, exercises consolidated as compact summary lines:
   - `Bench Press — 5 × 5 @ 185 lbs` (uniform sets)
   - `Squat — 5 sets, 135–225 lbs` (varying weights → range)
   - `Run — 3.5 mi` (distance)
   - `Plank — 60 s` (duration)
   - `Push-Ups — 3 × 20` (reps-only)
**FR-VIS-4** — Card truncation: first 3 exercises shown; if more, render `+ N more` that expands inline (no navigation).
**FR-VIS-5** — Pagination: feed paginates 20 cards per page (load-more button at the bottom). No infinite scroll in MVP.
**FR-VIS-6** — Tap card body → workout detail page (read-only for non-owners, editable for owner).

**User profile in group**
**FR-VIS-1** — A user's profile within a group context shows: their avatar + display name, member role badge, member-since date, total points (for the active time window), recent workouts list (reuses feed-card format), and aggregate stats (total workouts, total points all-time in this group).

---

## 7. Data Model Changes

### 7.1 New tables

**`groups`**
- `id` (auto)
- `name` (string 60, required)
- `description` (string 140, nullable)
- `invite_code` (string 16, unique, indexed)
- `timezone` (string 64, default `UTC`)
- `created_by_user_id` (FK users)
- timestamps, soft_deletes

**`group_members`**
- `id` (auto)
- `group_id` (FK groups)
- `user_id` (FK users)
- `role` (enum: `owner`, `admin`, `member`)
- `joined_at` (timestamp)
- timestamps, soft_deletes
- unique constraint on (`group_id`, `user_id`)
- check: exactly one `owner` per group (enforced at app level)

**`workout_groups`**
- `id` (auto)
- `workout_id` (FK workouts)
- `group_id` (FK groups)
- timestamps
- unique constraint on (`workout_id`, `group_id`)

**`group_exercise_points`**
- `id` (auto)
- `group_id` (FK groups)
- `exercise_id` (FK exercises)
- `points_per_unit` (decimal 14,6)
- `start_date` (timestamp, default now)
- `end_date` (timestamp, nullable)
- timestamps, soft_deletes
- Index on (`group_id`, `exercise_id`, `end_date`)
- Partial-unique-equivalent (enforced at app level): only one row per (group, exercise) where `end_date IS NULL`

**`workout_exercise_log_points`**
- `id` (auto)
- `workout_exercise_log_id` (FK workout_exercise_logs)
- `group_id` (FK groups)
- `points_earned` (decimal 14,4, nullable)
- timestamps
- unique constraint on (`workout_exercise_log_id`, `group_id`)

### 7.2 Modified tables

**`exercises`**
- Add `group_id` (FK groups, required)
- Add `measurement_type` (enum: `reps_only`, `weighted_reps`, `distance`, `duration`, required)
- Add `created_by_user_id` (FK users)

**`workouts`**
- Add `notes` (text, nullable, max 500 chars enforced at request level)
- Drop the inline use of `workout_type_id` from forms (schema retained)

**`workout_exercise_logs`**
- Rename `repitions` → `repetitions` (typo fix)
- Make `exercise_metric` and `metric_unit_id` nullable (to support `reps_only`)

**`metric_units`** — seed additions:
- `seconds` (for duration)
- `kilometers`, `meters` (for distance — alongside existing miles, yards)

### 7.3 Tables to drop or rework
- **`exercise_points_history`** — rework into `group_exercise_points` per above. The self-referential `belongsTo` bug is moot once the model is rewritten.

### 7.4 Seeders
- `MetricUnitsSeeder` — extend to include seconds, kilometers, meters
- `ExercisesSeeder` — convert to seed an "app-default exercise set" that's referenced when groups are created, not as live `exercises` rows
- `GroupPresetRubricsSeeder` (new) — Strength / Endurance / Balanced presets

---

## 8. Technical Requirements

### 8.1 Tech stack (no changes)
- Laravel 11 / PHP 8.2
- Inertia.js + React 18 + TypeScript 5
- Tailwind 3 + MUI 6
- Vite 5

### 8.2 Authorization
- Laravel **Policies** for all domain models: `GroupPolicy`, `ExercisePolicy`, `WorkoutPolicy`, `WorkoutExerciseLogPolicy`, `GroupExercisePointsPolicy`.
- All workout/exercise/log routes wrapped in middleware that enforces "user is a member of any group that grants visibility to this resource."
- Controllers must `$this->authorize(...)` against the relevant policy before action.

### 8.3 Background jobs
- `BackfillGroupExerciseLogPointsJob` — dispatched when a `group_exercise_points` row is created for an (group, exercise) pair that previously had no row. Iterates logs and inserts/updates `workout_exercise_log_points`.
- Queue driver: `sync` is acceptable for MVP given low volume. CI uses `sync` (already configured). Production starts on `database` driver — switchable later without code changes.

### 8.4 Timezones
- All timestamps stored in **UTC**.
- `users.timezone` (string 64) — set from browser at signup, editable.
- `groups.timezone` (string 64) — set from creator at group creation, editable.
- Displayed times converted to the **viewing user's** timezone.
- Week/month boundaries on the leaderboard computed in the **group's** timezone (a single canonical cutoff for the leaderboard, regardless of viewer).
- Remove the hardcoded `America/Detroit` accessor in `Workout` model.

### 8.5 Mobile-first responsive UI
- All screens designed mobile-first (target: iPhone SE viewport — 375px).
- Tap targets ≥44px.
- Forms use native mobile-friendly inputs (number type with `inputMode="decimal"`).
- The exercise picker and log-set entry are optimized for one-handed gym use.

### 8.6 Frontend conventions
- Group context held in a React context provider (current group + user's group list).
- Persist last-viewed group in `localStorage`; restore on next session.

### 8.7 Performance
- No formal performance budget for MVP. Soft target: any page interactive in <2s on a mid-range Android over 4G. Re-evaluate post-launch.

---

## 9. Test Strategy

### 9.1 In scope
- **Feature/integration tests** (PHPUnit, Laravel `RefreshDatabase`):
  - Every controller action: unauth redirect, auth happy path, authz denial, validation failure.
  - All group scoping rules (user not in group cannot view/modify).
  - Workout-group tagging rules.
  - Onboarding gate (zero-group user cannot reach workout routes).
- **Unit tests for `PointsCalculationService`**:
  - Each `measurement_type` formula produces correct points
  - Anchor-based input → `points_per_unit` derivation is correct
  - Snapshot-at-log-time: edits to point rate after log do not change historical `points_earned`
  - Backfill job: NULL-pointed logs filled on first-time set; subsequent edits do NOT trigger backfill
- **Policy tests**: ≥1 allow + ≥1 deny per policy method.
- **Model factories**: one per model with sensible defaults and named states (e.g., `Group::factory()->withOwner($user)`, `Workout::factory()->forGroup($group)`).

### 9.2 Out of scope (defer post-MVP)
- Frontend component tests (React)
- E2E tests (Dusk/Playwright)
- Visual regression
- Load/performance tests

### 9.3 Acceptance for test coverage
Not a percentage. Specific gates per the §9.1 list. CI must pass on every PR before merge.

### 9.4 CI
- GitHub Actions workflow:
  - Trigger: `push` and `pull_request` to any branch
  - Steps: PHP setup, composer install, npm install + build, `php artisan test`
  - Block merges on failure (configured in repo settings)

---

## 10. Technical Debt & Cleanup (bundled into MVP work)

These are existing issues called out in `Project-Summary.md` that are fixed as part of MVP because they are cheap to do now and load-bearing for new features:

- **`repitions` → `repetitions`** rename across migration, model, request, frontend forms, and any tests.
- **Remove `America/Detroit` hardcoding** in `Workout::getWorkoutDateAttribute` — replace with viewer-timezone-aware formatting (done frontend-side from UTC ISO string).
- **Drop `exercise_points_history`** (with its self-referential `belongsTo` bug) in favor of `group_exercise_points`.
- **Tighten Form Request validation** per existing TODOs (`StoreWorkoutExerciseRequest`, `StoreWorkoutExerciseLogRequest`) — strict types, foreign-key existence checks, ownership checks where appropriate.
- **Extract service classes** per existing TODOs: `WorkoutService`, `WorkoutExerciseService`, `WorkoutExerciseLogService`, `PointsCalculationService`. Controllers delegate.
- **Add Laravel Policies** to satisfy the existing "TODO: policy required for user workout access restriction" comment.

---

## 11. Work Breakdown & Prioritization

Each phase is sequenced; phases within can be parallelized.

### Phase 1 — Foundation (schema + scaffolding)
1. Schema: migrations for `groups`, `group_members`, `workout_groups`, `group_exercise_points`, `workout_exercise_log_points`
2. Schema modifications: `exercises.group_id`, `exercises.measurement_type`, `workouts.notes`, rename `repitions`
3. Models, factories, relationships
4. Seeders: extended `MetricUnitsSeeder`, new `GroupPresetRubricsSeeder`, refactored `ExercisesSeeder`
5. Policies for all domain models
6. Cleanup: drop `America/Detroit` hardcoding, drop `exercise_points_history`

**Exit:** schema + factories + policies in place; existing tests still green (Breeze auth suite).

### Phase 2 — Groups & onboarding
1. `GroupController`, `GroupMemberController` (create, show, join, leave, regenerate code, manage members)
2. Group settings page (admin/owner only)
3. Group Gate / onboarding flow (route guard for zero-group users)
4. Frontend: group context provider, group switcher in nav, group home shell
5. Feature tests for group CRUD, membership, role permissions

**Exit:** a user can sign up, create a group, share an invite code, and have a friend join; both land on a (mostly empty) group home.

### Phase 3 — Exercises & points configuration
1. `GroupExerciseController` (admin CRUD for exercises in a group, with `measurement_type`)
2. `GroupExercisePointsController` with anchor-based input UI
3. `PointsCalculationService` (unit-tested)
4. Preset rubric application at group creation
5. Frontend: exercise management screen, rubric configuration screen
6. Unit tests for points calc, feature tests for endpoints

**Exit:** a group owner can configure their full library of exercises with point values; preset rubrics work end-to-end.

### Phase 4 — Workouts, logs & multi-group attribution
1. Refactor `WorkoutController`, `WorkoutExerciseController`, `WorkoutExerciseLogController` to extract services
2. Workout multi-group tagging UI (multi-select)
3. Log form adapts to `measurement_type` of selected exercise
4. `workout_exercise_log_points` write on log save (snapshot at log time)
5. `BackfillGroupExerciseLogPointsJob`
6. Frontend: workout create/edit, log entry optimized for mobile
7. Feature + service tests

**Exit:** a user can log a workout tagged to one or more groups, with correct point attribution per group; first-time rubric set backfills correctly.

### Phase 5 — Leaderboard & feed & profile
1. Leaderboard queries (per group, per time window, in group timezone)
2. Activity feed queries with pagination
3. Feed card UI with exercise consolidation per §6.6 rules
4. User-in-group profile page
5. Frontend: group home composing leaderboard + feed; profile detail
6. Feature tests for visibility scoping

**Exit:** core social loop is end-to-end functional.

### Phase 6 — Polish, CI, ship
1. Mobile UX pass on every flow (real device testing)
2. Error states, loading skeletons, empty states
3. GitHub Actions CI workflow
4. README updates: setup, seed, run, test
5. Production deploy

**Exit:** MVP shipped.

---

## 12. Acceptance Criteria

### 12.1 Auth & Onboarding
- New user can register, verify email, log in, log out.
- Unverified user cannot reach any application route; sees verification prompt.
- Verified user with zero group memberships sees Group Gate; cannot reach workout/leaderboard routes.
- User can edit profile (name, email, timezone) and delete account.

### 12.2 Groups
- Any user can create a group; becomes owner.
- Any user can join a group via invite code; becomes member.
- Owner/admin can regenerate invite code; old code stops working.
- Admin/owner can remove member; removed user loses access immediately; their historical logs remain visible to remaining members.
- Owner can promote member → admin and demote admin → member.
- Owner can soft-delete the group; members lose access; user-owned workouts persist.
- Owner cannot leave the group in MVP (must delete).

### 12.3 Exercises
- Admin/owner can create exercises in their group with name, description, and a required `measurement_type`.
- Admin/owner can edit name/description; cannot change `measurement_type` after first log references the exercise.
- Admin/owner can soft-delete an exercise; it no longer appears in pickers but historical logs still display its name.
- Members cannot create, edit, or delete exercises.
- New groups are seeded with copies of the default exercise set.

### 12.4 Points
- Admin/owner can set point values per exercise using anchor-based input; the underlying `points_per_unit` is computed correctly per measurement_type.
- Setting a point value for the first time on an exercise dispatches the backfill job, which correctly fills `workout_exercise_log_points.points_earned` for all NULL rows for that (group, exercise).
- Editing an existing point value does NOT trigger backfill or alter historical `points_earned`.
- Preset rubrics correctly populate seed exercise points at group creation.
- Points formula matches §6.5 for each measurement_type — verified by unit tests.

### 12.5 Workouts & Logs
- Workout creation requires tagging ≥1 group the user is a member of.
- A user with zero groups cannot reach the workout-create flow.
- Log form adapts inputs to the exercise's `measurement_type`.
- On log save, `workout_exercise_log_points` rows are inserted for each tagged group with correct or NULL points.
- Workout owner can edit/delete their workout and its logs.
- Non-owners (even admins of tagged groups) cannot edit/delete another user's workout/logs.

### 12.6 Leaderboard
- Group home shows leaderboard with `This Week | This Month | All Time` tabs; default `This Week`.
- Week starts Monday 00:00 in the group's timezone.
- Ranking is by sum of `points_earned` for the time window, descending.
- Each row shows rank, name, total points, workouts in window, last workout date.
- Tap row → user-in-group profile.
- Logs with NULL `points_earned` do not contribute to totals.

### 12.7 Feed
- Group home shows reverse-chronological feed of workouts tagged to the group.
- Cards consolidate exercises per the rules in §6.6 / FR-VIS-3.
- Truncation at 3 exercises with `+ N more` inline-expand.
- Feed paginates 20 per page with load-more.
- Tap card → workout detail.

### 12.8 Test suite & CI
- All controller actions have feature tests covering unauth + auth + authz + validation.
- All policies have allow + deny tests.
- `PointsCalculationService` has dedicated unit tests covering each measurement_type, anchor derivation, snapshot, and backfill.
- CI green on every PR; merge blocked on failure.

---

## 13. Open Questions / Decisions Deferred to Implementation

These are details that don't need PRD-level decisions but will surface during build:

- Exact visual design / color palette / typography (will draw from existing Breeze + MUI starting point, refined during Phase 6).
- Avatar source: in MVP, initials in colored circles. Image upload deferred.
- Group invite code format: target 8–12 chars, alphanumeric, prefixed with `BELL-` for recognizability. Final character set & length TBD during Phase 2.
- Whether to persist "default group" per user vs. always use last-viewed. Lean: last-viewed via `localStorage`.
- Exact preset rubric values (Strength / Endurance / Balanced) — calibrated during Phase 3 with a few test workouts.

---

## 14. Future Considerations (post-MVP)

In rough order of likely demand:

1. **Reactions & comments** on workouts (Tier 3 social interactions)
2. **Notifications** (email digest, in-app banners)
3. **Group ownership transfer** (so owner can leave)
4. **Streaks & badges** (retention)
5. **PWA** (offline log queue + install)
6. **Custom date ranges & "seasons"** (e.g., 12-week challenge)
7. **Workout templates** (save a typical workout, repeat)
8. **Per-invite tokens** with revocation & email targeting
9. **Tiered/threshold scoring formulas**
10. **Native mobile apps** (React Native if web-stack continuity is valued)
