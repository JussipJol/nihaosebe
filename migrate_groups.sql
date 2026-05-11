-- Запустить в phpMyAdmin (Laragon → Database → SQL)
-- Обновляет таблицу groups: убирает subject, добавляет новые поля

ALTER TABLE `groups`
  ADD COLUMN `hsk_level`   tinyint(1)   DEFAULT NULL  AFTER `name`,
  ADD COLUMN `schedule`    varchar(50)  DEFAULT NULL  AFTER `hsk_level`,
  ADD COLUMN `lesson_time` varchar(20)  DEFAULT NULL  AFTER `schedule`;
