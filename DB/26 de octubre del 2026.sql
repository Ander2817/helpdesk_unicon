-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 27-10-2026 a las 01:40:08
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

--
-- Volcado de datos para la tabla `adjuntos`
--

INSERT INTO `adjuntos` (`id_adjunto`, `id_ticket`, `nombre_archivo`, `ruta_archivo`, `tipo_archivo`, `fecha_subida`) VALUES
(1, 26, 'captura-20261026215810.png', '2026/f7a438d679c36daad220f882061634cb.dat', 'png', '2026-10-26 17:58:14');

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
(1, 1, 2, 'Asignación', 'Sin asignar', 'Asignado a tecnico', '2026-09-07 06:14:35');

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
(26, 'TK-2026-0002', 'crear', 'uno dos tres', '2026-10-26 17:58:14', NULL, 3, NULL, 1, 1, 3, 1, NULL, NULL, NULL);

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
(7, 'Leandro', 'Monterrey', 'leandroargenis@gmail.com', 'leandroar', '$2y$10$0dFAmyPonsGGlflqU6cL9ucTk9PZ/poG8w.F/.oYA/BqBIW9a2y2S', NULL, 1, 1, 'activo');

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
  MODIFY `id_adjunto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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
  MODIFY `id_tickets` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

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
