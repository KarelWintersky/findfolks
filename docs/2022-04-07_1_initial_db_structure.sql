CREATE TABLE `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `dt_create` datetime DEFAULT current_timestamp(),
  `dt_update` datetime DEFAULT NULL ON UPDATE current_timestamp(),
  `city` varchar(100) DEFAULT '' COMMENT 'Город',
  `district` varchar(100) DEFAULT '' COMMENT 'Район/регион',
  `street` varchar(100) DEFAULT '' COMMENT 'улица',
  `address` varchar(100) DEFAULT '' COMMENT 'Адрес (номер дома итд)',
  `fio` varchar(100) DEFAULT '' COMMENT 'ФИО',
  `ticket` text DEFAULT '' COMMENT 'текст объявления',
  `ipv4` varchar(14) DEFAULT '127.0.0.1' COMMENT 'ipv4 в строковой форме',
  `is_verified` tinyint(4) DEFAULT 1 COMMENT 'подтверждено',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='таблица объявлений';