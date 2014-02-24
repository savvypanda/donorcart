ALTER TABLE `#__donorcart_orders`
	DROP COLUMN `recurring`,
	ADD COLUMN `completed_on` DATETIME DEFAULT NULL
;

ALTER TABLE `#__donorcart_carts` ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0;

ALTER TABLE `#__donorcart_cart_items`
	MODIFY COLUMN `url` VARCHAR(256),
	ADD COLUMN `img` VARCHAR(256)
;