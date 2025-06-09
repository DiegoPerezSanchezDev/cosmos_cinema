-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-04-2025 a las 13:33:42
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
-- Base de datos: `cosmos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

CREATE TABLE `administrador` (
  `id_administrador` bigint(20) UNSIGNED NOT NULL,
  `nombre_user_admin` varchar(30) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `numero_telefono` varchar(15) NOT NULL,
  `password` varchar(200) NOT NULL,
  `codigo_administrador` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asiento`
--

CREATE TABLE `asiento` (
  `id_asiento` bigint(20) UNSIGNED NOT NULL,
  `estado` int(11) NOT NULL,
  `id_sala` bigint(20) UNSIGNED NOT NULL,
  `id_tipo_asiento` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ciudades`
--

CREATE TABLE `ciudades` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `descuento`
--

CREATE TABLE `descuento` (
  `id_descuento` bigint(20) UNSIGNED NOT NULL,
  `descuento` int(11) DEFAULT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `edad_recomendada`
--

CREATE TABLE `edad_recomendada` (
  `id_edad_recomendada` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `entrada`
--

CREATE TABLE `entrada` (
  `id_entrada` bigint(20) UNSIGNED NOT NULL,
  `codigo_qr` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_compra` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_tipo_entrada` bigint(20) UNSIGNED NOT NULL,
  `monto_total_tipo_entrada` double NOT NULL,
  `id_factura` bigint(20) UNSIGNED NOT NULL,
  `id_pelicula` bigint(20) UNSIGNED NOT NULL,
  `id_asiento` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `factura`
--

CREATE TABLE `factura` (
  `id_factura` bigint(20) UNSIGNED NOT NULL,
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `monto_total` double NOT NULL,
  `id_impuesto` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero_pelicula`
--

CREATE TABLE `genero_pelicula` (
  `id_genero_pelicula` bigint(20) UNSIGNED NOT NULL,
  `genero` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `impuesto`
--

CREATE TABLE `impuesto` (
  `id_impuesto` bigint(20) UNSIGNED NOT NULL,
  `iva` decimal(5,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '2025_04_06_192937_create_edad_recomendada_table', 1),
(3, '2025_04_06_192940_create_genero_pelicula_table', 1),
(4, '2025_04_06_192940_create_tipo_asiento_table', 1),
(5, '2025_04_06_192941_create_impuesto_table', 1),
(6, '2025_04_06_192941_create_sala_table', 1),
(7, '2025_04_06_192942_create_administrador_table', 1),
(8, '2025_04_06_192942_create_descuento_table', 1),
(9, '2025_04_06_192943_create_pelicula_table', 1),
(10, '2025_04_06_192943_create_users_table', 1),
(11, '2025_04_06_192944_create_tipo_entrada_table', 1),
(12, '2025_04_06_192945_create_asiento_table', 1),
(13, '2025_04_06_192945_create_factura_table', 1),
(14, '2025_04_06_192946_create_entrada_table', 1),
(15, '2025_04_06_192946_create_pelicula_genero_table', 1),
(16, '2025_04_12_191431_create_sessions_table', 1),
(17, '2025_04_21_104040_create_ciudades_table', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pelicula`
--

CREATE TABLE `pelicula` (
  `id_pelicula` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `duracion` int(11) NOT NULL,
  `director` varchar(150) NOT NULL,
  `actor` varchar(250) NOT NULL,
  `sinopsis` text NOT NULL,
  `fecha_estreno` date NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_baja` date DEFAULT NULL,
  `id_edad_recomendada` bigint(20) UNSIGNED DEFAULT NULL,
  `id_sala` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pelicula_genero`
--

CREATE TABLE `pelicula_genero` (
  `id_pelicula` bigint(20) UNSIGNED NOT NULL,
  `id_genero_pelicula` bigint(20) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sala`
--

CREATE TABLE `salas` (
  `id_sala` bigint(20) UNSIGNED NOT NULL,
  `numero_asientos` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_asiento`
--

CREATE TABLE `tipo_asiento` (
  `id_tipo_asiento` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_entrada`
--

CREATE TABLE `tipo_entrada` (
  `id_tipo_entrada` bigint(20) UNSIGNED NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `precio` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id_user` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `numero_telefono` varchar(9) NOT NULL,
  `dni` varchar(9) NOT NULL,
  `direccion` varchar(150) DEFAULT NULL,
  `ciudad` varchar(30) NOT NULL,
  `codigo_postal` varchar(10) NOT NULL,
  `password` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  `mayor_edad` tinyint(4) NOT NULL DEFAULT 0,
  `id_descuento` bigint(20) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `nombre`, `apellidos`, `email`, `fecha_nacimiento`, `numero_telefono`, `dni`, `direccion`, `ciudad`, `codigo_postal`, `password`, `created_at`, `updated_at`, `mayor_edad`, `id_descuento`) VALUES
(1, 'Diego', 'Perez sanchez', 'dps@gmail.com', '2000-03-02', '111111111', '11111111K', 'c/oeste n22', 'Salamanca', '37188', '$2y$12$U/MQdVRia8fF1PInxHVc4uiiyAy2jPYDtaNFKImNqsk9VkUTV5BGu', '2025-04-21 09:26:28', '2025-04-21 09:26:28', 1, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administrador`
--
ALTER TABLE `administrador`
  ADD PRIMARY KEY (`id_administrador`),
  ADD UNIQUE KEY `administrador_nombre_user_admin_unique` (`nombre_user_admin`),
  ADD UNIQUE KEY `administrador_email_unique` (`email`),
  ADD UNIQUE KEY `administrador_codigo_administrador_unique` (`codigo_administrador`);

--
-- Indices de la tabla `asiento`
--
ALTER TABLE `asiento`
  ADD PRIMARY KEY (`id_asiento`),
  ADD KEY `asiento_id_sala_foreign` (`id_sala`),
  ADD KEY `asiento_id_tipo_asiento_foreign` (`id_tipo_asiento`);

--
-- Indices de la tabla `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indices de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `descuento`
--
ALTER TABLE `descuento`
  ADD PRIMARY KEY (`id_descuento`);

--
-- Indices de la tabla `edad_recomendada`
--
ALTER TABLE `edad_recomendada`
  ADD PRIMARY KEY (`id_edad_recomendada`);

--
-- Indices de la tabla `entrada`
--
ALTER TABLE `entrada`
  ADD PRIMARY KEY (`id_entrada`),
  ADD KEY `entrada_id_tipo_entrada_foreign` (`id_tipo_entrada`),
  ADD KEY `entrada_id_factura_foreign` (`id_factura`),
  ADD KEY `entrada_id_pelicula_foreign` (`id_pelicula`),
  ADD KEY `entrada_id_asiento_foreign` (`id_asiento`),
  ADD KEY `entrada_id_user_foreign` (`id_user`);

--
-- Indices de la tabla `factura`
--
ALTER TABLE `factura`
  ADD PRIMARY KEY (`id_factura`),
  ADD KEY `factura_id_user_foreign` (`id_user`),
  ADD KEY `factura_id_impuesto_foreign` (`id_impuesto`);

--
-- Indices de la tabla `genero_pelicula`
--
ALTER TABLE `genero_pelicula`
  ADD PRIMARY KEY (`id_genero_pelicula`);

--
-- Indices de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  ADD PRIMARY KEY (`id_impuesto`);

--
-- Indices de la tabla `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pelicula`
--
ALTER TABLE `pelicula`
  ADD PRIMARY KEY (`id_pelicula`),
  ADD KEY `pelicula_id_edad_recomendada_foreign` (`id_edad_recomendada`),
  ADD KEY `pelicula_id_sala_foreign` (`id_sala`);

--
-- Indices de la tabla `pelicula_genero`
--
ALTER TABLE `pelicula_genero`
  ADD KEY `pelicula_genero_id_pelicula_foreign` (`id_pelicula`),
  ADD KEY `pelicula_genero_id_genero_pelicula_foreign` (`id_genero_pelicula`);

--
-- Indices de la tabla `sala`
--
ALTER TABLE `sala`
  ADD PRIMARY KEY (`id_sala`);

--
-- Indices de la tabla `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indices de la tabla `tipo_asiento`
--
ALTER TABLE `tipo_asiento`
  ADD PRIMARY KEY (`id_tipo_asiento`);

--
-- Indices de la tabla `tipo_entrada`
--
ALTER TABLE `tipo_entrada`
  ADD PRIMARY KEY (`id_tipo_entrada`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD UNIQUE KEY `users_dni_unique` (`dni`),
  ADD KEY `users_id_descuento_foreign` (`id_descuento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administrador`
--
ALTER TABLE `administrador`
  MODIFY `id_administrador` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `asiento`
--
ALTER TABLE `asiento`
  MODIFY `id_asiento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ciudades`
--
ALTER TABLE `ciudades`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `descuento`
--
ALTER TABLE `descuento`
  MODIFY `id_descuento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `edad_recomendada`
--
ALTER TABLE `edad_recomendada`
  MODIFY `id_edad_recomendada` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `entrada`
--
ALTER TABLE `entrada`
  MODIFY `id_entrada` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `factura`
--
ALTER TABLE `factura`
  MODIFY `id_factura` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `genero_pelicula`
--
ALTER TABLE `genero_pelicula`
  MODIFY `id_genero_pelicula` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `impuesto`
--
ALTER TABLE `impuesto`
  MODIFY `id_impuesto` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `pelicula`
--
ALTER TABLE `pelicula`
  MODIFY `id_pelicula` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sala`
--
ALTER TABLE `sala`
  MODIFY `id_sala` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_asiento`
--
ALTER TABLE `tipo_asiento`
  MODIFY `id_tipo_asiento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tipo_entrada`
--
ALTER TABLE `tipo_entrada`
  MODIFY `id_tipo_entrada` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `asiento`
--
ALTER TABLE `asiento`
  ADD CONSTRAINT `asiento_id_sala_foreign` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`),
  ADD CONSTRAINT `asiento_id_tipo_asiento_foreign` FOREIGN KEY (`id_tipo_asiento`) REFERENCES `tipo_asiento` (`id_tipo_asiento`);

--
-- Filtros para la tabla `entrada`
--
ALTER TABLE `entrada`
  ADD CONSTRAINT `entrada_id_asiento_foreign` FOREIGN KEY (`id_asiento`) REFERENCES `asiento` (`id_asiento`),
  ADD CONSTRAINT `entrada_id_factura_foreign` FOREIGN KEY (`id_factura`) REFERENCES `factura` (`id_factura`),
  ADD CONSTRAINT `entrada_id_pelicula_foreign` FOREIGN KEY (`id_pelicula`) REFERENCES `pelicula` (`id_pelicula`),
  ADD CONSTRAINT `entrada_id_tipo_entrada_foreign` FOREIGN KEY (`id_tipo_entrada`) REFERENCES `tipo_entrada` (`id_tipo_entrada`),
  ADD CONSTRAINT `entrada_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Filtros para la tabla `factura`
--
ALTER TABLE `factura`
  ADD CONSTRAINT `factura_id_impuesto_foreign` FOREIGN KEY (`id_impuesto`) REFERENCES `impuesto` (`id_impuesto`),
  ADD CONSTRAINT `factura_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`);

--
-- Filtros para la tabla `pelicula`
--
ALTER TABLE `pelicula`
  ADD CONSTRAINT `pelicula_id_edad_recomendada_foreign` FOREIGN KEY (`id_edad_recomendada`) REFERENCES `edad_recomendada` (`id_edad_recomendada`),
  ADD CONSTRAINT `pelicula_id_sala_foreign` FOREIGN KEY (`id_sala`) REFERENCES `sala` (`id_sala`);

--
-- Filtros para la tabla `pelicula_genero`
--
ALTER TABLE `pelicula_genero`
  ADD CONSTRAINT `pelicula_genero_id_genero_pelicula_foreign` FOREIGN KEY (`id_genero_pelicula`) REFERENCES `genero_pelicula` (`id_genero_pelicula`),
  ADD CONSTRAINT `pelicula_genero_id_pelicula_foreign` FOREIGN KEY (`id_pelicula`) REFERENCES `pelicula` (`id_pelicula`);

--
-- Filtros para la tabla `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_id_descuento_foreign` FOREIGN KEY (`id_descuento`) REFERENCES `descuento` (`id_descuento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
