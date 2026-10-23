# Historial de Cambios — SIGPAF / FUNDACITE

Registro de todo lo implementado y corregido en el sistema durante el desarrollo
con asistencia de IA (octubre 2026). Última actualización: 2026-10-14.

> **Nota técnica:** los cambios se desarrollan en una copia de trabajo y se
> copian a `C:\xampp\htdocs\sigpaf-main`. Los JavaScript llevan control de
> versión (`?v=`) para evitar caché vieja: tras cada cambio conviene entrar
> al sistema con **Ctrl+F5**.

---

## 0. Permisos por rol (2026-10-14)

- **Reportes, Configuración y Bitácora: SOLO Super Usuario (Rol0001).**
  - Menú lateral: los tres enlaces quedan bajo un solo
    `RolHelper::esSuperUsuario()` (antes Reportes se veía para todos y
    Configuración para Administrador + Super Usuario).
  - Rutas en `index.php`: `reportes`, `reportePDF` y `configuracion` usan
    `RolHelper::verificarSuperUsuario()` (antes solo sesión o administrador).
  - Controladores: `ReporteController::listar/generarPDF` y
    `ConfiguracionController::Mostrar` verifican de nuevo el rol
    (defensa en profundidad; los accesos denegados quedan en la Bitácora).
  - Nuevo helper `RolHelper::esSuperUsuario()` para ocultar opciones del
    menú sin redirigir.
- Los demás módulos (Inicio, Actividades, Seguimiento, Perfil) siguen
  disponibles para cualquier usuario autenticado según su rol operativo.

---

## 0.1 Exportación de maestros a PDF (2026-10-14)

- **Los 21 módulos de Configuración ahora se pueden exportar a PDF** con el
  botón **"Exportar PDF"** junto al botón "Nuevo" en cada pantalla.
- Nuevo `ExportarMaestroController` genérico: un mapa blanco describe cada
  módulo (modelo + método + columnas + orientación); si el módulo no está en
  el mapa no se exporta (no se pueden pedir tablas por URL).
- Plantilla `ExportarMaestroPDFView` con el mismo estilo institucional del
  reporte mensual: banner, fondo sutil, total de registros, numeración de
  páginas y nombre de archivo `SIGPAF_<Modulo>_<fecha>.pdf`.
- Módulos en horizontal (muchas columnas): empleados, docentes, usuarios,
  lugares de actividad. En usuarios **jamás se exporta la contraseña**.
- Ruta `index.php?action=exportar&modulo=X`: **solo Super Usuario** (igual que
  Configuración); cada exportación queda en la Bitácora.
- Formato de campos: `esSede` → Sí/No, fechas → dd/mm/YYYY.

---

## 0.2 Conexión de la Bitácora a los maestros (2026-10-15) — cambio de ESQUEMA

- Análisis del diseñador de phpMyAdmin: **la única tabla operativa sin
  relación era `bitacora`** (todos los demás maestros o ya estaban
  referenciados por FK o son catálogos puros que no requieren salidas).
- **Nueva clave foránea:** `bitacora.usuario_id → usuarios.idUsuario` con
  `ON DELETE SET NULL`: si se elimina un usuario, su historial en la
  bitácora se conserva (queda `usuario_id` en NULL; `usuario_nombre` sigue
  guardado como texto para lectura).
- `bitacora.usuario_id` pasó a **aceptar NULL**: los eventos anónimos
  (login fallido, acceso denegado sin sesión) ya no usan el texto `'ANON'`
  (violaría la FK), guardan NULL. Los 16 registros históricos con
  'ANON'/'TESTCLI'/'USRTEST1' quedaron en NULL.
- `Logger.php`: `usuario_id` = `$_SESSION['usuario_id'] ?? null`.
- `BitacoraModel::registrar()`: enlace de parámetros explícito
  (`bindValue` con `PDO::PARAM_NULL`), porque `execute(array)` convierte
  null en cadena vacía y eso violaría la FK.
- Las copias del esquema en `database/` (sigpaf_DB.sql y sigpaf_db_real.sql)
  se regeneraron desde la base viva y ya incluyen la relación.
- Verificado: evento anónimo → NULL, evento con sesión → vinculado, y al
  borrar un usuario sus registros pasan a NULL (prueba en transacción con
  rollback).

---

## 0.3 Exportación a PDF estilo versión anterior (2026-10-15)

Replicamos **exactamente el patrón Dompdf de la versión anterior de SIGPAF**,
sin introducir estructuras nuevas:

- **Cada controlador tiene su propio `exportarPdf()`** (los 21 maestros):
  verifica sesión → carga `lib/dompdf/autoload.inc.php` → obtiene los datos con
  `$this->model->listarX()` → captura la vista con `ob_start(); require ...` →
  instancia Dompdf (`Options: isRemoteEnabled=true, defaultFont=Helvetica`) →
  `setPaper('letter')` (horizontal en LugarActividad, Empleado, Docente y
  Usuarios) → `loadHtml($html,'UTF-8')` →
  `stream("reporte_<modulo>_sigpaf.pdf", ["Attachment" => false])` → `exit()`.
  La diferencia con la versión vieja: además registra
  `GENERAR | Configuracion | ...` en la Bitácora (`require_once 'Logger.php'`
  dentro del método), porque la bitácora es función del sistema actual.
- **Vistas de reporte en `view/public/reportes/<Modulo>ReporteView.php`** (21
  archivos, siguiendo la estructura de la versión anterior), cada uno con sus
  propias columnas y anchos. Diseño institucional: banner base64 leído con
  `__DIR__.'/../img/banner_institucional.png'`, título centrado en mayúsculas
  #16305e, fecha de emisión `d/m/Y h:i A` (zona America/Caracas), tabla con
  encabezado #16305e y filas zebra #eef6f5, badge .codigo #475569 y pie
  `© {Y} — UPTYAB | FUNDACITE Yaracuy`.
- **El PDF se MUESTRA en el navegador** (`Attachment=false`), es decir, abre en
  una pestaña nueva donde el usuario decide imprimir o guardar desde el visor
  del navegador — así funcionaba la versión anterior. El botón Exportar lleva
  `target="_blank"`.
- **Botones**: igual que la versión anterior, el botón "Exportar" queda a la
  DERECHA del botón "Nuevo" dentro de un `<div class="d-flex gap-2">`, en rojo
  (`btn-danger btn-sm fw-bold`), apuntando a
  `index.php?action=exportarPdf<Modulo>`.
- **Rutas en `index.php`**: 21 acciones `exportarPdf<Modulo>` (ej.
  `exportarPdfCargos`), cada una con `verificarSesion()` +
  `RolHelper::verificarSuperUsuario()` (la versión anterior solo pedía sesión;
  se mantiene la restricción de solo Super Usuario acordada).
- **Eliminados**: `view/reportes/` completa, el controlador genérico
  `ExportarMaestroController.php` y las rutas `exportarVista`/`exportar` del
  enfoque intermedio.
- **Reportes mensual**: se conserva el botón **Imprimir** (window.print con
  `@media print` sobre `#area-reporte`) junto al Descargar PDF existente.

---

## 0.4 Reporte mensual unificado al patrón exportarPdf (2026-10-15)

El módulo **Reportes** ahora usa exactamente el mismo patrón de exportación que
los 21 maestros (versión anterior):

- **`ReporteController::exportarPDF()` renombrado a `exportarPdf()`** con el
  patrón viejo: `Options(isRemoteEnabled=true, defaultFont=Helvetica)`,
  `setPaper('letter', 'landscape')`, `stream(..., ["Attachment" => false])`
  (el PDF se MUESTRA en el navegador y el usuario decide imprimir o guardar
  desde el visor). Se conservan: la limpieza de buffers antes de la salida
  binaria, el chequeo de GD para el fondo, la numeración de páginas con el
  canvas de dompdf y el registro en la Bitácora.
- **Nueva vista `view/public/reportes/ReporteActividadesView.php`** con el
  diseño institucional de la versión anterior: banner en base64 (cargado por la
  vista desde `view/public/img/`), título centrado en mayúsculas #16305e,
  fecha de emisión + chip de filtros aplicados, cajas de resumen del mes
  (actividades, sesiones, asistencias, beneficiarios), tabla con encabezado
  #16305e y filas zebra #eef6f5, badge .codigo #475569, badges de estatus y
  pie `© {Y} — UPTYAB | FUNDACITE Yaracuy`. Se mantiene el **fondo sutil
  difuminado** (`background_science_sutil.jpg`) pedido anteriormente para el
  reporte mensual.
- **Ruta**: `reportePDF` → `exportarPdfReportes` en `index.php` (solo Super
  Usuario). El menú de `Layout.php` resalta con la acción nueva.
- **Botón en `ReporteView.php`**: "Descargar PDF" pasa a ser **"Exportar"**
  (`btn-danger btn-sm fw-bold`, `target="_blank"`) llevando siempre los
  filtros actuales (mes/año/docente). ~~El botón Imprimir~~ (eliminado el
  2026-10-15: el Exportar abre el PDF en el navegador, donde se imprime o
  guarda; se quitó también el CSS `@media print` y el área `#area-reporte`
  quedó solo como contenedor de la tabla).
- **Eliminado**: `view/ReportePDFView.php` (plantilla antigua del reporte).

---

## 0.5 Exportar PDF del módulo Actividades (2026-10-15)

El listado de Actividades también tiene su `exportarPdf()` con el patrón de la
versión anterior:

- **`ActividadController::exportarPdf()`**: igual patrón viejo (Dompdf con
  `Options(isRemoteEnabled, defaultFont Helvetica)`, `setPaper('letter',
  'landscape')`, `stream(..., ["Attachment" => false])` mostrado en el
  navegador). A diferencia del reporte mensual, **incluye TODAS las
  actividades sin importar el estatus** (usa `listarActividades()`, el mismo
  listado de la pantalla). Registra la acción en la Bitácora.
- **Nueva vista `view/public/reportes/ActividadReporteView.php`**: diseño
  institucional de la versión anterior (banner, título centrado #16305e,
  fecha de emisión + chip "Todas las actividades | Todos los estatus", tabla
  con encabezado #16305e y filas zebra, badge .codigo, pie UPTYAB |
  FUNDACITE). Columnas: Código, Actividad (nombre + tipo), Fechas, Sesiones,
  Responsables (empleado + docente), Área, Vértice y Estatus con el MISMO
  criterio de colores del sistema (verde = finalizada, rojo = cancelada/en
  espera, amarillo = demás estatus).
- **Permiso**: solo sesión iniciada (igual que el módulo Actividades; NO es
  de solo Super Usuario). Ruta `exportarPdfActividades` en `index.php`.
- **Botón "Exportar"** en `ActividadView.php`: rojo, a la derecha de "Nueva
  Actividad" dentro de `<div class="d-flex gap-2">`, abre en pestaña nueva.

---

## 0.6 Script canónico de la base de datos en formato original (2026-10-15)

El usuario recuperó el script de creación original de la base de datos
(`sigpaf_DBB`: `CREATE DATABASE sigpaf_DB` + `CREATE TABLE` + `INSERTs` con
banners) y pidió que la base actual tenga ese formato y que el sistema trabaje
sobre él.

- **Comparación estructural completa** (viejo vs base viva, tabla por tabla y
  columna por columna): la base actual YA estaba en ese formato. Únicas
  diferencias reales:
  - `actividad.objetivoActividad` / `descActividad`: `VARCHAR(300)` (actual)
    vs `VARCHAR(250)` (formato viejo). **Se mantuvo en 300** porque el
    sistema valida 300 caracteres (pedido del usuario); los datos actuales no
    superan los 106 caracteres, así que ambos formatos son compatibles.
  - Tabla `bitacora`: no existía en el script viejo (se creó aparte para el
    módulo Bitácora). **Se mantiene** y se documenta en el script canónico.
  - `esSede BOOLEAN` ≡ `TINYINT(1)` y `INT` ≡ `INT(11)`: equivalentes en
    MySQL, sin cambio real.
- **Nuevo `database/sigpaf_DBB.sql`** (generado con `generar_dbb.php` del
  workspace): script canónico en el FORMATO EXACTO del original — las 28
  `CREATE TABLE` textuales del archivo del usuario (mismo orden y definición),
  la sección `bitacora` agregada después de `sesiones_activas` con su banner
  explicativo, y todos los datos actuales como `INSERTs` agrupados por tabla
  con banners, en orden de dependencias de las claves foráneas.
- **Prueba de importación**: el script importa completo desde cero (29 tablas,
  37 actividades, 95 seguimientos, 818 asistencias, 26 beneficiarios, 3
  usuarios, 126 bitácora). Detalle clave: el `CREATE DATABASE ... COLLATE
  utf8mb4_unicode_ci` del script es lo que hace funcionar la FK de
  `sesiones_activas` (si la base se crea con otra collation, la clave
  `usuario_id → usuarios` falla con errno 150).
- **Corrección al vuelo**: `bitacora.accion` agregó `'GENERAR'` al ENUM (los
  `exportarPdf` la usan y antes fallaba en silencio porque Logger atrapa sus
  excepciones). Verificado: el registro GENERAR ya entra en la bitácora.
- El sistema **no necesitó adaptación de código**: ya usa exactamente esas
  tablas y columnas. Se verificó con las pruebas de humo (reporte mensual PDF
  + registro de bitácora).

---

## 0.7 Filtrado del módulo Actividades (pantalla + PDF) (2026-10-15)

Se aplicó el modelo de filtrado de la versión anterior al módulo Actividades
(archivos de referencia pasados por el usuario), manteniendo el criterio de
colores de estatus y el estilo del sistema:

- **`ActividadModel::listarActividades($filtros = [])`**: ahora acepta filtros
  opcionales y arma el WHERE con parámetros preparados (mismo patrón viejo):
  - `desde` / `hasta`: rango de fechas — trae las actividades que
    **intersectan** el rango (`fechafin >= desde` y `fechainicio <= hasta`).
  - `idEstatus`, `idTipoActividad`: igualdad directa.
  - `responsable`: un solo valor que busca en empleado O docente
    (`idEmpleado = :resp OR idDocente = :resp`).
  - Sin filtros = todas (compatible con las llamadas anteriores).
- **`ActividadController`**: nuevos `obtenerFiltros()` (lee GET) y
  `describirFiltros()` (texto legible: "Desde … | Hasta … | Estatus: … |
  Tipo: … | Responsable: …"). `listar()` pasa filtros + maestros de la barra
  (estatus, tipos, empleados y docentes con `nombreCompleto`).
  `exportarPdf()` aplica los MISMOS filtros que la pantalla.
- **`view/ActividadView.php`**: barra de filtros estilo versión anterior
  (Desde, Hasta, Estatus, Tipo de Actividad, Responsable con optgroups de
  empleados/docentes, botón Filtrar + botón limpiar). El botón **Exportar**
  arma su URL con `array_filter($filtros)`, así el PDF siempre lleva los
  filtros activos.
- **`ActividadReporteView.php`**: el chip "Filtros aplicados" ahora muestra
  los filtros reales; sin filtros conserva el texto "Todas las actividades |
  Todos los estatus".
- **Probado**: cada filtro por separado y combinado contra la base viva
  (p. ej. septiembre + Finalizada = 2 actividades, igual que el reporte
  mensual), PDF con chip de filtros correcto y PDF sin filtros con el texto
  de respaldo (37 actividades, 4 páginas).

---

## 1. Seguridad y sesiones

- **Sesión única por usuario:** un mismo usuario no puede entrar dos veces; si
  inicia sesión en otro dispositivo/navegador, la sesión anterior se invalida.
- **Detección de cierre del navegador:** si el usuario cierra la pestaña o el
  navegador (sin usar "Salir"), el sistema lo detecta por falta de "latido"
  (heartbeat periódico) y cierra la sesión.
- Eliminado el cuadro amarillo de "acceso no permitido" al entrar sin sesión
  (redirección limpia al login); los demás errores de sesión sí muestran aviso.
- **Zona horaria de Venezuela (UTC-4)** fijada en PHP (`index.php`) y en la
  conexión MySQL (`ConexionModel`). Corrige el Dashboard ("HOY" vs "MAÑANA"),
  "Sesiones Hoy" y la validación de sesiones con fecha futura.

## 2. Dashboard (Inicio)

- Pantalla alimentada con **datos reales** (`DashboardModel`): actividades en
  ejecución, beneficiarios, sesiones de hoy/semana, lugares y sedes.
- Mini-calendario de próximas actividades con etiquetas Hoy / Mañana / día de
  semana (con el arreglo de zona horaria).
- Lista "Estado de Actividades" con las más recientes.

## 3. Validaciones unificadas (todo el sistema)

- Helper central (`ValidationHelper`) usado por todos los módulos: mensajes
  coherentes y alertas con el estilo estándar (`Validar.aviso`).
- Longitudes: nombre 250; **objetivo, descripción y observación ~300–500** según
  campo; validación de cédula, teléfono, correo, fechas y rangos.
- **Disponibilidad del docente:** el sistema rechaza registrar una actividad si
  el docente ya tiene otra actividad el mismo horario con fechas traslapadas.
- **Registro de actividades (servidor):** obligatorios nombre, objetivo,
  descripción, tipo, vértice, área, responsable, **docente**, lugar, horario,
  **unidad de medida, grupo étnico, estrategia**, al menos un grupo etario;
  personas a atender > 0; **sesiones entre 1 y los días del rango de fechas**.

## 4. Diseño general

- Sistema **responsive** con apariencia de aplicación de escritorio: sidebar
  oscuro, menú hamburguesa funcional, tarjetas y tablas con estilo unificado.
- **Modales rediseñados** (Nuevo/Editar en todos los módulos) y corrección de
  z-index para que las alertas siempre aparezcan **delante** de los cuadros
  emergentes (ya no se "nubla" la pantalla ni bloquea la escritura).
- Badge de estatus con **código de colores único en todo el sistema**:
  🟢 verde = Finalizada · 🔴 rojo = Cancelada/En espera · 🟡 amarillo = resto.
  Aplicado en Actividades (lista y detalle), Seguimiento y Reportes.
- Control de caché: versionado de los JS incluidos desde las vistas.

## 5. Módulo Seguimiento

- Seguimiento por sesiones: registro/edición de logro, obstáculos, tipo de
  entrega y **asistencia de beneficiarios** (con alta rápida de beneficiario).
- Las actividades guardadas sin sesiones las genera automáticamente
  (`ensureSesiones`) según fechas y cantidad planificada.
- **Cambio de estatus desde el modal de seguimiento** (mismo cuadro donde se
  registran sesiones y asistencia), con:
  - **Alerta de confirmación:** "Estás a punto de cambiar el estatus de esta
    actividad…" mostrando el cambio con etiquetas de color; cancelar revierte.
  - **Permisos:** solo el responsable de la actividad o un administrador.
  - **Regla de finalización:** no se puede poner Finalizada sin al menos una
    sesión con seguimiento registrado (también validado en el servidor).
  - Al confirmar, el badge de la tabla se **actualiza al instante** (texto y
    color) sin recargar la página.
- Cambio de estatus queda registrado en la Bitácora.

## 6. Módulo Reportes (PDF)

- Reporte **mensual** de actividades realizadas por FUNDACITE con **Dompdf**
  (librería en `lib/dompdf`).
- **Solo entran actividades Finalizada**, y se agrupan por el **mes en que
  culminaron** (fecha de fin). Ya no hay filtro de estatus: es regla del sistema.
- El PDF lleva: **banner institucional (bandera de Venezuela)** en el
  encabezado, **fondo sutil difuminado** (`background_science_sutil.jpg`,
  requiere extensión GD — ver Pendientes), número de página por hoja, nombre de
  archivo `Reporte_Actividades_FUNDACITE_AAAA-MM.pdf` y solo el resumen del
  mes (se quitó la sección "Criterio del reporte" y el detalle innecesario).
- Pantalla de Reportes con selector Mes/Año/Docente, resumen numérico y vista
  previa idéntica a lo que descarga el PDF.

## 7. Módulo Actividades

- Colores de estatus corregidos (antes comparaban con nombres inexistentes y
  todo salía gris).
- **Eliminación corregida:** antes fallaba con "Error al eliminar" cuando la
  actividad tenía asistencias (llave foránea). Ahora borra en orden correcto
  (asistencias → sesiones → grupos/lugar → actividad) en una sola transacción.

## 8. Fechas y sesiones (formulario Nueva/Editar Actividad)

- **Aviso en vivo del rango:** "El rango tiene X día(s): caben hasta X
  sesiones"; en rojo si se excede (y marca el campo inválido).
- **Reparto automático en vivo:** al cambiar fechas o cantidad, las sesiones se
  distribuyen y previsualizan solas (el botón "Generar Fechas" sigue disponible).
- El calendario visual marca días ocupados, seleccionados y hoy.

## 9. Datos de simulación (1 año de operación)

- Archivo `simulacion_datos.sql` (en la raíz del proyecto): **36 actividades**
  (nov-2025 a oct-2026, 3 por mes), **25 finalizadas** repartidas en los 12
  meses, **94 sesiones**, **24 beneficiarios** y **827 asistencias**.
- **Re-ejecutable:** borra solo lo simulado (IDs `AC01xx`, `SA01xx`, `LR01xx`,
  `GA01xx`, `BEN0010+`) antes de insertar; no toca los datos reales.
- Importación con UTF-8: `mysql --default-character-set=utf8mb4 -u root sigpaf_db < simulacion_datos.sql`

## 10. Módulo Seguimiento: asistente por pasos (sin modales apilados)

- **Antes:** el botón de acción abría un modal de seguimiento, sobre él un modal
  de nuevo beneficiario y encima otro de confirmación de estatus (3 capas).
- **Ahora:** el botón de la tabla navega a una **página completa estilo "Nueva
  Actividad"** (`index.php?action=registrarSeguimiento&id=XX`) con asistente
  de 3 pasos: **1) Sesión** (select con sesiones futuras bloqueadas + barra de
  avance registradas/plan), **2) Desarrollo** (logro mín. 10 y máx. 500 con
  contador, observaciones, tipo de entrega), **3) Asistencia y Estatus**
  (checklist con contador, alta rápida de beneficiario en panel desplegable
  inline, selector de estatus solo para responsable/admin con alerta de
  confirmación).
- Reutiliza las clases del wizard existentes (`actividad.css`, ya cargado por
  el Layout) y los mismos endpoints AJAX (`obtenerSesionesSeguimiento`,
  `guardarSeguimiento`, `guardarBeneficiarioSeguimiento`,
  `cambiarEstatusActividad`): el backend no cambió, solo se agregó el método
  `nuevo()` en `SeguimientoController` y la ruta `registrarSeguimiento`.
- Al guardar, se vuelve al listado y se muestra el aviso de éxito guardado en
  `sessionStorage` (`sigpaf_seguimiento_aviso`).
- El listado (`SeguimientoView`) quedó limpio: sin modales, sin cfg global;
  `seguimiento.js` ya no se carga.

## 11. Respaldo e importación de la base de datos (Configuración)

- Nuevo controlador `controller/RespaldoController.php` con dos acciones,
  ambas **solo super usuario** (ruta + `RolHelper::verificarSuperUsuario()`):
  - **Exportar** (`index.php?action=exportarBd`): descarga un `.sql` completo
    y **portable** — la cabecera incluye `CREATE DATABASE IF NOT EXISTS` +
    `USE` + charset utf8mb4, así que en otra PC basta con importar el archivo
    en phpMyAdmin sin crear nada antes. Estructura `SHOW CREATE TABLE` +
    datos en INSERT por lotes de 100, `DROP TABLE IF EXISTS` incluido para
    que el archivo sea re-importable.
    Nombre: `sigpaf_respaldo_AAAA-MM-DD_HH-mm.sql`.
    **Sin comentarios:** el archivo contiene solo sentencias SQL puras.
    Validado creando la base desde cero en una temporal (86 sentencias,
    29 tablas, datos idénticos, base real sin tocar).
- **Vista compacta de Configuración:** la cuadrícula de módulos es más densa
  (más columnas, tarjetas más bajas) y el respaldo pasó de tarjeta grande a
  una **barra compacta de dos cajas** (Exportar / Importar), para ver todo el
  módulo sin hacer scroll. Clases nuevas: `.config-respaldo`, `.respaldo-box`
  (en `Style.css`); versión de assets subida a `2026-10-23.1`.
  - **Importar** (`index.php?action=importarBd`): sube un `.sql` (máx. 32 MB,
    exige contener `CREATE TABLE`), exige casilla de confirmación, guarda
    **automáticamente un respaldo previo** en `respaldos/` del proyecto y
    ejecuta el archivo con `FOREIGN_KEY_CHECKS=0`. Reporta sentencias
    ejecutadas o la sentencia exacta que falló.
- El divisor de sentencias respeta comillas, escapes y comentarios (`--`,
  `#`, `/* */`), por lo que acepta textos largos con `;` y acentos.
- Tarjeta "Respaldo de la Base de Datos" al final de Configuración con las
  dos opciones; modal de importación con advertencia roja, casilla de
  confirmación que habilita el botón y mensajes de resultado (ok/error con
  detalle) en la propia vista.
- Ambas acciones quedan registradas en la bitácora (`EXPORTAR_BD` /
  `IMPORTAR_BD`). Validado: el dump generado se ejecutó completo en una base
  temporal (84 sentencias, 29 tablas, datos idénticos) y la base de prueba se
  eliminó.

## 12. Filtrado de listados en los 21 módulos de Configuración

- **Buscador en vivo** en todos los módulos (mismo estilo en todos): input con
  icono de lupa sobre la tabla, botón X para limpiar y contador
  "Mostrando X de Y" / "Sin resultados". Filtra las filas al escribir.
- **El PDF exporta solo lo filtrado:** el buscador sincroniza el botón
  "Exportar PDF" (`&q=...`); cada `exportarPdf()` aplica el mismo filtro
  (coincidencia en cualquier columna, `mb_stripos`) sobre los registros antes
  de generar el documento, y el encabezado del PDF muestra
  "Filtro aplicado: ... — N resultado(s)" en ámbar (oculto sin filtro).
- Implementación uniforme vía inyección sobre la estructura común:
  - `view/public/js/filtroTabla.js` (nuevo, se carga en el Layout y solo
    actúa donde existe `#buscadorModulo`).
  - 21 vistas: toolbar con `#buscadorModulo` antes de la tabla + `id="btnExportarPdf"`.
  - 21 controladores: filtro por `$_GET['q']` después del `listar`.
  - 21 vistas de reporte en `view/public/reportes/`: línea de filtro.
- Validado: los 63 archivos PHP pasan `php -l`, el filtro reduce los
  resultados correctamente y el reporte muestra/oculta la línea según el caso.

---

## Pendientes / recordatorios

1. **Reiniciar Apache** para que PHP cargue la extensión GD; sin eso el PDF se
   genera bien pero **sin el fondo difuminado** (hay respaldo `php.ini.bak_sigpaf`).
2. Decisión pendiente: ¿bloquear la **eliminación de actividades Finalizada**
   (para proteger el historial reportado) y obligar a "cancelarlas"?
3. Idea pendiente: gráfica mensual en el Dashboard (actividades finalizadas
   por mes) aprovechando los datos del año simulado.

---

*Mantenimiento: actualizar este archivo al final de cada sesión de cambios.*
