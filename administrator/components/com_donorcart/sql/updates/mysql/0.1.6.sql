ALTER TABLE IF EXISTS `#__donorcart_orders`
	DROP COLUMN `notes`,
	DROP COLUMN `errors`,
	DROP COLUMN `submitted`
	ADD COLUMN `payment_name` VARCHAR(20) NOT NULL DEFAULT '',
	ADD COLUMN `recurring` TINYINT(1) NOT NULL DEFAULT 0,
;