# Historial de Cambios — SIGPAF / FUNDACITI

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

- Reporte **mensual** de actividades realizadas por FUNDACITI con **Dompdf**
  (librería en `lib/dompdf`).
- **Solo entran actividades Finalizada**, y se agrupan por el **mes en que
  culminaron** (fecha de fin). Ya no hay filtro de estatus: es regla del sistema.
- El PDF lleva: **banner institucional (bandera de Venezuela)** en el
  encabezado, **fondo sutil difuminado** (`background_science_sutil.jpg`,
  requiere extensión GD — ver Pendientes), número de página por hoja, nombre de
  archivo `Reporte_Actividades_FUNDACITI_AAAA-MM.pdf` y solo el resumen del
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
