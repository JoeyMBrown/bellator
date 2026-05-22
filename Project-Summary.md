# Bellator — Project Summary

## Overview

**Bellator** is a full-stack fitness tracking web application built with Laravel, Inertia.js, React, and TypeScript. It allows authenticated users to create workouts, attach exercises to those workouts, and log individual exercise performances (reps and measurable metrics like weight, distance, etc.) against a library of predefined exercises and metric units.

The codebase is in active development. Core CRUD flows for workouts, workout exercises, and exercise logs exist, while supporting features such as a points/scoring system, user-scoped authorization policies, and richer reporting are scaffolded but not yet implemented.

---

## Tech Stack

### Backend
- **PHP** ^8.2
- **Laravel Framework** ^11.9
- **Laravel Sanctum** ^4.0 (API token support)
- **Inertia.js (Laravel adapter)** ^1.0
- **Tightenco Ziggy** ^2.0 (route helpers exposed to the frontend)
- **Laravel Breeze** (dev dependency — auth scaffolding)
- **Laravel Sail**, **Pint**, **Tinker** (dev tooling)

### Frontend
- **React** ^18.2 with **TypeScript** ^5.0
- **Inertia.js React adapter** ^1.0
- **Vite** ^5 (build) with the React + Laravel plugins
- **Tailwind CSS** ^3.2 (+ Forms plugin, PostCSS, Autoprefixer)
- **Material-UI (MUI)** ^6.1 and **MUI X Date Pickers** ^7.22
- **Headless UI** for React ^2.0
- **Emotion** ^11.13 (CSS-in-JS, used by MUI)
- **Axios** ^1.7, **Day.js** ^1.11

### Testing & Quality
- **PHPUnit** ^11
- **Mockery** ^1.6
- **Faker** ^1.23
- **Laravel Pint** for PHP formatting

### Infrastructure
- **Docker Compose** environment provided (`docker-compose.yml`)

---

## Domain Model

The application revolves around **workouts**, which are sessions composed of one or more **exercises**, each of which can have one or more **logs** capturing actual performance.

| Model | Purpose |
|---|---|
| `User` | Authenticated user; UUID primary key. Owns many `Workout`s. |
| `Workout` | A workout session belonging to a user, with a date and optional `WorkoutType`. |
| `WorkoutType` | A user-definable category of workout (e.g. strength, cardio). |
| `Exercise` | A library exercise (e.g. Barbell Bench Press, Run) associated with an `ExerciseType`. |
| `ExerciseType` | Category for exercises. |
| `WorkoutExercise` | Pivot between a `Workout` and an `Exercise`; has many logs. |
| `WorkoutExerciseLog` | A single performance record (repetitions + metric value + unit) against a `WorkoutExercise`. |
| `MetricUnit` | Unit of measurement for an exercise metric (Lbs, Kg, Miles, Yards). |
| `ExercisePointHistory` | Time-bound point values per exercise/metric/unit; intended to drive a scoring system (not yet wired up). |

All domain models use **soft deletes**. `User` uses **UUIDs**; other tables use auto-increment IDs.

### Key relationships
- `User hasMany Workout`
- `Workout belongsTo WorkoutType`, `belongsToMany Exercise` (through `workout_exercises`)
- `WorkoutExercise hasMany WorkoutExerciseLog`
- `WorkoutExerciseLog belongsTo MetricUnit`
- `Exercise belongsTo ExerciseType`

---

## Database

Migrations under `database/migrations/`:

- `users`, `password_reset_tokens`, `sessions` — standard Laravel auth tables (UUID users)
- `cache`, `jobs` — Laravel standard
- `workout_types` — name (60), description (140, nullable), `created_by_user_id`
- `workouts` — `workout_date`, `workout_type_id` (nullable FK), `user_id` (FK)
- `exercise_types` — name (60), description (140, nullable), `created_by_user_id`
- `exercises` — name (60), description (140, nullable)
- `metric_units` — name (60), description (140)
- `workout_exercises` — pivot (`workout_id`, `exercise_id`)
- `workout_exercise_logs` — `repitions` (unsigned int), `exercise_metric` (decimal 16,2), FKs to `workout_exercises` and `metric_units`
- `exercise_points_history` — `exercise_points` (decimal 8,2), `exercise_metric`, `metric_unit_id`, `exercise_id`, `start_date`, `end_date` (nullable)

### Seeders
- `DatabaseSeeder` — orchestrates seeding
- `ExercisesSeeder` — seeds 5 default exercises (Barbell Bench Press, Barbell Squat, Run, Sprint, Swim)
- `MetricUnitsSeeder` — seeds 4 units (Lbs, Kg, Miles, Yards)

### Factories
- `UserFactory` — fake users with password `password` and configurable email-verified state

---

## Routes

### Application routes (`routes/web.php`) — all behind `auth, verified`

| Method | Path | Name | Action |
|---|---|---|---|
| GET | `/` | — | Renders `Welcome` page (public) |
| GET | `/dashboard` | `dashboard` | Renders `Dashboard/Dashboard` |
| GET | `/workouts` | `workout.index` | `WorkoutController@index` |
| GET | `/workout/create` | `workout.create` | `WorkoutController@create` |
| POST | `/workout` | `workout.store` | `WorkoutController@store` |
| GET | `/workout/{id}` | `workout.show` | `WorkoutController@show` |
| POST | `/workout/{id}/exercise` | `workout.exercise.store` | `WorkoutExerciseController@store` |
| GET | `/workout/{workout_id}/exercise/{exercise_id}` | `workout.exercise.show` | `WorkoutExerciseController@show` |
| POST | `/workout/{workout_id}/exercise/{exercise_id}/log` | `workout.exercise.log.store` | `WorkoutExerciseLogController@store` |
| GET / PATCH / DELETE | `/profile` | `profile.*` | `ProfileController` |

### Auth routes (`routes/auth.php`)
Standard Breeze suite: register, login, logout, forgot/reset password, email verification, password confirmation, password update.

---

## Application Code

### Controllers (`app/Http/Controllers/`)

- **`WorkoutController`** — `index`, `create`, `store`, `show`. Lists the authenticated user's workouts; on `show`, eager-loads exercises and provides exercise options for the "add exercise" modal via `ExerciseService`.
- **`WorkoutExerciseController`** — `store` (attach an exercise to a workout) and `show` (display a workout's exercise with its logs); pulls metric-unit options via `MetricUnitService`.
- **`WorkoutExerciseLogController`** — `store` a single performance log entry against a `WorkoutExercise`.
- **`ProfileController`** — Breeze-standard `edit`, `update`, `destroy`.
- **`Auth/*`** — Breeze-standard authentication controllers (login, registration, password reset, email verification, password confirmation).

### Services (`app/Services/`)

- **`ExerciseService::toOptionsArray()`** — returns all exercises as `{id, name}` for select dropdowns.
- **`MetricUnitService::toOptionsArray()`** — returns all metric units as `{id, name}` for select dropdowns.

### Form Requests (`app/Http/Requests/`)

- `StoreWorkoutRequest` — validates `workout_date` and optional `workout_type_id`.
- `StoreWorkoutExerciseRequest` — validates exercise `id`.
- `StoreWorkoutExerciseLogRequest` — validates `repitions`, `exercise_metric`, `workout_exercise_id`, `metric_unit_id`.
- `Auth/ProfileUpdateRequest`, `Auth/LoginRequest` — Breeze-standard.

### Middleware
- **`HandleInertiaRequests`** — sets the Inertia root view to `app` and shares the authenticated `user` via `auth.user` to every page.

---

## Frontend (`resources/js/`)

### Pages (`resources/js/Pages/`)

- **`Welcome.tsx`** — public landing page.
- **`Dashboard/Dashboard.tsx`** — entry point for authenticated users; links to the workout list and "create workout".
- **`Workouts/`**
  - `Index.tsx` — table of the user's workouts (sorted by date DESC).
  - `Create.tsx` — workout creation form (date picker + workout type).
  - `Show.tsx` — workout detail; lists exercises and exposes a FAB to add an exercise via a modal.
  - `Exercises/Show.tsx` — workout-exercise detail; displays the exercise's log entries and the form for recording new ones.
- **`Profile/`**
  - `Edit.tsx` plus `Partials/UpdateProfileInformationForm.tsx`, `Partials/UpdatePasswordForm.tsx`, `Partials/DeleteUserForm.tsx`.
- **`Auth/`** — Breeze suite: `Login`, `Register`, `ForgotPassword`, `ResetPassword`, `VerifyEmail`, `ConfirmPassword`.

### Components (`resources/js/Components/`)

- **Shared (Breeze defaults):** `TextInput`, `InputLabel`, `InputError`, `Checkbox`, `PrimaryButton`, `SecondaryButton`, `DangerButton`, `NavLink`, `ResponsiveNavLink`, `Dropdown`, `Modal`, `ApplicationLogo`.
- **Workouts (`Components/Workouts/`):** `WorkoutList`, `WorkoutExerciseList`, `CreateForm`, `BreadcrumbNav`.
- **Exercises (`Components/Exercises/`):** `CreateExerciseForm`, `CreateExerciseModal`.
- **Workout Exercise Logging (`Components/Workouts/Exercises/`):** `ExerciseLogForm`, `ExerciseLogFormRow`, `MetricInput`, `RepitionInput`, `MetricUnitSelector`, `BreadcrumbNav`.
- **General (`Components/General/`):** `FAB` — floating action button used for adding exercises to a workout.

---

## Authentication

- Provided by **Laravel Breeze** with the Inertia + React stack.
- Session-based authentication; `User` model implements `MustVerifyEmail`, so email verification is enforced (`auth, verified` middleware on application routes).
- Full password reset and password confirmation flows are wired up via the Breeze controllers and pages.
- The `User` model uses **UUIDs** as primary keys.

---

## Current Functionality (What Works Today)

1. **Account lifecycle** — registration, login/logout, email verification, password reset, password confirmation, profile update, account deletion.
2. **Workout management** — create a workout with a date and optional workout type; view a list of the current user's workouts; view a single workout's detail page.
3. **Adding exercises to a workout** — select an exercise from the seeded library and attach it to a workout via a modal on the workout detail page.
4. **Logging exercise performance** — for a given workout-exercise, record one or more logs consisting of repetitions, a metric value, and a metric unit.
5. **Seeded reference data** — five default exercises and four default metric units are available out of the box.

---

## Known Gaps & TODOs (Surfaced From Source Comments)

These are explicitly called out in the code and represent the most likely next areas of work:

- **Authorization** — no policies yet restrict workouts/exercises/logs to their owning user. Route binding currently fetches records by raw ID with no ownership check.
- **Service-layer extraction** — controllers (`WorkoutController`, `WorkoutExerciseController`, `WorkoutExerciseLogController`) have TODOs indicating the longer queries and mutations should be moved into service classes.
- **Validation hardening** — `StoreWorkoutExerciseRequest` and `StoreWorkoutExerciseLogRequest` flag the need for stricter rules and a review of the `id` naming convention.
- **Points calculation** — `ExercisePointHistory` exists with the schema in place, but the controller TODO confirms point calculation has not been implemented at log-creation time.
- **Error handling and redirects** — `WorkoutExerciseLogController@store` has TODOs for proper error handling and post-store redirect targeting.
- **Workout date formatting** — currently hardcoded to the `America/Detroit` timezone in the `Workout` model's accessor; user-timezone support is noted as needed.
- **Model bug** — `ExercisePointHistory` defines a `belongsTo` relation pointing at itself instead of `Exercise`.
- **Naming** — `repitions` (intended `repetitions`) is misspelled in both the migration and the model fillable, and is exposed through to the frontend forms.

---

## Project Layout (Top Level)

```
app/
  Http/
    Controllers/      # Workout, WorkoutExercise, WorkoutExerciseLog, Profile, Auth/*
    Middleware/       # HandleInertiaRequests
    Requests/         # Store* requests + Auth/*
  Models/             # User, Workout, WorkoutType, Exercise, ExerciseType,
                      # WorkoutExercise, WorkoutExerciseLog, MetricUnit,
                      # ExercisePointHistory
  Services/           # ExerciseService, MetricUnitService
database/
  factories/          # UserFactory
  migrations/         # Schema for users, workouts, exercises, logs, etc.
  seeders/            # DatabaseSeeder, ExercisesSeeder, MetricUnitsSeeder
resources/
  js/
    Pages/            # Inertia React pages (Welcome, Dashboard, Workouts, Profile, Auth)
    Components/       # Shared, Workouts, Exercises, General
routes/
  web.php             # Application routes
  auth.php            # Breeze auth routes
tests/                # PHPUnit test scaffolding
docker-compose.yml    # Local Docker environment
```
