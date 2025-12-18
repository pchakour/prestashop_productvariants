CREATE TABLE IF NOT EXISTS `PREFIX_pc_packvariants` (
  `id_pack` INT(11) NOT NULL,
  `id_variant` INT(11) NOT NULL,
  PRIMARY KEY (`id_pack`, `id_variant`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
