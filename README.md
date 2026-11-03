# HelpDesk Unicon

Sistema de gestión de tickets de soporte técnico para **Unicon** (La Victoria, Aragua). Centraliza las incidencias de TI: los usuarios reportan problemas, el personal técnico los atiende y la administración supervisa el rendimiento del departamento.

## Características

**Tickets**
- Creación, asignación y seguimiento por prioridad, con historial, comentarios y adjuntos.
- Semáforo de **SLA** por prioridad, con pausa mientras el ticket espera al usuario («Pendiente»).
- Calificación de la atención (1 a 5 estrellas), reapertura y autocierre de tickets resueltos.

**Inventario**
- Equipos con historial de mantenimiento y calendario de próximos mantenimientos.
- Etiquetas **QR** por equipo: al escanearlas se abre «Nuevo ticket» con el equipo elegido.

**Administración**
- Dashboards por rol (Administrador, Técnico, Usuario) y control de permisos por rol.
- Gestión de estados de usuarios, equipos y departamentos; borrado lógico (nada se elimina, se desactiva).
- Reportes de gestión en pantalla y PDF: tendencia mensual, comparación con el período anterior, SLA por prioridad, equipos con fallas repetidas y satisfacción. Exportación a CSV.
- Bitácora de auditoría, respaldo y restauración, y panel de estado del sistema.

**Asistente de IA** (opcional)
- Chat integrado por rol, con sugerencias al crear tickets y ayuda para diagnóstico y soluciones.
- Funciona con Gemini, OpenAI o Groq. Sin clave configurada responde con la base de conocimiento interna.

**Seguridad**
- Protección CSRF, límite de intentos de inicio de sesión, cierre por inactividad, cabeceras de seguridad y carpetas internas bloqueadas por `.htaccess`.

## Tecnologías

- **Backend:** PHP 8 (procedural, mysqli con consultas preparadas)
- **Base de datos:** MySQL / MariaDB (XAMPP)
- **Frontend:** HTML5, CSS3, Bootstrap 5, FontAwesome 6, SweetAlert2
- **PDF:** FPDF

## Instalación

**Requisitos:** XAMPP (Apache + MariaDB) con PHP 8 o superior.

1. Clona o copia el proyecto en `C:\xampp\htdocs\helpdesk_unicon`.
2. Inicia Apache y MySQL desde el panel de XAMPP.
3. En phpMyAdmin crea la base de datos e importa `DB/helpdesk_unicon.sql`.
4. Copia `config/conexion.ejemplo.php` a `config/conexion.php` y ajusta tus credenciales.
5. Revisa `URL_BASE` en `includes/constants.php`. Debe ser la dirección real del servidor (necesaria para los QR y los enlaces de los correos).
6. Abre `http://localhost/helpdesk_unicon/index.php`.

## Configurar el asistente de IA (opcional)

Cada integrante usa **su propia clave** (así no se agotan las cuotas del otro y nadie comparte secretos).

1. Copia `config/ia.ejemplo.php` a `config/ia.php`.
2. Pon tu clave en `api_key` y el proveedor en `proveedor` (`gemini`, `openai` o `groq`).
3. Verifica con: `php herramientas/probar_ia.php`

> `config/ia.php` está en `.gitignore`. **Nunca subas una clave al repositorio.**
> Si una clave se publicó por error, revócala y crea una nueva.

## Estructura

| Carpeta | Contenido |
|---|---|
| `admin/` · `tecnico/` · `usuario/` | Pantallas de cada rol |
| `auth/` | Inicio de sesión, registro y recuperación de clave |
| `includes/` | Funciones, SLA, IA, reportes y plantillas comunes |
| `procesos/` | Endpoints que reciben formularios y peticiones AJAX |
| `config/` | Conexión y configuración local (no se sube) |
| `DB/` | Scripts SQL de instalación y migraciones |
| `herramientas/` | Utilidades de línea de comandos |
| `uploads/` | Adjuntos de los usuarios (no se sube) |

## Trabajo en equipo (Git)

- No subas `config/ia.php`, `config/conexion.php`, `uploads/` ni copias `*.bak*`: ya están en `.gitignore`.
- Antes de subir, revisa `git status`.
- Trabaja en ramas y mezcla con `main` mediante pull request.

## Autores

Proyecto desarrollado por **Ángel Romero, Jhoander Adrian y Leandro Monterrey**.
Empresa: Unicon anteriormente (Conduven), Sede: La Victoria – Aragua.