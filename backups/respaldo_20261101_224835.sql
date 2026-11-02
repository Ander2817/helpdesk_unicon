-- Respaldo HelpDesk Unicon
-- Base de datos: `helpdesk_unicon`
-- Generado: 2026-11-01 22:48:35

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
  `id_comentario` int(11) DEFAULT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(30) NOT NULL,
  `fecha_subida` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_adjunto`),
  KEY `id_ticket` (`id_ticket`),
  KEY `idx_adjuntos_comentario` (`id_comentario`),
  CONSTRAINT `fk_adjunto_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `adjuntos` (`id_adjunto`, `id_ticket`, `id_comentario`, `nombre_archivo`, `ruta_archivo`, `tipo_archivo`, `fecha_subida`) VALUES
(1, 26, NULL, 'captura-20261026215810.png', '2026/f7a438d679c36daad220f882061634cb.dat', 'png', 0x323032362d31302d32362031373a35383a3134),
(2, 27, 58, '3.jpg', '2026/75eb159c5f5a829c9511b0ec6db9a3c6.dat', 'jpg', 0x323032362d31312d30312030393a31373a3138);

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
) ENGINE=InnoDB AUTO_INCREMENT=129 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(65, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030383a34373a3134),
(66, 1, 'admin', 3, 'BACKUP', NULL, NULL, 'Creó el respaldo respaldo_20261101_134744.sql (15 tablas, 130 filas)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030383a34373a3434),
(67, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030383a34383a3530),
(68, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a30333a3538),
(69, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a30343a3139),
(70, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a30353a3039),
(71, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31333a3532),
(72, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31333a3536),
(73, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31353a3531),
(74, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31363a3036),
(75, 1, 'admin', 3, 'INSERT', 'comentarios', '58', 'Comentó en el ticket #27 (con 1 archivo(s))', NULL, '{\"id_ticket\":27,\"comentario\":\"as\",\"adjuntos\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31373a3138),
(76, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31373a3435),
(77, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31373a3439),
(78, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31383a3438),
(79, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a31383a3534),
(80, 1, 'admin', 3, 'INSERT', 'inventario_tecnologico', '7', 'Registró el equipo PC-CONT-001', NULL, '{\"codigo_equipo\":\"PC-CONT-001\",\"tipo_equipo\":\"PC de escritorio\",\"marca\":\"Dell\",\"modelo\":\"OptiPlex 3090\",\"serial\":\"SN987654321\",\"estado\":\"activo\",\"ubicacion\":\"Piso 2, oficina de Contabilidad\",\"id_departamento\":3}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32303a3430),
(81, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32363a3130),
(82, 1, 'admin', 3, 'INSERT', 'tickets', '30', 'Creó el ticket TK-2026-0006', NULL, '{\"codigo_ticket\":\"TK-2026-0006\",\"asunto\":\"asd\",\"id_categoria\":3,\"id_prioridad\":3,\"id_usuario_solicitante\":1,\"creado_por\":1,\"id_departamento\":1,\"id_inventario\":null,\"adjuntos\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32373a3231),
(83, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.mantenimiento\",\"inventario.ver\",\"kb.editar\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\",\"kb.editar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32383a3039),
(84, 1, 'admin', 3, 'UPDATE', 'roles', '2', 'Editó el rol Tecnico', '{\"id_rol\":2,\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"kb.editar\",\"tickets.crear_para_otros\",\"tickets.tomar\"]}', '{\"nombre\":\"Tecnico\",\"descripcion\":\"Gestión y resolución de tickets\",\"nivel_acceso\":2,\"permisos\":[\"inventario.ver\",\"tickets.crear_para_otros\",\"tickets.tomar\",\"inventario.mantenimiento\",\"kb.editar\"]}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32383a3133),
(85, 1, 'admin', 3, 'UPDATE', 'categoria_incidencias', '5', 'Cambió el estado de la categoría Otro', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"activo\"}', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"inactivo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32383a3232),
(86, 1, 'admin', 3, 'UPDATE', 'categoria_incidencias', '5', 'Cambió el estado de la categoría Otro', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"inactivo\"}', '{\"id_categoria\":5,\"nombre\":\"Otro\",\"descripcion\":\"Cualquier otra solicitud no clasificada\",\"id_prioridad_default\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a32383a3235),
(87, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a33303a3333),
(88, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a33383a3031),
(89, 1, 'admin', 3, 'INSERT', 'kb_articulos', '1', 'Creó el artículo de ayuda «', NULL, '{\"titulo\":\"Guía rápida para la solución de problemas de red y Wi-Fi\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a33393a3434),
(90, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a34303a3533),
(91, 3, 'usuario', 1, 'LOGIN', 'usuarios', '3', 'Inició sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a34303a3538),
(92, 3, 'usuario', 1, 'LOGOUT', 'usuarios', '3', 'Cerró sesión: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a34313a3039),
(93, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a34333a3034),
(94, 1, 'admin', 3, 'INSERT', 'kb_articulos', '2', 'Creó el artículo de ayuda «', NULL, '{\"titulo\":\"prbando\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a34353a3038),
(95, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a35373a3134),
(96, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a35373a3139),
(97, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a35383a3030),
(98, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a35383a3034),
(99, 1, 'admin', 3, 'DELETE', 'usuarios', '7', 'Eliminó al usuario leandroar', '{\"id_usuario\":7,\"nombres\":\"Leandro\",\"apellidos\":\"Monterrey\",\"correo\":\"leandroargenis@gmail.com\",\"usuario_login\":\"leandroar\",\"contraseña\":\"[OCULTO]\",\"telefono\":\"12\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312030393a35383a3535),
(100, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30313a3339),
(101, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30313a3433),
(102, 1, 'admin', 3, 'UPDATE', 'usuarios', '3', 'Editó al usuario usuario', '{\"id_usuario\":3,\"nombres\":\"Usuario\",\"apellidos\":\"Externo\",\"correo\":\"usuario@unicon.com\",\"usuario_login\":\"usuario\",\"contraseña\":\"[OCULTO]\",\"telefono\":\"2\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '{\"nombres\":\"Usuario\",\"apellidos\":\"Externo\",\"correo\":\"usuario@unicon.com\",\"usuario_login\":\"usuario\",\"telefono\":\"22\",\"id_departamento\":1,\"id_rol\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30313a3530),
(103, 1, 'admin', 3, 'INSERT', 'kb_articulos', '3', 'Creó el artículo de ayuda «prueba 2»', NULL, '{\"titulo\":\"prueba 2\",\"publicado\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30343a3537),
(104, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30373a3032),
(105, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031303a30373a3036),
(106, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a32383a3037),
(107, 1, 'admin', 3, 'INSERT', 'departamentos', '7', 'Creó el departamento Tecnología de la Información 2', NULL, '{\"nombre\":\"Tecnología de la Información 2\",\"descripcion\":\"jol\",\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a32393a3034),
(108, 1, 'admin', 3, 'DELETE', 'departamentos', '7', 'Eliminó el departamento Tecnología de la Información 2', '{\"id_departamento\":7,\"nombre\":\"Tecnología de la Información 2\",\"descripcion\":\"jol\",\"estado\":\"activo\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a32393a3132),
(109, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a33333a3239),
(110, NULL, 'sistema', NULL, 'LOGIN', 'usuarios', '1', 'Cuenta bloqueada 15 min por intentos fallidos: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a33343a3031),
(111, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '2', 'Solicitud de restablecimiento de contraseña: tecnico', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a33373a3537),
(112, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '3', 'Solicitud de restablecimiento de contraseña: usuario', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a34353a3430),
(113, NULL, 'sistema', NULL, 'INSERT', 'usuarios', '16', 'Nuevo registro de usuario: Andersito (auto-registro)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a34363a3435),
(114, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '16', 'Solicitud de restablecimiento de contraseña: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a34363a3537),
(115, NULL, 'sistema', NULL, 'RECUPERAR', 'usuarios', '16', 'Solicitud de restablecimiento de contraseña: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a34393a3239),
(116, NULL, 'sistema', NULL, 'UPDATE', 'usuarios', '16', 'Restableció su contraseña mediante enlace de recuperación', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a35323a3237),
(117, 16, 'Andersito', 1, 'LOGIN', 'usuarios', '16', 'Inició sesión: andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a35323a3435),
(118, 16, 'Andersito', 1, 'LOGOUT', 'usuarios', '16', 'Cerró sesión: Andersito', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312031313a35323a3439),
(119, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032313a32353a3537),
(120, 1, 'admin', 3, 'INSERT', 'usuarios', '17', 'Creó al usuario juanci', NULL, '{\"nombres\":\"Juan\",\"apellidos\":\"Alvarado\",\"correo\":\"juan_unicon@gmail.com\",\"usuario_login\":\"juanci\",\"id_rol\":1,\"id_departamento\":1,\"estado\":\"activo\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032313a32373a3036),
(121, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a31383a3530),
(122, 1, 'admin', 3, 'INSERT', 'inventario_tecnologico', '8', 'Registró el equipo PC-CONT-002', NULL, '{\"codigo_equipo\":\"PC-CONT-002\",\"tipo_equipo\":\"PC de escritorio\",\"marca\":\"HP\",\"modelo\":\"ProBook 640 g2\",\"serial\":\"Desconocido\",\"estado\":\"activo\",\"ubicacion\":\"Piso 2, oficina de TI\",\"id_departamento\":1}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a31393a3238),
(123, 1, 'admin', 3, 'LOGOUT', 'usuarios', '1', 'Cerró sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a31393a3431),
(124, NULL, 'sistema', NULL, 'LOGIN', 'usuarios', '1', 'Cuenta bloqueada 60 s por intentos fallidos: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a32363a3435),
(125, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a32393a3238),
(126, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a33373a3439),
(127, 1, 'admin', 3, 'EXPORT', 'bitacora', NULL, 'Exportó la bitácora a CSV (126 registros)', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a33383a3137),
(128, 1, 'admin', 3, 'LOGIN', 'usuarios', '1', 'Inició sesión: admin', NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 0x323032362d31312d30312032323a34383a3234);

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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `comentarios` (`id_comentario`, `id_ticket`, `id_usuario`, `comentario`, `fecha_comentario`) VALUES
(56, 28, 2, 'Sirven', 0x323032362d31302d33312030363a34393a3238),
(57, 28, 3, 'Gracias', 0x323032362d31302d33312030363a34393a3439),
(58, 27, 1, 'as', 0x323032362d31312d30312030393a31373a3138);

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `departamentos` (`id_departamento`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'Tecnología de la Información', 'Soporte técnico y sistemas', 'activo'),
(3, 'Contabilidad', 'Finanzas y contabilidad', 'activo'),
(4, 'Recursos Humanos', 'Gestión del personal', 'activo'),
(5, 'Manufactura', 'Producción y planta', 'activo');

-- --------------------------------------------------------
-- Tabla `encuestas_ticket`

DROP TABLE IF EXISTS `encuestas_ticket`;
CREATE TABLE `encuestas_ticket` (
  `id_ticket` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `puntuacion` tinyint(4) NOT NULL,
  `comentario` varchar(500) DEFAULT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ticket`),
  CONSTRAINT `fk_enc_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `inventario_tecnologico` (`id_inventario`, `codigo_equipo`, `tipo_equipo`, `marca`, `modelo`, `serial`, `estado`, `ubicacion`, `id_departamento`) VALUES
(7, 'PC-CONT-001', 'PC de escritorio', 'Dell', 'OptiPlex 3090', 'SN987654321', 'activo', 'Piso 2, oficina de Contabilidad', 3),
(8, 'PC-CONT-002', 'PC de escritorio', 'HP', 'ProBook 640 g2', 'Desconocido', 'activo', 'Piso 2, oficina de TI', 1);

-- --------------------------------------------------------
-- Tabla `kb_articulos`

DROP TABLE IF EXISTS `kb_articulos`;
CREATE TABLE `kb_articulos` (
  `id_articulo` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(200) NOT NULL,
  `contenido` mediumtext NOT NULL,
  `id_categoria` int(11) DEFAULT NULL,
  `publicado` tinyint(1) NOT NULL DEFAULT 1,
  `vistas` int(11) NOT NULL DEFAULT 0,
  `id_autor` int(11) DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  `actualizado` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_articulo`),
  KEY `idx_kb_publicado` (`publicado`,`actualizado`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kb_articulos` (`id_articulo`, `titulo`, `contenido`, `id_categoria`, `publicado`, `vistas`, `id_autor`, `creado`, `actualizado`) VALUES
(1, 'Guía rápida para la solución de problemas de red y Wi-Fi', 'Paso 1: Verifique que el cable UTP esté correctamente conectado al puerto LAN del equipo y al switch o router.\r\n\r\nPaso 2: Compruebe la asignación de la dirección IP por DHCP o revise la configuración estática de la tarjeta de red.\r\n\r\nSi el problema persiste, puede consultar la documentación oficial de redes y los estándares de direccionamiento IP en la siguiente guía técnica: https://www.cisco.com/c/es_mx/support/index.html', 3, 1, 3, 1, 0x323032362d31312d30312030393a33393a3434, 0x323032362d31312d30312031303a30343a3434),
(2, 'prbando', 'paso 1\r\npaso 2\r\npaso 3\r\npaso 4\r\npaso 5\r\npaso 6', NULL, 1, 0, 1, 0x323032362d31312d30312030393a34353a3038, 0x323032362d31312d30312030393a34353a3038),
(3, 'prueba 2', 'Paso 1: Verifique que el cable UTP esté correctamente conectado al puerto LAN del equipo y al switch o router.\r\n\r\nPaso 2: Compruebe la asignación de la dirección IP por DHCP o revise la configuración estática de la tarjeta de red.\r\n\r\nSi el problema persiste, puede consultar la documentación oficial de redes y los estándares de direccionamiento IP en la siguiente guía técnica: https://www.cisco.com/c/es_mx/support/index.htm', NULL, 1, 0, 1, 0x323032362d31312d30312031303a30343a3537, 0x323032362d31312d30312031303a30343a3537);

-- --------------------------------------------------------
-- Tabla `login_intentos`

DROP TABLE IF EXISTS `login_intentos`;
CREATE TABLE `login_intentos` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `usuario_login` varchar(50) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_login_usuario_ip` (`usuario_login`,`ip`,`fecha`),
  KEY `idx_login_ip` (`ip`,`fecha`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


-- --------------------------------------------------------
-- Tabla `mantenimientos_equipo`

DROP TABLE IF EXISTS `mantenimientos_equipo`;
CREATE TABLE `mantenimientos_equipo` (
  `id_mantenimiento` int(11) NOT NULL AUTO_INCREMENT,
  `id_inventario` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `tipo` enum('preventivo','correctivo','actualizacion','otro') NOT NULL DEFAULT 'preventivo',
  `descripcion` text NOT NULL,
  `fecha` date NOT NULL,
  `proximo` date DEFAULT NULL,
  `creado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_mantenimiento`),
  KEY `idx_mant_equipo` (`id_inventario`,`fecha`),
  CONSTRAINT `fk_mant_equipo` FOREIGN KEY (`id_inventario`) REFERENCES `inventario_tecnologico` (`id_inventario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `id_ticket`, `tipo`, `mensaje`, `url`, `leida`, `fecha`) VALUES
(1, 3, 27, 'ticket', 'Se creó el ticket TK-2026-0003 a tu nombre: Holis', NULL, 0, 0x323032362d31302d33312030363a34363a3237),
(2, 1, 28, 'ticket', 'Nuevo ticket TK-2026-0004 de Usuario Externo: Probar Notificaciones — sin asignar', NULL, 1, 0x323032362d31302d33312030363a34383a3137),
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
(13, 1, 26, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0002: crear', NULL, 1, 0x323032362d31302d33312030363a35343a3334),
(14, 3, 26, 'ticket', 'Tu ticket TK-2026-0002 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 0, 0x323032362d31302d33312030363a35343a3334),
(15, 1, 29, 'ticket', 'Tecnico Informatica tomó el ticket TK-2026-0005: No pernde la pc', NULL, 1, 0x323032362d31302d33312031313a35323a3234),
(16, 1, 29, 'ticket', 'Tu ticket TK-2026-0005 fue tomado por Tecnico Informatica y ya está en atención.', NULL, 1, 0x323032362d31302d33312031313a35323a3234),
(17, 1, 26, 'sla', '[SLA VENCIDO] El ticket TK-2026-0002 (Alta) superó su tiempo objetivo de 8 h.', NULL, 1, 0x323032362d31312d30312030393a31333a3536),
(18, 2, 26, 'sla', '[SLA VENCIDO] El ticket TK-2026-0002 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, 0x323032362d31312d30312030393a31333a3536),
(19, 1, 27, 'sla', '[SLA VENCIDO] El ticket TK-2026-0003 (Media) superó su tiempo objetivo de 1 d.', NULL, 1, 0x323032362d31312d30312030393a31333a3536),
(20, 1, 29, 'sla', '[SLA VENCIDO] El ticket TK-2026-0005 (Alta) superó su tiempo objetivo de 8 h.', NULL, 1, 0x323032362d31312d30312030393a31333a3536),
(21, 2, 29, 'sla', '[SLA VENCIDO] El ticket TK-2026-0005 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, 0x323032362d31312d30312030393a31333a3536),
(22, 3, 27, 'ticket', 'Administrador Sistema comentó en TK-2026-0003: «as»', NULL, 0, 0x323032362d31312d30312030393a31373a3138),
(23, 1, NULL, 'usuario', 'Nuevo usuario registrado: Jhoander Adrian (@Andersito). Revisa su rol y departamento.', 'admin/procesos/usuarios.php?q=Andersito', 0, 0x323032362d31312d30312031313a34363a3435),
(24, 1, 30, 'sla', '[SLA VENCIDO] El ticket TK-2026-0006 (Alta) superó su tiempo objetivo de 8 h.', NULL, 0, 0x323032362d31312d30312032313a32353a3538);

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `recuperaciones` (`id`, `id_usuario`, `token`, `fecha_expiracion`, `usado`) VALUES
(1, 1, '6c3ce3ec7cd93adb371d432dee25260f424b9c48a459fa03e01bf9ef8fc0d794', 0x323032362d30372d31352030303a34363a3235, 0),
(9, 2, 'b1c5f32a5ae50a40d3b79bb0d503c501c05d95e271e84464f25f85f23e45c8c1', 0x323032362d31312d30312031323a33373a3537, 0),
(10, 3, 'f54c8fa77ce926f62714dab11d9f065b2f5b5dd536ee2356f5a308ac6cb4515f', 0x323032362d31312d30312031323a34353a3430, 0),
(12, 16, 'c408d6670a856992178401315dd7049ce3e683c688a22163b9c7fb31d21c4724', 0x323032362d31312d30312031323a34393a3236, 1);

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
(2, 'inventario.mantenimiento'),
(2, 'inventario.ver'),
(2, 'kb.editar'),
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
-- Tabla `sistema_estado`

DROP TABLE IF EXISTS `sistema_estado`;
CREATE TABLE `sistema_estado` (
  `clave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL DEFAULT '',
  `actualizado` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sistema_estado` (`clave`, `valor`, `actualizado`) VALUES
('sla_revision', '', 0x323032362d31312d30312032323a34383a3235);

-- --------------------------------------------------------
-- Tabla `sla_alertas`

DROP TABLE IF EXISTS `sla_alertas`;
CREATE TABLE `sla_alertas` (
  `id_ticket` int(11) NOT NULL,
  `nivel` enum('riesgo','vencido') NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_ticket`,`nivel`),
  CONSTRAINT `fk_sla_ticket` FOREIGN KEY (`id_ticket`) REFERENCES `tickets` (`id_tickets`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `sla_alertas` (`id_ticket`, `nivel`, `fecha`) VALUES
(26, 'vencido', 0x323032362d31312d30312030393a31333a3536),
(27, 'vencido', 0x323032362d31312d30312030393a31333a3536),
(29, 'vencido', 0x323032362d31312d30312030393a31333a3536),
(30, 'vencido', 0x323032362d31312d30312032313a32353a3538);

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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tickets` (`id_tickets`, `codigo_ticket`, `asunto`, `descripcion`, `fecha_creacion`, `fecha_cierre`, `id_usuario_solicitante`, `id_usuario_asignado`, `id_departamento`, `id_categoria`, `id_prioridad`, `id_estado`, `id_inventario`, `solucion`, `observaciones`) VALUES
(1, 'TK-2026-0001', 'No pernde la pc', 'ni idea', 0x323032362d30392d30372030343a33363a3134, 0x323032362d31302d33302032313a30393a3033, 3, 2, 1, 1, 3, 4, NULL, 'Se enchufo', NULL),
(26, 'TK-2026-0002', 'crear', 'uno dos tres', 0x323032362d31302d32362031373a35383a3134, NULL, 3, 2, 1, 1, 3, 2, NULL, NULL, NULL),
(27, 'TK-2026-0003', 'Holis', 'Holis', 0x323032362d31302d33312030363a34363a3237, NULL, 3, NULL, 1, 4, 2, 1, NULL, NULL, NULL),
(28, 'TK-2026-0004', 'Probar Notificaciones', '1', 0x323032362d31302d33312030363a34383a3137, 0x323032362d31302d33312030363a35303a3333, 3, 2, 1, 2, 2, 5, NULL, 'Intentando', NULL),
(29, 'TK-2026-0005', 'No pernde la pc', '1213', 0x323032362d31302d33312031313a35303a3230, NULL, 1, 2, 1, 3, 3, 2, NULL, NULL, NULL),
(30, 'TK-2026-0006', 'asd', 'prueba', 0x323032362d31312d30312030393a32373a3231, NULL, 1, NULL, 1, 3, 3, 1, NULL, NULL, NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `usuarios` (`id_usuario`, `nombres`, `apellidos`, `correo`, `usuario_login`, `contraseña`, `telefono`, `id_departamento`, `id_rol`, `estado`) VALUES
(1, 'Administrador', 'Sistema', 'admin@unicon.com', 'admin', '$2y$10$F3nU5rVsnJ9w/G8eqLFqle7paWN0t.zF0W1DUJFLhcqdXkWMu4FHu', NULL, 1, 3, 'activo'),
(2, 'Tecnico', 'Informatica', 'tecnico@unicon.com', 'tecnico', '$2y$10$NIBG0mQlqW0/P/T7lR2wzOAUOGaWk0eTcHoTj.9IUPPIj1Ib3Ht.S', NULL, 1, 2, 'activo'),
(3, 'Usuario', 'Externo', 'usuario@unicon.com', 'usuario', '$2y$10$RepCaFpZOXxUdOyBqvskTuBXbEJ9/NROaX2G2zNrJFX0waGDPBuhq', '22', 1, 1, 'activo'),
(16, 'Jhoander', 'Adrian', 'JHOANDERALEJANDROADRIANMARCANO@GMAIL.COM', 'Andersito', '$2y$10$1hWjQbs8dtIYZEcbXeABbeMDZwX1KqCD0nemp8I589KapBo70DJ/C', NULL, 1, 1, 'activo'),
(17, 'Juan', 'Alvarado', 'juan_unicon@gmail.com', 'juanci', '$2y$10$cAix/vDzeQcn3pbkY07abeSUULkFdp3hacD2yQCXRIXjjXWPRMuS.', NULL, 1, 1, 'activo');

SET FOREIGN_KEY_CHECKS = 1;
SET UNIQUE_CHECKS = 1;
-- Fin del respaldo
