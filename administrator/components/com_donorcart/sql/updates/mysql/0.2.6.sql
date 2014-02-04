ALTER TABLE IF EXISTS `#__donorcart_orders`
	DROP COLUMN `notes`,
	DROP COLUMN `errors`,
	DROP COLUMN `submitted`
	ADD COLUMN `payment_name` VARCHAR(20) NOT NULL DEFAULT '',
	ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0,
;

ALTER TABLE IF EXISTS `#__donorcart_carts`
	ADD COLUMN `subtotal` DECIMAL(8,2) DEFAULT 0.00
;

ALTER TABLE IF EXISTS `#__donorcart_cart_items`
	MODIFY COLUMN `price` DECIMAL(8,2) DEFAULT 0.00
;

ALTER TABLE IF EXISTS `#__donorcart_payments`
	DROP COLUMN `user_id`,
	DROP COLUMN `status`
;

DROP TABLE IF EXISTS `#__donorcart_custom_fields`;