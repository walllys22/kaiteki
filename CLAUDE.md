# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Documentation rule

`CLAUDE.md` is the operational source of truth for this repository. Any change that adds or modifies behavior, routes, models, migrations, permissions, business rules, views, integrations, or deployment/runtime assumptions must update this file in the same change. Do not leave new rules only in chat, code comments, side documents, or memory.

## Project Overview

**Kaiteki** is a karate dojo management system built on Laravel 10 + Voyager admin panel. It manages students (alumnos), people (people), schedules (horarios), attendance (asistencias), monthly payments (mensualidades), belt grades (grados), exam/repaso fees (aranceles), certificates, and per-branch (dojo/sucursal) data isolation.

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

### Seeder behavior
`DatabaseSeeder` disables foreign key checks during the full seed run and enables them again in a `finally` block. This is intentional: the example data has circular references between `users`, `people`, and `dojos` (`users.person_id`, `users.dojo_id`, `people.dojo_id`, `people.registerUser_id`, and `dojos.person_id`).

Current full seed package:
1. `VoyagerDatabaseSeeder`
2. `CiudadsTableSeeder`
3. `DataTypesTableSeeder`
4. `DataRowsTableSeeder`
5. `MenuItemsTableSeeder`
6. `GradosTableSeeder`
7. `ParentescosTableSeeder`
8. `DojosTableSeeder`
9. `PeopleTableSeeder`
10. `UsersTableSeeder`
11. `SettingsTableSeeder`
12. `ArancelesTableSeeder`
13. `HorariosTableSeeder`
14. `HorarioReponsablesTableSeeder`

Do not remove seeders from this list to make `example:install` pass. The demo dataset must keep users, horarios, grados, aranceles, horario responsables, dojos, people, ciudads, and parentescos. There is no separate `DojoUsersTableSeeder`; global users and branch/operator users live in `UsersTableSeeder`.

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
Most admin routes live under `/admin` and pass through two custom middleware:
- `loggin` — logs every HTTP request (method, status, user, device, input) to a `requests` log channel
- `system` — redirects non-admin users to a 503 page when `system.development` setting is enabled

Custom controllers **override** Voyager's BREAD by registering named routes that match Voyager's expected route names (e.g. `voyager.alumnos.index`). The app-level controllers take priority over Voyager's generic BREAD for those entities. Most custom override routes are registered **after** `Voyager::routes()` so they win the name conflict. Exception: `admin/grados/reorder` is registered before `Voyager::routes()` so Voyager's `grados/{id}` route cannot capture it.

Voyager controllers are extended/overridden in [app/Http/Controllers/Voyager/](app/Http/Controllers/Voyager/).

### Multi-dojo (multi-branch) isolation
`dojo_id` is the core isolation key. The rule is consistent across all controllers:

```php
$userDojoId = auth()->user()->dojo_id;
// If set → scope all queries to that dojo (user cannot see other branches)
// If null → user is global/admin and sees everything
```

This pattern is applied in `PersonController`, `AlumnoController`, `UserController`, `AsistenciaController`, `HorarioController`, `AjaxController`, `ConsultaController`, `GradoController` arancel actions, `AlumnoMensualidadController`, and grade receipt/certificate actions. Apply it to any new module that should respect branch isolation.

- `users.dojo_id` — which branch a user belongs to
- `people.dojo_id` — which branch registered the person
- `registerUser_id` — audit only, does not determine ownership

### User types
| Type | `person_id` | `dojo_id` | Example |
|------|------------|-----------|---------|
| Global/Admin | `null` | `null` | `admin@soluciondigital.dev`, `wallys@admin.com` |
| Branch operator | set | set (from person) | `admin@gusuku.com`, `admin@ljpzabala.com` |

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

### File storage (S3 / Contabo)
`FILESYSTEM_DISK=s3` points to a Contabo S3-compatible bucket (`AWS_ENDPOINT=https://usc1.contabostorage.com`, `use_path_style_endpoint=true`, bucket `tenant:kaiteki`, `AWS_ROOT=demo`). The `s3` disk in `config/filesystems.php` sets `root => env('AWS_ROOT')` and `visibility => public`, so files written via `Storage::disk('s3')` get public-read ACL automatically. The adapter packages are `league/flysystem-aws-s3-v3` + `aws/aws-sdk-php`.

- Public read is granted bucket-wide for the whole `demo/*` prefix via a `PutBucketPolicy` (`s3:GetObject`, `Principal: *`, ARN `arn:aws:s3::<tenant>:kaiteki/demo/*`). New uploads still also rely on the per-disk `visibility=public`. The `:` in the Contabo bucket name (`<tenant>:kaiteki`) is significant — the policy ARN uses the `arn:aws:s3::<tenant>:resource` form.
- The disk is referenced everywhere as `Storage::disk(env('FILESYSTEM_DRIVER'))` (both in `StorageController::store_image()` for uploads and in all views). `FILESYSTEM_DRIVER` is an unset/legacy env key, so `env()` returns null and `Storage::disk(null)` falls back to the **default** disk (`FILESYSTEM_DISK=s3`). This is the project convention — keep new image code using `Storage::disk(env('FILESYSTEM_DRIVER'))` for consistency. (Caveat: with cached config `env()` outside config files returns null anyway, which still resolves to the default disk.)
- Uploaded images are stored as the original `.avif` plus a `-cropped.webp` variant (same key, `.avif` stripped). Person/tutor/user avatars and grade thumbnails render the cropped variant: `\Storage::disk(env('FILESYSTEM_DRIVER'))->url(str_replace('.avif','',$image).'-cropped.webp')`. Dojo logos (`Dojo.logo`) and certificate grade images (`Grado.image`) render the raw key: `\Storage::disk(env('FILESYSTEM_DRIVER'))->url($key)`. Fallback is `asset('images/default.jpg')` with an `onerror` handler on `<img>` for missing keys.
- **All entity image displays were migrated from `asset('storage/...')` to `\Storage::disk(env('FILESYSTEM_DRIVER'))->url(...)`**: `administrations/people/list.blade.php` + `read.blade.php`, `alumnos/list.blade.php` + `read.blade.php` + `tutores/list.blade.php`, `consulta/show.blade.php`, `grados/list.blade.php` + `edit-add.blade.php` + `reorder.blade.php`, `vendor/voyager/users/list.blade.php`, and the print partials `kardex`, `historial-grados`, `certificadoExamenEka`, `certificadoExamenLjp`, `comprobanteExamen`, `comprobantePunta`. The two kardex/historial print partials dropped their old local `file_exists(public_path(...))` branch (does not work against S3).
- Legacy local files in `storage/app/public/{people,dojos,grados}` were synced to S3 (keys preserved). Some DB `logo`/`image` values reference seed paths that never had a real file on disk — those just fall back to the default image. Static app assets (`asset('images/...')`, certificate templates `images/dojos/{dojo}/certificados/*.png`, Voyager UI via `Voyager::image`/`voyager_asset`) are NOT on S3 and stay local.
- `DojoMensualidadController` still writes generated receipt PDFs to `Storage::disk('public')` (local) and links them with `asset('storage/...')`; not migrated to S3.
- **Voyager edit-form image preview**: `vendor/voyager/formfields/image.blade.php` was patched to render the current image from the default disk (`\Storage::disk(env('FILESYSTEM_DRIVER'))->url(...)`) instead of `Voyager::image()` (which points at `voyager.storage.disk='public'` = local and showed nothing for S3 keys). It renders the `-cropped.webp` variant first and falls back via `onerror` to the raw `.avif` key. The only BREAD image fields are `people.image`, `dojos.logo`, `grados.image` (all `type=image`; no `multiple_images` in use).
- Save-side caveat: `people` image saves go through the custom `PersonController@update` → `StorageController::store_image()` → S3. `dojos`/`grados` images saved through Voyager's generic BREAD still write to `voyager.storage.disk='public'` (local), so a logo/grade image edited via plain BREAD lands locally while the views read from S3. If this becomes a problem, either route those saves through `StorageController` or set `voyager.storage.disk` to the S3 disk (which also affects Voyager media manager/settings).

### View structure
- `resources/views/administrations/people/` — People module
- `resources/views/alumnos/` — Students module (browse, read, tutores/, grados/, enfermedades/, horarios/, asistencias/, mensualidades/)
- `resources/views/alumnos/partials/` — Student print/receipt/certificate partials (kardex, grade history, repaso/examen receipts, certificates)
- `resources/views/consulta/` — Inter-dojo read-only lookup
- `resources/views/horarios/` — Schedules
- `resources/views/partials/` — Reusable modals
- `resources/views/vendor/voyager/` — Overridden Voyager templates
- `resources/views/layouts-print/` — Print templates (letter, legal, horizontal)

### Student detail (read)
The alumno detail page (`alumnos/read.blade.php`) is the hub for managing: tutors (`AlumnoTutor`), health conditions (`AlumnoEnfermedad`), belt grades (`AlumnoGrado`), schedules (`AlumnoHorario`), attendance history, monthly payments, kardex, and grade history. Editing alumnos from the list is intentionally disabled — only creation and status toggle are allowed from the list.

The tutors list (`alumnos/tutores/list.blade.php`) has a per-row "Ver" button (eye icon) that opens modal `#modal-ver-tutor` with the tutor person's full info (photo, document, parentesco, birth date, gender, blood type, phone, email, address, estado). Data comes from `data-*` attributes already loaded in `tutorList()` — no extra route. Modal + delegated click handler (`$(document).off().on('click', '.btn-ver-tutor')`) live inside the AJAX partial so they survive list reloads.

### Alumno progression filter (report) — `AlumnoController::list()`
The Alumnos browse list (`alumnos/browse.blade.php`) has a `#filter-estado` dropdown that passes an `estado` query param to `list()`:
- `repaso` → alumnos whose **active** grade is Kyu (`usaRepasos()`) with puntas still incomplete (approved repasos `< Grado.puntas`) — i.e. still able to take a repaso/punta.
- `examen` → alumnos whose active grade has puntas complete **or** is a Dan grade — i.e. ready for the final exam.
- empty → all alumnos (normal list).

Both branches stay dojo-scoped (`auth()->user()->dojo_id`). When `estado` is set, `list()` evaluates progression in PHP over the dojo-scoped collection (active grade = `ultimoGrado` with `status != '1'`; puntas = count of `repasos` with `aprobado=1`) and rebuilds a `LengthAwarePaginator` manually (no per-alumno asistencia query — date/arancel gating is irrelevant to the report). The list view (`alumnos/list.blade.php`) shows a puntas progress badge (`x/y puntas`, "Listo p/ examen", or "Examen directo") in the grade cell whenever the active grade's `repasos` relation is loaded.

Inactive students (`Alumno.status != 1`) are read-only for operational actions, except debt collection. Existing unpaid repaso, exam, and mensualidad debts can still be paid while the alumno is inactive. Shared controller guard: `Controller::ensureAlumnoActivo()`. It also enforces dojo scope for branch users when it receives an alumno id. Do not use this guard in payment-only actions that must allow inactive alumnos; keep explicit dojo scoping instead.

### Student auxiliary routes
```
GET  admin/alumnos/{id}/kardex             alumnos.kardex
GET  admin/alumnos/{id}/historial-grados   alumnos.historial.grados
GET  admin/alumnos/{id}/check-historial    alumnos.check_historial
POST admin/alumnos/{id}/status             alumnos.status.update
PUT  admin/alumnos/{id}/fecha-ingreso      alumnos.fecha_ingreso.update
GET  admin/alumnos/check-registration/{person_id} alumnos.check_registration
GET  admin/alumnos/imprimir/reporte        alumnos.print
```

`updateStatus()` cannot inactivate a student while they have an active monthly plan or a currently vigente mensualidad. `checkRegistration()` prevents registering a person already used as an alumno anywhere in the system and also detects when the selected person is the responsible person for the same dojo.

`updateFechaIngreso()` edits `Alumno.fechaIngreso` and is restricted to users whose role is `admin` or `administrador` (`abort(403)` otherwise). Role `administrador_dojo` is NOT allowed. The new date cannot be after today. The edit pencil next to "Fecha de Ingreso" in `alumnos/read.blade.php` (modal `#modal-edit-fecha-ingreso`) is rendered only for those roles.

---

## Grade ordering (`Grado.orden`)

The `grados` table has an `orden` column (`unsignedSmallInteger`, nullable) that defines the progression sequence following Japanese karate rules:
- Kyu grades count **down** (10th Kyu = beginner, 1st Kyu = highest Kyu)
- Dan grades count **up** (1st Dan → higher Dan = more advanced)
- `orden=1` is the first grade a beginner student receives; higher numbers are more advanced

### Scope
`Grado::scopeOrdenado()` orders by `COALESCE(orden, 99999) ASC, id ASC`. All queries that list grades for display or student selection must use this scope — never `orderBy('tipo')/orderBy('numero')`.

### Reorder UI
- Button "Ordenar" in `grados/browse.blade.php` (requires `edit_grados` permission)
- Dedicated page `grados/reorder.blade.php` — shows all grades in a drag-and-drop table (SortableJS CDN)
- On "Guardar Orden" fires AJAX POST to `grados.reorder`; assigns sequential `orden` values starting at 1

### Routes
```
GET  admin/grados/reorder   grados.reorder.index
POST admin/grados/reorder   grados.reorder
```

### Key rules
- New grades created via Voyager BREAD get `orden = null` and appear at the bottom of all lists until the user visits the reorder page and saves a new position.
- `orden` is managed exclusively through the reorder view — it is not part of the edit form.
- The seeder sets `orden` 1–10 for the 10 Kyu grades (10th Kyu → 1st Kyu).

---

## Grade progression system (`AlumnoGrado`)

Belt grade advancement is enforced through a structured progression system.

### Models
- `Grado` — belt definition. `isDan()` returns true when `tipo` is `Dan`; `usaRepasos()` returns false for Dan grades.
- `AlumnoGrado` — records each grade a student is assigned. `status='0'`/`null` = in progress, `status='1'` = completed. Has `isCompletado()` helper.
- `AlumnoGradoRepaso` — each practice session (punta). `aprobado=1` counts toward the required total. Stores `arancel_id`, `monto`, `monto_pagado`.
- `AlumnoGradoExamen` — each final exam attempt. `aprobado=1` marks the grade as completed. Stores `arancel_id`, `monto`, `monto_pagado`.
- `Arancele` — configurable price per `grado_id`, `dojo_id`, and `tipo` (`Repaso` / `Examen`).

### Business rules (all enforced server-side in `AlumnoGradoController`)
1. A student cannot register a new grade while one is in progress (`status='0'`).
2. Kyu grades require approved repasos equal to `Grado.puntas` before the final exam.
3. Dan grades do not use puntas/repasos (`Grado::usaRepasos()` returns false) and can go directly to final exam.
4. Once the puntas quota is met on a Kyu grade, no more repasos can be added — the exam must be taken next.
5. The exam can be retaken any number of times if failed (aplazado).
6. The grade is marked completed (`status='1'`) only when the exam is approved.
7. The same grade cannot be registered twice for the same student (regardless of soft-deletes).
8. Already-completed grades are excluded from the grade select dropdown.
9. The start date of a new grade cannot be earlier than the approved final exam date of the previous grade.
10. To add a repaso, an active `Arancele` of type `Repaso` must exist for the same `grado_id` and `dojo_id`.
11. To add an exam, an active `Arancele` of type `Examen` must exist for the same `grado_id` and `dojo_id`.
12. A Dan grade must not have `Repaso` aranceles; `GradoController::storeArancel()` blocks them.
13. Repaso/exam prices default from the arancel but can be adjusted at registration time.
14. Each repaso/exam records its own `monto` (historical — unaffected by future arancel changes).
15. The user selects payment state: `Pagado` → `monto_pagado = monto`; `Pendiente` → `monto_pagado = 0`.
16. Pending repaso/exam payments can be marked paid later through `pagarRepaso()` / `pagarExamen()`, even when the alumno is inactive. Direct access is blocked if the item is already fully paid.
17. Printed comprobantes are only available when the repaso or exam is fully paid.
18. When an exam is approved and there are active unused grades after the current one (or grades with `orden=null`), `next_grado_id` is required and the controller creates the next `AlumnoGrado` with the same exam date and `status='0'`.
19. The start date (`AlumnoGrado.fecha`) of an in-progress grade can be edited via `updateGradoFecha()` **only** by users with role `admin` or `administrador` (`abort(403)` otherwise; `administrador_dojo` is NOT allowed) and only while the grade has no repasos and no examenes. The new date cannot be after today (system date) and cannot be earlier than the approved final exam of the previous completed grade. The edit pencil in `alumnos/grados/list.blade.php` (modal `#modal-edit-grado-fecha`) renders only for those roles under the same conditions.

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
Returns an array with: `puntasRequeridas`, `puntasObtenidas`, `diasRequeridos`, `diasTranscurridos`, `cumplePuntas`, `cumpleDias`, `puedeExamen`, `examenAprobado`, `isComplete`, `usaRepasos`.

- `diasTranscurridos` counts real `AsistenciaAlumno` records with `estado='asistencia'` from the grade start date onward (not calendar days). This is informational/reference only — it does NOT gate the exam.
- For Dan grades, `usaRepasos=false`, `puntasRequeridas=0`, and `puedeExamen=true` as long as date/arancel rules pass.
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
- Dan grades only use `Examen` aranceles. `Repaso` aranceles are blocked for Dan grades.
- The grades AJAX list (`GradoController::list()` + `resources/views/grados/list.blade.php`) shows the aranceles for each grade. Branch users only see aranceles for their own `auth()->user()->dojo_id`; global users see aranceles for all dojos with the dojo name shown.

### Comprobante de Punta
View: `resources/views/alumnos/partials/comprobantePunta.blade.php`

A repaso is considered paid when `monto > 0` AND `monto_pagado >= monto`. Only paid repasos show a print button. The controller (`comprobanteRepaso()`) also blocks direct URL access to unpaid repasos.

The comprobante shows: dojo info, alumno info, grade info, repaso details, payment summary, and a voucher number based on `repaso.id`.

### Comprobante de Examen
View: `resources/views/alumnos/partials/comprobanteExamen.blade.php`

An exam is considered paid when `monto > 0` AND `monto_pagado >= monto`. Only paid exams show a print button. The controller (`comprobanteExamen()`) also blocks direct URL access to unpaid exams.

### Certificates
View: `resources/views/alumnos/partials/certificadoExamenLjp.blade.php`

Certificate support is currently configured only for `dojo_id = 3` (`L.J.P. Zabala Dojo`) with template `public/images/dojos/ljp/certificados/examen.png`.

- `certificadoExamen()` prints a certificate only for approved exams (`aprobado=1`) and only when the alumno belongs to dojo 3.
- `certificadoCursando()` prints a "currently studying this grade" certificate for an in-progress grade, only after the student already has at least one completed grade.
- Both certificate routes respect branch scoping through `auth()->user()->dojo_id`.
- The template places the student photo, registration number, grade text, optional grade image (`grados.image`), QR code generated by `simplesoftwareio/simple-qrcode`, and dojo responsible signature.
- When adding certificates for other dojos, add the template under `public/images/dojos/{dojo}/certificados/`, add the controller rule, and document the coordinates here.

### AJAX state communication
`alumnos/grados/list.blade.php` (loaded via AJAX) communicates state back to `read.blade.php` via hidden inputs:
- `#puede-agregar-grado` — `'1'` if the "Nuevo Grado" button should be visible
- `#min-fecha-grado` — ISO date string for the minimum allowed start date of the next grade
- `#active-grado-id` — ID of the currently active grade

### Routes (`AlumnoGradoController`)
```
POST   admin/alumnos/grado/store                       alumno.grado.store
PUT    admin/alumnos/grado/{id}/fecha                   alumno.grado.fecha.update
POST   admin/alumnos/grado/repaso/store                alumno.grado.repaso.store
GET    admin/alumnos/grado/repaso/{id}/comprobante     alumno.grado.repaso.comprobante
PUT    admin/alumnos/grado/repaso/{id}/pagar           alumno.grado.repaso.pagar
DELETE admin/alumnos/grado/repaso/{id}/delete          alumno.grado.repaso.destroy
POST   admin/alumnos/grado/examen/store                alumno.grado.examen.store
GET    admin/alumnos/grado/examen/{id}/comprobante     alumno.grado.examen.comprobante
GET    admin/alumnos/grado/examen/{id}/certificado     alumno.grado.examen.certificado
GET    admin/alumnos/grado/{id}/certificado-cursando   alumno.grado.certificado.cursando
PUT    admin/alumnos/grado/examen/{id}/pagar           alumno.grado.examen.pagar
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

## Alumno schedules (`AlumnoHorarioController`)

Student schedule assignment is managed from the alumno detail page (`resources/views/alumnos/horarios/list.blade.php`).

**Rules:**
- Mutating actions require an active alumno via `ensureAlumnoActivo()`.
- A student cannot have the same active schedule twice.
- Assigning a new schedule sets all previous active `AlumnoHorario` rows for that student to `status='0'`, then creates a new active row.
- Deleting is allowed only for an active assignment (`status='1'`).
- Available schedules are active schedules from the user's dojo for branch users; global users can see all active schedules.

**Routes:**
```
GET    admin/alumnos/{id}/horarios/list     alumno.horario.list
POST   admin/alumnos/horario/store          alumno.horario.store
DELETE admin/alumnos/horario/{id}/delete    alumno.horario.destroy
```

---

## Mensualidades (`AlumnoMensualidadController`)

Monthly payments live inside the alumno detail page (`resources/views/alumnos/mensualidades/list.blade.php`) and are scoped by `dojo_id`.

### Models
- `AlumnoMensualidadPlan` — payment generation configuration for a student. Fields include `monto_mensual`, `descuento`, `fecha_inicio`, `fecha_fin`, `tipo_generacion` (`automatica` / `fecha_fin`), `status`.
- `AlumnoMensualidad` — generated month/period. Fields include `periodo`, `fecha_fin`, `monto`, `descuento`, `monto_pagado`, `status`.
- `AlumnoMensualidadPago` — individual payment applied to a generated mensualidad.

### Generation rules
- `list()` generates missing mensualidades automatically only when there is an active plan and the alumno is active.
- `tipo_generacion='automatica'` generates from `fecha_inicio` through today, month by month.
- `tipo_generacion='fecha_fin'` generates only through `fecha_fin`, then the plan is marked inactive (`status=0`).
- Month boundaries use Carbon `addMonthNoOverflow()`.
- Partial final periods are prorated by days and stored in `monto`, `descuento`, and `fecha_fin`.
- `fecha_inicio` cannot be in the future. The first plan cannot start before `Alumno.fechaIngreso`.
- Only one active plan is allowed per alumno. A current plan must be paused/finalized before another one is configured.
- A new plan cannot start while the latest generated mensualidad is still vigente or while a programmed cutoff is still in the future.
- If a same `alumno_id + periodo` row exists in soft-deleted state, generation restores it instead of creating a duplicate.

### Payment rules
- Payments require an authenticated user, except the public signed receipt route.
- Monthly debt payments are allowed for inactive alumnos. Do not call `ensureAlumnoActivo()` from `pagar()`.
- A payment cannot be registered on `status='anulado'`, on a mensualidad with no saldo, or with `monto` greater than the current saldo.
- Older pending mensualidades must be paid first.
- `pagar()` uses `lockForUpdate()` before creating `AlumnoMensualidadPago` and updating `monto_pagado`.
- `resolverStatus()` returns: `anulado`, `exonerado`, `pagado`, `parcial`, or `pendiente`.

### Pause / activate / delete
- `pausarPlan()` supports `tipo_corte='mes_completo'` or `tipo_corte='fecha'`.
- Plans generated with `fecha_fin` can only be finalized by specific date, not by full month.
- Date cuts must fall inside the latest generated period and recalculate that period proportionally.
- `activarPlan()` only reactivates paused automatic plans, and only if the alumno has no other active plan.
- `destroy()` can delete only the most recent generated mensualidad and only when it has no payments.
- `beca` and `mora` columns were removed; do not reintroduce them. Use `descuento` and payment status instead.

### Receipts
View: `resources/views/alumnos/mensualidades/comprobantePago.blade.php`

- Authenticated route: `alumno.mensualidades.pago.comprobante`.
- Public route: `/comprobantes/mensualidades/pagos/{id}` with `signed` middleware and route name `alumno.mensualidades.pago.comprobante.public`.
- Receipt output includes dojo info, alumno info, payment amount, period range, collector, and a QR generated client-side with `qrcodejs`.

### Routes
```
GET    admin/alumnos/{id}/mensualidades/list              alumno.mensualidades.list
POST   admin/alumnos/mensualidades/plan/store             alumno.mensualidades.plan.store
PUT    admin/alumnos/mensualidades/plan/{id}/pausar       alumno.mensualidades.plan.pausar
PUT    admin/alumnos/mensualidades/plan/{id}/activar      alumno.mensualidades.plan.activar
PUT    admin/alumnos/mensualidades/{id}/pagar             alumno.mensualidades.pagar
DELETE admin/alumnos/mensualidades/{id}/delete            alumno.mensualidades.destroy
GET    admin/alumnos/mensualidades/pagos/{id}/comprobante alumno.mensualidades.pago.comprobante
POST   admin/alumnos/mensualidades/pagos/{id}/whatsapp   alumno.mensualidades.pago.whatsapp
GET    /comprobantes/mensualidades/pagos/{id}             alumno.mensualidades.pago.comprobante.public (signed)
```

---

## Dashboard (`resources/views/vendor/voyager/index.blade.php`)

The Voyager dashboard is customized for financial and operational summary by dojo.

- Branch users are fixed to `auth()->user()->dojo_id`.
- Global users choose a dojo with `?dojo_id=`.
- It summarizes repaso fees, exam fees, mensualidades, pending debts, current-month totals, and latest monthly payments.
- Dashboard queries are view-local and should stay dojo-scoped when new financial sources are added.

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

**Menu:** item ID 52 in `menu_items`, top-level "Consulta" (`parent_id=null`, `order=3`). Registered in `MenuItemsTableSeeder`.

---

## Dojos (`DojoController`)

Custom read-only override for the Voyager dojos show page. Displays dojo info + users assigned to that branch.

**Controller:** `app/Http/Controllers/DojoController.php`

**View:** `resources/views/dojos/read.blade.php`

**Route:**
```
GET  admin/dojos/{id}   voyager.dojos.show
```

**Security rules:**
- Requires `read_dojos` permission.
- Branch user (`dojo_id` set): can only view their own dojo (403 if `$id !== $userDojoId`).
- Global admin (`dojo_id` null): can view any dojo.
- Users table only shows users from that dojo; links to `voyager.users.show` when user has `read_users`.
- "Agregar Usuario" button shows only when user has `add_users` permission.

---

## WhatsApp microservice stub

Route: `GET admin/whatsapp` (`whatsapp.message`) handled by `MicroServiceController::message()`.

This is an authenticated admin route inside the `/admin` group. Current implementation is a local/dev stub: `tokenGenerator()` posts to `http://127.0.0.1:3005/api/tokens/generate`, `message()` posts to `http://127.0.0.1:3002/send`, and `id='dev'` plus the recipient phone are hardcoded. Do not treat this as production-ready without externalizing configuration and removing hardcoded recipients.

---

## Dojo Mensualidades (`DojoMensualidadController`)

Monthly SaaS billing per branch. If a dojo has no vigente (paid + not expired) mensualidad, all its users are blocked from the admin panel.

**Controller:** `app/Http/Controllers/DojoMensualidadController.php`

**Model:** `App\Models\DojoMensualidad`, table: `dojo_mensualidades`

**Fields:** `dojo_id`, `periodo` (date, first day of billing month), `fecha_vencimiento` (date), `monto`, `monto_pagado`, `observacion`.

**Middleware:** `CheckDojoMensualidad` (`app/Http/Middleware/CheckDojoMensualidad.php`) — registered as `dojo.mensualidad` in Kernel, applied to the entire `/admin` group. Skips unauthenticated requests and global admins (`dojo_id = null`). Redirects blocked users to `/info/402`.

**Blocking logic:** A mensualidad is "vigente" when `fecha_vencimiento >= today` AND `monto_pagado >= monto`. No grace period — blocking is immediate on expiry.

**Error page:** `resources/views/errors/402.blade.php` — shown to blocked branch users.

**Access rules:**
- Global admin (`dojo_id = null`): never blocked, can create/pay/delete mensualidades for any dojo.
- Branch operator (`dojo_id` set): read-only view of their own dojo mensualidades; cannot create or pay.

**Panel:** Embedded in `resources/views/dojos/read.blade.php` via AJAX. Modal to create new mensualidad (global admin only).

**Status values (computed, not stored):**
- `Pagado` — `monto_pagado >= monto`
- `Vencido` — `fecha_vencimiento < today` AND not paid
- `Pendiente` — `fecha_vencimiento >= today` AND not paid

**Routes:**
```
GET    admin/dojos/{id}/mensualidades/list        dojo.mensualidades.list
POST   admin/dojos/{id}/mensualidades/store       dojo.mensualidades.store
PUT    admin/dojos/mensualidades/{id}/pagar       dojo.mensualidades.pagar
DELETE admin/dojos/mensualidades/{id}/delete      dojo.mensualidades.destroy
```

**Business rules:**
- One mensualidad per period per dojo (enforced in controller, checked by year+month of `periodo`).
- `pagar()` sets `monto_pagado = monto` (full payment only, no partials).
- `destroy()` soft-deletes; no restriction on paid mensualidades (admin can always delete).
- `monto = 0` counts as paid (free branch) — `0 >= 0` passes the vigente check.

---

## Public / utility routes
- `GET /comprobantes/mensualidades/pagos/{id}` — signed public monthly-payment receipt (`alumno.mensualidades.pago.comprobante.public`), no auth but protected by `signed` middleware.
- `GET /admin/clear-cache` — calls `php artisan optimize:clear` and redirects to `/admin/profile`. It is currently declared outside the authenticated `/admin` route group; do not add similar utility routes without explicit auth/middleware.
- `resources/views/kumite_temporizador.blade.php` and `resources/views/tablero_kata.blade.php` exist, but `routes/web.php` currently does not register `/kumite-temporizador` or `/tablero-kata`. Add routes explicitly before documenting them as public endpoints.

---

## Key constraints

- `Person` uses only `first_name` — old fields (`middle_name`, `paternal_surname`, `maternal_surname`, `last_name`) are removed and must not be reintroduced.
- A person can only be registered as an alumno once across the entire system (unique `person_id` in `alumnos`, checked including soft-deleted records).
- Alumno status changes happen directly on `Alumno.status` — no dependency on grade history.
- Inactive alumnos cannot receive new operational records, but existing debts can still be paid.
- `AlumnoGrado.status`: `'0'`/`null` = in progress, `'1'` = completed. Never skip the progression system by setting `status='1'` directly on creation.
- Dan grades (`Grado.tipo = Dan`) do not use repasos/puntas. Do not add repaso flows or Repaso aranceles for them.
- `registerUser_id` is audit-only — it does not determine dojo ownership.
- `dojo_id` on users comes from the associated person (`users.dojo_id = people.dojo_id`). The UI does not allow selecting dojo directly on users — it auto-fills from the selected person.
- Repasos cannot have their date set earlier than the grade start date, the last repaso, or the last examen — validated both server-side and via `min` attribute in the view.
- Exam comprobantes follow the same paid-only rule as repaso comprobantes.
- Mensualidades use `descuento`; `beca` and `mora` were removed and must not be reintroduced.
- The `Arancele` model uses the name `Arancele` (not `Arancel`) — this matches the migration and must not be renamed.
- Multi-branch isolation is applied to: `people`, `users`, `alumnos`, `horarios`, `asistencias`, `consulta`, `aranceles`, `mensualidades`, and grade receipts/certificates. Other modules must be reviewed before assuming they are dojo-scoped.
