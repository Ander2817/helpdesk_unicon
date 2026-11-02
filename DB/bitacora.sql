-- Tabla de bitácora de acciones (ejecutar una sola vez en phpMyAdmin sobre `helpdesk_unicon`)
-- No lleva FOREIGN KEY a propósito: si se elimina un usuario, su rastro en la bitácora debe permanecer.

CREATE TABLE IF NOT EXISTS `bitacora` (
  `id_bitacora`     bigint(20) NOT NULL AUTO_INCREMENT,
  `id_usuario`      int(11) DEFAULT NULL,
  `usuario_login`   varchar(50) NOT NULL DEFAULT 'sistema',
  `id_rol`          int(11) DEFAULT NULL,
  `accion`          varchar(20) NOT NULL COMMENT 'INSERT, UPDATE, DELETE, LOGIN, LOGOUT, BACKUP, EXPORT, IMPORT, RESTORE, ...',
  `tabla_afectada`  varchar(64) DEFAULT NULL,
  `id_registro`     varchar(64) DEFAULT NULL,
  `descripcion`     varchar(500) NOT NULL,
  `datos_anteriores` longtext DEFAULT NULL,
  `datos_nuevos`    longtext DEFAULT NULL,
  `ip`              varchar(45) DEFAULT NULL,
  `user_agent`      varchar(255) DEFAULT NULL,
  `fecha`           datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_bitacora`),
  KEY `idx_bitacora_fecha` (`fecha`),
  KEY `idx_bitacora_accion` (`accion`),
  KEY `idx_bitacora_tabla` (`tabla_afectada`),
  KEY `idx_bitacora_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
