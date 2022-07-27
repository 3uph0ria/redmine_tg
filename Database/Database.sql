-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Июл 27 2022 г., 18:49
-- Версия сервера: 5.7.27-30
-- Версия PHP: 7.1.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `u1433101_redmine`
--

-- --------------------------------------------------------

--
-- Структура таблицы `img`
--

CREATE TABLE `img` (
  `Id` int(11) NOT NULL,
  `UserId` bigint(15) NOT NULL,
  `FileId` varchar(200) NOT NULL,
  `FileName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Структура таблицы `permissions`
--

CREATE TABLE `permissions` (
  `Id` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `permissions`
--

INSERT INTO `permissions` (`Id`, `Name`) VALUES
(1, 'Руководитель'),
(2, 'Исполнитель');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `Id` int(11) NOT NULL,
  `Name` varchar(50) NOT NULL,
  `PeerId` bigint(15) NOT NULL,
  `PermissionId` int(11) DEFAULT NULL,
  `ActiveIssue` int(11) DEFAULT NULL,
  `Text` varchar(1500) DEFAULT NULL,
  `AuthorId` int(11) DEFAULT NULL,
  `ProjectId` int(11) NOT NULL,
  `FullName` varchar(100) DEFAULT NULL,
  `Banned` tinyint(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`Id`, `Name`, `PeerId`, `PermissionId`, `ActiveIssue`, `Text`, `AuthorId`, `ProjectId`, `FullName`, `Banned`) VALUES
(80, 'Ясницкий Владимир Рук', 525972028, 1, NULL, NULL, 262, 0, NULL, NULL),
(93, 'Вячеслав', 5167825632, 2, 48436, NULL, NULL, 48, 'Кучмеев Вячеслав Владимирович', NULL),
(94, 'Игорь', 5197725783, 2, 48436, NULL, NULL, 48, 'Дощицын Игорь', NULL),
(96, 'Glosav', 5529000499, 2, 48501, NULL, NULL, 59, 'Кофе на Тверской', NULL),
(98, 'Anastasia', 797577812, 1, NULL, NULL, 240, 0, NULL, NULL),
(102, 'InCeDeNt', 275481785, 2, 48529, NULL, NULL, 59, 'Воропаев Алексей', NULL),
(103, '3uph0ria', 608689555, 2, 48530, NULL, NULL, 59, 'Саломатин Сергей', NULL);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `img`
--
ALTER TABLE `img`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`Id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`Id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `img`
--
ALTER TABLE `img`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT для таблицы `permissions`
--
ALTER TABLE `permissions`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `Id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
