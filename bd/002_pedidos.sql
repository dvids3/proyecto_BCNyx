-- Aplicar una sola vez después de base_de_datos_bcnyx.sql.
ALTER TABLE usuarios
  MODIFY apellidos VARCHAR(100) NOT NULL,
  MODIFY documento VARCHAR(30) NOT NULL,
  MODIFY pais VARCHAR(80) NOT NULL,
  MODIFY departamento VARCHAR(80) NOT NULL,
  MODIFY ciudad VARCHAR(80) NOT NULL;
ALTER TABLE usuarios ADD COLUMN session_version INT NOT NULL DEFAULT 0;

ALTER TABLE carrito ADD COLUMN clave_propietario VARCHAR(260) NOT NULL DEFAULT '';
UPDATE carrito SET clave_propietario = CASE WHEN id_usuario > 0 THEN CONCAT('u:', id_usuario) ELSE CONCAT('s:', id_sesion) END;
ALTER TABLE carrito ADD UNIQUE KEY uq_carrito_propietario_producto (clave_propietario, id_producto);

ALTER TABLE inventario_tallas
  ADD UNIQUE KEY uq_producto_talla (id_producto, talla),
  ADD CONSTRAINT chk_inventario CHECK (stock >= 0 AND reservado >= 0 AND reservado <= stock);

CREATE TABLE pedidos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  referencia CHAR(32) NOT NULL UNIQUE,
  solicitud_id CHAR(32) NOT NULL UNIQUE,
  usuario_id INT(11) NULL,
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
  CONSTRAINT chk_pedido_total CHECK (total >= 0),
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  actualizado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_pedidos_usuario (usuario_id),
  INDEX idx_pedidos_estado (estado_pago, estado_pedido),
  INDEX idx_pedidos_reserva (estado_pago, reserva_expira_en),
  CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pedido_items (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  producto_id INT(11) NOT NULL,
  nombre VARCHAR(100) NOT NULL,
  imagen VARCHAR(255) NOT NULL,
  talla ENUM('S','M','L','XL') NOT NULL,
  precio DECIMAL(12,2) NOT NULL,
  cantidad INT NOT NULL,
  CONSTRAINT chk_item_valores CHECK (precio >= 0 AND cantidad > 0),
  UNIQUE KEY uq_item_pedido_producto_talla (pedido_id, producto_id, talla),
  CONSTRAINT fk_items_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
  CONSTRAINT fk_items_producto_restrict FOREIGN KEY (producto_id) REFERENCES productos(id_producto) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE pago_eventos (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  pedido_id BIGINT UNSIGNED NOT NULL,
  proveedor VARCHAR(40) NOT NULL,
  evento_externo VARCHAR(120) NOT NULL,
  tipo ENUM('confirmado','fallido','reembolsado') NOT NULL,
  creado_en DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_evento_proveedor (proveedor, evento_externo),
  CONSTRAINT fk_evento_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE limites_solicitud (
  clave CHAR(64) NOT NULL PRIMARY KEY,
  inicio DATETIME NOT NULL,
  contador INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
