DROP DATABASE IF EXISTS taller_reparaciones;
CREATE DATABASE taller_reparaciones;
USE taller_reparaciones;

-- ================================================================
-- TABLAS CATÁLOGO (MASTER DATA)
-- ================================================================

-- Ubicaciones geográficas para saber donde se tienen mas clientes
CREATE TABLE municipios (
    id_municipio INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    departamento VARCHAR(100) NOT NULL
);

-- Tipos de equipos que se reparan
CREATE TABLE tipos_equipo (
    id_tipo_equipo INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL, -- 'Laptop', 'Smartphone', 'Impresora', 'consolas', etc.
    descripcion TEXT
);

-- Marcas de equipos
CREATE TABLE marcas (
    id_marca INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL -- 'HP', 'Samsung', 'Apple', etc.
);

-- Especialidades técnicas
CREATE TABLE especialidades (
    id_especialidad INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL, -- 'Reparación de teléfonos', 'Reparación de laptops'
    id_tipo_equipo INT NOT NULL, -- Cada especialidad aplica a un tipo de equipo
    FOREIGN KEY (id_tipo_equipo) REFERENCES tipos_equipo(id_tipo_equipo)
);

-- Permite hacer estimaciones automáticas de tiempo +al recibir un equipo
CREATE TABLE tipos_dano (
    id_tipo_dano INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL, -- 'Pantalla rota', 'No enciende', 'Batería descarga rápido'
    id_tipo_equipo INT NOT NULL,
    tiempo_estimado_horas DECIMAL(5,2) DEFAULT 0.00, -- Tiempo promedio histórico o ingresado
    descripcion TEXT,
    FOREIGN KEY (id_tipo_equipo) REFERENCES tipos_equipo(id_tipo_equipo)
);

-- Partes/componentes de equipos
CREATE TABLE partes (
    id_parte INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL -- 'Pantalla', 'Batería', 'Placa base', etc.
);

-- Detalles específicos de partes
CREATE TABLE detalles_parte (
    id_detalle INT AUTO_INCREMENT PRIMARY KEY,
    id_parte INT NOT NULL,
    descripcion VARCHAR(200), -- 'LCD 5.5"', 'Batería Li-ion 3000mAh'
    FOREIGN KEY (id_parte) REFERENCES partes(id_parte)
);

-- ================================================================
-- MÓDULO DE CLIENTES
-- ================================================================

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    direccion VARCHAR(200),
    id_municipio INT,
    -- MEJORA: Enum para rastrear origen de clientes (marketing)
    origen ENUM('Publicidad','Redes sociales','Recomendacion','Boca a boca','Otro') DEFAULT 'Otro',
    -- MEJORA: Contador automático de visitas
    visitas_totales INT DEFAULT 0,
    fecha_registro DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (id_municipio) REFERENCES municipios(id_municipio),
    INDEX idx_cliente_telefono (telefono),
    INDEX idx_cliente_email (email)
);

-- ================================================================
-- MÓDULO DE TÉCNICOS
-- ================================================================

CREATE TABLE tecnicos (
    id_tecnico INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    activo BOOLEAN DEFAULT TRUE
);

-- Permite asignar trabajo según experiencia y capacidad
CREATE TABLE tecnico_especialidades (
    id_tecnico INT NOT NULL,
    id_especialidad INT NOT NULL,
    nivel ENUM('Aprendiz','Bueno','Experto') NOT NULL,
    PRIMARY KEY (id_tecnico, id_especialidad),
    FOREIGN KEY (id_tecnico) REFERENCES tecnicos(id_tecnico) ON DELETE CASCADE,
    FOREIGN KEY (id_especialidad) REFERENCES especialidades(id_especialidad),
    INDEX idx_nivel (nivel)
);

-- ================================================================
-- MÓDULO DE EQUIPOS
-- ================================================================

CREATE TABLE equipos (
    id_equipo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_tipo_equipo INT NOT NULL,
    id_marca INT NOT NULL,
    modelo VARCHAR(100),
    numero_serie VARCHAR(100),
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_tipo_equipo) REFERENCES tipos_equipo(id_tipo_equipo),
    FOREIGN KEY (id_marca) REFERENCES marcas(id_marca),
    INDEX idx_cliente_equipo (id_cliente)
);

-- ================================================================
-- MÓDULO DE SERVICIOS (Core del sistema)
-- ================================================================

CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_equipo INT NOT NULL,
    fecha_recepcion DATETIME NOT NULL,
    -- MEJORA: Reporte preliminar del cliente antes de diagnóstico técnico
    id_tipo_dano_preliminar INT,
    problema_reportado TEXT, -- Lo que dice el cliente
    fecha_entrega DATETIME NULL,
    observaciones TEXT,
    FOREIGN KEY (id_equipo) REFERENCES equipos(id_equipo),
    FOREIGN KEY (id_tipo_dano_preliminar) REFERENCES tipos_dano(id_tipo_dano),
    INDEX idx_servicio_equipo (id_equipo),
    INDEX idx_fecha_recepcion (fecha_recepcion)
);

-- Permite colaboración experto-aprendiz o trabajos complejos
CREATE TABLE servicio_tecnicos (
    id_servicio INT NOT NULL,
    id_tecnico INT NOT NULL,
    fecha_asignacion DATETIME DEFAULT CURRENT_TIMESTAMP,
    rol ENUM('Principal','Asistente') DEFAULT 'Principal',
    PRIMARY KEY (id_servicio, id_tecnico),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    FOREIGN KEY (id_tecnico) REFERENCES tecnicos(id_tecnico),
    INDEX idx_tecnico_servicio (id_tecnico)
);

-- Permite auditoría y tracking preciso del flujo de trabajo
CREATE TABLE historial_estados (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio INT NOT NULL,
    estado ENUM('Recibido','Diagnosticando','Esperando repuestos','Reparando','Finalizado','Entregado','Cancelado') NOT NULL,
    fecha_cambio DATETIME DEFAULT CURRENT_TIMESTAMP,
    id_usuario_responsable INT, -- Quién hizo el cambio
    motivo TEXT, -- Por qué cambió (ej: "Cliente canceló", "Repuesto llegó")
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario_responsable) REFERENCES tecnicos(id_tecnico),
    INDEX idx_servicio_fecha (id_servicio, fecha_cambio DESC)
);

-- El cliente reporta "no enciende", el técnico diagnostica "placa base quemada"
CREATE TABLE servicio_partes (
    id_servicio_parte INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio INT NOT NULL,
    id_parte INT NOT NULL,
    id_detalle INT,
    descripcion TEXT,
    -- NUEVO: Distingue reporte cliente vs diagnóstico técnico
    es_diagnostico_tecnico BOOLEAN DEFAULT FALSE,
    fecha_diagnostico DATETIME,
    id_tecnico_diagnostico INT, -- Quién hizo el diagnóstico
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    FOREIGN KEY (id_parte) REFERENCES partes(id_parte),
    FOREIGN KEY (id_detalle) REFERENCES detalles_parte(id_detalle),
    FOREIGN KEY (id_tecnico_diagnostico) REFERENCES tecnicos(id_tecnico)
);

-- ================================================================
-- MÓDULO DE INVENTARIO Y COSTOS
-- ================================================================

CREATE TABLE repuestos (
    id_repuesto INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    descripcion TEXT,
    cantidad INT DEFAULT 0,
    -- MEJORA: Separación costo interno vs precio al cliente
    costo_unitario DECIMAL(10,2) NOT NULL, -- Lo que nos cuesta
    precio_unitario DECIMAL(10,2) NOT NULL, -- Lo que cobramos
    activo BOOLEAN DEFAULT TRUE,
    INDEX idx_activo (activo)
);

CREATE TABLE servicio_repuestos (
    id_servicio_repuesto INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio INT NOT NULL,
    id_repuesto INT NOT NULL,
    cantidad INT NOT NULL,
    costo_total DECIMAL(10,2), -- cantidad * costo_unitario
    precio_total DECIMAL(10,2), -- cantidad * precio_unitario
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    FOREIGN KEY (id_repuesto) REFERENCES repuestos(id_repuesto)
);

-- Permite estimar tiempos si falta alguna pieza
CREATE TABLE pedidos_repuestos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_repuesto INT NOT NULL,
    id_servicio INT, -- Si es para un servicio específico
    cantidad INT NOT NULL,
    fecha_pedido DATE NOT NULL,
    fecha_estimada_llegada DATE,
    fecha_llegada_real DATE,
    estado ENUM('Pedido','En tránsito','Recibido','Cancelado') DEFAULT 'Pedido',
    proveedor VARCHAR(150),
    FOREIGN KEY (id_repuesto) REFERENCES repuestos(id_repuesto),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio),
    INDEX idx_estado_pedido (estado),
    INDEX idx_servicio_pedido (id_servicio)
);

-- Permite calcular rentabilidad de cada trabajo
CREATE TABLE servicio_tiempos (
    id_servicio_tiempo INT AUTO_INCREMENT PRIMARY KEY,
    id_servicio INT NOT NULL,
    id_tecnico INT NOT NULL,
    horas_estimadas DECIMAL(5,2),
    horas_reales DECIMAL(5,2), -- Al finalizar se registran las horas reales
    costo_hora DECIMAL(10,2), -- Lo que le pagamos al técnico
    precio_hora DECIMAL(10,2), -- Lo que cobramos al cliente
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio) ON DELETE CASCADE,
    FOREIGN KEY (id_tecnico) REFERENCES tecnicos(id_tecnico)
);

-- ================================================================
-- VISTAS PARA CONSULTAS FRECUENTES
-- ================================================================

-- Vista: Estado actual de cada servicio (sin subconsultas en cada query)
CREATE VIEW vista_servicios_actuales AS
SELECT 
    s.id_servicio,
    s.id_equipo,
    s.fecha_recepcion,
    s.problema_reportado,
    s.fecha_entrega,
    h.estado as estado_actual,
    h.fecha_cambio as fecha_ultimo_cambio,
    h.id_usuario_responsable
FROM servicios s
INNER JOIN historial_estados h ON h.id_servicio = s.id_servicio
WHERE h.fecha_cambio = (
    SELECT MAX(h2.fecha_cambio)
    FROM historial_estados h2
    WHERE h2.id_servicio = s.id_servicio
);

-- Vista: Resumen financiero de servicios
CREATE VIEW vista_costos_servicios AS
SELECT 
    s.id_servicio,
    COALESCE(SUM(sr.costo_total), 0) as costo_repuestos,
    COALESCE(SUM(sr.precio_total), 0) as precio_repuestos,
    COALESCE(SUM(st.horas_estimadas * st.costo_hora), 0) as costo_mano_obra,
    COALESCE(SUM(st.horas_estimadas * st.precio_hora), 0) as precio_mano_obra,
    COALESCE(SUM(sr.costo_total), 0) + COALESCE(SUM(st.horas_estimadas * st.costo_hora), 0) as costo_total,
    COALESCE(SUM(sr.precio_total), 0) + COALESCE(SUM(st.horas_estimadas * st.precio_hora), 0) as precio_total,
    (COALESCE(SUM(sr.precio_total), 0) + COALESCE(SUM(st.horas_estimadas * st.precio_hora), 0)) - 
    (COALESCE(SUM(sr.costo_total), 0) + COALESCE(SUM(st.horas_estimadas * st.costo_hora), 0)) as ganancia_estimada
FROM servicios s
LEFT JOIN servicio_repuestos sr ON sr.id_servicio = s.id_servicio
LEFT JOIN servicio_tiempos st ON st.id_servicio = s.id_servicio
GROUP BY s.id_servicio;

-- Vista: Carga de trabajo de técnicos
CREATE VIEW vista_carga_tecnicos AS
SELECT 
    t.id_tecnico,
    t.nombre,
    t.activo,
    COUNT(DISTINCT vsa.id_servicio) as servicios_activos,
    COALESCE(SUM(st.horas_estimadas), 0) as horas_pendientes_estimadas
FROM tecnicos t
LEFT JOIN servicio_tecnicos stec ON stec.id_tecnico = t.id_tecnico
LEFT JOIN vista_servicios_actuales vsa ON vsa.id_servicio = stec.id_servicio
    AND vsa.estado_actual IN ('Recibido','Diagnosticando','Reparando','Esperando repuestos')
LEFT JOIN servicio_tiempos st ON st.id_servicio = vsa.id_servicio AND st.id_tecnico = t.id_tecnico
WHERE t.activo = TRUE
GROUP BY t.id_tecnico, t.nombre, t.activo;

-- ================================================================
-- PROCEDIMIENTOS ALMACENADOS (Lógica de negocio)
-- ================================================================

DELIMITER //

-- Procedimiento: Registrar nuevo servicio
-- Actualiza contador de visitas del cliente automáticamente
CREATE PROCEDURE registrar_servicio(
    IN p_id_equipo INT,
    IN p_problema_reportado TEXT,
    IN p_id_tipo_dano_preliminar INT,
    OUT p_id_servicio INT
)
BEGIN
    DECLARE v_id_cliente INT;
    
    -- Obtener cliente del equipo
    SELECT id_cliente INTO v_id_cliente
    FROM equipos WHERE id_equipo = p_id_equipo;
    
    -- Crear servicio
    INSERT INTO servicios (id_equipo, fecha_recepcion, problema_reportado, id_tipo_dano_preliminar)
    VALUES (p_id_equipo, NOW(), p_problema_reportado, p_id_tipo_dano_preliminar);
    
    SET p_id_servicio = LAST_INSERT_ID();
    
    -- Estado inicial
    INSERT INTO historial_estados (id_servicio, estado, motivo)
    VALUES (p_id_servicio, 'Recibido', 'Equipo recibido del cliente');
    
    -- MEJORA: Incrementar contador de visitas
    UPDATE clientes SET visitas_totales = visitas_totales + 1
    WHERE id_cliente = v_id_cliente;
END //

-- Procedimiento: Asignar técnico óptimo según especialidad y carga
-- CORE del sistema inteligente de asignación
CREATE PROCEDURE asignar_tecnico_optimo(
    IN p_id_servicio INT,
    IN p_id_tipo_equipo INT
)
BEGIN
    DECLARE v_id_tecnico_optimo INT;
    DECLARE v_nombre_tecnico VARCHAR(150);
    DECLARE v_cola_trabajo INT;
    DECLARE v_nivel VARCHAR(20);
    
    -- Buscar técnico con menor carga y mejor nivel en esa especialidad
    SELECT 
        t.id_tecnico,
        t.nombre,
        te.nivel,
        COALESCE(vct.servicios_activos, 0) as cola
    INTO v_id_tecnico_optimo, v_nombre_tecnico, v_nivel, v_cola_trabajo
    FROM tecnicos t
    INNER JOIN tecnico_especialidades te ON te.id_tecnico = t.id_tecnico
    INNER JOIN especialidades e ON e.id_especialidad = te.id_especialidad
    LEFT JOIN vista_carga_tecnicos vct ON vct.id_tecnico = t.id_tecnico
    WHERE t.activo = TRUE
    AND e.id_tipo_equipo = p_id_tipo_equipo
    ORDER BY 
        FIELD(te.nivel, 'Experto', 'Bueno', 'Aprendiz'), -- Priorizar expertos
        COALESCE(vct.servicios_activos, 0) ASC, -- Menor cola
        COALESCE(vct.horas_pendientes_estimadas, 0) ASC -- Menos horas
    LIMIT 1;
    
    -- Asignar técnico
    IF v_id_tecnico_optimo IS NOT NULL THEN
        INSERT INTO servicio_tecnicos (id_servicio, id_tecnico, rol)
        VALUES (p_id_servicio, v_id_tecnico_optimo, 'Principal');
        
        -- Cambiar estado a Diagnosticando
        INSERT INTO historial_estados (id_servicio, estado, id_usuario_responsable, motivo)
        VALUES (p_id_servicio, 'Diagnosticando', v_id_tecnico_optimo, 
                CONCAT('Asignado a ', v_nombre_tecnico, ' (', v_nivel, ') - Cola: ', v_cola_trabajo, ' equipos'));
    END IF;
END //

-- Procedimiento: Calcular estimación de tiempo para nuevo servicio
-- Permite informar al cliente cuándo estará listo aproximadamente
CREATE PROCEDURE estimar_tiempo_servicio(
    IN p_id_tipo_equipo INT,
    IN p_id_tipo_dano INT
)
BEGIN
    SELECT 
        t.id_tecnico,
        t.nombre,
        te.nivel,
        COALESCE(vct.servicios_activos, 0) as equipos_en_cola,
        COALESCE(vct.horas_pendientes_estimadas, 0) as horas_cola,
        td.tiempo_estimado_horas as tiempo_este_equipo,
        COALESCE(vct.horas_pendientes_estimadas, 0) + td.tiempo_estimado_horas as tiempo_total_estimado,
        -- Estimación de cuándo empezaría a reparar este equipo (en días laborables)
        ROUND((COALESCE(vct.horas_pendientes_estimadas, 0) / 8), 1) as dias_hasta_inicio,
        -- Estimación total (días hasta que empiece + días de reparación)
        ROUND(((COALESCE(vct.horas_pendientes_estimadas, 0) + td.tiempo_estimado_horas) / 8), 1) as dias_totales_estimados
    FROM tecnicos t
    INNER JOIN tecnico_especialidades te ON te.id_tecnico = t.id_tecnico
    INNER JOIN especialidades e ON e.id_especialidad = te.id_especialidad
    LEFT JOIN vista_carga_tecnicos vct ON vct.id_tecnico = t.id_tecnico
    CROSS JOIN tipos_dano td
    WHERE t.activo = TRUE
    AND e.id_tipo_equipo = p_id_tipo_equipo
    AND td.id_tipo_dano = p_id_tipo_dano
    ORDER BY 
        FIELD(te.nivel, 'Experto', 'Bueno', 'Aprendiz'),
        tiempo_total_estimado ASC
    LIMIT 3; -- Mostrar las 3 mejores opciones
END //

-- Procedimiento: Obtener equipos de un cliente en servicio activo
CREATE PROCEDURE equipos_cliente_en_servicio(
    IN p_id_cliente INT
)
BEGIN
    SELECT 
        e.id_equipo,
        te.nombre as tipo_equipo,
        m.nombre as marca,
        e.modelo,
        vsa.id_servicio,
        vsa.estado_actual,
        vsa.fecha_recepcion,
        s.problema_reportado,
        GROUP_CONCAT(DISTINCT tec.nombre SEPARATOR ', ') as tecnicos_asignados
    FROM equipos e
    INNER JOIN servicios s ON s.id_equipo = e.id_equipo
    INNER JOIN vista_servicios_actuales vsa ON vsa.id_servicio = s.id_servicio
    INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
    INNER JOIN marcas m ON m.id_marca = e.id_marca
    LEFT JOIN servicio_tecnicos st ON st.id_servicio = s.id_servicio
    LEFT JOIN tecnicos tec ON tec.id_tecnico = st.id_tecnico
    WHERE e.id_cliente = p_id_cliente
    AND vsa.estado_actual IN ('Recibido','Diagnosticando','Reparando','Esperando repuestos')
    GROUP BY e.id_equipo, te.nombre, m.nombre, e.modelo, vsa.id_servicio, 
             vsa.estado_actual, vsa.fecha_recepcion, s.problema_reportado;
END //

-- Procedimiento: Dashboard general del taller
CREATE PROCEDURE dashboard_taller()
BEGIN
    -- Resumen general
    SELECT 
        COUNT(DISTINCT CASE WHEN vsa.estado_actual = 'Recibido' THEN vsa.id_servicio END) as equipos_recibidos,
        COUNT(DISTINCT CASE WHEN vsa.estado_actual = 'Diagnosticando' THEN vsa.id_servicio END) as equipos_diagnosticando,
        COUNT(DISTINCT CASE WHEN vsa.estado_actual = 'Esperando repuestos' THEN vsa.id_servicio END) as esperando_repuestos,
        COUNT(DISTINCT CASE WHEN vsa.estado_actual = 'Reparando' THEN vsa.id_servicio END) as equipos_reparando,
        COUNT(DISTINCT CASE WHEN vsa.estado_actual = 'Finalizado' THEN vsa.id_servicio END) as equipos_finalizados,
        COUNT(DISTINCT t.id_tecnico) as tecnicos_disponibles,
        COALESCE(AVG(vct.servicios_activos), 0) as promedio_carga_tecnicos,
        COUNT(DISTINCT CASE WHEN pr.estado IN ('Pedido','En tránsito') THEN pr.id_pedido END) as repuestos_pendientes
    FROM vista_servicios_actuales vsa
    CROSS JOIN tecnicos t
    LEFT JOIN vista_carga_tecnicos vct ON vct.id_tecnico = t.id_tecnico
    LEFT JOIN pedidos_repuestos pr ON pr.estado IN ('Pedido','En tránsito')
    WHERE t.activo = TRUE;
    
    -- Carga por técnico
    SELECT * FROM vista_carga_tecnicos ORDER BY servicios_activos DESC;
    
    -- Servicios por estado
    SELECT 
        vsa.estado_actual,
        COUNT(*) as cantidad,
        GROUP_CONCAT(DISTINCT CONCAT(c.nombre, ' - ', te.nombre) SEPARATOR '; ') as ejemplos
    FROM vista_servicios_actuales vsa
    INNER JOIN servicios s ON s.id_servicio = vsa.id_servicio
    INNER JOIN equipos e ON e.id_equipo = s.id_equipo
    INNER JOIN clientes c ON c.id_cliente = e.id_cliente
    INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
    GROUP BY vsa.estado_actual
    ORDER BY FIELD(vsa.estado_actual, 'Recibido','Diagnosticando','Esperando repuestos','Reparando','Finalizado','Entregado');
END //

-- Procedimiento: Cambiar estado de servicio con auditoría
CREATE PROCEDURE cambiar_estado_servicio(
    IN p_id_servicio INT,
    IN p_nuevo_estado ENUM('Recibido','Diagnosticando','Esperando repuestos','Reparando','Finalizado','Entregado','Cancelado'),
    IN p_id_tecnico INT,
    IN p_motivo TEXT
)
BEGIN
    INSERT INTO historial_estados (id_servicio, estado, id_usuario_responsable, motivo)
    VALUES (p_id_servicio, p_nuevo_estado, p_id_tecnico, p_motivo);
    
    -- Si se entrega, registrar fecha
    IF p_nuevo_estado = 'Entregado' THEN
        UPDATE servicios SET fecha_entrega = NOW()
        WHERE id_servicio = p_id_servicio;
    END IF;
END //

DELIMITER ;

-- ================================================================
-- ÍNDICES PARA OPTIMIZACIÓN
-- ================================================================

CREATE INDEX idx_servicio_fecha_recep ON servicios(fecha_recepcion DESC);
CREATE INDEX idx_historial_estado ON historial_estados(estado);
CREATE INDEX idx_tecnico_activo ON tecnicos(activo);
CREATE INDEX idx_equipo_tipo ON equipos(id_tipo_equipo);

-- ================================================================
-- DATOS DE EJEMPLO
-- ================================================================

-- Municipios
INSERT INTO municipios (nombre, departamento) VALUES 
('Guatemala', 'Guatemala'),
('Mixco', 'Guatemala'),
('Villa Nueva', 'Guatemala');

-- Tipos de equipos
INSERT INTO tipos_equipo (nombre) VALUES 
('Smartphone'),
('Laptop'),
('Computadora de escritorio'),
('Impresora'),
('Tablet'),
('Consola de videojuegos');

-- Marcas
INSERT INTO marcas (nombre) VALUES 
('Samsung'),
('Apple'),
('HP'),
('Dell'),
('Lenovo'),
('Epson'),
('Sony'),
('Nintendo');

-- Especialidades
INSERT INTO especialidades (nombre, id_tipo_equipo) VALUES 
('smartphones', 1),
('laptops', 2),
('PCs de escritorio', 3),
('impresoras', 4),
('tablets', 5),
('consolas', 6);

-- Tipos de daños comunes
INSERT INTO tipos_dano (nombre, id_tipo_equipo, tiempo_estimado_horas) VALUES 
('Pantalla rota', 1, 2.5),
('No enciende', 1, 4.0),
('Batería se descarga rápido', 1, 1.5),
('Problemas de carga', 1, 2.0),
('Pantalla rota', 2, 3.0),
('No enciende', 2, 5.0),
('Teclado no funciona', 2, 2.5),
('Sobrecalentamiento', 2, 4.0),
('No imprime', 4, 1.5),
('Atasco de papel', 4, 1.0),
('Calidad de impresión baja', 4, 2.0);

-- Partes
INSERT INTO partes (nombre) VALUES 
('Pantalla'),
('Batería'),
('Placa base'),
('Teclado'),
('Disco duro'),
('RAM'),
('Cargador'),
('Puerto USB'),
('Ventilador'),
('Cabezal de impresión');

-- Repuestos ejemplo
INSERT INTO repuestos (nombre, descripcion, cantidad, costo_unitario, precio_unitario) VALUES 
('Pantalla Samsung Galaxy S21', 'LCD Original', 5, 1800.00, 2200.00),
('Batería iPhone 12', 'Batería original Apple', 10, 450.00, 650.00),
('Teclado HP Pavilion', 'Teclado español retroiluminado', 3, 350.00, 550.00),
('Disco SSD 256GB', 'SSD SATA 2.5"', 8, 280.00, 420.00),
('RAM DDR4 8GB', 'Memoria RAM laptop', 12, 320.00, 480.00);

-- Técnicos
INSERT INTO tecnicos (nombre, telefono, email, activo) VALUES 
('Carlos Méndez', '5551-1234', 'carlos.mendez@taller.com', TRUE),
('Ana García', '5551-5678', 'ana.garcia@taller.com', TRUE),
('Luis Pérez', '5551-9012', 'luis.perez@taller.com', TRUE),
('María López', '5551-3456', 'maria.lopez@taller.com', TRUE);

-- Asignar especialidades a técnicos
INSERT INTO tecnico_especialidades (id_tecnico, id_especialidad, nivel) VALUES 
(1, 1, 'Experto'),    -- Carlos: Experto en smartphones
(1, 6, 'Bueno'),      -- Carlos: Bueno en consolas
(2, 2, 'Experto'),    -- Ana: Experto en laptops
(2, 3, 'Bueno'),      -- Ana: Buena en PCs escritorio
(3, 4, 'Experto'),    -- Luis: Experto en impresoras
(3, 1, 'Aprendiz'),   -- Luis: Aprendiz en smartphones
(4, 2, 'Bueno'),      -- María: Buena en laptops
(4, 5, 'Experto');    -- María: Experto en tablets

-- ================================================================
-- EJEMPLOS DE USO
-- ================================================================

/*
-- 1. Registrar cliente nuevo
INSERT INTO clientes (nombre, telefono, email, id_municipio, origen) VALUES 
('Juan Pérez', '5555-1234', 'juan.perez@email.com', 1, 'Redes sociales');

-- 2. Registrar equipo del cliente
INSERT INTO equipos (id_cliente, id_tipo_equipo, id_marca, modelo, numero_serie) VALUES 
(1, 1, 1, 'Galaxy S21', 'SN123456789');

-- 3. Crear servicio nuevo (usa procedimiento para actualizar visitas)
CALL registrar_servicio(1, 'La pantalla no responde al tacto', 1, @id_servicio);
SELECT @id_servicio; -- Ver el ID del servicio creado

-- 4. Asignar técnico óptimo automáticamente
CALL asignar_tecnico_optimo(@id_servicio, 1); -- 1 = Smartphone

-- 5. Ver estimación de tiempo para un nuevo equipo
CALL estimar_tiempo_servicio(1, 1); -- Tipo: Smartphone, Daño: Pantalla rota

-- 6. Agregar diagnóstico técnico
INSERT INTO servicio_partes (id_servicio, id_parte, descripcion, es_diagnostico_tecnico, fecha_diagnostico, id_tecnico_diagnostico)
VALUES (@id_servicio, 1, 'Pantalla LCD dañada, táctil no responde', TRUE, NOW(), 1);

-- 7. Cambiar estado a reparando
CALL cambiar_estado_servicio(@id_servicio, 'Reparando', 1, 'Diagnóstico completado, iniciando reparación');

-- 8. Agregar repuestos usados
INSERT INTO servicio_repuestos (id_servicio, id_repuesto, cantidad, costo_total, precio_total)
VALUES (@id_servicio, 1, 1, 1800.00, 2200.00);

-- 9. Agregar tiempo de mano de obra
INSERT INTO servicio_tiempos (id_servicio, id_tecnico, horas_estimadas, costo_hora, precio_hora)
VALUES (@id_servicio, 1, 2.5, 100.00, 150.00);

-- 10. Ver costos y ganancias estimadas del servicio
SELECT * FROM vista_costos_servicios WHERE id_servicio = @id_servicio;

-- 11. Finalizar servicio
CALL cambiar_estado_servicio(@id_servicio, 'Finalizado', 1, 'Reparación completada exitosamente');

-- 12. Entregar al cliente
CALL cambiar_estado_servicio(@id_servicio, 'Entregado', 1, 'Cliente retiró el equipo');

-- 13. Ver equipos de un cliente en servicio
CALL equipos_cliente_en_servicio(1);

-- 14. Dashboard del taller
CALL dashboard_taller();

-- 15. Ver carga de trabajo de técnicos
SELECT * FROM vista_carga_tecnicos;

-- 16. Ver historial completo de un servicio
SELECT 
    h.id_historial,
    h.estado,
    h.fecha_cambio,
    t.nombre as responsable,
    h.motivo
FROM historial_estados h
LEFT JOIN tecnicos t ON t.id_tecnico = h.id_usuario_responsable
WHERE h.id_servicio = @id_servicio
ORDER BY h.fecha_cambio;

-- 17. Pedir un repuesto que no hay en stock
INSERT INTO pedidos_repuestos (id_repuesto, id_servicio, cantidad, fecha_pedido, fecha_estimada_llegada, estado, proveedor)
VALUES (1, @id_servicio, 2, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 5 DAY), 'Pedido', 'Proveedor Samsung');

-- 18. Cambiar estado a "Esperando repuestos"
CALL cambiar_estado_servicio(@id_servicio, 'Esperando repuestos', 1, 'Pantalla agotada, pedido realizado');

-- 19. Ver servicios esperando repuestos
SELECT 
    s.id_servicio,
    c.nombre as cliente,
    te.nombre as tipo_equipo,
    vsa.estado_actual,
    pr.id_pedido,
    r.nombre as repuesto,
    pr.fecha_estimada_llegada,
    DATEDIFF(pr.fecha_estimada_llegada, CURDATE()) as dias_restantes
FROM servicios s
INNER JOIN vista_servicios_actuales vsa ON vsa.id_servicio = s.id_servicio
INNER JOIN equipos e ON e.id_equipo = s.id_equipo
INNER JOIN clientes c ON c.id_cliente = e.id_cliente
INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
LEFT JOIN pedidos_repuestos pr ON pr.id_servicio = s.id_servicio
LEFT JOIN repuestos r ON r.id_repuesto = pr.id_repuesto
WHERE vsa.estado_actual = 'Esperando repuestos';

-- 20. Consulta: Clientes con más visitas
SELECT 
    c.id_cliente,
    c.nombre,
    c.telefono,
    c.visitas_totales,
    c.origen,
    COUNT(DISTINCT e.id_equipo) as total_equipos,
    COUNT(DISTINCT s.id_servicio) as total_servicios
FROM clientes c
LEFT JOIN equipos e ON e.id_cliente = c.id_cliente
LEFT JOIN servicios s ON s.id_equipo = e.id_equipo
GROUP BY c.id_cliente, c.nombre, c.telefono, c.visitas_totales, c.origen
ORDER BY c.visitas_totales DESC;

-- 21. Consulta: Servicios más rentables
SELECT 
    s.id_servicio,
    c.nombre as cliente,
    te.nombre as tipo_equipo,
    vcs.costo_total,
    vcs.precio_total,
    vcs.ganancia_estimada,
    ROUND((vcs.ganancia_estimada / vcs.costo_total * 100), 2) as margen_porcentaje
FROM servicios s
INNER JOIN vista_costos_servicios vcs ON vcs.id_servicio = s.id_servicio
INNER JOIN equipos e ON e.id_equipo = s.id_equipo
INNER JOIN clientes c ON c.id_cliente = e.id_cliente
INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
WHERE vcs.precio_total > 0
ORDER BY vcs.ganancia_estimada DESC;

-- 22. Consulta: Rendimiento de técnicos
SELECT 
    t.id_tecnico,
    t.nombre,
    COUNT(DISTINCT st.id_servicio) as servicios_totales,
    AVG(stime.horas_reales) as promedio_horas_por_servicio,
    SUM(stime.horas_reales * stime.precio_hora) as ingresos_generados,
    GROUP_CONCAT(DISTINCT CONCAT(e.nombre, ' (', te.nivel, ')') SEPARATOR ', ') as especialidades
FROM tecnicos t
LEFT JOIN servicio_tecnicos st ON st.id_tecnico = t.id_tecnico
LEFT JOIN servicio_tiempos stime ON stime.id_tecnico = t.id_tecnico AND stime.id_servicio = st.id_servicio
LEFT JOIN tecnico_especialidades te ON te.id_tecnico = t.id_tecnico
LEFT JOIN especialidades e ON e.id_especialidad = te.id_especialidad
GROUP BY t.id_tecnico, t.nombre
ORDER BY servicios_totales DESC;

-- 23. Consulta: Tipos de daños más comunes
SELECT 
    td.nombre as tipo_dano,
    te.nombre as tipo_equipo,
    COUNT(s.id_servicio) as cantidad_reportes,
    AVG(td.tiempo_estimado_horas) as tiempo_promedio_reparacion,
    GROUP_CONCAT(DISTINCT m.nombre) as marcas_afectadas
FROM servicios s
INNER JOIN tipos_dano td ON td.id_tipo_dano = s.id_tipo_dano_preliminar
INNER JOIN equipos e ON e.id_equipo = s.id_equipo
INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
INNER JOIN marcas m ON m.id_marca = e.id_marca
GROUP BY td.id_tipo_dano, td.nombre, te.nombre
ORDER BY cantidad_reportes DESC;

-- 24. Consulta: Equipos con múltiples servicios (clientes recurrentes)
SELECT 
    e.id_equipo,
    c.nombre as cliente,
    te.nombre as tipo_equipo,
    m.nombre as marca,
    e.modelo,
    COUNT(s.id_servicio) as veces_reparado,
    MIN(s.fecha_recepcion) as primera_reparacion,
    MAX(s.fecha_recepcion) as ultima_reparacion
FROM equipos e
INNER JOIN clientes c ON c.id_cliente = e.id_cliente
INNER JOIN tipos_equipo te ON te.id_tipo_equipo = e.id_tipo_equipo
INNER JOIN marcas m ON m.id_marca = e.id_marca
INNER JOIN servicios s ON s.id_equipo = e.id_equipo
GROUP BY e.id_equipo, c.nombre, te.nombre, m.nombre, e.modelo
HAVING COUNT(s.id_servicio) > 1
ORDER BY veces_reparado DESC;

*/

-- ================================================================
-- TRIGGERS PARA AUTOMATIZACIÓN
-- ================================================================

DELIMITER //

-- Trigger: Actualizar stock de repuestos al usar en servicio
CREATE TRIGGER after_insert_servicio_repuestos
AFTER INSERT ON servicio_repuestos
FOR EACH ROW
BEGIN
    UPDATE repuestos 
    SET cantidad = cantidad - NEW.cantidad
    WHERE id_repuesto = NEW.id_repuesto;
END //

-- Trigger: Calcular totales automáticamente en servicio_repuestos
CREATE TRIGGER before_insert_servicio_repuestos
BEFORE INSERT ON servicio_repuestos
FOR EACH ROW
BEGIN
    DECLARE v_costo DECIMAL(10,2);
    DECLARE v_precio DECIMAL(10,2);
    
    SELECT costo_unitario, precio_unitario 
    INTO v_costo, v_precio
    FROM repuestos 
    WHERE id_repuesto = NEW.id_repuesto;
    
    SET NEW.costo_total = v_costo * NEW.cantidad;
    SET NEW.precio_total = v_precio * NEW.cantidad;
END //

-- Trigger: Actualizar stock cuando llega un pedido de repuestos
CREATE TRIGGER after_update_pedido_repuestos
AFTER UPDATE ON pedidos_repuestos
FOR EACH ROW
BEGIN
    IF NEW.estado = 'Recibido' AND OLD.estado != 'Recibido' THEN
        UPDATE repuestos 
        SET cantidad = cantidad + NEW.cantidad
        WHERE id_repuesto = NEW.id_repuesto;
        
        -- Si el pedido era para un servicio específico, cambiar estado
        IF NEW.id_servicio IS NOT NULL THEN
            INSERT INTO historial_estados (id_servicio, estado, motivo)
            VALUES (NEW.id_servicio, 'Reparando', 
                    CONCAT('Repuesto recibido: ', (SELECT nombre FROM repuestos WHERE id_repuesto = NEW.id_repuesto)));
        END IF;
    END IF;
END //

DELIMITER ;