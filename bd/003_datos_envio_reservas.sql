-- Aplicar una sola vez en bases que ya tienen la tabla pedidos.
ALTER TABLE pedidos
  ADD COLUMN tipo_documento VARCHAR(30) NOT NULL DEFAULT '' AFTER telefono,
  ADD COLUMN documento VARCHAR(30) NOT NULL DEFAULT '' AFTER tipo_documento,
  ADD COLUMN pais VARCHAR(80) NOT NULL DEFAULT '' AFTER documento,
  ADD COLUMN departamento VARCHAR(80) NOT NULL DEFAULT '' AFTER pais,
  ADD COLUMN ciudad VARCHAR(80) NOT NULL DEFAULT '' AFTER departamento,
  ADD COLUMN reserva_expira_en DATETIME NULL AFTER total,
  ADD INDEX idx_pedidos_reserva (estado_pago, reserva_expira_en);

UPDATE pedidos
SET reserva_expira_en = DATE_ADD(creado_en, INTERVAL 30 MINUTE)
WHERE metodo_pago IN ('tarjeta', 'pse') AND estado_pago = 'pendiente' AND estado_pedido = 'recibido';
