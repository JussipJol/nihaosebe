-- Запустить в HeidiSQL: выбери базу nunihaosebe, File → Load SQL file → F9

-- Переименовываем email → login
ALTER TABLE `users` CHANGE COLUMN `email` `login` VARCHAR(100) NOT NULL;

-- Обновляем логин администратора
UPDATE `users` SET `login` = 'admin' WHERE `role` = 'admin';
