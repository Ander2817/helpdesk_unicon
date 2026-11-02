-- Respaldo HelpDesk Unicon
-- Base de datos: `helpdesk_unicon`
-- Generado: 2026-11-01 13:47:44

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
SET UNIQUE_CHECKS = 0;

-- --------------------------------------------------------
-- Tabla `adjuntos`

DROP TABLE IF EXISTS `adjuntos`;
CREATE TABLE `adjuntos` (
  `id_adjunto` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(30) NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_adjunto`),
  KEY `id_ticket` (`id_ticket`),
  CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `adjuntos` (`id_adjunto`, `id_ticket`, `nombre_archivo`, `ruta_archivo`, `tipo_archivo`, `fecha_subida`) VALUES
(1, 26, 'captura-20261026215810.png', '2026/f7a438d679c36daad220f882061634cb.dat', 'png', 0x323032362d31302d32362031373a35383a3134);

-- --------------------------------------------------------
-- Tabla `bitacora`

DROP TABLE IF EXISTS `bitacora`;
CREATE TABLE `bitacora` (
  `id_bitacora` bigint(20) NOT NULL AUTO_INCREMENT,
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
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bitacora_fecha` (`fecha`),
  KEY `idx_bitacora_accion` (`accion`),
  KEY `idx_bitacora_tabla` (`tabla_afectada`),
  KEY `idx_bitacora_usuario` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `bitacora` (`id_bitacora`, `id_usuario`, `usuario_login`, `id_rol`, `accion`, `tabla_afectada`, `id_registro`, `descripcion`, `datos_anteriores`, `datos_nuevos`, `ip`, `user_agent`, `fecha`) VALUES
(1, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302030383a31343a3037),
(2, 1, 'admin', 3, 'DELETE', 'usuarios', '6', 'Eliminó al usuario adn', '{\"id_usuario\":6,\"nombres\":\"Jhoander\",\"apellidos\":\"Externo\",\"correo\":\"admin@unisscon.com\",\"usuario_login\":\"adn\",\"contraseña\":\"[OCULTO]\",\"telefono\":null,\"id_departamento\":1,\"id_rol\":3,\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302030383a31363a3032),
(3, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a33383a3436),
(4, 1, 'admin', 3, 'EXPORT', NULL, NULL, 'Descargó los adjuntos de tickets (adjuntos_20261031_013912.zip, 3 archivos)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a33393a3133),
(5, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35303a3333),
(6, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35303a3536),
(7, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35313a3138),
(8, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35313a3236),
(9, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35313a3239),
(10, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35313a3333),
(11, 1, 'admin', 3, 'UPDATE', 'usuarios', '7', 'Editó al usuario leandroar', '{\"id_usuario\":7,\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"contraseña\":\"[OCULTO]\",\"telefono\":null,\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '{\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"telefono\":\"12\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35313a3434),
(12, 1, 'admin', 3, 'DELETE', 'categoria_incidencias', '6', 'Eliminó la categoría orueba', '{\"id_categoria\":6,\"nombre\":\"orueba\",\"descripcion\":\"22\",\"id_prioridad_default\":null,\"estado\":\"inactivo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35333a3237),
(13, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032303a35353a3234),
(14, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a30383a3138),
(15, 1, 'admin', 3, 'UPDATE', 'tickets', '1', 'Cambió el estado del ticket #1: En Proceso → Resuelto', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Resuelto\",\"solucion\":\"Se enchufo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a30393a3033),
(16, 1, 'admin', 3, 'DELETE', 'departamentos', '6', 'Eliminó el departamento Logística', '{\"id_departamento\":6,\"nombre\":\"Logística\",\"descripcion\":\"Almacén y distribución\",\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a30393a3337),
(17, 1, 'admin', 3, 'BACKUP', NULL, NULL, 'Creó el respaldo respaldo_20261031_021003.sql (14 tablas, 48 filas)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a31303a3033),
(18, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a31323a3336),
(19, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33302032313a31323a3431),
(20, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34323a3339),
(21, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"tickets.crear_para_otros\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34343a3435),
(22, 1, 'admin', 3, 'INSERT', 'tickets', '27', 'Creó el ticket TK-2026-0003', NULL, '{\"codigo_ticket\":\"TK-2026-0003\",\"asunto\":\"Holis\",\"id_categoria\":4,\"id_prioridad\":2,\"id_usuario_solicitante\":3,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34363a3237),
(23, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34363a3334),
(24, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34363a3431),
(25, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34363a3536),
(26, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34373a3031),
(27, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34373a3336),
(28, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34373a3438),
(29, 3, 'usuario', 1, 'INSERT', 'tickets', '28', 'Creó el ticket TK-2026-0004', NULL, '{\"codigo_ticket\":\"TK-2026-0004\",\"asunto\":\"Probar Notificaciones\",\"id_categoria\":2,\"id_prioridad\":2,\"id_usuario_solicitante\":3,\"creado_por\":3,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34383a3137),
(30, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34383a3339),
(31, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Abierto → Pendiente', '{\"estado\":\"Abierto\"}', '{\"estado\":\"Pendiente\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34383a3535),
(32, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Asignó el ticket #28 a Tecnico Informatica', '{\"tecnico\":\"Sin asignar\",\"id_estado\":3}', '{\"tecnico\":\"Tecnico Informatica\",\"id_usuario_asignado\":2,\"id_estado\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3031),
(33, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3037),
(34, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3132),
(35, 2, 'tecnico', 2, 'INSERT', 'comentarios', '56', 'Comentó en el ticket #28', NULL, '{\"id_ticket\":28,\"comentario\":\"Sirven\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3238),
(36, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3330),
(37, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3337),
(38, 3, 'usuario', 1, 'INSERT', 'comentarios', '57', 'Comentó en el ticket #28', NULL, '{\"id_ticket\":28,\"comentario\":\"Gracias\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3439),
(39, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3532),
(40, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a34393a3539),
(41, 2, 'tecnico', 2, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Pendiente → En Proceso', '{\"estado\":\"Pendiente\"}', '{\"estado\":\"En Proceso\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35303a3134),
(42, 2, 'tecnico', 2, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: En Proceso → Resuelto', '{\"estado\":\"En Proceso\"}', '{\"estado\":\"Resuelto\",\"solucion\":\"Intentando\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35303a3333),
(43, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35303a3431),
(44, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35303a3435),
(45, 1, 'admin', 3, 'UPDATE', 'tickets', '28', 'Cambió el estado del ticket #28: Resuelto → Cerrado', '{\"estado\":\"Resuelto\"}', '{\"estado\":\"Cerrado\",\"solucion\":null}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35303a3539),
(46, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35323a3530),
(47, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35323a3533),
(48, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35333a3034),
(49, 2, 'tecnico', 2, 'UPDATE', 'tickets', '26', 'Tomó el ticket #26 (sin asignar → Tecnico Informatica)', '{\"id_usuario_asignado\":null,\"id_estado\":1}', '{\"id_usuario_asignado\":2,\"id_estado\":2}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35343a3334),
(50, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35343a3533),
(51, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35343a3537),
(52, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312030363a35363a3435),
(53, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312031313a34393a3039),
(54, 1, 'admin', 3, 'INSERT', 'tickets', '29', 'Creó el ticket TK-2026-0005', NULL, '{\"codigo_ticket\":\"TK-2026-0005\",\"asunto\":\"No pernde la pc\",\"id_categoria\":3,\"id_prioridad\":3,\"id_usuario_solicitante\":1,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312031313a35303a3230),
(55, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312031313a35303a3235),
(56, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31302d33312031313a35303a3330),
(57, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35303a3335),
(58, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35313a3037),
(59, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35313a3437),
(60, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35323a3031),
(61, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35323a3033),
(62, 2, 'tecnico', 2, 'LOGIN', 'usuarios', '2', 'Inició sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35323a3039),
(63, 2, 'tecnico', 2, 'UPDATE', 'tickets', '29', 'Tomó el ticket #29 (sin asignar → Tecnico Informatica)', '{\"id_usuario_asignado\":null,\"id_estado\":1}', '{\"id_usuario_asignado\":2,\"id_estado\":2}', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35323a3234),
(64, 2, 'tecnico', 2, 'LOGOUT', 'usuarios', '2', 'Cerró sesión: tecnico', NULL, NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:159.0) Gecko/20100101 Firefox/159.0', 0x323032362d31302d33312031313a35323a3238),
(65, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030383a34373a3134);

-- --------------------------------------------------------
-- Tabla `categoria_incidencias`

DROP TABLE IF EXISTS `categoria_incidencias`;
CREATE TABLE `categoria_incidencias` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(80) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `id_prioridad_default` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_categoria`),
  UNIQUE KEY `nombre` (`nombre`),
  KEY `fk_categoria_prioridad` (`id_prioridad_default`),
  CONSTRAINT `fk_categoria_prioridad` FOREIGN KEY (`id_prioridad_default`) REFERENCES `prioridades` (`id_prioridad`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `categoria_incidencias` (`id_categoria`, `nombre`, `descripcion`, `id_prioridad_default`, `estado`) VALUES
(1, 'Hardware', 'Fallas físicas de equipos (PC, impresoras, periféricos)', 3, 'activo'),
(2, 'Software', 'Instalación, errores o configuración de programas', 2, 'activo'),
(3, 'Redes', 'Conectividad, internet, red interna', 3, 'activo'),
(4, 'Cuentas de Usuario', 'Accesos, contraseñas, permisos', 2, 'activo'),
(5, 'Otro', 'Cualquier otra solicitud no clasificada', 1, 'activo');

-- --------------------------------------------------------
-- Tabla `comentarios`

DROP TABLE IF EXISTS `comentarios`;
CREATE TABLE `comentarios` (
  `id_comentario` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `comentario` text NOT NULL,
  `fecha_comentario` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_comentario`),
  KEY `fk_comentario_usuario` (`id_usuario`),
  KEY `fk_comentario_ticket` (`id_ticket`),
  CONSTRAINT `fk_comentario_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_comentario_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=58 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comentarios` (`id_comentario`, `id_ticket`, `id_usuario`, `comentario`, `fecha_comentario`) VALUES
(56, 28, 2, 'Sirven', 0x323032362d31302d33312030363a34393a3238),
(57, 28, 3, 'Gracias', 0x323032362d31302d33312030363a34393a3439);

-- --------------------------------------------------------
-- Tabla `departamentos`

DROP TABLE IF EXISTS `departamentos`;
CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo','clausurado') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_departamento`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departamentos` (`id_departamento`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Tecnología de la Información', 'Soporte técnico y sistemas', 'activo'),
(3, 'Contabilidad', 'Finanzas y contabilidad', 'activo'),
(4, 'Recursos Humanos', 'Gestión del personal', 'activo'),
(5, 'Manufactura', 'Producción y planta', 'activo');

-- --------------------------------------------------------
-- Tabla `estados_ticket`

DROP TABLE IF EXISTS `estados_ticket`;
CREATE TABLE `estados_ticket` (
  `id_estado_ticket` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  PRIMARY KEY (`id_estado_ticket`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `estados_ticket` (`id_estado_ticket`, `nombre`, `descripcion`) VALUES
(1, 'Abierto', NULL),
(2, 'En Proceso', 'Un técnico tomó el ticket y está trabajando en él'),
(3, 'Pendiente', 'Esperando respuesta o información del solicitante'),
(4, 'Resuelto', 'El problema fue solucionado'),
(5, 'Cerrado', 'Ticket cerrado y archivado');

-- --------------------------------------------------------
-- Tabla `historial_ticket`

DROP TABLE IF EXISTS `historial_ticket`;
CREATE TABLE `historial_ticket` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `campo_modificado` varchar(100) NOT NULL,
  `valor_anterior` text DEFAULT NULL,
  `valor_nuevo` text NOT NULL,
  `fecha_cambio` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_historial`),
  KEY `fk_historial_usuario` (`id_usuario`),
  KEY `fk_historial_ticket` (`id_ticket`),
  CONSTRAINT `fk_historial_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `historial_ticket` (`id_historial`, `id_ticket`, `id_usuario`, `campo_modificado`, `valor_anterior`, `valor_nuevo`, `fecha_cambio`) VALUES
(1, 1, 2, 'Asignación', 'Sin asignar', 'Asignado a tecnico', 0x323032362d30392d30372030363a31343a3335),
(70, 1, 1, 'Estado', 'En Proceso', 'Resuelto', 0x323032362d31302d33302032313a30393a3033),
(71, 28, 1, 'Estado', 'Abierto', 'Pendiente', 0x323032362d31302d33312030363a34383a3535),
(72, 28, 1, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica', 0x323032362d31302d33312030363a34393a3031),
(73, 28, 2, 'Estado', 'Pendiente', 'En Proceso', 0x323032362d31302d33312030363a35303a3134),
(74, 28, 2, 'Estado', 'En Proceso', 'Resuelto', 0x323032362d31302d33312030363a35303a3333),
(75, 28, 1, 'Estado', 'Resuelto', 'Cerrado', 0x323032362d31302d33312030363a35303a3539),
(76, 26, 2, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica (lo tomó él mismo)', 0x323032362d31302d33312030363a35343a3334),
(77, 26, 2, 'Estado', 'Abierto', 'En Proceso', 0x323032362d31302d33312030363a35343a3334),
(78, 29, 2, 'Técnico asignado', 'Sin asignar', 'Tecnico Informatica (lo tomó él mismo)', 0x323032362d31302d33312031313a35323a3234),
(79, 29, 2, 'Estado', 'Abierto', 'En Proceso', 0x323032362d31302d33312031313a35323a3234);

-- --------------------------------------------------------
-- Tabla `inventario_tecnologico`

DROP TABLE IF EXISTS `inventario_tecnologico`;
CREATE TABLE `inventario_tecnologico` (
  `id_inventario` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_equipo` varchar(30) NOT NULL,
  `tipo_equipo` varchar(80) NOT NULL,
  `marca` varchar(80) NOT NULL,
  `modelo` varchar(80) NOT NULL,
  `serial` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo','mantenimiento') NOT NULL DEFAULT 'activo',
  `ubicacion` varchar(150) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  PRIMARY KEY (`id_inventario`),
  UNIQUE KEY `codigo_equipo` (`codigo_equipo`),
  UNIQUE KEY `serial` (`serial`),
  KEY `id_departamento` (`id_departamento`),
  CONSTRAINT `fk_inventario_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Tabla `notificaciones`

DROP TABLE IF EXISTS `notificaciones`;
CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `id_ticket` int(11) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL DEFAULT 'ticket',
  `mensaje` varchar(255) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `leida` tinyint(1) NOT NULL DEFAULT 0,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_notificacion`),
  KEY `fk_noti_usuario` (`id_usuario`),
  KEY `fk_noti_ticket` (`id_ticket`),
  KEY `idx_noti_usuario_leida` (`id_usuario`,`leida`,`fecha`),
  CONSTRAINT `fk_noti_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_noti_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `id_ticket`, `tipo`, `mensaje`, `url`, `leida`, `fecha`) VALUES
(1, 3, 27, 'ticket', 'Se creó el ticket TK-2026-0003 a tu nombre: Holis', NULL, 0, 0x323032362d31302d33312030363a34363a3237),
(2, 1, 28, 'ticket', 'Nuevo ticket TK-2026-0004 de Usuario Externo: Probar Notificaciones — sin asignar', NULL, 0, 0x323032362d31302d33312030363a34383a3137),
(3, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 quedó pendiente: se necesita tu respuesta o más información.', NULL, 0, 0x323032362d31302d33312030363a34383a3535),
(4, 2, 28, 'ticket', 'Se te asignó el ticket TK-2026-0004: Probar Notificaciones', NULL, 1, 0x323032362d31302d33312030363a34393a3031),
(5, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue asignado a Tecnico Informatica y ya está en atención.', NULL, 0, 0x323032362d31302d33312030363a34393a3031),
(6, 3, 28, 'ticket', 'Tecnico Informatica comentó en TK-2026-0004: «Sirven»', NULL, 1, 0x323032362d31302d33312030363a34393a3238),
(7, 2, 28, 'ticket', 'Usuario Externo comentó en TK-2026-0004: «Gracias»', NULL, 0, 0x323032362d31302d33312030363a34393a3439),
(8, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 está en proceso.', NULL, 0, 0x323032362d31302d33312030363a35303a3134),
(9, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue resuelto. Revisa la solución en el detalle del ticket.', NULL, 0, 0x323032362d31302d33312030363a35303a3333),
(10, 1, 28, 'ticket', 'Tecnico Informatica resolvió el ticket TK-2026-0004 (listo para cerrar).', NULL, 1, 0x323032362d31302d33312030363a35303a3333),
(11, 3, 28, 'ticket', 'Tu ticket TK-2026-0004 fue cerrado.', NULL, 0, 0x323032362d31302d33312030363a35303a3539),
(12, 2, 28, 'ticket', 'El administrador cambió el estado de TK-2026-0004 a «.', NULL, 0, 0x323032362d31302d33312030363a35303a3539),
(13, 1, 26, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0002: crear', NULL, 0, 0x323032362d31302d33312030363a35343a3334),
(14, 3, 26, 'ticket', 'Tu ticket TK-2026-0002 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 0, 0x323032362d31302d33312030363a35343a3334),
(15, 1, 29, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0005: No pernde la pc', NULL, 0, 0x323032362d31302d33312031313a35323a3234),
(16, 1, 29, 'ticket', 'Tu ticket TK-2026-0005 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 0, 0x323032362d31302d33312031313a35323a3234);

-- --------------------------------------------------------
-- Tabla `prioridades`

DROP TABLE IF EXISTS `prioridades`;
CREATE TABLE `prioridades` (
  `id_prioridad` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `nivel` int(11) NOT NULL DEFAULT 2,
  `tiempo_respuesta_objetivo` int(11) NOT NULL DEFAULT 2880 COMMENT 'Guardar minutos',
  PRIMARY KEY (`id_prioridad`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `prioridades` (`id_prioridad`, `nombre`, `nivel`, `tiempo_respuesta_objetivo`) VALUES
(1, 'Baja', 1, 2880),
(2, 'Media', 2, 1440),
(3, 'Alta', 3, 480),
(4, 'Crítica', 4, 120);

-- --------------------------------------------------------
-- Tabla `recuperaciones`

DROP TABLE IF EXISTS `recuperaciones`;
CREATE TABLE `recuperaciones` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_usuario` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `fecha_expiracion` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `fk_rec_usuario` (`id_usuario`),
  CONSTRAINT `fk_rec_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recuperaciones` (`id`, `id_usuario`, `token`, `fecha_expiracion`, `usado`) VALUES
(1, 1, '6c3ce3ec7cd93adb371d432dee25260f424b9c48a459fa03e01bf9ef8fc0d794', 0x323032362d30372d31352030303a34363a3235, 0),
(7, 7, 'ed4c1cda159600e80a4d7df8f0b9cf33f6ca73d8db2a6e540bf4f2b7a80ebd6d', 0x323032362d30372d31352030313a30333a3331, 1);

-- --------------------------------------------------------
-- Tabla `rol_permisos`

DROP TABLE IF EXISTS `rol_permisos`;
CREATE TABLE `rol_permisos` (
  `id_rol` int(11) NOT NULL,
  `permiso` varchar(60) NOT NULL,
  PRIMARY KEY (`id_rol`,`permiso`),
  CONSTRAINT `fk_rolperm_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `rol_permisos` (`id_rol`, `permiso`) VALUES
(2, 'inventario.ver'),
(2, 'tickets.crear_para_otros'),
(2, 'tickets.tomar');

-- --------------------------------------------------------
-- Tabla `roles`

DROP TABLE IF EXISTS `roles`;
CREATE TABLE `roles` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel_acceso` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id_rol`),
  UNIQUE KEY `nombre` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `roles` (`id_rol`, `nombre`, `descripcion`, `nivel_acceso`) VALUES
(1, 'Usuario', 'Creación y seguimiento de tickets', 1),
(2, 'Tecnico', 'Gestión y resolución de tickets', 2),
(3, 'Administrador', 'Acceso total al sistema', 3);

-- --------------------------------------------------------
-- Tabla `tickets`

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE `tickets` (
  `id_tickets` int(11) NOT NULL AUTO_INCREMENT,
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
  `observaciones` text DEFAULT NULL,
  PRIMARY KEY (`id_tickets`),
  UNIQUE KEY `codigo_ticket` (`codigo_ticket`),
  KEY `fecha_creacion` (`fecha_creacion`),
  KEY `fk_ticket_categoria` (`id_categoria`),
  KEY `fk_ticket_usuario_asignado` (`id_usuario_asignado`),
  KEY `fk_ticket_usuario_solicitante` (`id_usuario_solicitante`),
  KEY `fk_ticket_prioridad` (`id_prioridad`),
  KEY `fk_ticket_departamento` (`id_departamento`),
  KEY `id_inventario` (`id_inventario`),
  KEY `id_estado` (`id_estado`),
  CONSTRAINT `fk_ticket_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_incidencias` (`id_categoria`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_estado` FOREIGN KEY (`id_estado`) REFERENCES `estados_ticket` (`id_estado_ticket`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_inventario` FOREIGN KEY (`id_inventario`) REFERENCES `inventario_tecnologico` (`id_inventario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_prioridad` FOREIGN KEY (`id_prioridad`) REFERENCES `prioridades` (`id_prioridad`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_usuario_asignado` FOREIGN KEY (`id_usuario_asignado`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE,
  CONSTRAINT `fk_ticket_usuario_solicitante` FOREIGN KEY (`id_usuario_solicitante`) REFERENCES `usuarios` (`id_usuario`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tickets` (`id_tickets`, `codigo_ticket`, `asunto`, `descripcion`, `fecha_creacion`, `fecha_cierre`, `id_usuario_solicitante`, `id_usuario_asignado`, `id_departamento`, `id_categoria`, `id_prioridad`, `id_estado`, `id_inventario`, `solucion`, `observaciones`) VALUES
(1, 'TK-2026-0001', 'No pernde la pc', 'ni idea', 0x323032362d30392d30372030343a33363a3134, 0x323032362d31302d33302032313a30393a3033, 3, 2, 1, 1, 3, 4, NULL, 'Se enchufo', NULL),
(26, 'TK-2026-0002', 'crear', 'uno dos tres', 0x323032362d31302d32362031373a35383a3134, NULL, 3, 2, 1, 1, 3, 2, NULL, NULL, NULL),
(27, 'TK-2026-0003', 'Holis', 'Holis', 0x323032362d31302d33312030363a34363a3237, NULL, 3, NULL, 1, 4, 2, 1, NULL, NULL, NULL),
(28, 'TK-2026-0004', 'Probar Notificaciones', '1', 0x323032362d31302d33312030363a34383a3137, 0x323032362d31302d33312030363a35303a3333, 3, 2, 1, 2, 2, 5, NULL, 'Intentando', NULL),
(29, 'TK-2026-0005', 'No pernde la pc', '1213', 0x323032362d31302d33312031313a35303a3230, NULL, 1, 2, 1, 3, 3, 2, NULL, NULL, NULL);

-- --------------------------------------------------------
-- Tabla `usuarios`

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `usuario_login` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `id_departamento` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL DEFAULT 1,
  `estado` enum('activo','inactivo','reposo','pasante','vacaciones','suspendido') NOT NULL DEFAULT 'activo',
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `correo` (`correo`),
  UNIQUE KEY `usuario_login` (`usuario_login`),
  KEY `id_departamento` (`id_departamento`),
  KEY `id_rol` (`id_rol`),
  CONSTRAINT `fk_usuario_departamento` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`) ON UPDATE CASCADE,
  CONSTRAINT `fk_usuario_rol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id_rol`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id_usuario`, `nombres`, `apellidos`, `correo`, `usuario_login`, `contraseña`, `telefono`, `id_departamento`, `id_rol`, `estado`) VALUES
(1, 'Administrador', 'Sistema', 'admin@unicon.com', 'admin', '$2y$10$F3nU5rVsnJ9w/G8eqLFqle7paWN0t.zF0W1DUJFLhcqdXkWMu4FHu', NULL, 1, 3, 'activo'),
(2, 'Tecnico', 'Informatica', 'tecnico@unicon.com', 'tecnico', '$2y$10$NIBG0mQlqW0/P/T7lR2wzOAUOGaWk0eTcHoTj.9IUPPIj1Ib3Ht.S', NULL, 1, 2, 'activo'),
(3, 'Usuario', 'Externo', 'usuario@unicon.com', 'usuario', '$2y$10$RepCaFpZOXxUdOyBqvskTuBXbEJ9/NROaX2G2zNrJFX0waGDPBuhq', '2', 1, 1, 'activo'),
(7, 'Leandro', 'Monterrey', 'leandroargenis@gmail.com', 'leandroar', '$2y$10$0dFAmyPonsGGlflqU6cL9ucTk9PZ/poG8w.F/.oYA/BqBIW9a2y2S', '12', 1, 1, 'activo');

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
-- Fin del respaldo
