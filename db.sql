-- usuarios del sistema
CREATE TABLE morataya_usuario (
    usu_id SERIAL PRIMARY KEY,
    usu_nombre VARCHAR(100) NOT NULL,
    usu_codigo INTEGER UNIQUE NOT NULL,
    usu_password VARCHAR(150) NOT NULL,
    usu_correo VARCHAR(100) UNIQUE,
    usu_fotografia VARCHAR(255),
    usu_situacion SMALLINT DEFAULT 1
);

-- roles de usuario
CREATE TABLE morataya_rol (
    rol_id SERIAL PRIMARY KEY,
    rol_nombre VARCHAR(75) NOT NULL UNIQUE,
    rol_nombre_ct VARCHAR(25),
    rol_situacion SMALLINT DEFAULT 1
);

-- permisos por usuario y rol
CREATE TABLE morataya_permiso (
    permiso_id SERIAL PRIMARY KEY,
    permiso_usuario INT NOT NULL,
    permiso_rol INT NOT NULL,
    permiso_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (permiso_usuario) REFERENCES morataya_usuario(usu_id),
    FOREIGN KEY (permiso_rol) REFERENCES morataya_rol(rol_id)
);

-- aplicaciones o módulos del sistema
CREATE TABLE morataya_aplicacion (
    app_id SERIAL PRIMARY KEY,
    app_nombre VARCHAR(100) NOT NULL UNIQUE,
    app_url VARCHAR(255),
    app_icono VARCHAR(100),
    app_situacion SMALLINT DEFAULT 1
);

-- permisos de acceso por aplicación y rol
CREATE TABLE morataya_permiso_aplicacion (
    perm_app_id SERIAL PRIMARY KEY,
    rol_id INT NOT NULL,
    app_id INT NOT NULL,
    acceso SMALLINT DEFAULT 1,
    FOREIGN KEY (rol_id) REFERENCES morataya_rol(rol_id),
    FOREIGN KEY (app_id) REFERENCES morataya_aplicacion(app_id)
);

-- historial de acciones del sistema
CREATE TABLE morataya_auditoria (
    aud_id SERIAL PRIMARY KEY,
    usu_id INT NOT NULL,
    aud_usuario_nombre VARCHAR(100) NOT NULL,
    aud_modulo VARCHAR(100) NOT NULL,
    aud_accion VARCHAR(100) NOT NULL,
    aud_descripcion VARCHAR(255),
    aud_ruta VARCHAR(255),
    aud_ip VARCHAR(50),
    aud_navegador VARCHAR(100),
    aud_fecha DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    aud_fecha_creacion DATETIME YEAR TO SECOND DEFAULT CURRENT YEAR TO SECOND,
    aud_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (usu_id) REFERENCES morataya_usuario(usu_id)
);

-- personal que recibe dotación
CREATE TABLE morataya_personal (
    personal_id SERIAL PRIMARY KEY,
    personal_nombre VARCHAR(100) NOT NULL,
    personal_cui CHAR(13) UNIQUE NOT NULL,
    personal_puesto VARCHAR(100),
    personal_fecha_ingreso DATE,
    personal_situacion SMALLINT DEFAULT 1
);

-- tipos de dotación: botas, camisa, pantalón
CREATE TABLE morataya_tipos_dotacion (
    tipo_id SERIAL PRIMARY KEY,
    tipo_nombre VARCHAR(50) NOT NULL UNIQUE,
    tipo_descripcion VARCHAR(100),
    tipo_situacion SMALLINT DEFAULT 1
);

-- tallas disponibles
CREATE TABLE morataya_tallas (
    talla_id SERIAL PRIMARY KEY,
    talla_etiqueta VARCHAR(10) NOT NULL UNIQUE,
    talla_situacion SMALLINT DEFAULT 1
);

-- inventario por tipo y talla
CREATE TABLE morataya_inventario_dotacion (
    inv_id SERIAL PRIMARY KEY,
    tipo_id INT NOT NULL,
    talla_id INT NOT NULL,
    cantidad INT NOT NULL,
    fecha_ingreso DATE DEFAULT TODAY,
    inv_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (tipo_id) REFERENCES morataya_tipos_dotacion(tipo_id),
    FOREIGN KEY (talla_id) REFERENCES morataya_tallas(talla_id)
);


-- solicitudes realizadas por el personal
CREATE TABLE morataya_solicitudes_dotacion (
    solicitud_id SERIAL PRIMARY KEY,
    personal_id INT NOT NULL,
    tipo_id INT NOT NULL,
    talla_id INT NOT NULL,
    cantidad INT NOT NULL DEFAULT 1,
    fecha_solicitud DATE DEFAULT TODAY,
    estado_entrega SMALLINT DEFAULT 0,
    solicitud_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (personal_id) REFERENCES morataya_personal(personal_id),
    FOREIGN KEY (tipo_id) REFERENCES morataya_tipos_dotacion(tipo_id),
    FOREIGN KEY (talla_id) REFERENCES morataya_tallas(talla_id)
);


-- entregas realizadas
CREATE TABLE morataya_entregas_dotacion (
    entrega_id SERIAL PRIMARY KEY,
    solicitud_id INT NOT NULL,
    usuario_id INT NOT NULL,
    fecha_entrega DATE DEFAULT TODAY,
    entrega_situacion SMALLINT DEFAULT 1,
    FOREIGN KEY (solicitud_id) REFERENCES morataya_solicitudes_dotacion(solicitud_id),
    FOREIGN KEY (usuario_id) REFERENCES morataya_usuario(usu_id)
);

-- Insertar roles del sistema
INSERT INTO morataya_rol (rol_nombre, rol_nombre_ct, rol_situacion) VALUES 
('ADMINISTRADOR', 'ADMIN', 1);
INSERT INTO morataya_rol (rol_nombre, rol_nombre_ct, rol_situacion) VALUES 
('USUARIO', 'USER', 1);
INSERT INTO morataya_rol (rol_nombre, rol_nombre_ct, rol_situacion) VALUES 
('SUPERVISOR', 'SUPER', 1);
INSERT INTO morataya_rol (rol_nombre, rol_nombre_ct, rol_situacion) VALUES 
('ENCARGADO_INVENTARIO', 'INV', 1);
INSERT INTO morataya_rol (rol_nombre, rol_nombre_ct, rol_situacion) VALUES 
('SOLO_CONSULTA', 'READ', 1);


INSERT INTO morataya_permiso (permiso_usuario, permiso_rol, permiso_situacion) VALUES 
(1, 1, 1);
INSERT INTO morataya_permiso (permiso_usuario, permiso_rol, permiso_situacion) VALUES 
(3, 3, 1);
INSERT INTO morataya_permiso (permiso_usuario, permiso_rol, permiso_situacion) VALUES 
(4, 4, 1);
INSERT INTO morataya_permiso (permiso_usuario, permiso_rol, permiso_situacion) VALUES 
(5, 5, 1);

INSERT INTO morataya_personal (personal_nombre, personal_cui, personal_puesto, personal_fecha_ingreso, personal_situacion) VALUES 
('Soldado Juan Morales', '1234567890123', 'Soldado Raso', '2023-01-15', 1);
INSERT INTO morataya_personal (personal_nombre, personal_cui, personal_puesto, personal_fecha_ingreso, personal_situacion) VALUES 
('Cabo Pedro Sanchez', '2345678901234', 'Cabo', '2022-06-10', 1);
INSERT INTO morataya_personal (personal_nombre, personal_cui, personal_puesto, personal_fecha_ingreso, personal_situacion) VALUES 
('Sargento Luis Ramirez', '3456789012345', 'Sargento Segundo', '2021-03-20', 1);
INSERT INTO morataya_personal (personal_nombre, personal_cui, personal_puesto, personal_fecha_ingreso, personal_situacion) VALUES 
('Teniente Sofia Martinez', '4567890123456', 'Teniente', '2020-09-05', 1);
INSERT INTO morataya_personal (personal_nombre, personal_cui, personal_puesto, personal_fecha_ingreso, personal_situacion) VALUES 
('Capitan Roberto Hernandez', '5678901234567', 'Capitan', '2019-11-12', 1);

INSERT INTO morataya_tipos_dotacion (tipo_nombre, tipo_descripcion, tipo_situacion) VALUES 
('BOTAS', 'Botas militares de campaña', 1);
INSERT INTO morataya_tipos_dotacion (tipo_nombre, tipo_descripcion, tipo_situacion) VALUES 
('CAMISA', 'Camisa de uniforme militar', 1);
INSERT INTO morataya_tipos_dotacion (tipo_nombre, tipo_descripcion, tipo_situacion) VALUES 
('PANTALON', 'Pantalón de uniforme militar', 1);
INSERT INTO morataya_tipos_dotacion (tipo_nombre, tipo_descripcion, tipo_situacion) VALUES 
('GORRA', 'Gorra militar reglamentaria', 1);
INSERT INTO morataya_tipos_dotacion (tipo_nombre, tipo_descripcion, tipo_situacion) VALUES 
('CINTURON', 'Cinturón militar de cuero', 1);

INSERT INTO morataya_tallas (talla_etiqueta, talla_situacion) VALUES 
('S', 1);
INSERT INTO morataya_tallas (talla_etiqueta, talla_situacion) VALUES 
('M', 1);
INSERT INTO morataya_tallas (talla_etiqueta, talla_situacion) VALUES 
('L', 1);
INSERT INTO morataya_tallas (talla_etiqueta, talla_situacion) VALUES 
('XL', 1);
INSERT INTO morataya_tallas (talla_etiqueta, talla_situacion) VALUES 
('XXL', 1);

INSERT INTO morataya_inventario_dotacion (tipo_id, talla_id, cantidad, fecha_ingreso, inv_situacion) VALUES 
(1, 1, 50, TODAY, 1);
INSERT INTO morataya_inventario_dotacion (tipo_id, talla_id, cantidad, fecha_ingreso, inv_situacion) VALUES 
(1, 2, 75, TODAY, 1);
INSERT INTO morataya_inventario_dotacion (tipo_id, talla_id, cantidad, fecha_ingreso, inv_situacion) VALUES 
(2, 2, 100, TODAY, 1);
INSERT INTO morataya_inventario_dotacion (tipo_id, talla_id, cantidad, fecha_ingreso, inv_situacion) VALUES 
(3, 3, 80, TODAY, 1);
INSERT INTO morataya_inventario_dotacion (tipo_id, talla_id, cantidad, fecha_ingreso, inv_situacion) VALUES 
(4, 1, 45, TODAY, 1);

INSERT INTO morataya_solicitudes_dotacion (personal_id, tipo_id, talla_id, fecha_solicitud, estado_entrega, solicitud_situacion) VALUES 
(1, 1, 2, TODAY, 0, 1);
INSERT INTO morataya_solicitudes_dotacion (personal_id, tipo_id, talla_id, fecha_solicitud, estado_entrega, solicitud_situacion) VALUES 
(2, 2, 3, TODAY, 1, 1);
INSERT INTO morataya_solicitudes_dotacion (personal_id, tipo_id, talla_id, fecha_solicitud, estado_entrega, solicitud_situacion) VALUES 
(3, 3, 3, TODAY, 0, 1);
INSERT INTO morataya_solicitudes_dotacion (personal_id, tipo_id, talla_id, fecha_solicitud, estado_entrega, solicitud_situacion) VALUES 
(4, 4, 1, TODAY, 1, 1);
INSERT INTO morataya_solicitudes_dotacion (personal_id, tipo_id, talla_id, fecha_solicitud, estado_entrega, solicitud_situacion) VALUES 
(5, 5, 4, TODAY, 0, 1);

-- Nota: Las contraseñas están hasheadas
INSERT INTO morataya_usuario (usu_nombre, usu_codigo, usu_password, usu_correo, usu_situacion) VALUES 
('Carlos Rodriguez', 100001, '123456', 'carlos@mindef.gob.gt', 1);
INSERT INTO morataya_usuario (usu_nombre, usu_codigo, usu_password, usu_correo, usu_situacion) VALUES 
('Maria Lopez', 100002, '123456', 'maria@mindef.gob.gt', 1);
INSERT INTO morataya_usuario (usu_nombre, usu_codigo, usu_password, usu_correo, usu_situacion) VALUES 
('Jose Perez', 100003, '123456', 'jose@mindef.gob.gt', 1);
INSERT INTO morataya_usuario (usu_nombre, usu_codigo, usu_password, usu_correo, usu_situacion) VALUES 
('Ana Garcia', 100004, '123456', 'ana@mindef.gob.gt', 1);