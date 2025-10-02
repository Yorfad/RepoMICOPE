DROP DATABASE IF EXISTS taller_reparaciones;
CREATE DATABASE taller_reparaciones;
USE taller_reparaciones;

CREATE TABLE departamentos (
    id_departamento INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE municipios (
    id_municipio INT AUTO_INCREMENT PRIMARY KEY,
    id_departamento INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_departamento) REFERENCES departamentos(id_departamento)
);

CREATE TABLE origen_clientes (
    id_origen INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    direccion VARCHAR(200),
    id_municipio INT,
    id_origen INT,
    FOREIGN KEY (id_municipio) REFERENCES municipios(id_municipio),
    FOREIGN KEY (id_origen) REFERENCES origen_clientes(id_origen)
);

CREATE TABLE tipos_equipo (
    id_tipo_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE marcas (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_tipo_equipo INT NOT NULL,
    id_marca INT NOT NULL,
    modelo VARCHAR(100),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_tipo_equipo) REFERENCES tipos_equipo(id_tipo_equipo),
    FOREIGN KEY (id_marca) REFERENCES marcas(id_marca)
);

CREATE TABLE partes (
    id_parte INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE detalles_parte (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_parte INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    FOREIGN KEY (id_parte) REFERENCES partes(id_parte)
);

CREATE TABLE tecnicos (
    id_tecnico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    especialidad VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE
);

CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_tecnico INT NOT NULL,
    fecha_recepcion DATETIME NOT NULL,
    problema_reportado TEXT,
    id_parte INT,
    id_detalle INT,
    estado ENUM('Recibido','Reparando','Finalizado','Entregado') DEFAULT 'Recibido',
    fecha_entrega DATETIME NULL,
    costo_reparacion DECIMAL(10,2),
    precio_cobrado DECIMAL(10,2),
    observaciones TEXT,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo),
    FOREIGN KEY (id_tecnico) REFERENCES tecnicos(id_tecnico),
    FOREIGN KEY (id_parte) REFERENCES partes(id_parte),
    FOREIGN KEY (id_detalle) REFERENCES detalles_parte(id_detalle)
);
