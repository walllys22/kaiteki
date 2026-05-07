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
2. `UsersTableSeeder` — creates `admin@soluciondigital.dev` (global, no person/dojo)
3. `CiudadsTableSeeder`
4. `DataTypesTableSeeder`
5. `DataRowsTableSeeder`
6. `MenuItemsTableSeeder`
7. `GradosTableSeeder`
8. `ParentescosTableSeeder`
9. `DojosTableSeeder`
10. `PeopleTableSeeder`

`DojoUsersTableSeeder` is separate — creates an operational user linked to a person + dojo. Run manually after the above if needed.

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

Custom controllers **override** Voyager's BREAD by registering named routes that match Voyager's expected route names (e.g. `voyager.alumnos.index`). The app-level controllers take priority over Voyager's generic BREAD for those entities. Custom routes are registered **after** `Voyager::routes()` so they win the name conflict.

Voyager controllers are extended/overridden in [app/Http/Controllers/Voyager/](app/Http/Controllers/Voyager/).

### Multi-dojo (multi-branch) isolation
`dojo_id` is the core isolation key. The rule is consistent across all controllers:

```php
$userDojoId = auth()->user()->dojo_id;
// If set → scope all queries to that dojo (user cannot see other branches)
// If null → user is global/admin and sees everything
```

This pattern is applied in `PersonController`, `AlumnoController`, `UserController`, `AsistenciaController`, `HorarioController`, `AjaxController`, and must be applied to any new module that should respect branch isolation.

- `users.dojo_id` — which branch a user belongs to
- `people.dojo_id` — which branch registered the person
- `registerUser_id` — audit only, does not determine ownership

### User types
| Type | `person_id` | `dojo_id` | Example |
|------|------------|-----------|---------|
| Global/Admin | `null` | `null` | `admin@soluciondigital.dev` |
| Branch operator | set | set (from person) | operational users |

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
- `resources/views/consulta/` — Inter-dojo read-only lookup
- `resources/views/torneos/` — Tournaments
- `resources/views/horarios/` — Schedules
- `resources/views/partials/` — Reusable modals
- `resources/views/vendor/voyager/` — Overridden Voyager templates
- `resources/views/layouts-print/` — Print templates (letter, legal, horizontal)

### Student detail (read)
The alumno detail page (`alumnos/read.blade.php`) is the hub for managing: tutors (`AlumnoTutor`), health conditions (`AlumnoEnfermedad`), and belt grades (`AlumnoGrado`). Editing alumnos from the list is intentionally disabled — only creation and status toggle are allowed from the list.

---

## Grade progression system (`AlumnoGrado`)

Belt grade advancement is enforced through a structured progression system.

### Models
- `AlumnoGrado` — records each grade a student is assigned. `status='0'`/`null` = in progress, `status='1'` = completed. Has `isCompletado()` helper.
- `AlumnoGradoRepaso` — each practice session (punta). `aprobado=1` counts toward the required total. Stores `arancel_id`, `monto`, `monto_pagado`.
- `AlumnoGradoExamen` — each final exam attempt. `aprobado=1` marks the grade as completed.
- `Arancele` — configurable price per `grado_id`, `dojo_id`, and `tipo` (`Repaso` / `Examen`).

### Business rules (all enforced server-side in `AlumnoGradoController`)
1. A student cannot register a new grade while one is in progress (`status='0'`).
2. To enable the final exam, the student must accumulate approved repasos equal to `Grado.puntas`.
3. Once the puntas quota is met, no more repasos can be added — the exam must be taken next.
4. The exam can be retaken any number of times if failed (aplazado).
5. The grade is marked completed (`status='1'`) only when the exam is approved.
6. The same grade cannot be registered twice for the same student (regardless of soft-deletes).
7. Already-completed grades are excluded from the grade select dropdown.
8. The start date of a new grade cannot be earlier than the approved final exam date of the previous grade.
9. To add a repaso, an active `Arancele` of type `Repaso` must exist for the same `grado_id` and `dojo_id`.
10. The repaso price defaults from the arancel but can be adjusted at registration time.
11. Each repaso records its own `monto` (historical — unaffected by future arancel changes).
12. The user selects payment state: `Pagado` → `monto_pagado = monto`; `Pendiente` → `monto_pagado = 0`.
13. The printed comprobante is only available when the repaso is fully paid.

### Date validation rules for repasos and examenes
All validated server-side in `storeRepaso()` and `storeExamen()`. Also enforced client-side via `min` attribute on date inputs in the modal.

**For a new repaso**, the date must be strictly greater than:
- The grade start date (`AlumnoGrado.fecha`)
- The last registered repaso date (if any)
- The last registered examen date (if any)

**For a new examen**, the date must be strictly greater than:
- The grade start date (`AlumnoGrado.fecha`)
- The last registered repaso date (if any)
- The previous examen date (if any)

The view (`alumnos/grados/list.blade.php`) computes `$minFechaRepaso` and `$minFechaExamen` server-side as `max(all candidates) + 1 day` and sets them as `min` on the date inputs. The default value pre-selected is `max($minFecha, today)`. Hint text below each input shows which record is the blocking reference.

### `calcularProgreso(AlumnoGrado)` — static method
Returns an array with: `puntasRequeridas`, `puntasObtenidas`, `diasRequeridos`, `diasTranscurridos`, `cumplePuntas`, `cumpleDias`, `puedeExamen`, `examenAprobado`, `isComplete`.

- `diasTranscurridos` counts real `AsistenciaAlumno` records with `estado='asistencia'` from the grade start date onward (not calendar days). This is informational/reference only — it does NOT gate the exam.
- Used by both the controller actions and `AlumnoController::gradoList()`.

### Critical implementation detail
`storeExamen()` sets `status='1'` when the exam is approved. This means when `storeGrado()` runs afterward, the query `where(status=null or '0')` returns null. The date validation against the previous grade's exam date uses an **independent query** on `AlumnoGrado` (status='1') + `AlumnoGradoExamen`, not on `$activeGrado`. The same pattern applies to `$minFechaGrado` in `gradoList()`.

### Initial grade on alumno registration
When an alumno is first registered via the browse modal, the initial `AlumnoGrado` is created with `status='0'` (in progress) so the student must go through the full progression system.

### Aranceles
Managed from the grade detail view (`resources/views/grados/read.blade.php`).
- Model: `App\Models\Arancele`, table: `aranceles`
- Fields: `grado_id`, `dojo_id`, `tipo` (`Repaso`/`Examen`), `precio`, `observacion`, `status`
- If no active `Repaso` arancel exists for the grade+dojo, the "Agregar Repaso" button is disabled and a warning is shown. The controller also blocks the save.

### Comprobante de Punta
View: `resources/views/alumnos/partials/comprobantePunta.blade.php`

A repaso is considered paid when `monto > 0` AND `monto_pagado >= monto`. Only paid repasos show a print button. The controller (`comprobanteRepaso()`) also blocks direct URL access to unpaid repasos.

The comprobante shows: dojo info, alumno info, grade info, repaso details, payment summary, and a voucher number based on `repaso.id`.

### AJAX state communication
`alumnos/grados/list.blade.php` (loaded via AJAX) communicates state back to `read.blade.php` via hidden inputs:
- `#puede-agregar-grado` — `'1'` if the "Nuevo Grado" button should be visible
- `#min-fecha-grado` — ISO date string for the minimum allowed start date of the next grade
- `#active-grado-id` — ID of the currently active grade

### Routes (`AlumnoGradoController`)
```
POST   admin/alumnos/grado/store                       alumno.grado.store
POST   admin/alumnos/grado/repaso/store                alumno.grado.repaso.store
GET    admin/alumnos/grado/repaso/{id}/comprobante     alumno.grado.repaso.comprobante
DELETE admin/alumnos/grado/repaso/{id}/delete          alumno.grado.repaso.destroy
POST   admin/alumnos/grado/examen/store                alumno.grado.examen.store
DELETE admin/alumnos/grado/examen/{id}/delete          alumno.grado.examen.destroy
```

### Select2 in modals
Grade selects inside Bootstrap modals must be initialized with `dropdownParent: $(modal)` to prevent the dropdown from appearing behind the modal overlay. See `initGradoSelect()` in `browse.blade.php` and the `shown.bs.modal` handler in `read.blade.php`.

---

## Asistencias (`AsistenciaController`)

Custom BREAD override. Branch users always see/create attendance for their own dojo. Global admin can select any dojo.

**Routes:**
```
GET    admin/asistencias                    voyager.asistencias.index
GET    admin/asistencias/ajax/list          (list partial)
GET    admin/asistencias/create             voyager.asistencias.create
GET    admin/asistencias/alumnos            asistencias.load_alumnos  (AJAX: load students for a schedule+date+dojo)
POST   admin/asistencias/store              voyager.asistencias.store
GET    admin/asistencias/{id}               voyager.asistencias.show
PUT    admin/asistencias/{id}/update        asistencias.update  (edit estado per student, no AJAX)
DELETE admin/asistencias/{id}/delete        voyager.asistencias.destroy
```

**Edit estado:** the read view shows radio buttons (asistencia / licencia / falta) per student with a "Guardar cambios" button (PUT form, no AJAX). Users without `edit_asistencias` permission see static colored labels only.

---

## Horarios (`HorarioController`)

Custom BREAD override for create and edit. List and show also use custom controllers.

**Create/Edit rules:**
- Branch user: dojo is auto-assigned (readonly field), status always saved as `1` on create.
- Global admin: select2 to choose the dojo.
- `tipo` is a select with fixed options: `Mañana`, `Tarde`, `Noche`.
- Status toggle only shown on edit (not on create — always defaults to active).

**View:** `resources/views/horarios/edit-add.blade.php` handles both create and edit modes via `$editing = isset($horario)`.

**Routes:**
```
GET    admin/horarios                       voyager.horarios.index
GET    admin/horarios/ajax/list             (list partial)
GET    admin/horarios/create                voyager.horarios.create
POST   admin/horarios/store                 voyager.horarios.store
GET    admin/horarios/{id}                  voyager.horarios.show
GET    admin/horarios/{id}/edit             voyager.horarios.edit
PUT    admin/horarios/{id}/update           voyager.horarios.update
POST   admin/horarios/responsables/store    horarios.responsables.store
```

---

## Consulta Inter-Dojo (`ConsultaController`)

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

**Menu:** item ID 51 in `menu_items`, under "Administración" (parent_id=40). Registered in `MenuItemsTableSeeder`.

---

## Public-only routes (no auth)
- `/kumite-temporizador` — fight timer board
- `/tablero-kata` — kata scoreboard

---

## Key constraints

- `Person` uses only `first_name` — old fields (`middle_name`, `paternal_surname`, `maternal_surname`, `last_name`) are removed and must not be reintroduced.
- A person can only be registered as an alumno once across the entire system (unique `person_id` in `alumnos`, checked including soft-deleted records).
- Alumno status changes happen directly on `Alumno.status` — no dependency on grade history.
- `AlumnoGrado.status`: `'0'`/`null` = in progress, `'1'` = completed. Never skip the progression system by setting `status='1'` directly on creation.
- `registerUser_id` is audit-only — it does not determine dojo ownership.
- `dojo_id` on users comes from the associated person (`users.dojo_id = people.dojo_id`). The UI does not allow selecting dojo directly on users — it auto-fills from the selected person.
- Repasos cannot have their date set earlier than the grade start date, the last repaso, or the last examen — validated both server-side and via `min` attribute in the view.
- The `Arancele` model uses the name `Arancele` (not `Arancel`) — this matches the migration and must not be renamed.
- Multi-branch isolation is applied to: `people`, `users`, `alumnos`, `horarios`, `asistencias`, `consulta`. Other modules (payments, reports, torneos) are not yet scoped by dojo.
