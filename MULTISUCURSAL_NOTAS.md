# Notas de Multisucursal

## Objetivo

Dejar la base del sistema preparada para trabajar por sucursal usando `dojo` como sucursal.

## Decisiones principales

- `users.dojo_id` define a que sucursal pertenece un usuario operativo.
- `people.dojo_id` guarda desde que sucursal se registro la persona.
- `registerUser_id` queda como auditoria:
  no define pertenencia, solo indica que usuario creo el registro.

## Regla importante de usuarios

Existen 2 tipos de usuarios en la estructura actual:

- Usuario global/admin:
  no debe estar asociado a `person_id` ni a `dojo_id`.
- Usuario operativo de sucursal:
  si puede estar asociado a `person_id` y `dojo_id`.

## Estado actual esperado en seeders

### Usuario admin global

Archivo: `database/seeders/UsersTableSeeder.php`

- email: `admin@admin.com`
- `person_id = null`
- `dojo_id = null`

Este usuario queda libre para administracion global del sistema.

### Usuario operativo de sucursal

Archivo: `database/seeders/DojoUsersTableSeeder.php`

- email: `ignacio@admin.com`
- `person_id = 1`
- `dojo_id = 1`

Este usuario representa un usuario vinculado a una persona y a un dojo.

## Cambios realizados

### Base de datos

- Se agrego `dojo_id` a `users`.
- Se agrego `dojo_id` a `people`.
- Se corrigio migracion de `alumnos` porque tenia `status` duplicado.

### Modelos

- `App\Models\User`
  ahora incluye `dojo_id` en `fillable` y relacion `dojo()`.
- `App\Models\Person`
  ahora incluye `dojo_id` en `fillable` y relacion `dojo()`.
- `App\Models\Dojo`
  ahora incluye relaciones `users()` y `people()`.

### Usuarios

- Crear usuario obliga seleccionar sucursal/dojo.
- Editar usuario obliga mantener sucursal/dojo.
- Se mejoro `UserController` para validar mejor y evitar consultas inseguras.
- En usuarios, `dojo_id` ahora se toma desde la persona seleccionada:
  `users.dojo_id = people.dojo_id`.
- La vista de usuarios ya no selecciona dojo manualmente; solo lo muestra
  como dato informativo segun la persona elegida.

### Alumnos

- Si el usuario logueado pertenece a un dojo, `AlumnoController`
  toma internamente `auth()->user()->dojo_id` al crear o actualizar.
- En la vista de alta/edicion de alumnos ya no necesita seleccionar dojo
  cuando el usuario pertenece a una sucursal.
- Si el usuario es global y no pertenece a un dojo, la vista permite
  seleccionar la sucursal manualmente.
- El listado, edicion y lectura de alumnos se filtran por `dojo_id`
  cuando el usuario pertenece a una sucursal.

### Personas

- El sistema ahora trabaja solo con `first_name`.
- Se limpiaron referencias viejas a:
  `middle_name`, `paternal_surname`, `maternal_surname`, `last_name`.
- El modal de registro de persona ahora envia:
  `documentType`, `dojo_id`, `status`, `first_name`, `ci`, `gender`, etc.
- En el modal de registro de persona:
  si el usuario tiene `dojo_id`, el dojo se muestra fijo y no puede cambiarse.
- Si el usuario no tiene `dojo_id`, el modal permite seleccionar la sucursal manualmente.
- Si el usuario logueado pertenece a un dojo, `PersonController`
  toma internamente `auth()->user()->dojo_id`.
- En ese caso, el backend ya no depende de que la interfaz mande el dojo correcto.
- Si el usuario logueado no pertenece a un dojo, puede definir la sucursal
  desde la interfaz para registrar o actualizar personas.
- Si el usuario logueado no tiene dojo y tampoco se envia uno desde la interfaz,
  igual puede registrar personas; en ese caso `people.dojo_id` queda `null`.
- El registro rapido por modal (`AjaxController@personStore`) sigue la misma regla:
  prioriza el `dojo_id` del usuario logueado y solo usa el formulario para usuarios globales.
- `PersonController@list` y `PersonController@show` aplican el filtro por dojo
  directamente con `where('dojo_id', auth()->user()->dojo_id)` cuando corresponde.
- `AjaxController@personList` aplica el mismo criterio para el buscador/select AJAX.
- Si el usuario tiene `dojo_id`, solo ve personas de su dojo.
- Si el usuario no tiene `dojo_id`, ve todas las personas.

### BREAD / Voyager

- Se limpio `DataRowsTableSeeder` para `people`.
- Se reemplazo el campo viejo `middle_name` por `dojo_id` dentro del bloque de `people`.
- Se eliminaron referencias de BREAD a `paternal_surname` y `maternal_surname`.
- Se agrego `dojo_id` al bloque de `users` en `data_rows`.

### Seeders

- `UsersTableSeeder` ahora solo crea el usuario admin global.
- `DojoUsersTableSeeder` crea el usuario operativo relacionado a persona/dojo.
- `DatabaseSeeder` fue reordenado para respetar dependencias:
  1. `VoyagerDatabaseSeeder`
  2. `UsersTableSeeder`
  3. `CiudadsTableSeeder`
  4. `PeopleTableSeeder`
  5. `DojosTableSeeder`
  6. `DojoUsersTableSeeder`

## Problemas corregidos durante el proceso

- Error por columna `status` duplicada en migracion de `alumnos`.
- Error por `data_rows.id` duplicado en `DataRowsTableSeeder`.
- Error de foreign key en `users.person_id` durante `example:install`.
- Referencias rotas a campos viejos de `Person`.

## Lo que todavia NO significa

Aunque ya existe base multisucursal, eso no implica que todo el sistema este aislado por sucursal.

Todavia falta, si se quiere multisucursal real:

- filtrar otros modulos por `auth()->user()->dojo_id`
- restringir accesos por sucursal en personas, alumnos, pagos, reportes, etc.
- impedir que usuarios de una sucursal vean o editen datos de otra

## Comandos utiles

Instalacion completa:

```powershell
php artisan example:install
```

Migrar desde cero:

```powershell
php artisan migrate:fresh --seed
```

## Resumen corto

- `admin@admin.com` = usuario global, sin persona ni dojo
- `ignacio@admin.com` = usuario operativo, con persona y dojo
- `registerUser_id` = auditoria
- `dojo_id` = pertenencia a sucursal
