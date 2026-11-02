-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 02-11-2026 a las 03:35:46
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
  `id_comentario` int(11) DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(30) NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `adjuntos`
--

INSERT INTO `adjuntos` (`id_adjunto`, `id_ticket`, `id_comentario`, `nombre_archivo`, `ruta_archivo`, `tipo_archivo`, `fecha_subida`) VALUES
(1, 26, NULL, 'captura-20261026215810.png', '2026/f7a438d679c36daad220f882061634cb.dat', 'png', '2026-10-26 17:58:14'),
(2, 27, 58, '3.jpg', '2026/75eb159c5f5a829c9511b0ec6db9a3c6.dat', 'jpg', '2026-11-01 09:17:18');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bitacora`
--

CREATE TABLE `bitacora` (
  `id_bitacora` bigint(20) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `usuario_login` varchar(50) NOT NULL DEFAULT 'sistema',
  `id_rol` int(11) DEFAULT NULL,
  `accion` varchar(20) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, BACKUP, EXPORT, IMPORT, RESTORE, ...',
  `tabla_afectada` varchar(64) DEFAULT NULL,
  `id_registro` varchar(64) DEFAULT NULL,
  `descripcion` varchar(500) NOT NULL,
  `datos_anteriores` longtext DEFAULT NULL,
  `datos_nuevos` longtext DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `bitacora`
--

INSERT INTO `bitacora` (`id_bitacora`, `id_usuario`, `usuario_login`, `id_rol`, `accion`, `tabla_afectada`, `id_registro`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `ip`, `user_agent`, `fecha`) VALUES
(1, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 08:14:07'),
(2, 1, 'admin', 3, 'DELETE', 'usuarios', '6', 'Eliminó al usuario adn', '{\"id_usuario\":6,\"nombres\":\"Jhoander\",\"apellidos\":\"Externo\",\"correo\":\"admin@unisscon.com\",\"usuario_login\":\"adn\",\"contraseña\":\"[OCULTO]\",\"telefono\":null,\"id_departamento\":1,\"id_rol\":3,\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 08:16:02'),
(3, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:38:46'),
(4, 1, 'admin', 3, 'EXPORT', NULL, NULL, 'Descargó los adjuntos de tickets (adjuntos_20261031_013912.zip, 3 archivos)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:39:13'),
(5, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:50:33'),
(6, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:50:56'),
(7, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:51:18'),
(8, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:51:26'),
(9, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:51:29'),
(10, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:51:33'),
(11, 1, 'admin', 3, 'UPDATE', 'usuarios', '7', 'Editó al usuario leandroar', '{\"id_usuario\":7,\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"contraseña\":\"[OCULTO]\",\"telefono\":null,\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '{\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"telefono\":\"12\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:51:44'),
(12, 1, 'admin', 3, 'DELETE', 'categoria_incidencias', '6', 'Eliminó la categoría orueba', '{\"id_categoria\":6,\"nombre\":\"orueba\",\"descripcion\":\"22\",\"id_prioridad_default\":null,\"estado\":\"inactivo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:53:27'),
(13, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 20:55:24'),
(14, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:08:18'),
(15, 1, 'admin', 3, 'UPDATE', 'tickets', '1', 'Cambió el estado del ticket #1: En Proceso → Resuelto', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Resuelto\",\"solucion\":\"Se enchufo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:09:03'),
(16, 1, 'admin', 3, 'DELETE', 'departamentos', '6', 'Eliminó el departamento Logística', '{\"id_departamento\":6,\"nombre\":\"Logística\",\"descripcion\":\"Almacén y distribución\",\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:09:37'),
(17, 1, 'admin', 3, 'BACKUP', NULL, NULL, 'Creó el respaldo respaldo_20261031_021003.sql (14 tablas, 48 filas)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:10:03'),
(18, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:12:36'),
(19, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-30 21:12:41'),
(20, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:42:39'),
(21, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"tickets.crear_para_otros\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:44:45'),
(22, 1, 'admin', 3, 'INSERT', 'tickets', '27', 'Creó el ticket TK-2026-0003', NULL, '{\"codigo_ticket\":\"TK-2026-0003\",\"asunto\":\"Holis\",\"id_categoria\":4,\"id_prioridad\":2,\"id_usuario_solicitante\":3,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:46:27'),
(23, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:46:34'),
(24, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:46:41'),
(25, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:46:56'),
(26, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:47:01'),
(27, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:47:36'),
(28, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:47:48'),
(29, 3, 'usuario', 1, 'INSERT', 'tickets', '28', 'Creó el ticket TK-2026-0004', NULL, '{\"codigo_ticket\":\"TK-2026-0004\",\"asunto\":\"Probar Notificaciones\",\"id_categoria\":2,\"id_prioridad\":2,\"id_usuario_solicitante\":3,\"creado_por\":3,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:48:17'),
(30, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:48:39'),
(31, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Abierto → Pendiente', '{\"estado\":\"Abierto\"}', '{\"estado\":\"Pendiente\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:48:55'),
(32, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Asignó el ticket #28 a Tecnico Informatica', '{\"tecnico\":\"Sin asignar\",\"id_estado\":3}', '{\"tecnico\":\"Tecnico Informatica\",\"id_usuario_asignado\":2,\"id_estado\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:01'),
(33, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:07'),
(34, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:12'),
(35, 2, 'tecnico', 2, 'INSERT', 'comentarios', '56', 'Comentó en el ticket #28', NULL, '{\"id_ticket\":28,\"comentario\":\"Sirven\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:28'),
(36, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:30'),
(37, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:37'),
(38, 3, 'usuario', 1, 'INSERT', 'comentarios', '57', 'Comentó en el ticket #28', NULL, '{\"id_ticket\":28,\"comentario\":\"Gracias\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:49'),
(39, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:52'),
(40, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:49:59'),
(41, 2, 'tecnico', 2, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Pendiente → En Proceso', '{\"estado\":\"Pendiente\"}', '{\"estado\":\"En Proceso\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:50:14'),
(42, 2, 'tecnico', 2, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: En Proceso → Resuelto', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Resuelto\",\"solucion\":\"Intentando\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:50:33'),
(43, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:50:41'),
(44, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:50:45'),
(45, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Resuelto → Cerrado', '{\"estado\":\"Resuelto\"}', '{\"estado\":\"Cerrado\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:50:59'),
(46, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:52:50'),
(47, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:52:53'),
(48, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:53:04'),
(49, 2, 'tecnico', 2, 'UPDATE', 'tickets', '26', 'Tomó el ticket #26 (sin asignar → Tecnico Informatica)', '{\"id_usuario_asignado\":null,\"id_estado\":1}', '{\"id_usuario_asignado\":2,\"id_estado\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:54:34'),
(50, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:54:53'),
(51, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:54:57'),
(52, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 06:56:45'),
(53, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 11:49:09'),
(54, 1, 'admin', 3, 'INSERT', 'tickets', '29', 'Creó el ticket TK-2026-0005', NULL, '{\"codigo_ticket\":\"TK-2026-0005\",\"asunto\":\"No pernde la pc\",\"id_categoria\":3,\"id_prioridad\":3,\"id_usuario_solicitante\":1,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 11:50:20'),
(55, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 11:50:25'),
(56, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-10-31 11:50:30'),
(57, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:50:35'),
(58, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:51:07'),
(59, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:51:47'),
(60, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:52:01'),
(61, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:52:03'),
(62, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:52:09'),
(63, 2, 'tecnico', 2, 'UPDATE', 'tickets', '29', 'Tomó el ticket #29 (sin asignar → Tecnico Informatica)', '{\"id_usuario_asignado\":null,\"id_estado\":1}', '{\"id_usuario_asignado\":2,\"id_estado\":2}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:52:24'),
(64, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', '2026-10-31 11:52:28'),
(65, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 08:47:14'),
(66, 1, 'admin', 3, 'BACKUP', NULL, NULL, 'Creó el respaldo respaldo_20261101_134744.sql (15 tablas, 130 filas)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 08:47:44'),
(67, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 08:48:50'),
(68, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:03:58'),
(69, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:04:19'),
(70, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:05:09'),
(71, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:13:52'),
(72, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:13:56'),
(73, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:15:51'),
(74, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:16:06'),
(75, 1, 'admin', 3, 'INSERT', 'comentarios', '58', 'Comentó en el ticket #27 (con 1 archivo(s))', NULL, '{\"id_ticket\":27,\"comentario\":\"as\",\"adjuntos\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:17:18'),
(76, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:17:45'),
(77, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:17:49'),
(78, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:18:48'),
(79, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:18:54'),
(80, 1, 'admin', 3, 'INSERT', 'inventario_tecnologico', '7', 'Registró el equipo PC-CONT-001', NULL, '{\"codigo_equipo\":\"PC-CONT-001\",\"tipo_equipo\":\"PC de escritorio\",\"marca\":\"Dell\",\"modelo\":\"OptiPlex 3090\",\"serial\":\"SN987654321\",\"estado\":\"activo\",\"ubicacion\":\"Piso 2, oficina de Contabilidad\",\"id_departamento\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:20:40'),
(81, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:26:10'),
(82, 1, 'admin', 3, 'INSERT', 'tickets', '30', 'Creó el ticket TK-2026-0006', NULL, '{\"codigo_ticket\":\"TK-2026-0006\",\"asunto\":\"asd\",\"id_categoria\":3,\"id_prioridad\":3,\"id_usuario_solicitante\":1,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:27:21'),
(83, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.mantenimiento\",\"inventario.ver\",\"kb.editar\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\",\"kb.editar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:28:09'),
(84, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"kb.editar\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\",\"inventario.mantenimiento\",\"kb.editar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:28:13'),
(85, 1, 'admin', 3, 'UPDATE', 'categoria_incidencias', '5', 'Cambió el estado de la categoría Otro', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"activo\"}', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"inactivo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:28:22'),
(86, 1, 'admin', 3, 'UPDATE', 'categoria_incidencias', '5', 'Cambió el estado de la categoría Otro', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"inactivo\"}', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:28:25'),
(87, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:30:33'),
(88, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:38:01'),
(89, 1, 'admin', 3, 'INSERT', 'kb_articulos', '1', 'Creó el artículo de ayuda «', NULL, '{\"titulo\":\"Guía rápida para la solución de problemas de red y Wi-Fi\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:39:44'),
(90, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:40:53'),
(91, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:40:58'),
(92, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:41:09'),
(93, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:43:04'),
(94, 1, 'admin', 3, 'INSERT', 'kb_articulos', '2', 'Creó el artículo de ayuda «', NULL, '{\"titulo\":\"prbando\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:45:08'),
(95, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:57:14'),
(96, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:57:19'),
(97, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:58:00'),
(98, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:58:04'),
(99, 1, 'admin', 3, 'DELETE', 'usuarios', '7', 'Eliminó al usuario leandroar', '{\"id_usuario\":7,\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"contraseña\":\"[OCULTO]\",\"telefono\":\"12\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 09:58:55'),
(100, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:01:39'),
(101, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:01:43'),
(102, 1, 'admin', 3, 'UPDATE', 'usuarios', '3', 'Editó al usuario usuario', '{\"id_usuario\":3,\"nombres\":\"Usuario\",\"apellidos\":\"Externo\",\"correo\":\"usuario@unicon.com\",\"usuario_login\":\"usuario\",\"contraseña\":\"[OCULTO]\",\"telefono\":\"2\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '{\"nombres\":\"Usuario\",\"apellidos\":\"Externo\",\"correo\":\"usuario@unicon.com\",\"usuario_login\":\"usuario\",\"telefono\":\"22\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:01:50'),
(103, 1, 'admin', 3, 'INSERT', 'kb_articulos', '3', 'Creó el artículo de ayuda «prueba 2»', NULL, '{\"titulo\":\"prueba 2\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:04:57'),
(104, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:07:02'),
(105, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 10:07:06'),
(106, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:28:07'),
(107, 1, 'admin', 3, 'INSERT', 'departamentos', '7', 'Creó el departamento Tecnología de la Información 2', NULL, '{\"nombre\":\"Tecnología de la Información 2\",\"descripcion\":\"jol\",\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:29:04'),
(108, 1, 'admin', 3, 'DELETE', 'departamentos', '7', 'Eliminó el departamento Tecnología de la Información 2', '{\"id_departamento\":7,\"nombre\":\"Tecnología de la Información 2\",\"descripcion\":\"jol\",\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:29:12'),
(109, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:33:29'),
(110, NULL, 'sistema', NULL, 'LOGIN', 'usuarios', '1', 'Cuenta bloqueada 15 min por intentos fallidos: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:34:01'),
(111, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '2', 'Solicitud de restablecimiento de contraseña: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:37:57'),
(112, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '3', 'Solicitud de restablecimiento de contraseña: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:45:40'),
(113, NULL, 'sistema', NULL, 'INSERT', 'usuarios', '16', 'Nuevo registro de usuario: Andersito (auto-registro)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:46:45'),
(114, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '16', 'Solicitud de restablecimiento de contraseña: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:46:57'),
(115, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '16', 'Solicitud de restablecimiento de contraseña: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:49:29'),
(116, NULL, 'sistema', NULL, 'UPDATE', 'usuarios', '16', 'Restableció su contraseña mediante enlace de recuperación', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:52:27'),
(117, 16, 'Andersito', 1, 'LOGIN', 'usuarios', '16', 'Inició sesión: andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:52:45'),
(118, 16, 'Andersito', 1, 'LOGOUT', 'usuarios', '16', 'Cerró sesión: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 11:52:49'),
(119, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 21:25:57'),
(120, 1, 'admin', 3, 'INSERT', 'usuarios', '17', 'Creó al usuario juanci', NULL, '{\"nombres\":\"Juan\",\"apellidos\":\"Alvarado\",\"correo\":\"juan_unicon@gmail.com\",\"usuario_login\":\"juanci\",\"id_rol\":1,\"id_departamento\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 21:27:06'),
(121, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 22:18:50'),
(122, 1, 'admin', 3, 'INSERT', 'inventario_tecnologico', '8', 'Registró el equipo PC-CONT-002', NULL, '{\"codigo_equipo\":\"PC-CONT-002\",\"tipo_equipo\":\"PC de escritorio\",\"marca\":\"HP\",\"modelo\":\"ProBook 640 g2\",\"serial\":\"Desconocido\",\"estado\":\"activo\",\"ubicacion\":\"Piso 2, oficina de TI\",\"id_departamento\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 22:19:28'),
(123, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 22:19:41'),
(124, NULL, 'sistema', NULL, 'LOGIN', 'usuarios', '1', 'Cuenta bloqueada 60 s por intentos fallidos: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 22:26:45'),
(125, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', '2026-11-01 22:29:28');

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
(5, 'Otro', 'Cualquier otra solicitud no clasificada', 1, 'activo');

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
(56, 28, 2, 'Sirven', '2026-10-31 06:49:28'),
(57, 28, 3, 'Gracias', '2026-10-31 06:49:49'),
(58, 27, 1, 'as', '2026-11-01 09:17:18');

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
(5, 'Manufactura', 'Producción y planta', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuestas_ticket`
--

CREATE TABLE `encuestas_ticket` (
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(70, 1, 1, 'Estado', 'En Proceso', 'Resuelto', '2026-10-30 21:09:03'),
(71, 28, 1, 'Estado', 'Abierto', 'Pendiente', '2026-10-31 06:48:55'),
(72, 28, 1, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica', '2026-10-31 06:49:01'),
(73, 28, 2, 'Estado', 'Pendiente', 'En Proceso', '2026-10-31 06:50:14'),
(74, 28, 2, 'Estado', 'En Proceso', 'Resuelto', '2026-10-31 06:50:33'),
(75, 28, 1, 'Estado', 'Resuelto', 'Cerrado', '2026-10-31 06:50:59'),
(76, 26, 2, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica (lo tomó él mismo)', '2026-10-31 06:54:34'),
(77, 26, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-31 06:54:34'),
(78, 29, 2, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica (lo tomó él mismo)', '2026-10-31 11:52:24'),
(79, 29, 2, 'Estado', 'Abierto', 'En Proceso', '2026-10-31 11:52:24');

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
(7, 'PC-CONT-001', 'PC de escritorio', 'Dell', 'OptiPlex 3090', 'SN987654321', 'activo', 'Piso 2, oficina de Contabilidad', 3),
(8, 'PC-CONT-002', 'PC de escritorio', 'HP', 'ProBook 640 g2', 'Desconocido', 'activo', 'Piso 2, oficina de TI', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kb_articulos`
--

CREATE TABLE `kb_articulos` (
  `id_articulo` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `contenido` mediumtext NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT 1,
  `vistas` int(11) NOT NULL DEFAULT 0,
  `id_autor` int(11) DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `kb_articulos`
--

INSERT INTO `kb_articulos` (`id_articulo`, `titulo`, `contenido`, `id_categoria`, `publicado`, `vistas`, `id_autor`, `creado`, `actualizado`) VALUES
(1, 'Guía rápida para la solución de problemas de red y Wi-Fi', 'Paso 1: Verifique que el cable UTP esté correctamente conectado al puerto LAN del equipo y al switch o router.\r\n\r\nPaso 2: Compruebe la asignación de la dirección IP por DHCP o revise la configuración estática de la tarjeta de red.\r\n\r\nSi el problema persiste, puede consultar la documentación oficial de redes y los estándares de direccionamiento IP en la siguiente guía técnica: https://www.cisco.com/c/es_mx/support/index.html', 3, 1, 3, 1, '2026-11-01 09:39:44', '2026-11-01 10:04:44'),
(2, 'prbando', 'paso 1\r\npaso 2\r\npaso 3\r\npaso 4\r\npaso 5\r\npaso 6', NULL, 1, 0, 1, '2026-11-01 09:45:08', '2026-11-01 09:45:08'),
(3, 'prueba 2', 'Paso 1: Verifique que el cable UTP esté correctamente conectado al puerto LAN del equipo y al switch o router.\r\n\r\nPaso 2: Compruebe la asignación de la dirección IP por DHCP o revise la configuración estática de la tarjeta de red.\r\n\r\nSi el problema persiste, puede consultar la documentación oficial de redes y los estándares de direccionamiento IP en la siguiente guía técnica: https://www.cisco.com/c/es_mx/support/index.htm', NULL, 1, 0, 1, '2026-11-01 10:04:57', '2026-11-01 10:04:57');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_intentos`
--

CREATE TABLE `login_intentos` (
  `id` bigint(20) NOT NULL,
  `usuario_login` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mantenimientos_equipo`
--

CREATE TABLE `mantenimientos_equipo` (
  `id_mantenimiento` int(11) NOT NULL,
  `id_inventario` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo` enum('preventivo','correctivo','actualizacion','otro') NOT NULL DEFAULT 'preventivo',
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `proximo` date DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL DEFAULT 'ticket',
  `mensaje` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `id_ticket`, `tipo`, `mensaje`, `url`, `leida`, `fecha`) VALUES
(1, 3, 27, 'ticket', 'Se creó el ticket TK-2026-0003 a tu nombre: Holis', NULL, 0, '2026-10-31 06:46:27'),
(2, 1, 28, 'ticket', 'Nuevo ticket TK-2026-0004 de Usuario Externo: Probar Notificaciones — sin asignar', NULL, 1, '2026-10-31 06:48:17'),
(3, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 quedó pendiente: se necesita tu respuesta o más información.', NULL, 0, '2026-10-31 06:48:55'),
(4, 2, 28, 'ticket', 'Se te asignó el ticket TK-2026-0004: Probar Notificaciones', NULL, 1, '2026-10-31 06:49:01'),
(5, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue asignado a Tecnico Informatica y ya está en atención.', NULL, 0, '2026-10-31 06:49:01'),
(6, 3, 28, 'ticket', 'Tecnico Informatica comentó en TK-2026-0004: «Sirven»', NULL, 1, '2026-10-31 06:49:28'),
(7, 2, 28, 'ticket', 'Usuario Externo comentó en TK-2026-0004: «Gracias»', NULL, 0, '2026-10-31 06:49:49'),
(8, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 está en proceso.', NULL, 0, '2026-10-31 06:50:14'),
(9, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue resuelto. Revisa la solución en el detalle del ticket.', NULL, 0, '2026-10-31 06:50:33'),
(10, 1, 28, 'ticket', 'Tecnico Informatica resolvió el ticket TK-2026-0004 (listo para cerrar).', NULL, 1, '2026-10-31 06:50:33'),
(11, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue cerrado.', NULL, 0, '2026-10-31 06:50:59'),
(12, 2, 28, 'ticket', 'El administrador cambió el estado de TK-2026-0004 a «.', NULL, 0, '2026-10-31 06:50:59'),
(13, 1, 26, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0002: crear', NULL, 1, '2026-10-31 06:54:34'),
(14, 3, 26, 'ticket', 'Tu ticket TK-2026-0002 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 0, '2026-10-31 06:54:34'),
(15, 1, 29, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0005: No pernde la pc', NULL, 1, '2026-10-31 11:52:24'),
(16, 1, 29, 'ticket', 'Tu ticket TK-2026-0005 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 1, '2026-10-31 11:52:24'),
(17, 1, 26, 'sla', '[SLA VENCIDO] El ticket TK-2026-0002 (Alta) superó su tiempo objetivo de 8 h.', NULL, 1, '2026-11-01 09:13:56'),
(18, 2, 26, 'sla', '[SLA VENCIDO] El ticket TK-2026-0002 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, '2026-11-01 09:13:56'),
(19, 1, 27, 'sla', '[SLA VENCIDO] El ticket TK-2026-0003 (Media) superó su tiempo objetivo de 1 d.', NULL, 1, '2026-11-01 09:13:56'),
(20, 1, 29, 'sla', '[SLA VENCIDO] El ticket TK-2026-0005 (Alta) superó su tiempo objetivo de 8 h.', NULL, 1, '2026-11-01 09:13:56'),
(21, 2, 29, 'sla', '[SLA VENCIDO] El ticket TK-2026-0005 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, '2026-11-01 09:13:56'),
(22, 3, 27, 'ticket', 'Administrador Sistema comentó en TK-2026-0003: «as»', NULL, 0, '2026-11-01 09:17:18'),
(23, 1, NULL, 'usuario', 'Nuevo usuario registrado: Jhoander Adrian (@Andersito). Revisa su rol y departamento.', 'admin/procesos/usuarios.php?q=Andersito', 0, '2026-11-01 11:46:45'),
(24, 1, 30, 'sla', '[SLA VENCIDO] El ticket TK-2026-0006 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, '2026-11-01 21:25:58');

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
(9, 2, 'b1c5f32a5ae50a40d3b79bb0d503c501c05d95e271e84464f25f85f23e45c8c1', '2026-11-01 12:37:57', 0),
(10, 3, 'f54c8fa77ce926f62714dab11d9f065b2f5b5dd536ee2356f5a308ac6cb4515f', '2026-11-01 12:45:40', 0),
(12, 16, 'c408d6670a856992178401315dd7049ce3e683c688a22163b9c7fb31d21c4724', '2026-11-01 12:49:26', 1);

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
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id_rol` int(11) NOT NULL,
  `permiso` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id_rol`, `permiso`) VALUES
(2, 'inventario.mantenimiento'),
(2, 'inventario.ver'),
(2, 'kb.editar'),
(2, 'tickets.crear_para_otros'),
(2, 'tickets.tomar');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sistema_estado`
--

CREATE TABLE `sistema_estado` (
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL DEFAULT '',
  `actualizado` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sistema_estado`
--

INSERT INTO `sistema_estado` (`clave`, `valor`, `actualizado`) VALUES
('sla_revision', '', '2026-11-01 22:29:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sla_alertas`
--

CREATE TABLE `sla_alertas` (
  `id_ticket` int(11) NOT NULL,
  `nivel` enum('riesgo','vencido') NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sla_alertas`
--

INSERT INTO `sla_alertas` (`id_ticket`, `nivel`, `fecha`) VALUES
(26, 'vencido', '2026-11-01 09:13:56'),
(27, 'vencido', '2026-11-01 09:13:56'),
(29, 'vencido', '2026-11-01 09:13:56'),
(30, 'vencido', '2026-11-01 21:25:58');

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
(1, 'TK-2026-0001', 'No pernde la pc', 'ni idea', '2026-09-07 04:36:14', '2026-10-30 21:09:03', 3, 2, 1, 1, 3, 4, NULL, 'Se enchufo', NULL),
(26, 'TK-2026-0002', 'crear', 'uno dos tres', '2026-10-26 17:58:14', NULL, 3, 2, 1, 1, 3, 2, NULL, NULL, NULL),
(27, 'TK-2026-0003', 'Holis', 'Holis', '2026-10-31 06:46:27', NULL, 3, NULL, 1, 4, 2, 1, NULL, NULL, NULL),
(28, 'TK-2026-0004', 'Probar Notificaciones', '1', '2026-10-31 06:48:17', '2026-10-31 06:50:33', 3, 2, 1, 2, 2, 5, NULL, 'Intentando', NULL),
(29, 'TK-2026-0005', 'No pernde la pc', '1213', '2026-10-31 11:50:20', NULL, 1, 2, 1, 3, 3, 2, NULL, NULL, NULL),
(30, 'TK-2026-0006', 'asd', 'prueba', '2026-11-01 09:27:21', NULL, 1, NULL, 1, 3, 3, 1, NULL, NULL, NULL);

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
(3, 'Usuario', 'Externo', 'usuario@unicon.com', 'usuario', '$2y$10$RepCaFpZOXxUdOyBqvskTuBXbEJ9/NROaX2G2zNrJFX0waGDPBuhq', '22', 1, 1, 'activo'),
(16, 'Jhoander', 'Adrian', 'JHOANDERALEJANDROADRIANMARCANO@GMAIL.COM', 'Andersito', '$2y$10$1hWjQbs8dtIYZEcbXeABbeMDZwX1KqCD0nemp8I589KapBo70DJ/C', NULL, 1, 1, 'activo'),
(17, 'Juan', 'Alvarado', 'juan_unicon@gmail.com', 'juanci', '$2y$10$cAix/vDzeQcn3pbkY07abeSUULkFdp3hacD2yQCXRIXjjXWPRMuS.', NULL, 1, 1, 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adjuntos`
--
ALTER TABLE `adjuntos`
  ADD PRIMARY KEY (`id_adjunto`),
  ADD KEY `id_ticket` (`id_ticket`),
  ADD KEY `idx_adjuntos_comentario` (`id_comentario`);

--
-- Indices de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  ADD PRIMARY KEY (`id_bitacora`),
  ADD KEY `idx_bitacora_fecha` (`fecha`),
  ADD KEY `idx_bitacora_accion` (`accion`),
  ADD KEY `idx_bitacora_tabla` (`tabla_afectada`),
  ADD KEY `idx_bitacora_usuario` (`id_usuario`);

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
-- Indices de la tabla `encuestas_ticket`
--
ALTER TABLE `encuestas_ticket`
  ADD PRIMARY KEY (`id_ticket`);

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
-- Indices de la tabla `kb_articulos`
--
ALTER TABLE `kb_articulos`
  ADD PRIMARY KEY (`id_articulo`),
  ADD KEY `idx_kb_publicado` (`publicado`,`actualizado`);

--
-- Indices de la tabla `login_intentos`
--
ALTER TABLE `login_intentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_login_usuario_ip` (`usuario_login`,`ip`,`fecha`),
  ADD KEY `idx_login_ip` (`ip`,`fecha`);

--
-- Indices de la tabla `mantenimientos_equipo`
--
ALTER TABLE `mantenimientos_equipo`
  ADD PRIMARY KEY (`id_mantenimiento`),
  ADD KEY `idx_mant_equipo` (`id_inventario`,`fecha`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `fk_noti_usuario` (`id_usuario`),
  ADD KEY `fk_noti_ticket` (`id_ticket`),
  ADD KEY `idx_noti_usuario_leida` (`id_usuario`,`leida`,`fecha`);

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
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id_rol`,`permiso`);

--
-- Indices de la tabla `sistema_estado`
--
ALTER TABLE `sistema_estado`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `sla_alertas`
--
ALTER TABLE `sla_alertas`
  ADD PRIMARY KEY (`id_ticket`,`nivel`);

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
  MODIFY `id_adjunto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `bitacora`
--
ALTER TABLE `bitacora`
  MODIFY `id_bitacora` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=126;

--
-- AUTO_INCREMENT de la tabla `categoria_incidencias`
--
ALTER TABLE `categoria_incidencias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `comentarios`
--
ALTER TABLE `comentarios`
  MODIFY `id_comentario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT de la tabla `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `estados_ticket`
--
ALTER TABLE `estados_ticket`
  MODIFY `id_estado_ticket` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `historial_ticket`
--
ALTER TABLE `historial_ticket`
  MODIFY `id_historial` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT de la tabla `inventario_tecnologico`
--
ALTER TABLE `inventario_tecnologico`
  MODIFY `id_inventario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `kb_articulos`
--
ALTER TABLE `kb_articulos`
  MODIFY `id_articulo` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `login_intentos`
--
ALTER TABLE `login_intentos`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `mantenimientos_equipo`
--
ALTER TABLE `mantenimientos_equipo`
  MODIFY `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `prioridades`
--
ALTER TABLE `prioridades`
  MODIFY `id_prioridad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `recuperaciones`
--
ALTER TABLE `recuperaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tickets`
--
ALTER TABLE `tickets`
  MODIFY `id_tickets` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

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
-- Filtros para la tabla `encuestas_ticket`
--
ALTER TABLE `encuestas_ticket`
  ADD CONSTRAINT `fk_enc_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `mantenimientos_equipo`
--
ALTER TABLE `mantenimientos_equipo`
  ADD CONSTRAINT `fk_mant_equipo` FOREIGN KEY (`id_inventario`) REFERENCES `inventario_tecnologico` (`id_inventario`) ON DELETE CASCADE ON UPDATE CASCADE;

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
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `fk_rolperm_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sla_alertas`
--
ALTER TABLE `sla_alertas`
  ADD CONSTRAINT `fk_sla_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE;

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
