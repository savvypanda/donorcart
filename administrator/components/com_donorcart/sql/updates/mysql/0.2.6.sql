ALTER TABLE `#__donorcart_orders`
	DROP COLUMN `notes`,
	DROP COLUMN `errors`,
	DROP COLUMN `submitted`,
	ADD COLUMN `payment_name` VARCHAR(20) NOT NULL,
	ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0
;

ALTER TABLE `#__donorcart_carts`
	ADD COLUMN `subtotal` DECIMAL(8,2) DEFAULT 0.00
;

ALTER TABLE `#__donorcart_cart_items`
	MODIFY COLUMN `price` DECIMAL(8,2) DEFAULT 0.00
;

ALTER TABLE `#__donorcart_payments`
	DROP COLUMN `user_id`,
	DROP COLUMN `status`
;

DROP TABLE `#__donorcart_custom_fields`;