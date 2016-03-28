-- phpMyAdmin SQL Dump
-- version 4.5.1
-- http://www.phpmyadmin.net
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-03-2016 a las 21:23:22
-- Versión del servidor: 10.1.8-MariaDB
-- Versión de PHP: 5.6.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `seo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `adwdata`
--

CREATE TABLE `adwdata` (
  `idQuery` int(11) NOT NULL,
  `SearchVolume` int(11) NOT NULL,
  `CPC` int(11) NOT NULL,
  `Dificultad` int(11) NOT NULL,
  `Enero` int(11) NOT NULL,
  `Febrero` int(11) NOT NULL,
  `Marzo` int(11) NOT NULL,
  `Abril` int(11) NOT NULL,
  `Mayo` int(11) NOT NULL,
  `Junio` int(11) NOT NULL,
  `Julio` int(11) NOT NULL,
  `Agosto` int(11) NOT NULL,
  `Septiembre` int(11) NOT NULL,
  `Octubre` int(11) NOT NULL,
  `Noviembre` int(11) NOT NULL,
  `Diciembre` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `querys`
--

CREATE TABLE `querys` (
  `idQuery` int(11) NOT NULL,
  `Query` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wtdata`
--

CREATE TABLE `wtdata` (
  `idQuery` int(11) NOT NULL,
  `Imp` int(11) NOT NULL,
  `Clic` int(11) NOT NULL,
  `CTR` float NOT NULL,
  `PM` float NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `adwdata`
--
ALTER TABLE `adwdata`
  ADD PRIMARY KEY (`idQuery`);

--
-- Indices de la tabla `querys`
--
ALTER TABLE `querys`
  ADD PRIMARY KEY (`idQuery`);

--
-- Indices de la tabla `wtdata`
--
ALTER TABLE `wtdata`
  ADD PRIMARY KEY (`idQuery`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
