-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-10-2026 a las 03:39:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `helpdesk_unicon`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adjuntos`
--

CREATE TABLE `adjuntos` (
  `id_adjunto` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(30) NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria_incidencias`
--

CREATE TABLE `categoria_incidencias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_prioridad_default` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria_incidencias`
--

INSERT INTO `categoria_incidencias` (`id_categoria`, `nombre`, `descripcion`, `id_prioridad_default`, `estado`) VALUES
(1, 'Hardware', 'Fallas físicas de equipos (PC, impresoras, periféricos)', 3, 'activo'),
(2, 'Software', 'Instalación, errores o configuración de programas', 2, 'activo'),
(3, 'Redes', 'Conectividad, internet, red interna', 3, 'activo'),
(4, 'Cuentas de Usuario', 'Accesos, contraseñas, permisos', 2, 'activo'),
(5, 'Otro', 'Cualquier otra solicitud no clasificada', 1, 'activo'),
(6, 'orueba', '22', NULL, 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comentarios`
--

CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `comentarios`
--

INSERT INTO `comentarios` (`id_comentario`, `id_ticket`, `id_usuario`, `comentario`, `fecha_comentario`) VALUES
(1, 2, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-05-23 09:31:32'),
(2, 3, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-05-30 07:31:32'),
(3, 4, 13, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-06-07 10:31:32'),
(4, 5, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-06-20 08:31:32'),
(5, 6, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-06-28 06:31:32'),
(6, 7, 13, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-07-12 09:31:32'),
(7, 8, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-07-20 05:31:32'),
(8, 9, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-08-02 11:31:32'),
(9, 10, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-08-13 08:31:32'),
(10, 11, 13, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-08-27 10:31:32'),
(11, 12, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-09-07 07:31:32'),
(12, 13, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-09-15 09:31:32'),
(13, 14, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-09-24 06:31:32'),
(14, 15, 13, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-01 10:31:32'),
(15, 16, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-06 08:31:32'),
(16, 17, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-09 11:31:32'),
(17, 18, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-12 09:31:32'),
(18, 19, 14, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-14 10:31:32'),
(19, 20, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-15 07:31:32'),
(20, 21, 13, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-16 11:31:32'),
(21, 22, 2, 'Recibido. Ya estoy revisando el caso, te aviso cualquier novedad.', '2026-10-17 10:31:32'),
(32, 18, 2, 'Quedo a la espera de tu confirmación para continuar con la atención.', '2026-10-12 11:51:32'),
(33, 19, 9, 'Gracias, quedo atento a cualquier novedad.', '2026-10-14 10:51:32'),
(34, 20, 12, 'Gracias, quedo atento a cualquier novedad.', '2026-10-15 07:51:32'),
(35, 21, 11, 'Gracias, quedo atento a cualquier novedad.', '2026-10-16 11:51:32'),
(36, 22, 3, 'Gracias, quedo atento a cualquier novedad.', '2026-10-17 10:51:32'),
(40, 2, 9, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-05-23 15:41:32'),
(41, 3, 12, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-05-30 22:21:32'),
(42, 4, 11, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-06-07 11:56:32'),
(43, 5, 3, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-06-20 18:21:32'),
(44, 6, 10, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-06-30 10:01:32'),
(45, 7, 7, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-07-12 16:21:32'),
(46, 8, 9, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-07-21 03:01:32'),
(47, 9, 12, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-08-03 15:41:32'),
(48, 10, 11, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-08-14 09:21:32'),
(49, 11, 10, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-08-27 13:41:32'),
(50, 12, 3, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-09-07 09:11:32'),
(51, 13, 9, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-09-15 22:21:32'),
(52, 14, 12, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-09-24 15:41:32'),
(53, 15, 7, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-10-01 15:21:32'),
(54, 16, 11, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-10-06 14:11:32'),
(55, 17, 10, 'Confirmado, el problema quedó resuelto. Muchas gracias por la ayuda.', '2026-10-10 17:21:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo','clausurado') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Tecnología de la Información', 'Soporte técnico y sistemas', 'activo'),
(3, 'Contabilidad', 'Finanzas y contabilidad', 'activo'),
(4, 'Recursos Humanos', 'Gestión del personal', 'activo'),
(5, 'Manufactura', 'Producción y planta', 'activo'),
(6, 'Logística', 'Almacén y distribución', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estados_ticket`
--

CREATE TABLE `estados_ticket` (
  `id_estado_ticket` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `estados_ticket`
--

INSERT INTO `estados_ticket` (`id_estado_ticket`, `nombre`, `descripcion`) VALUES
(1, 'Abierto', NULL),
(2, 'En Proceso', 'Un técnico tomó el ticket y está trabajando en él'),
(3, 'Pendiente', 'Esperando respuesta o información del solicitante'),
(4, 'Resuelto', 'El problema fue solucionado'),
(5, 'Cerrado', 'Ticket cerrado y archivado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_ticket`
--

CREATE TABLE `historial_ticket` (
  `id_historial` int(11) NOT NULL,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `campo_modificado` varchar(100) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text NOT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_ticket`
--

INSERT INTO `historial_ticket` (`id_historial`, `id_ticket`, `id_usuario`, `campo_modificado`, `valor_anterior`, `valor_nuevo`, `fecha_cambio`) VALUES
(1, 1, 2, 'Asignación', 'Sin asignar', 'Asignado a tecnico', '2026-09-07 06:14:35'),
(2, 2, 2, 'Estado', 'Abierto', 'En Proceso', '2026-05-23 09:11:32'),
(3, 3, 14, 'Estado', 'Abierto', 'En Proceso', '2026-05-30 07:11:32'),
(4, 4, 13, 'Estado', 'Abierto', 'En Proceso', '2026-06-07 10:11:32'),
(5, 5, 2, 'Estado', 'Abierto', 'En Proceso', '2026-06-20 08:11:32'),
(6, 6, 14, 'Estado', 'Abierto', 'En Proceso', '2026-06-28 06:11:32'),
(7, 7, 13, 'Estado', 'Abierto', 'En Proceso', '2026-07-12 09:11:32'),
(8, 8, 2, 'Estado', 'Abierto', 'En Proceso', '2026-07-20 05:11:32'),
(9, 9, 14, 'Estado', 'Abierto', 'En Proceso', '2026-08-02 11:11:32'),
(10, 10, 2, 'Estado', 'Abierto', 'En Proceso', '2026-08-13 08:11:32'),
(11, 11, 13, 'Estado', 'Abierto', 'En Proceso', '2026-08-27 10:11:32'),
(12, 12, 2, 'Estado', 'Abierto', 'En Proceso', '2026-09-07 07:11:32'),
(13, 13, 14, 'Estado', 'Abierto', 'En Proceso', '2026-09-15 09:11:32'),
(14, 14, 2, 'Estado', 'Abierto', 'En Proceso', '2026-09-24 06:11:32'),
(15, 15, 13, 'Estado', 'Abierto', 'En Proceso', '2026-10-01 10:11:32'),
(16, 16, 14, 'Estado', 'Abierto', 'En Proceso', '2026-10-06 08:11:32'),
(17, 17, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-09 11:11:32'),
(18, 18, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-12 09:11:32'),
(19, 19, 14, 'Estado', 'Abierto', 'En Proceso', '2026-10-14 10:11:32'),
(20, 20, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-15 07:11:32'),
(21, 21, 13, 'Estado', 'Abierto', 'En Proceso', '2026-10-16 11:11:32'),
(22, 22, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-17 10:11:32'),
(33, 18, 2, 'Estado', 'En Proceso', 'Pendiente', '2026-10-12 11:51:32'),
(34, 2, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-05-23 15:11:32'),
(35, 3, 14, 'Estado', 'En Proceso', 'Resuelto', '2026-05-30 21:51:32'),
(36, 4, 13, 'Estado', 'En Proceso', 'Resuelto', '2026-06-07 11:26:32'),
(37, 5, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-06-20 17:51:32'),
(38, 6, 14, 'Estado', 'En Proceso', 'Resuelto', '2026-06-30 09:31:32'),
(39, 7, 13, 'Estado', 'En Proceso', 'Resuelto', '2026-07-12 15:51:32'),
(40, 8, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-07-21 02:31:32'),
(41, 9, 14, 'Estado', 'En Proceso', 'Resuelto', '2026-08-03 15:11:32'),
(42, 10, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-08-14 08:51:32'),
(43, 11, 13, 'Estado', 'En Proceso', 'Resuelto', '2026-08-27 13:11:32'),
(44, 12, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-09-07 08:41:32'),
(45, 13, 14, 'Estado', 'En Proceso', 'Resuelto', '2026-09-15 21:51:32'),
(46, 14, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-09-24 15:11:32'),
(47, 15, 13, 'Estado', 'En Proceso', 'Resuelto', '2026-10-01 14:51:32'),
(48, 16, 14, 'Estado', 'En Proceso', 'Resuelto', '2026-10-06 13:41:32'),
(49, 17, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-10-10 16:51:32'),
(65, 2, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-05-24 15:11:32'),
(66, 3, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-05-31 21:51:32'),
(67, 4, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-06-08 11:26:32'),
(68, 5, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-06-21 17:51:32'),
(69, 6, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-07-01 09:31:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventario_tecnologico`
--

CREATE TABLE `inventario_tecnologico` (
  `id_inventario` int(11) NOT NULL,
  `codigo_equipo` varchar(30) NOT NULL,
  `tipo_equipo` varchar(80) NOT NULL,
  `marca` varchar(80) NOT NULL,
  `modelo` varchar(80) NOT NULL,
  `serial` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo','mantenimiento') NOT NULL DEFAULT 'activo',
  `ubicacion` varchar(150) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `inventario_tecnologico`
--

INSERT INTO `inventario_tecnologico` (`id_inventario`, `codigo_equipo`, `tipo_equipo`, `marca`, `modelo`, `serial`, `estado`, `ubicacion`, `id_departamento`) VALUES
(1, 'DEMO-PC-CONT-01', 'PC de escritorio', 'Dell', 'OptiPlex 3080', 'DEMO-SN-0001', 'activo', 'Oficina de Contabilidad', 3),
(2, 'DEMO-LAP-LOG-01', 'Laptop', 'HP', 'ProBook 450 G8', 'DEMO-SN-0004', 'activo', 'Almacén central', 6),
(3, 'DEMO-PC-MANU-01', 'PC industrial', 'Lenovo', 'ThinkCentre M70', 'DEMO-SN-0003', 'activo', 'Planta - Línea 2', 5),
(4, 'DEMO-IMP-RRHH-01', 'Impresora', 'HP', 'LaserJet Pro M404', 'DEMO-SN-0002', 'activo', 'Recepción de RRHH', 4),
(5, 'DEMO-SRV-TI-01', 'Servidor', 'Dell', 'PowerEdge T140', 'DEMO-SN-0006', 'activo', 'Cuarto de servidores', 1),
(6, 'DEMO-PC-TI-01', 'PC de escritorio', 'Dell', 'OptiPlex 7090', 'DEMO-SN-0005', 'activo', 'Oficina de TI', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `mensaje` varchar(255) NOT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prioridades`
--

CREATE TABLE `prioridades` (
  `id_prioridad` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `nivel` int(11) NOT NULL DEFAULT 2,
  `tiempo_respuesta_objetivo` int(11) NOT NULL DEFAULT 2880 COMMENT 'Guardar minutos'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `prioridades`
--

INSERT INTO `prioridades` (`id_prioridad`, `nombre`, `nivel`, `tiempo_respuesta_objetivo`) VALUES
(1, 'Baja', 1, 2880),
(2, 'Media', 2, 1440),
(3, 'Alta', 3, 480),
(4, 'Crítica', 4, 120);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recuperaciones`
--

CREATE TABLE `recuperaciones` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `recuperaciones`
--

INSERT INTO `recuperaciones` (`id`, `id_usuario`, `token`, `fecha_expiracion`, `usado`) VALUES
(1, 1, '6c3ce3ec7cd93adb371d432dee25260f424b9c48a459fa03e01bf9ef8fc0d794', '2026-07-15 00:46:25', 0),
(7, 7, 'ed4c1cda159600e80a4d7df8f0b9cf33f6ca73d8db2a6e540bf4f2b7a80ebd6d', '2026-07-15 01:03:31', 1),
(8, 6, '92e19ee8d2ffc4158c4d3a04e75705f1170fda32820fbe905a2c5332d436a036', '2026-07-15 07:14:33', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_acceso` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `nivel_acceso`) VALUES
(1, 'Usuario', 'Creación y seguimiento de tickets', 1),
(2, 'Tecnico', 'Gestión y resolución de tickets', 2),
(3, 'Administrador', 'Acceso total al sistema', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tickets`
--

CREATE TABLE `tickets` (
  `id_tickets` int(11) NOT NULL,
  `codigo_ticket` varchar(20) NOT NULL,
  `asunto` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_cierre` datetime DEFAULT NULL,
  `id_usuario_solicitante` int(11) NOT NULL,
  `id_usuario_asignado` int(11) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_prioridad` int(11) NOT NULL DEFAULT 2,
  `id_estado` int(11) NOT NULL DEFAULT 1,
  `id_inventario` int(11) DEFAULT NULL,
  `solucion` text DEFAULT NULL,
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tickets`
--

INSERT INTO `tickets` (`id_tickets`, `codigo_ticket`, `asunto`, `descripcion`, `fecha_creacion`, `fecha_cierre`, `id_usuario_solicitante`, `id_usuario_asignado`, `id_departamento`, `id_categoria`, `id_prioridad`, `id_estado`, `id_inventario`, `solucion`, `observaciones`) VALUES
(1, 'TK-2026-0001', 'No pernde la pc', 'ni idea', '2026-09-07 04:36:14', NULL, 3, 2, 1, 1, 3, 2, NULL, NULL, NULL),
(2, 'TK-2026-2.00', 'La PC de contabilidad no enciende', 'Al presionar el botón de encendido no hay ninguna señal en el equipo. Se necesita para cerrar la quincena.', '2026-05-23 08:51:32', '2026-05-23 15:11:32', 9, 2, 3, 1, 3, 5, 1, 'Se reemplazó la fuente de poder defectuosa y se verificó el arranque del equipo.', '[DEMO]'),
(3, 'TK-2026-3.00', 'Error al abrir el sistema de nómina', 'El sistema muestra un error de conexión a la base de datos cada vez que intento ingresar.', '2026-05-30 06:51:32', '2026-05-30 21:51:32', 12, 14, 4, 2, 2, 5, NULL, 'Se reinstaló el cliente y se restauró el acceso a la base de datos de nómina.', '[DEMO]'),
(4, 'TK-2026-4.00', 'Sin conexión de red en la línea de producción', 'Los equipos de la línea 2 perdieron la conexión con el servidor de producción.', '2026-06-07 09:51:32', '2026-06-07 11:26:32', 11, 13, 5, 3, 4, 5, NULL, 'Se reinició el switch principal y se reemplazó un cable de red dañado.', '[DEMO]'),
(5, 'TK-2026-5.00', 'Solicitud de restablecimiento de contraseña', 'Olvidé mi contraseña y no puedo ingresar al sistema.', '2026-06-20 07:51:32', '2026-06-20 17:51:32', 3, 2, 1, 4, 2, 5, NULL, 'Se restableció la contraseña y se verificó el acceso del usuario.', '[DEMO]'),
(6, 'TK-2026-6.00', 'Mouse y teclado sin responder', 'El mouse y el teclado de la laptop dejaron de funcionar de un día para otro.', '2026-06-28 05:51:32', '2026-06-30 09:31:32', 10, 14, 6, 1, 1, 5, 2, 'Se reemplazaron los periféricos por un kit nuevo.', '[DEMO]'),
(7, 'TK-2026-7.00', 'Excel se cierra al abrir archivos grandes', 'Cada vez que abro un archivo de más de 20 MB el programa se cierra sin avisar.', '2026-07-12 08:51:32', '2026-07-12 15:51:32', 7, 13, 1, 2, 3, 4, 6, 'Se reparó la instalación de Office y se desactivaron los complementos conflictivos.', '[DEMO]'),
(8, 'TK-2026-8.00', 'Internet lento en el área de contabilidad', 'La navegación y las descargas están muy lentas desde hace varios días.', '2026-07-20 04:51:32', '2026-07-21 02:31:32', 9, 2, 3, 3, 2, 4, NULL, 'Se reubicó el punto de acceso y se ajustó el canal inalámbrico.', '[DEMO]'),
(9, 'TK-2026-9.00', 'Impresora con atascos frecuentes', 'La impresora de la oficina se atasca con casi cada trabajo que se envía.', '2026-08-02 10:51:32', '2026-08-03 15:11:32', 12, 14, 4, 1, 2, 4, 4, 'Se limpió el rodillo de arrastre y se reemplazó el kit de mantenimiento.', '[DEMO]'),
(10, 'TK-2026-10.0', 'Solicitud de instalación de software de diseño', 'Se requiere instalar el programa de diseño para el área de ingeniería.', '2026-08-13 07:51:32', '2026-08-14 08:51:32', 11, 2, 5, 5, 1, 4, NULL, 'Se instaló y licenció el software solicitado.', '[DEMO]'),
(11, 'TK-2026-11.0', 'Usuario bloqueado en el sistema SAP', 'Después de varios intentos fallidos el sistema bloqueó mi usuario.', '2026-08-27 09:51:32', '2026-08-27 13:11:32', 10, 13, 6, 4, 3, 4, NULL, 'Se desbloqueó la cuenta y se reforzó la política de contraseñas.', '[DEMO]'),
(12, 'TK-2026-12.0', 'Servidor de archivos sin respuesta', 'Nadie puede acceder a las carpetas compartidas desde esta mañana.', '2026-09-07 06:51:32', '2026-09-07 08:41:32', 3, 2, 1, 1, 4, 4, 5, 'Se reinició el servicio y se liberó espacio en el volumen principal.', '[DEMO]'),
(13, 'TK-2026-13.0', 'Error al exportar reportes contables', 'Al exportar el balance a Excel el sistema muestra un mensaje de error.', '2026-09-15 08:51:32', '2026-09-15 21:51:32', 9, 14, 3, 2, 2, 4, 1, 'Se actualizó el módulo de exportación a la última versión.', '[DEMO]'),
(14, 'TK-2026-14.0', 'Sin acceso a la VPN desde casa', 'No puedo conectarme a la red de la empresa de forma remota.', '2026-09-24 05:51:32', '2026-09-24 15:11:32', 12, 2, 4, 3, 3, 4, NULL, 'Se regeneró el certificado VPN y se validó el acceso remoto.', '[DEMO]'),
(15, 'TK-2026-15.0', 'Alta de nuevo usuario en el correo corporativo', 'Ingresó un nuevo compañero al departamento y necesita su cuenta de correo.', '2026-10-01 09:51:32', '2026-10-01 14:51:32', 7, 13, 1, 4, 2, 4, NULL, 'Se creó el buzón y se configuraron los grupos de distribución.', '[DEMO]'),
(16, 'TK-2026-16.0', 'Monitor sin señal en estación de trabajo', 'El monitor se queda en negro aunque el equipo está encendido.', '2026-10-06 07:51:32', '2026-10-06 13:41:32', 11, 14, 5, 1, 3, 4, 3, 'Se sustituyó el cable de video y se probó el monitor.', '[DEMO]'),
(17, 'TK-2026-17.0', 'Actualización de antivirus pendiente', 'El antivirus muestra un aviso de que sus definiciones están desactualizadas.', '2026-10-09 10:51:32', '2026-10-10 16:51:32', 10, 2, 6, 2, 1, 4, 2, 'Se actualizó el antivirus y se programó el escaneo semanal.', '[DEMO]'),
(18, 'TK-2026-18.0', 'Cable de red dañado en oficina', 'El cable que conecta mi equipo al punto de red está cortado y la conexión es intermitente.', '2026-10-12 08:51:32', NULL, 3, 2, 1, 3, 3, 3, NULL, NULL, '[DEMO]'),
(19, 'TK-2026-19.0', 'Falla total de la PC del área de facturación', 'El equipo no arranca y el área no puede emitir facturas.', '2026-10-14 09:51:32', NULL, 9, 14, 3, 1, 4, 2, 1, NULL, '[DEMO]'),
(20, 'TK-2026-20.0', 'El sistema de nómina no carga el reporte mensual', 'Al generar el reporte del mes la pantalla se queda cargando indefinidamente.', '2026-10-15 06:51:32', NULL, 12, 2, 4, 2, 2, 2, NULL, NULL, '[DEMO]'),
(21, 'TK-2026-21.0', 'Caída del enlace en planta de producción', 'Se perdió la comunicación entre la planta y las oficinas administrativas.', '2026-10-16 10:51:32', NULL, 11, 13, 5, 3, 4, 2, NULL, NULL, '[DEMO]'),
(22, 'TK-2026-22.0', 'No puedo iniciar sesión en el sistema de inventario', 'El sistema indica que mis credenciales son incorrectas aunque no las he cambiado.', '2026-10-17 09:51:32', NULL, 3, 2, 1, 4, 2, 2, NULL, NULL, '[DEMO]'),
(23, 'TK-2026-23.0', 'Necesito acceso a la carpeta compartida de proyectos', 'Solicito permisos de lectura y escritura sobre la carpeta de proyectos del área.', '2026-10-17 11:51:32', NULL, 7, NULL, 1, 4, 2, 1, NULL, NULL, '[DEMO]'),
(24, 'TK-2026-24.0', 'Solicitud de mouse nuevo', 'El mouse actual tiene el clic izquierdo dañado y falla constantemente.', '2026-10-18 06:51:32', NULL, 3, NULL, 1, 1, 1, 1, 6, NULL, '[DEMO]'),
(25, 'TK-2026-25.0', 'Sin internet en todo el almacén', 'Ningún equipo del almacén tiene acceso a internet ni a la red interna.', '2026-10-18 10:51:32', NULL, 10, NULL, 6, 3, 4, 1, NULL, NULL, '[DEMO]');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `usuario_login` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL DEFAULT 1,
  `estado` enum('activo','inactivo','reposo','pasante','vacaciones','suspendido') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombres`, `apellidos`, `correo`, `usuario_login`, `contraseña`, `telefono`, `id_departamento`, `id_rol`, `estado`) VALUES
(1, 'Administrador', 'Sistema', 'admin@unicon.com', 'admin', '$2y$10$F3nU5rVsnJ9w/G8eqLFqle7paWN0t.zF0W1DUJFLhcqdXkWMu4FHu', NULL, 1, 3, 'activo'),
(2, 'Tecnico', 'Informatica', 'tecnico@unicon.com', 'tecnico', '$2y$10$NIBG0mQlqW0/P/T7lR2wzOAUOGaWk0eTcHoTj.9IUPPIj1Ib3Ht.S', NULL, 1, 2, 'activo'),
(3, 'Usuario', 'Externo', 'usuario@unicon.com', 'usuario', '$2y$10$RepCaFpZOXxUdOyBqvskTuBXbEJ9/NROaX2G2zNrJFX0waGDPBuhq', '2', 1, 1, 'activo'),
(6, 'Jhoander', 'Externo', 'admin@unisscon.com', 'adn', '$2y$10$gGKoq5z9qbF4J/vi5tlR6.K2tj2Pn92blZXC7XzAannNlFOJJIkFG', NULL, 1, 3, 'activo'),
(7, 'Leandro', 'Monterrey', 'leandroargenis@gmail.com', 'leandroar', '$2y$10$0dFAmyPonsGGlflqU6cL9ucTk9PZ/poG8w.F/.oYA/BqBIW9a2y2S', NULL, 1, 1, 'activo'),
(8, 'penecio', 'prueba', '123@gmail.com', 'pruebauno', '$2y$10$niRRRHuUREhzO9qKPlI/luF.St3K.B5gPictWA9a/9VKVhlgo2pl2', NULL, 1, 2, 'activo'),
(9, 'María', 'Rodríguez', 'demo.mrodriguez@unicon.com', 'demo_user1', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 3, 1, 'activo'),
(10, 'Rosa', 'Díaz', 'demo.rdiaz@unicon.com', 'demo_user4', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 6, 1, 'activo'),
(11, 'Luis', 'Hernández', 'demo.lhernandez@unicon.com', 'demo_user3', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 5, 1, 'activo'),
(12, 'José', 'Gómez', 'demo.jgomez@unicon.com', 'demo_user2', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 4, 1, 'activo'),
(13, 'Ana', 'Pérez', 'demo.aperez@unicon.com', 'demo_tec2', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 1, 2, 'activo'),
(14, 'Carlos', 'Méndez', 'demo.cmendez@unicon.com', 'demo_tec1', '$2y$10$1BGJKIKpIsWmAMLEqsKO8uac/xFpajN8NtMe98G0T7caodwJ0hxXy', NULL, 1, 2, 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  ADD PRIMARY KEY (`id_adjunto`),
  ADD KEY `id_ticket` (`id_ticket`);

--
-- Indices de la tabla `categoria_incidencias`
--
ALTER TABLE `categoria_incidencias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `fk_categoria_prioridad` (`id_prioridad_default`);

--
-- Indices de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD PRIMARY KEY (`id_comentario`),
  ADD KEY `fk_comentario_usuario` (`id_usuario`),
  ADD KEY `fk_comentario_ticket` (`id_ticket`);

--
-- Indices de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  ADD PRIMARY KEY (`id_estado_ticket`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `historial_ticket`
--
ALTER TABLE `historial_ticket`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_historial_usuario` (`id_usuario`),
  ADD KEY `fk_historial_ticket` (`id_ticket`);

--
-- Indices de la tabla `inventario_tecnologico`
--
ALTER TABLE `inventario_tecnologico`
  ADD PRIMARY KEY (`id_inventario`),
  ADD UNIQUE KEY `codigo_equipo` (`codigo_equipo`),
  ADD UNIQUE KEY `serial` (`serial`),
  ADD KEY `id_departamento` (`id_departamento`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_noti_usuario` (`id_usuario`),
  ADD KEY `fk_noti_ticket` (`id_ticket`);

--
-- Indices de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  ADD PRIMARY KEY (`id_prioridad`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `recuperaciones`
--
ALTER TABLE `recuperaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `fk_rec_usuario` (`id_usuario`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD PRIMARY KEY (`id_tickets`),
  ADD UNIQUE KEY `codigo_ticket` (`codigo_ticket`),
  ADD KEY `fecha_creacion` (`fecha_creacion`),
  ADD KEY `fk_ticket_categoria` (`id_categoria`),
  ADD KEY `fk_ticket_usuario_asignado` (`id_usuario_asignado`),
  ADD KEY `fk_ticket_usuario_solicitante` (`id_usuario_solicitante`),
  ADD KEY `fk_ticket_prioridad` (`id_prioridad`),
  ADD KEY `fk_ticket_departamento` (`id_departamento`),
  ADD KEY `id_inventario` (`id_inventario`),
  ADD KEY `id_estado` (`id_estado`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `usuario_login` (`usuario_login`),
  ADD KEY `id_departamento` (`id_departamento`),
  ADD KEY `id_rol` (`id_rol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  MODIFY `id_adjunto` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria_incidencias`
--
ALTER TABLE `categoria_incidencias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=56;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  MODIFY `id_estado_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_ticket`
--
ALTER TABLE `historial_ticket`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de la tabla `inventario_tecnologico`
--
ALTER TABLE `inventario_tecnologico`
  MODIFY `id_inventario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  MODIFY `id_prioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `recuperaciones`
--
ALTER TABLE `recuperaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_tickets` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  ADD CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `categoria_incidencias`
--
ALTER TABLE `categoria_incidencias`
  ADD CONSTRAINT `fk_categoria_prioridad` FOREIGN KEY (`id_prioridad_default`) REFERENCES `prioridades` (`id_prioridad`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `comentarios`
--
ALTER TABLE `comentarios`
  ADD CONSTRAINT `fk_comentario_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `historial_ticket`
--
ALTER TABLE `historial_ticket`
  ADD CONSTRAINT `fk_historial_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `inventario_tecnologico`
--
ALTER TABLE `inventario_tecnologico`
  ADD CONSTRAINT `fk_inventario_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_noti_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_noti_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `recuperaciones`
--
ALTER TABLE `recuperaciones`
  ADD CONSTRAINT `fk_rec_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `fk_ticket_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_incidencias` (`id_categoria`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_ticket` (`id_estado_ticket`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_inventario` FOREIGN KEY (`id_inventario`) REFERENCES `inventario_tecnologico` (`id_inventario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_prioridad` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_usuario_asignado` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ticket_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
