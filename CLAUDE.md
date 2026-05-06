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

### Grade progression system (`AlumnoGrado`)
Belt grade advancement is enforced through a structured progression system:

**Models:**
- `AlumnoGrado` — records each grade a student is assigned. `status='0'`/`null` = in progress, `status='1'` = completed. Has `isCompletado()` helper.
- `AlumnoGradoRepaso` — each practice session (punta). `aprobado=1` counts toward the required total.
- `AlumnoGradoExamen` — each final exam attempt. `aprobado=1` marks the grade as completed.

**Business rules (enforced server-side in `AlumnoGradoController`):**
1. A student cannot register a new grade while one is in progress (`status='0'`).
2. To enable the final exam, the student must accumulate approved repasos equal to `Grado.puntas`.
3. Once the puntas quota is met, no more repasos can be added — the exam must be taken next.
4. The exam can be retaken any number of times if failed (aplazado).
5. The grade is marked completed (`status='1'`) only when the exam is approved.
6. The same grade cannot be registered twice for the same student (regardless of soft-deletes).
7. Already-completed grades are excluded from the grade select dropdown.
8. The start date of a new grade cannot be earlier than the approved final exam date of the previous grade.

**`calcularProgreso(AlumnoGrado)`** — static method on `AlumnoGradoController`. Returns an array with:
`puntasRequeridas`, `puntasObtenidas`, `diasRequeridos`, `diasTranscurridos`, `cumplePuntas`, `cumpleDias`, `puedeExamen`, `examenAprobado`, `isComplete`. Used by both the controller actions and `AlumnoController::gradoList()`.

**Critical implementation detail:** `storeExamen()` sets `status='1'` when the exam is approved. This means when `storeGrado()` runs afterward, the query `where(status=null or '0')` returns null. The date validation against the previous grade's exam date uses an **independent query** on `AlumnoGrado` (status='1') + `AlumnoGradoExamen`, not on `$activeGrado`. The same pattern applies to `$minFechaGrado` in `gradoList()`.

**Initial grade on alumno registration:** When an alumno is first registered via the browse modal, the initial `AlumnoGrado` is created with `status='0'` (in progress) so the student must go through the full progression system.

**Routes (`AlumnoGradoController`):**
```
POST   admin/alumnos/grado/store               alumno.grado.store
POST   admin/alumnos/grado/repaso/store        alumno.grado.repaso.store
DELETE admin/alumnos/grado/repaso/{id}/delete  alumno.grado.repaso.destroy
POST   admin/alumnos/grado/examen/store        alumno.grado.examen.store
DELETE admin/alumnos/grado/examen/{id}/delete  alumno.grado.examen.destroy
```

**AJAX state communication:** `alumnos/grados/list.blade.php` (the loaded partial) communicates state back to `read.blade.php` via hidden inputs:
- `#puede-agregar-grado` — `'1'` if the "Nuevo Grado" button should be visible
- `#min-fecha-grado` — ISO date string for the minimum allowed start date of the next grade
- `#active-grado-id` — ID of the currently active grade

**Select2 in modals:** Grade selects inside Bootstrap modals must be initialized with `dropdownParent: $(modal)` to prevent the dropdown from appearing behind the modal overlay. See `initGradoSelect()` in `browse.blade.php` and the `shown.bs.modal` handler in `read.blade.php`.

### Public-only routes (no auth)
- `/kumite-temporizador` — fight timer board
- `/tablero-kata` — kata scoreboard

### Consulta Inter-Dojo (`ConsultaController`)
Read-only cross-branch lookup. Allows users to search and view students from **other** dojos without being able to edit anything.

**Controller:** `app/Http/Controllers/ConsultaController.php`

**Views:**
- `resources/views/consulta/browse.blade.php` — dojo selector + AJAX student search + results table
- `resources/views/consulta/show.blade.php` — read-only student detail (personal info, active grade progress, completed grades history, health conditions)

**Routes:**
```
GET  admin/consulta                    consulta.index
GET  admin/consulta/search             consulta.search   (AJAX JSON)
GET  admin/consulta/alumno/{id}        consulta.show
```

**Permission:** `browse_consulta`

**Security rules (enforced in every method):**
- Branch user (`dojo_id` set): can query any dojo **except** their own (`where('dojo_id', '!=', $userDojoId)`)
- Global admin (`dojo_id` null): can query all dojos
- `searchAlumnos()` returns 403 if the requested `dojo_id` matches the user's own dojo
- `show()` applies `when($userDojoId, fn($q) => $q->where('dojo_id', '!=', $userDojoId))` so a branch user cannot fetch their own alumno through this endpoint

**Data shown (read-only, no action buttons):**
- Personal info (name, CI, gender, birth date, phone, email, address)
- Active grade: name, start date, puntas progress bar, exam status
- Completed grades history: table with grade name, start date, puntas, exam approval date
- Health conditions: condition name + medication

**What is NOT shown:** tutors, attendance, schedule assignments — those are operational and belong to the owning dojo only.

## Key constraints from `.codex`

- `Person` uses only `first_name` — old fields (`middle_name`, `paternal_surname`, `maternal_surname`, `last_name`) are removed and must not be reintroduced.
- A person can only be registered as an alumno once across the entire system (unique `person_id` in `alumnos`, checked including soft-deleted records).
- Alumno status changes happen directly on `Alumno.status` — no dependency on grade history.
- Multi-branch isolation is partial: currently applied to `people`, `users`, `alumnos`. Other modules (payments, reports, torneos) are not yet scoped by dojo.
- `AlumnoGrado.status`: `'0'`/`null` = in progress, `'1'` = completed. Never skip the progression system by setting `status='1'` directly on creation (except when explicitly bypassing for admin purposes).
