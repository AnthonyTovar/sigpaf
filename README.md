# SIGPAF

Sistema web desarrollado para **FUNDACITE Yaracuy**, orientado a centralizar la planificación, el seguimiento y el reporte de cursos, talleres, charlas y demás actividades dirigidas a las comunidades.

**Institución destinataria:** Fundación para el Desarrollo de la Ciencia y la Tecnología en el estado Yaracuy.

**Institución académica:** UPTYAB.

**Año:** 2026.

---

## Descripción

SIGPAF organiza en un solo lugar la información institucional de las actividades de FUNDACITE. Permite definir fechas, lugares, horarios, responsables y docentes; registrar cada sesión; controlar la asistencia y conocer cuántas personas fueron atendidas realmente.

El sistema diferencia la cantidad de beneficiarios propuestos durante la planificación de la cantidad de beneficiarios atendidos, evita duplicar personas dentro de una misma actividad y consolida la información para la presentación de reportes institucionales.

## Funcionalidades principales

- Gestión de usuarios y control de acceso por roles.
- Planificación y actualización de actividades.
- Selección de múltiples lugares, horarios, responsables y docentes.
- Validación de disponibilidad de docentes por fecha y horario.
- Registro y seguimiento de sesiones.
- Registro manual e importación masiva de beneficiarios mediante Excel.
- Validación del rango de edad de los beneficiarios según la actividad.
- Dashboard con indicadores y listados operativos.
- Gestión de lugares, espacios, horarios, docentes, empleados y configuraciones generales.
- Exportación de reportes en PDF y Excel.
- Exportación de la **Ficha de Seguimiento AE**, con actividades y beneficiarios organizados por área específica.
- Bitácora de acciones y respaldo de la base de datos.

## Tecnologías utilizadas

- PHP con programación orientada a objetos.
- MySQL o MariaDB.
- PDO para el acceso a la base de datos.
- HTML5, CSS3 y JavaScript.
- Bootstrap y Bootstrap Icons.
- Dompdf para los reportes PDF.
- OpenXML y ZipArchive para la lectura y generación de archivos Excel.
- Apache como servidor web.

## Requisitos

- Apache.
- PHP con las extensiones `pdo_mysql`, `mbstring`, `zip`, `dom`, `simplexml`, `xml` y `gd`.
- MySQL o MariaDB.
- Un navegador web actualizado.
- XAMPP como opción para el entorno local de desarrollo.

## Instalación

### 1. Copiar el proyecto

En Windows con XAMPP:

```text
C:\xampp\htdocs\sigpaf-main
```

En Linux, puede utilizarse el directorio configurado para el servidor Apache, por ejemplo:

```text
/var/www/html/sigpaf-main
```

### 2. Crear e importar la base de datos

La estructura se encuentra en:

```text
database/sigpaf_db.sql
```

Puede importarse desde phpMyAdmin o mediante consola:

```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS sigpaf_DB CHARACTER SET utf8mb4;"
mysql -u root -p sigpaf_DB < database/sigpaf_db.sql
```

El nombre de la base de datos debe conservarse exactamente como `sigpaf_DB`, respetando mayúsculas y minúsculas.

### 3. Configurar la conexión

Editar los datos de conexión en `model/ConexionModel.php`:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'sigpaf_DB');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_clave');
```

En producción se recomienda utilizar un usuario de base de datos con una clave segura y únicamente los permisos necesarios.

### 4. Abrir el sistema

```text
http://localhost/sigpaf/
```

El acceso debe realizarse con un usuario registrado y activo. Las credenciales iniciales deben ser suministradas por la persona encargada de administrar el sistema.

## Estructura del código

```text
sigpaf/
├── controller/                 Controladores de los módulos
├── database/
│   └── sigpaf_db.sql           Estructura de la base de datos
├── lib/
│   ├── dompdf/                 Generación de documentos PDF
│   ├── plantillas/             Formatos institucionales de Excel
│   └── *.php                   Utilidades de exportación e importación
├── model/                      Consultas, conexión y lógica de datos
├── respaldo/                   Respaldos generados del sistema
├── view/
│   ├── public/
│   │   ├── css/                Estilos de las vistas
│   │   ├── img/                Recursos institucionales
│   │   ├── js/                 Comportamiento de las interfaces
│   │   └── reportes/           Plantillas de reportes PDF
│   └── *.php                   Vistas del sistema
├── index.php                   Enrutador principal
├── Logger.php                  Registro de acciones
├── RolHelper.php               Control de permisos y roles
└── README.md                   Documentación y firma de autoría
```

## Roles del sistema

- **Superusuario:** acceso completo, configuración, reportes, respaldos y bitácora.
- **Administrador:** gestión de módulos y exportaciones autorizadas.
- **Usuario común:** consulta y operaciones limitadas según los permisos establecidos.

## Consideraciones para Linux

- Respetar exactamente los nombres de archivos, clases, controladores, modelos y vistas.
- No cambiar `sigpaf_DB` por `sigpaf_db` en la configuración de conexión.
- Verificar que Apache y PHP tengan permiso de lectura sobre el proyecto.
- Habilitar las extensiones PHP necesarias para PDF y Excel.
- Mantener la zona horaria configurada en `America/Caracas`.

---

<!-- INICIO DE LA FIRMA DE AUTORÍA PROTEGIDA DE SIGPAF -->

# Firma de autoría

Los siguientes estudiantes declaran y firman como **autores y desarrolladores originales del sistema SIGPAF**:

| Autor | Cédula de identidad | Condición |
|---|---:|---|
| **Rodríguez Guimar** | **18.547.662** | **Firmado — Autor** |
| **Torres Fraiber** | **24.772.541** | **Firmado — Autor** |
| **Salazar Luis** | **27.166.779** | **Firmado — Autor** |
| **Tovar Anthony** | **28.182.555** | **Firmado — Autor** |
| **Cordido Luis** | **28.259.414** | **Firmado — Autor** |

## Asesor del proyecto

| Asesor | Cédula de identidad | Título profesional | Condición |
|---|---:|---|---|
| **Josué Ordóñez** | **27.529.570** | **Ingeniero en Informática** | **Firmado — Asesor** |

**Fecha de firma:** 21 de noviembre de 2026.

**Proyecto:** SIGPAF — FUNDACITE Yaracuy.

## Protección de la autoría y derechos reservados

**© 2026 Rodríguez Guimar, Torres Fraiber, Salazar Luis, Tovar Anthony y Cordido Luis. Todos los derechos reservados.**

La firma de autoría forma parte integral de este proyecto. Queda prohibido eliminar, alterar, sustituir o atribuir a otras personas la autoría del sistema, de este documento o de sus componentes sin la autorización previa y escrita de **todos los autores**.

El reconocimiento del asesor **Josué Ordóñez, C.I. 27.529.570, Ingeniero en Informática**, también forma parte de esta declaración y no podrá ser eliminado ni modificado sin su consentimiento y el de los autores.

Este proyecto y su documentación son de **uso privado e institucional**. No se concede autorización para copiar, modificar, distribuir, comercializar o presentar el sistema como propio sin el consentimiento previo y escrito de los autores.

<!-- FIN DE LA FIRMA DE AUTORÍA PROTEGIDA DE SIGPAF -->
