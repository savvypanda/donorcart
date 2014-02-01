CREATE TABLE IF NOT EXISTS `#__donorcart_addresses` (
	`donorcart_address_id` SERIAL,
	`user_id` INT(11),
	`address_type` VARCHAR(50) NOT NULL DEFAULT 'residential',
	`first_name` VARCHAR(80) NOT NULL DEFAULT '',
	`middle_name` VARCHAR(80) NOT NULL DEFAULT '',
	`last_name` VARCHAR(80) NOT NULL DEFAULT '',
	`business_name` VARCHAR(80) NOT NULL DEFAULT '',
	`address1` VARCHAR(80) NOT NULL DEFAULT '',
	`address2` VARCHAR(80) NOT NULL DEFAULT '',
	`city` VARCHAR(80) NOT NULL DEFAULT '',
	`state` VARCHAR(80) NOT NULL DEFAULT '',
	`zip` VARCHAR(16) NOT NULL DEFAULT '',
	`country` VARCHAR(32) NOT NULL DEFAULT '',
	`locked` TINYINT(1) NOT NULL DEFAULT 0,
	`created_by` BIGINT(20) NOT NULL DEFAULT '0',
	`created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` BIGINT(20) NOT NULL DEFAULT '0',
	`modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`donorcart_address_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_payments` (
	`donorcart_payment_id` SERIAL,
	`external_reference` VARCHAR(80),
	`user_id` INT(11),
	`payment_type` VARCHAR(80) NOT NULL,
	`status` VARCHAR(50) NOT NULL DEFAULT 'pending',
	`infohash` TEXT,
	PRIMARY KEY (`donorcart_payment_id`),
	INDEX (`external_reference`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_carts` (
	`donorcart_cart_id` SERIAL,
	`user_id` INT(11),
	`session_id` VARCHAR(200),
	PRIMARY KEY (`donorcart_cart_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_cart_items` (
	`donorcart_cart_item_id` SERIAL,
	`cart_id` BIGINT(20) UNSIGNED NOT NULL,
	`sku` VARCHAR(80) NOT NULL,
	`name` VARCHAR (80) NOT NULL,
	`price` VARCHAR(8) NOT NULL,
	`qty` INT(6) NOT NULL DEFAULT 1,
	`url` VARCHAR(80),
	PRIMARY KEY (`donorcart_cart_item_id`),
	FOREIGN KEY (`cart_id`) REFERENCES `#__donorcart_carts`(`donorcart_cart_id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_orders` (
	`donorcart_order_id` SERIAL,
	`user_id` INT(11),
	`email` VARCHAR(80) NOT NULL DEFAULT '',
	`status` VARCHAR(50) NOT NULL DEFAULT 'cart',
	`cart_id` BIGINT(20) UNSIGNED,
	`shipping_address_id` BIGINT(20) UNSIGNED DEFAULT NULL,
	`billing_address_id` BIGINT(20) UNSIGNED DEFAULT NULL,
	`payment_name` VARCHAR(20) NOT NULL DEFAULT '',
	`payment_id` BIGINT(20) UNSIGNED DEFAULT NULL,
	`order_total` DOUBLE NOT NULL DEFAULT 0,
	`recurring` TINYINT(1) NOT NULL DEFAULT 0,
	`special_instr` TEXT,
	`viewtoken` VARCHAR(30) NOT NULL DEFAULT '',
	`created_by` BIGINT(20) NOT NULL DEFAULT '0',
	`created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` BIGINT(20) NOT NULL DEFAULT '0',
	`modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`donorcart_order_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`),
	FOREIGN KEY (`cart_id`) REFERENCES `#__donorcart_carts`(`donorcart_cart_id`),
	FOREIGN KEY (`shipping_address_id`) REFERENCES `#__donorcart_addresses`(`donorcart_address_id`),
	FOREIGN KEY (`billing_address_id`) REFERENCES `#__donorcart_addresses`(`donorcart_address_id`),
	FOREIGN KEY (`payment_id`) REFERENCES `#__donorcart_payments`(`donorcart_payment_id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_custom_fields` (
	`donorcart_custom_field_id` SERIAL,
	`order_id` BIGINT(20) UNSIGNED,
	`field` VARCHAR(80),
	`value` VARCHAR(80),
	PRIMARY KEY (`donorcart_custom_field_id`),
	FOREIGN KEY (`order_id`) REFERENCES `#__donorcart_orders`(`donorcart_order_id`)
);
