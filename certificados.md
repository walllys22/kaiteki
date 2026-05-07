# Certificados

Este documento registra las reglas y plantillas usadas para imprimir certificados por dojo.

## Estado actual

Por el momento solo esta implementado el certificado de examen para el dojo:

- `dojo_id = 3`
- Dojo: `L.J.P. Zabala Dojo`
- Plantilla: `public/images/dojos/ljp/certificados/examen.png`
- Vista: `resources/views/alumnos/partials/certificadoExamenLjp.blade.php`
- Ruta: `admin/alumnos/grado/examen/{id}/certificado`
- Nombre de ruta: `alumno.grado.examen.certificado`
- Controlador: `AlumnoGradoController::certificadoExamen`

## Reglas

- Solo se puede imprimir certificado si el examen esta aprobado (`aprobado = 1`).
- Solo se permite para alumnos cuyo `alumnos.dojo_id = 3`.
- Si un usuario de sucursal intenta abrir un certificado, se respeta su `dojo_id`.
- Si el alumno no pertenece al dojo 3, el controlador redirige al detalle del alumno con aviso.
- El boton de certificado aparece en la tabla de examenes solo para examenes aprobados del dojo 3.
- El certificado se abre en una vista imprimible con botones `Cancelar` e `Imprimir`.

## Datos impresos

La vista coloca datos dinamicos encima de la plantilla:

- foto del alumno
- numero de registro: `alumno.id`
- nombre completo desde `people.first_name`
- grado aprobado desde `grados.numero`, `grados.tipo`, `grados.nombre`
- fecha del examen aprobado
- ciudad y texto institucional fijo para LJP Zabala

## Plantilla LJP Zabala

Archivo:

```text
public/images/dojos/ljp/certificados/examen.png
```

Dimensiones de la imagen:

```text
6000 x 4500 px
```

Relacion:

```text
4:3
```

La vista usa CSS con `aspect-ratio: 4 / 3` y `@page size: 280mm 210mm`.

## Coordenadas CSS actuales

Las posiciones se definieron en porcentajes para que escalen con la plantilla:

- foto del alumno:
  - `left: 70.2%`
  - `top: 9.2%`
  - `width: 17.1%`
  - `height: 23.5%`
- registro:
  - `left: 74.2%`
  - `top: 30.4%`
  - `width: 13%`
- texto principal:
  - `left: 12%`
  - `top: 47%`
  - `width: 76%`

Si se cambia la plantilla, estas coordenadas deben revisarse.

## Texto generado

El texto base es:

```text
A: {alumno}, por haber vencido las pruebas
fisicas y teoricas, se lo promueve al grado {grado},
es dado a los {dia} dias del mes de {mes} del Año {anio},
en la ciudad de la Santisima Trinidad, Departamento del Beni,
Bolivia.
```

El grado se arma asi:

```text
{grados.numero} {grados.tipo} - CINTA {grados.nombre sin "Cinturon"}
```

Ejemplo:

```text
8vo. Kyu - CINTA AMARILLA
```

## Pendiente para otros dojos

Para agregar certificados de otros dojos:

1. Crear carpeta de plantilla en `public/images/dojos/{dojo}/certificados/`.
2. Agregar la imagen base.
3. Crear una vista Blade especifica o parametrizar una existente.
4. Agregar regla por `dojo_id` en controlador.
5. Documentar coordenadas en este archivo.
