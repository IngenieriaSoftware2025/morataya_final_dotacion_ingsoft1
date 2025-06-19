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
