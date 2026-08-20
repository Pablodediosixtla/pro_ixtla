-- =============================================================
-- PRESUPUESTO IXTLAHUACAN - DEP02
-- 00_create_schema.sql
-- MySQL 8 / Azure Database for MySQL Flexible Server
-- =============================================================
CREATE DATABASE IF NOT EXISTS `ixtla01_dep02`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `ixtla01_dep02`;

SELECT DATABASE() AS schema_activo,
       @@character_set_database AS character_set_database,
       @@collation_database AS collation_database;
