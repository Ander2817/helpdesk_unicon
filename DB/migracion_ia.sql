-- Módulo 08 (Asistente IA): ejecutar UNA vez en phpMyAdmin sobre `helpdesk_unicon`. Es seguro repetirla.
-- Guarda cada consulta al asistente: sirve para limitar el uso por usuario y para auditar qué se preguntó.
CREATE TABLE IF NOT EXISTS `ia_consultas` (
  `id_consulta` bigint(20) NOT NULL AUTO_INCREMENT,
  `id_usuario`  int(11) NOT NULL,
  `id_rol`      int(11) NOT NULL,
  `accion`      varchar(30) NOT NULL,
  `id_ticket`   int(11) DEFAULT NULL,
  `pregunta`    varchar(500) DEFAULT NULL,
  `fuente`      varchar(10) NOT NULL DEFAULT 'ia' COMMENT 'ia = respondió el modelo, local = respuesta sin IA',
  `ok`          tinyint(1) NOT NULL DEFAULT 1,
  `fecha`       datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_consulta`),
  KEY `idx_ia_usuario_fecha` (`id_usuario`, `fecha`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
