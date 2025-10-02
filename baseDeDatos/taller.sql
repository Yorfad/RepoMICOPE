CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    correo VARCHAR(100) UNIQUE,
    direccion VARCHAR(200)
);

CREATE TABLE tecnicos (
    id_tecnico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    especialidad VARCHAR(100),
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE
);

CREATE TABLE marcas (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL
);

CREATE TABLE equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_marca INT NOT NULL,
    tipo_equipo ENUM('Laptop','PC','Smartphone','Impresora','Otro') NOT NULL,
    modelo VARCHAR(100),
    serie VARCHAR(100) UNIQUE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_marca) REFERENCES marcas(id_marca)
);

CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    id_tecnico INT NOT NULL,
    fecha_recepcion DATETIME NOT NULL,
    problema_reportado TEXT NOT NULL,
    diagnostico TEXT,
    solucion TEXT,
    estado ENUM('Recibido','Reparando','Finalizado','Entregado') DEFAULT 'Recibido',
    fecha_entrega DATETIME NULL,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo),
    FOREIGN KEY (id_tecnico) REFERENCES tecnicos(id_tecnico)
);
