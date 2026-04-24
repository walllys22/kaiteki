# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**Kaiteki** is a karate dojo management system built on Laravel 10 + Voyager admin panel. It manages students (alumnos), people (people), schedules (horarios), tournaments (torneos), belt grades (grados), and per-branch (dojo/sucursal) data isolation.

## Commands

### First-time setup
```bash
composer install
cp .env.example .env
php artisan example:install   # generates key, runs migrate:fresh + db:seed + storage:link
sudo chmod -R 775 storage bootstrap/cache
```

### Daily development
```bash
php artisan serve             # start dev server
npm run dev                   # start Vite (frontend assets)
```

### Database
```bash
php artisan migrate:fresh --seed   # full reset
php artisan optimize:clear         # clear all caches
php artisan view:clear
```

### Seeder order (must follow dependency order)
1. `VoyagerDatabaseSeeder`
2. `UsersTableSeeder` — creates `admin@admin.com` (global, no person/dojo)
3. `CiudadsTableSeeder`
4. `DataRowsTableSeeder`
5. `DataTypesTableSeeder`
6. `MenuItemsTableSeeder`
7. `GradosTableSeeder`
8. `ParentescosTableSeeder`

`DojoUsersTableSeeder` is separate and creates `ignacio@admin.com` (operational user linked to person + dojo). Run manually after the above if needed.

### Tests
```bash
php artisan test
php artisan test --filter=TestClassName
./vendor/bin/phpunit tests/Unit/ExampleTest.php
```

### Code style
```bash
./vendor/bin/pint     # Laravel Pint (PSR-12 formatter)
```

## Architecture

### Voyager Integration
All routes live under `/admin` and pass through two custom middleware:
- `loggin` — logs every HTTP request (method, status, user, device, input) to a `requests` log channel
- `system` — redirects non-admin users to a 503 page when `system.development` setting is enabled

Custom controllers **override** Voyager's BREAD by registering named routes that match Voyager's expected route names (e.g. `voyager.alumnos.index`). The app-level controllers take priority over Voyager's generic BREAD for those entities.

Voyager controllers are extended/overridden in [app/Http/Controllers/Voyager/](app/Http/Controllers/Voyager/).

### Multi-dojo (multi-branch) isolation
`dojo_id` is the core isolation key. The rule is consistent across all controllers:

```php
$userDojoId = auth()->user()->dojo_id;
// If set → scope all queries to that dojo (user cannot see other branches)
// If null → user is global/admin and sees everything
```

This pattern is applied in `PersonController`, `AlumnoController`, `AjaxController`, and must be applied to any new module that should respect branch isolation.

- `users.dojo_id` — which branch a user belongs to
- `people.dojo_id` — which branch registered the person
- `registerUser_id` — audit only, does not determine ownership

### User types
| Type | `person_id` | `dojo_id` | Example |
|------|------------|-----------|---------|
| Global/Admin | `null` | `null` | `admin@admin.com` |
| Branch operator | set | set (from person) | `ignacio@admin.com` |

When a branch user creates a Person or Alumno, `dojo_id` is taken from `auth()->user()->dojo_id` server-side — the UI cannot override it.

### Audit trail — `RegistersUserEvents` trait
All core models (`Person`, `User`, `Alumno`, etc.) use this trait. It automatically fills on Eloquent events:
- `creating` → sets `registerUser_id`, `registerRole`
- `deleting` → sets `deleteUser_id`, `deleteRole`, `deleteObservation` (reads from request input)

### Authorization
Controllers call `$this->custom_authorize('browse_alumnos')` (defined in [app/Http/Controllers/Controller.php](app/Http/Controllers/Controller.php)), which calls `Auth::user()->hasPermission($permission)` — Voyager's permission system.

### AJAX / list pattern
Most list views are loaded via AJAX. The controller has two methods:
- `index()` → returns the shell view (browse)
- `list()` → returns the paginated partial view (list), called via `GET /admin/{resource}/ajax/list`

### Person quick-register modal
`AjaxController@personStore` and `AjaxController@personList` provide AJAX endpoints used in the shared modal (`resources/views/partials/modal-registerPerson.blade.php`) to create/select people from any form without navigating away.

### View structure
- `resources/views/administrations/people/` — People module
- `resources/views/alumnos/` — Students module (browse, read, tutores/, grados/, enfermedades/)
- `resources/views/torneos/` — Tournaments
- `resources/views/horarios/` — Schedules
- `resources/views/partials/` — Reusable modals
- `resources/views/vendor/voyager/` — Overridden Voyager templates
- `resources/views/layouts-print/` — Print templates (letter, legal, horizontal)

### Student detail (read)
The alumno detail page (`alumnos/read.blade.php`) is the hub for managing: tutors (`AlumnoTutor`), health conditions (`AlumnoEnfermedad`), and belt grades (`AlumnoGrado`). Editing alumnos from the list is intentionally disabled — only creation and status toggle are allowed from the list.

### Public-only routes (no auth)
- `/kumite-temporizador` — fight timer board
- `/tablero-kata` — kata scoreboard

## Key constraints from `.codex`

- `Person` uses only `first_name` — old fields (`middle_name`, `paternal_surname`, `maternal_surname`, `last_name`) are removed and must not be reintroduced.
- A person can only be registered as an alumno once across the entire system (unique `person_id` in `alumnos`, checked including soft-deleted records).
- Alumno status changes happen directly on `Alumno.status` — no dependency on grade history.
- Multi-branch isolation is partial: currently applied to `people`, `users`, `alumnos`. Other modules (payments, reports, torneos) are not yet scoped by dojo.
