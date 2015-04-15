/*
Navicat MySQL Data Transfer

Source Server         : Server Develop PHP
Source Server Version : 50137
Source Host           : 192.168.230.28:3306
Source Database       : is_cronjobs

Target Server Type    : MYSQL
Target Server Version : 50137
File Encoding         : 65001

Date: 2015-04-15 12:17:53
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for cron_logs
-- ----------------------------
DROP TABLE IF EXISTS `cron_logs`;
CREATE TABLE `cron_logs` (
  `id_cron_log` int(11) NOT NULL AUTO_INCREMENT,
  `id_cronjob` int(11) DEFAULT NULL,
  `inicio` datetime DEFAULT NULL,
  `fin` datetime DEFAULT NULL,
  `respuesta` text COLLATE utf8_spanish_ci,
  `timestamp` datetime DEFAULT NULL,
  PRIMARY KEY (`id_cron_log`),
  KEY `i_cronjob` (`id_cronjob`)
) ENGINE=MyISAM AUTO_INCREMENT=41 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------
-- Records of cron_logs
-- ----------------------------
INSERT INTO `cron_logs` VALUES ('26', '1', '2015-04-15 11:57:10', '2015-04-15 11:57:10', 'Ejecutado OK: 2015-04-15 11:57:10', '2015-04-15 11:57:10');
INSERT INTO `cron_logs` VALUES ('27', '2', '2015-04-15 11:57:10', '2015-04-15 11:57:10', 'Error al ejecutar comando: <sh test/backup_mysql.sh>', '2015-04-15 11:57:10');
INSERT INTO `cron_logs` VALUES ('28', '3', '2015-04-15 11:57:10', '2015-04-15 11:57:10', 'Ejecutado OK: 2015-04-15 11:57:10', '2015-04-15 11:57:10');
INSERT INTO `cron_logs` VALUES ('29', '1', '2015-04-15 11:58:34', '2015-04-15 11:58:37', 'Ejecutado OK: 2015-04-15 11:58:37', '2015-04-15 11:58:37');
INSERT INTO `cron_logs` VALUES ('30', '2', '2015-04-15 11:58:38', '2015-04-15 11:58:38', 'Error al ejecutar comando: <sh test/backup_mysql.sh>', '2015-04-15 11:58:38');
INSERT INTO `cron_logs` VALUES ('31', '3', '2015-04-15 11:58:38', '2015-04-15 11:58:41', 'Ejecutado OK: 2015-04-15 11:58:41', '2015-04-15 11:58:41');
INSERT INTO `cron_logs` VALUES ('32', '1', '2015-04-15 12:06:24', '2015-04-15 12:06:27', 'Ejecutado OK: 2015-04-15 12:06:27', '2015-04-15 12:06:27');
INSERT INTO `cron_logs` VALUES ('33', '2', '2015-04-15 12:06:27', '2015-04-15 12:06:27', 'Error al ejecutar comando: <sh test/backup_mysql.sh>', '2015-04-15 12:06:27');
INSERT INTO `cron_logs` VALUES ('34', '3', '2015-04-15 12:06:27', '2015-04-15 12:06:30', 'Ejecutado OK: 2015-04-15 12:06:30', '2015-04-15 12:06:30');
INSERT INTO `cron_logs` VALUES ('35', '1', '2015-04-15 12:10:39', '2015-04-15 12:10:43', 'Ejecutado OK: 2015-04-15 12:10:43', '2015-04-15 12:10:43');
INSERT INTO `cron_logs` VALUES ('36', '2', '2015-04-15 12:10:43', '2015-04-15 12:10:43', 'Error al ejecutar comando: <sh test/backup_mysql.sh>', '2015-04-15 12:10:43');
INSERT INTO `cron_logs` VALUES ('37', '3', '2015-04-15 12:10:43', '2015-04-15 12:10:46', 'Ejecutado OK: 2015-04-15 12:10:46', '2015-04-15 12:10:46');
INSERT INTO `cron_logs` VALUES ('38', '1', '2015-04-15 12:16:03', '2015-04-15 12:16:06', 'Ejecutado OK: 2015-04-15 12:16:06', '2015-04-15 12:16:06');
INSERT INTO `cron_logs` VALUES ('39', '2', '2015-04-15 12:16:06', '2015-04-15 12:16:06', 'Error al ejecutar comando: <sh cron/test/backup_mysql.sh>', '2015-04-15 12:16:06');
INSERT INTO `cron_logs` VALUES ('40', '3', '2015-04-15 12:16:06', '2015-04-15 12:16:09', 'Ejecutado OK: 2015-04-15 12:16:09', '2015-04-15 12:16:09');

-- ----------------------------
-- Table structure for cron_tareas
-- ----------------------------
DROP TABLE IF EXISTS `cron_tareas`;
CREATE TABLE `cron_tareas` (
  `id_cronjob` int(11) NOT NULL AUTO_INCREMENT,
  `id_sistema` int(11) DEFAULT '0',
  `cron_nombre` varchar(50) COLLATE utf8_spanish_ci DEFAULT NULL,
  `cron_descripcion` text COLLATE utf8_spanish_ci,
  `inicio_fecha` date DEFAULT NULL,
  `inicio_hora` time DEFAULT NULL,
  `fin_fecha` date DEFAULT NULL,
  `fin_hora` time DEFAULT NULL,
  `cada_dias` smallint(3) DEFAULT NULL,
  `cada_horas` tinyint(2) DEFAULT NULL,
  `cada_minutos` tinyint(2) DEFAULT NULL,
  `tipo` enum('PHP','LINUX') COLLATE utf8_spanish_ci DEFAULT 'PHP',
  `ejecuta` text COLLATE utf8_spanish_ci,
  `activo` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id_cronjob`),
  KEY `i_sistema` (`id_sistema`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------
-- Records of cron_tareas
-- ----------------------------
INSERT INTO `cron_tareas` VALUES ('1', '0', 'test', 'Prueba de Cronjob', '2015-04-13', '13:41:14', '2099-01-01', '00:00:00', null, null, '1', 'PHP', 'test/test.php', '1');
INSERT INTO `cron_tareas` VALUES ('2', '0', 'backup_db_mysql', 'Respaldo de BD MySQL de sistemas en produccion', '2015-04-13', '02:00:00', '2099-01-01', '00:00:00', null, '24', null, 'LINUX', 'test/backup_mysql.sh', '1');
INSERT INTO `cron_tareas` VALUES ('3', '0', 'backup_db_mysql', 'Respaldo de BD MySQL de sistemas en produccion', '2015-04-13', '02:00:00', '2099-01-01', '00:00:00', null, '24', null, 'PHP', 'test/backup_mysql.sh', '1');

-- ----------------------------
-- Table structure for sis_sistemas
-- ----------------------------
DROP TABLE IF EXISTS `sis_sistemas`;
CREATE TABLE `sis_sistemas` (
  `id_sistema` int(11) NOT NULL AUTO_INCREMENT,
  `empresa` enum('GContempo','PAE','Intelligent Solution') COLLATE utf8_spanish_ci DEFAULT 'Intelligent Solution',
  `nombre` varchar(120) COLLATE utf8_spanish_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8_spanish_ci,
  `activo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_sistema`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

-- ----------------------------
-- Records of sis_sistemas
-- ----------------------------
