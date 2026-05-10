-- Запустить в HeidiSQL: выбери базу nunihaosebe, File → Load SQL file → F9

ALTER TABLE `groups`
  ADD COLUMN `hsk_level`   tinyint(1)   DEFAULT NULL  AFTER `name`,
  ADD COLUMN `schedule`    varchar(50)  DEFAULT NULL  AFTER `hsk_level`,
  ADD COLUMN `lesson_time` varchar(20)  DEFAULT NULL  AFTER `schedule`;
