-- Instalar en una base VACÍA seleccionada previamente. No contiene clientes ni pedidos de muestra.
CREATE TABLE usuarios (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  correo VARCHAR(100) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  telefono VARCHAR(20) NOT NULL,
  tipo_documento VARCHAR(20) NOT NULL,
  documento VARCHAR(30) NOT NULL,
  pais VARCHAR(80) NOT NULL,
  departamento VARCHAR(80) NOT NULL,
  ciudad VARCHAR(80) NOT NULL,
  session_version INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE productos (
  id_producto INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre_producto VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  CONSTRAINT chk_producto_precio CHECK (precio >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE inventario_tallas (
  id_inventario INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  talla ENUM('S','M','L','XL') NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  reservado INT NOT NULL DEFAULT 0,
  UNIQUE KEY uq_producto_talla (id_producto, talla),
  CONSTRAINT fk_inventario_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto) ON DELETE CASCADE,
  CONSTRAINT chk_inventario CHECK (stock >= 0 AND reservado >= 0 AND reservado <= stock)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE carrito (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_usuario INT NOT NULL DEFAULT 0,
  id_producto INT NOT NULL,
  id_sesion VARCHAR(225) NOT NULL DEFAULT '',
  clave_propietario VARCHAR(260) NOT NULL,
  nombre_producto VARCHAR(100) NOT NULL,
  precio DECIMAL(10,2) NOT NULL,
  cantidad INT NOT NULL DEFAULT 1,
  imagen VARCHAR(255) NOT NULL,
  INDEX idx_carrito_usuario (id_usuario),
  INDEX idx_carrito_sesion (id_sesion),
  UNIQUE KEY uq_carrito_propietario_producto (clave_propietario, id_producto),
  CONSTRAINT fk_carrito_producto FOREIGN KEY (id_producto) REFERENCES productos(id_producto),
  CONSTRAINT chk_carrito_cantidad CHECK (cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE recuperaciones_clave (
  usuario_id INT NOT NULL PRIMARY KEY,
  token_hash CHAR(64) NOT NULL UNIQUE,
  expira_en DATETIME NOT NULL,
  CONSTRAINT fk_recuperaciones_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedidos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  referencia CHAR(32) NOT NULL UNIQUE,
  solicitud_id CHAR(32) NOT NULL UNIQUE,
  usuario_id INT NULL,
  sesion_hash CHAR(64) NULL,
  nombre VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  correo VARCHAR(190) NULL,
  telefono VARCHAR(30) NOT NULL,
  tipo_documento VARCHAR(30) NOT NULL,
  documento VARCHAR(30) NOT NULL,
  pais VARCHAR(80) NOT NULL,
  departamento VARCHAR(80) NOT NULL,
  ciudad VARCHAR(80) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  observaciones TEXT NULL,
  metodo_pago ENUM('tarjeta','pse','contraentrega') NOT NULL,
  estado_pago ENUM('pendiente','confirmado','fallido','reembolsado') NOT NULL DEFAULT 'pendiente',
  estado_pedido ENUM('recibido','preparando','enviado','entregado','cancelado','devuelto') NOT NULL DEFAULT 'recibido',
  total DECIMAL(12,2) NOT NULL,
  reserva_expira_en DATETIME NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pedidos_usuario (usuario_id),
  INDEX idx_pedidos_estado (estado_pago, estado_pedido),
  INDEX idx_pedidos_reserva (estado_pago, reserva_expira_en),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL,
  CONSTRAINT chk_pedido_total CHECK (total >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pedido_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  producto_id INT NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  talla ENUM('S','M','L','XL') NOT NULL,
  precio DECIMAL(12,2) NOT NULL,
  cantidad INT NOT NULL,
  UNIQUE KEY uq_item_pedido_producto_talla (pedido_id, producto_id, talla),
  CONSTRAINT fk_items_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_producto_restrict FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE RESTRICT,
  CONSTRAINT chk_item_valores CHECK (precio >= 0 AND cantidad > 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE pago_eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  proveedor VARCHAR(40) NOT NULL,
  evento_externo VARCHAR(120) NOT NULL,
  tipo ENUM('confirmado','fallido','reembolsado') NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_evento_proveedor (proveedor, evento_externo),
  CONSTRAINT fk_evento_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE notificaciones (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  canal ENUM('email','whatsapp') NOT NULL,
  evento VARCHAR(40) NOT NULL,
  estado ENUM('pendiente','enviado','fallido') NOT NULL DEFAULT 'pendiente',
  intentos INT NOT NULL DEFAULT 0,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_notificacion (pedido_id, canal, evento),
  CONSTRAINT fk_notificacion_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE limites_solicitud (
  clave CHAR(64) NOT NULL PRIMARY KEY,
  inicio DATETIME NOT NULL,
  contador INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ideas (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contenido TEXT NOT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE preguntas (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  contenido TEXT NOT NULL,
  fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO productos (id_producto, nombre_producto, precio, imagen) VALUES
  (1, 'Detras del DJ', 100000.00, 'Buzos/B7-frente.png'),
  (2, 'Pretty GIRL', 100000.00, 'Buzos/B6-frente.png'),
  (3, 'Jocelyn...', 100000.00, 'Buzos/B3-frente.png'),
  (4, 'Game Explained', 100000.00, 'Buzos/B2-frente.png'),
  (5, 'INTRO', 100000.00, 'Buzos/B1-frente.png'),
  (6, 'XXX', 100000.00, 'Buzos/B4-frente.png'),
  (7, 'New Sunset', 100000.00, 'Buzos/B8-frente.png'),
  (8, 'Cieelo', 100000.00, 'Buzos/B5-frente.png');
INSERT INTO inventario_tallas (id_producto, talla, stock)
  SELECT p.id_producto, t.talla, 0 FROM productos p
  CROSS JOIN (SELECT 'S' AS talla UNION ALL SELECT 'M' UNION ALL SELECT 'L' UNION ALL SELECT 'XL') t;
