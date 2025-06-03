-- phpMyAdmin SQL Dump
-- version 5.1.1deb5ubuntu1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 18-05-2025 a las 12:08:59
-- Versión del servidor: 8.0.41-0ubuntu0.22.04.1
-- Versión de PHP: 8.1.2-1ubuntu2.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `receta_magica`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `allergens`
--

CREATE TABLE `allergens` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog`
--

CREATE TABLE `blog` (
  `id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `views` int DEFAULT '0',
  `likes` int DEFAULT '0',
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `description` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_comments`
--

CREATE TABLE `blog_comments` (
  `id` int NOT NULL,
  `blog_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_likes`
--

CREATE TABLE `blog_likes` (
  `user_id` int NOT NULL,
  `blog_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `blog_tags`
--

CREATE TABLE `blog_tags` (
  `blog_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categories`
--

CREATE TABLE `categories` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) CHARACTER SET utf32 COLLATE utf32_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `challenges`
--

CREATE TABLE `challenges` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `main_ingredient` int DEFAULT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `allergens` text COLLATE utf8mb4_unicode_ci,
  `description` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `phrases`
--

CREATE TABLE `phrases` (
  `id` int NOT NULL,
  `author` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phrase` text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `phrases`
--

INSERT INTO `phrases` (`id`, `author`, `phrase`) VALUES
(1, 'Thomas Keller', 'Una receta no tiene alma. Es el cocinero quien debe dársela.'),
(2, 'Anónimo', 'Cocinar es un acto de amor, un regalo, una forma de compartir con otros un poco de nuestro ser.'),
(3, 'George Bernard Shaw', 'No hay amor más sincero que el amor a la comida.'),
(4, 'Jean Anthelme Brillat-Savarin', 'Dime lo que comes y te diré quién eres.'),
(5, 'Anónimo', 'Barriga llena, corazón contento.'),
(6, 'Alberto Chicote', 'El problema no es cocinar mal, es no querer aprender a hacerlo bien.'),
(7, 'Ferran Adrià', 'Cocinar es un acto de amor.'),
(8, 'Ferran Adrià', 'No hay cocina sin técnica, pero tampoco hay cocina sin emoción.'),
(9, 'Anónimo', 'Comer es una necesidad, pero cocinar es un arte.'),
(10, 'Massimo Bottura', 'La tradición es una innovación que tuvo éxito.'),
(11, 'Homer Simpson', 'Tú tienes la pizza y yo tengo el hambre. ¿Ves? ¡Estamos destinados a estar juntos!'),
(12, 'Homer Simpson', '¿Se puede vivir del amor? No. Pero se puede vivir del tocino.'),
(13, 'Homer Simpson', 'La comida es la causa y la solución de todos los problemas.'),
(14, 'Peter Griffin', 'Lo bueno de comer solo es que puedes lamer el plato sin juicio.'),
(15, 'Gordon Ramsay', 'La cocina es la base de todo. No importa tu origen, la buena comida une a todos.'),
(16, 'Julia Child', 'Nadie nace siendo un gran cocinero, uno aprende haciéndolo.'),
(17, 'Alberto Chicote', 'La cocina habla. Y si no te dice nada, es que no le has puesto alma.'),
(18, 'Alberto Chicote', 'Una buena receta no vale nada sin un buen producto y ganas de hacerlo bien.'),
(19, 'Julia Child', 'Cocinar es como enamorarse, hay que entregarse sin miedo.'),
(20, 'Anthony Bourdain', 'Comer bien es una forma de vida.'),
(21, 'Anthony Bourdain', 'Toda la cultura comienza con la comida.'),
(22, 'Anónimo', 'Dieta: palabra que viene del griego ‘pasar hambre’.'),
(23, 'Anónimo', 'Comer no engorda. Lo que engorda es no parar.'),
(24, 'Anónimo', 'Cocinar es como hacer vino, lleva tiempo, paciencia y el conocimiento de cuándo se debe detener.'),
(25, 'Anónimo', 'Si la cerveza no te resuelve el problema, entonces es hora de buscar un mejor tipo de cerveza.'),
(26, 'Anónimo', 'No hay nada más mágico que la transformación de un simple grano de cebada en un delicioso vaso de cerveza.'),
(27, 'Anónimo', 'Cocinar es el arte de transformar ingredientes en recuerdos.'),
(28, 'Anónimo', 'Los grandes cocineros prueban todo, incluso sus errores.'),
(29, 'Ferran Adrià', 'La cocina no es química, es pasión.'),
(30, 'Anónimo', 'El secreto está en la masa… y en no contarle a nadie más.'),
(31, 'Anónimo', 'Si el vino es la respuesta, ¿cuál era la pregunta?\"'),
(32, 'Anónimo', 'Cerveza, la bebida que une a los amigos, acompaña a las cenas y suaviza cualquier conversación.'),
(33, 'Anónimo', 'Si una receta no sale bien, no es un fracaso. Es una oportunidad para aprender.'),
(34, 'Publio Virgilio', 'El vino es el arte del hombre que quiere hacer lo mejor de su tiempo.'),
(35, 'Anónimo', 'La cocina es la base de todo. No importa tu origen, lo que importa es lo que pones en la mesa.'),
(36, 'Marco Tulio Cicerón​', 'La mejor salsa para la comida es el apetito.'),
(37, 'Plinio el Viejo', 'El vino es un don de los dioses; como todo lo divino, es necesario usarlo con moderación.'),
(38, 'Marco Aurelio', 'La comida no se consume solo para llenar el estómago, sino para dar fuerza a la mente y al cuerpo.'),
(39, 'Homero', 'La comida es la mayor bendición de los dioses.'),
(40, 'Platón', 'Alimentar la mente es tan importante como alimentar el cuerpo, y la comida es la fuente de energía de ambos.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipes`
--

CREATE TABLE `recipes` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(80) CHARACTER SET utf32 COLLATE utf32_unicode_ci NOT NULL,
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `style` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `difficulty` enum('Fácil','Media','Difícil') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_time` int NOT NULL,
  `date_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `challenge_id` int NOT NULL,
  `views` int NOT NULL DEFAULT '0',
  `likes` int NOT NULL DEFAULT '0',
  `user_created` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_category`
--

CREATE TABLE `recipe_category` (
  `recipe_id` bigint UNSIGNED NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_challenges`
--

CREATE TABLE `recipe_challenges` (
  `recipe_id` int NOT NULL,
  `challenge_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_ingredient`
--

CREATE TABLE `recipe_ingredient` (
  `recipe_id` int NOT NULL,
  `ingredient_id` int NOT NULL,
  `quantity` decimal(6,2) DEFAULT NULL,
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_likes`
--

CREATE TABLE `recipe_likes` (
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_steps`
--

CREATE TABLE `recipe_steps` (
  `id` int NOT NULL,
  `recipe_id` int NOT NULL,
  `step_number` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time` bigint DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_style`
--

CREATE TABLE `recipe_style` (
  `recipe_id` int NOT NULL,
  `style_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `recipe_tags`
--

CREATE TABLE `recipe_tags` (
  `recipe_id` int NOT NULL,
  `tag_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `styles`
--

CREATE TABLE `styles` (
  `id` int NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tags`
--

CREATE TABLE `tags` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname1` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `surname2` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `country` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `birthdate` date NOT NULL,
  `points` int DEFAULT '0',
  `date_created` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `role` enum('admin','mod','user') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `jwt` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `genre` enum('Hombre','Mujer','Prefiero no contestar') COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `name`, `surname1`, `surname2`, `email`, `password`, `country`, `birthdate`, `points`, `date_created`, `role`, `avatar`, `token`, `jwt`, `genre`) VALUES
(1, 'PATiTO', 'Marc', 'Escuder', 'Gómez', 'marc_esku@hotmail.es', '$2y$10$OCXhU.EKP5PshE/kyZzFA.N95YfKEB378t3RoTAEhZ2G82YA3P2A.', 'España', '1995-07-15', 10, '2025-05-13 20:01:03', 'user', NULL, 'a58a8dd138ac4975f4b6a05c159738aa', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxLCJ1c2VybmFtZSI6IlBBVGlUTyIsImV4cCI6MTc1MDAwNjUzMn0.PhnG2Pw11nZFsnJJ-RqaB0ovIUDXrEtbe0N2W_t3rwE', 'Hombre'),
(11, 'MrWorldwide', 'Marc', 'Vila', 'Acosta', 'marcescu@gmail.com', '$2y$10$F6cpBdDnknE.3UhLWi6B7elBGfHSP3G5wdgp3YNADhSzPN4CXxUSC', 'España', '1991-07-07', 0, '2025-05-12 20:44:15', 'user', NULL, '', '', 'Hombre'),
(13, 'ctapasco', 'cristian', 'tapasco ', 'zabala', 'ctapasco@gmail.com', '$2y$10$UlMvtUMEjpsPGUT0BRQKT.rW9texA7Q9pWH0f6D8S3P2QSukt0Tvm', 'España', '1992-06-29', 0, '2025-05-15 13:36:42', 'user', NULL, '', '', 'Hombre'),
(14, 'ctapasco29', 'cristian', 'tapasco', 'zabala', 'ctapasco29@gmail.com', '$2y$10$UbiHff5aCzngE1DSa8wJF.NJLT0T4cUhGK7H0xCGkBD4g/7MrjHX.', 'España', '1992-06-29', 0, '2025-05-15 13:39:01', 'user', NULL, '', '', 'Hombre'),
(15, 'Mouad', 'mouad', 'sedjari', ' ', 'mouadtest@gmail.com', '$2y$10$sbc2ObyorSd2/iBxoYfSt.d7Z7chst7th0O57YzwxeluKww3O2bda', 'España', '2003-07-13', 0, '2025-05-16 16:51:12', 'user', NULL, '737910ab78ea1d779c25fb787e6a1cca', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxNSwidXNlcm5hbWUiOiJNb3VhZCIsImV4cCI6MTc1MDAwNjg1Nn0.vQSy1MEIvzwBTDKX-gjBh_nmY3MKl8bCwdvgeVrw5kU', 'Hombre'),
(16, 'LittleBunny', 'Mireia', 'Chacón', 'Lazo', 'mchacon@gmail.com', '$2y$10$ZD6KfYheNMsq417JHhlGoeSrPeTcepQW3EJYLwyDxBm1clifrldpe', 'España', '1999-01-06', 140, '2025-05-17 22:08:47', 'user', 'img/profile/avatar_682908ef192275.31484533.jpg', '3e64742af6f642ac35285e064c8fad33', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxNiwidXNlcm5hbWUiOiJMaXR0bGVCdW5ueSIsImV4cCI6MTc1MDE1Mjc1NH0.WD_wzRW6NsXBR1FnF9OepP_LCLrL1TkXomEWSLdUIBI', 'Mujer');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_favorites`
--

CREATE TABLE `user_favorites` (
  `user_id` int NOT NULL,
  `recipe_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `allergens`
--
ALTER TABLE `allergens`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `blog`
--
ALTER TABLE `blog`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `blog_id` (`blog_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `blog_likes`
--
ALTER TABLE `blog_likes`
  ADD PRIMARY KEY (`user_id`,`blog_id`),
  ADD KEY `blog_id` (`blog_id`);

--
-- Indices de la tabla `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD PRIMARY KEY (`blog_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indices de la tabla `categories`
--
ALTER TABLE `categories`
  ADD UNIQUE KEY `id` (`id`);

--
-- Indices de la tabla `challenges`
--
ALTER TABLE `challenges`
  ADD PRIMARY KEY (`id`),
  ADD KEY `main_ingredient` (`main_ingredient`);

--
-- Indices de la tabla `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `phrases`
--
ALTER TABLE `phrases`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `recipes`
--
ALTER TABLE `recipes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_created` (`user_created`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indices de la tabla `recipe_category`
--
ALTER TABLE `recipe_category`
  ADD UNIQUE KEY `recipe_id` (`recipe_id`),
  ADD UNIQUE KEY `recipe_category_name` (`name`);

--
-- Indices de la tabla `recipe_challenges`
--
ALTER TABLE `recipe_challenges`
  ADD PRIMARY KEY (`recipe_id`,`challenge_id`),
  ADD KEY `challenge_id` (`challenge_id`);

--
-- Indices de la tabla `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  ADD PRIMARY KEY (`recipe_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indices de la tabla `recipe_likes`
--
ALTER TABLE `recipe_likes`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indices de la tabla `recipe_steps`
--
ALTER TABLE `recipe_steps`
  ADD PRIMARY KEY (`id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- Indices de la tabla `recipe_style`
--
ALTER TABLE `recipe_style`
  ADD PRIMARY KEY (`recipe_id`,`style_id`),
  ADD KEY `style_id` (`style_id`);

--
-- Indices de la tabla `recipe_tags`
--
ALTER TABLE `recipe_tags`
  ADD PRIMARY KEY (`recipe_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indices de la tabla `styles`
--
ALTER TABLE `styles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `tags`
--
ALTER TABLE `tags`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD PRIMARY KEY (`user_id`,`recipe_id`),
  ADD KEY `recipe_id` (`recipe_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `allergens`
--
ALTER TABLE `allergens`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `blog`
--
ALTER TABLE `blog`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `blog_comments`
--
ALTER TABLE `blog_comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categories`
--
ALTER TABLE `categories`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `challenges`
--
ALTER TABLE `challenges`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `phrases`
--
ALTER TABLE `phrases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT de la tabla `recipes`
--
ALTER TABLE `recipes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recipe_category`
--
ALTER TABLE `recipe_category`
  MODIFY `recipe_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `recipe_steps`
--
ALTER TABLE `recipe_steps`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `styles`
--
ALTER TABLE `styles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tags`
--
ALTER TABLE `tags`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `blog`
--
ALTER TABLE `blog`
  ADD CONSTRAINT `blog_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `blog_comments`
--
ALTER TABLE `blog_comments`
  ADD CONSTRAINT `blog_comments_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`),
  ADD CONSTRAINT `blog_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Filtros para la tabla `blog_likes`
--
ALTER TABLE `blog_likes`
  ADD CONSTRAINT `blog_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `blog_likes_ibfk_2` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`);

--
-- Filtros para la tabla `blog_tags`
--
ALTER TABLE `blog_tags`
  ADD CONSTRAINT `blog_tags_ibfk_1` FOREIGN KEY (`blog_id`) REFERENCES `blog` (`id`),
  ADD CONSTRAINT `blog_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);

--
-- Filtros para la tabla `challenges`
--
ALTER TABLE `challenges`
  ADD CONSTRAINT `challenges_ibfk_1` FOREIGN KEY (`main_ingredient`) REFERENCES `ingredients` (`id`);

--
-- Filtros para la tabla `recipes`
--
ALTER TABLE `recipes`
  ADD CONSTRAINT `recipes_ibfk_1` FOREIGN KEY (`user_created`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipes_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`);

--
-- Filtros para la tabla `recipe_challenges`
--
ALTER TABLE `recipe_challenges`
  ADD CONSTRAINT `recipe_challenges_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`),
  ADD CONSTRAINT `recipe_challenges_ibfk_2` FOREIGN KEY (`challenge_id`) REFERENCES `challenges` (`id`);

--
-- Filtros para la tabla `recipe_ingredient`
--
ALTER TABLE `recipe_ingredient`
  ADD CONSTRAINT `recipe_ingredient_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_ingredient_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recipe_likes`
--
ALTER TABLE `recipe_likes`
  ADD CONSTRAINT `recipe_likes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `recipe_likes_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

--
-- Filtros para la tabla `recipe_steps`
--
ALTER TABLE `recipe_steps`
  ADD CONSTRAINT `recipe_steps_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);

--
-- Filtros para la tabla `recipe_style`
--
ALTER TABLE `recipe_style`
  ADD CONSTRAINT `recipe_style_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `recipe_style_ibfk_2` FOREIGN KEY (`style_id`) REFERENCES `styles` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `recipe_tags`
--
ALTER TABLE `recipe_tags`
  ADD CONSTRAINT `recipe_tags_ibfk_1` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`),
  ADD CONSTRAINT `recipe_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`);

--
-- Filtros para la tabla `user_favorites`
--
ALTER TABLE `user_favorites`
  ADD CONSTRAINT `user_favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_favorites_ibfk_2` FOREIGN KEY (`recipe_id`) REFERENCES `recipes` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
