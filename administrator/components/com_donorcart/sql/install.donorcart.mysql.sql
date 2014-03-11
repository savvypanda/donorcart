CREATE TABLE IF NOT EXISTS `#__donorcart_addresses` (
	`donorcart_address_id` SERIAL,
	`user_id` INT(11),
	`address_type` VARCHAR(50) NOT NULL DEFAULT 'residential',
	`first_name` VARCHAR(80),
	`middle_name` VARCHAR(80),
	`last_name` VARCHAR(80),
	`business_name` VARCHAR(80),
	`address1` VARCHAR(80),
	`address2` VARCHAR(80),
	`city` VARCHAR(80),
	`state` VARCHAR(80),
	`zip` VARCHAR(16),
	`country` VARCHAR(32),
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
	`payment_type` VARCHAR(80) NOT NULL,
	`external_reference` VARCHAR(80),
	`infohash` TEXT,
	PRIMARY KEY (`donorcart_payment_id`),
	INDEX (`external_reference`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_carts` ( #Deprecated. In the future, the entire carts table will be merged with the orders table
	`donorcart_cart_id` SERIAL,
	`user_id` INT(11),
	#`recurring` TINYINT(1) NOT NULL DEFAULT 0, #Deprecated. In the future this information will be moved to new fields on the orders table
	`session_id` VARCHAR(200),
	`subtotal` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	PRIMARY KEY (`donorcart_cart_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_cart_items` ( #In the future, this will be renamed to #__donorcart_order_items
	`donorcart_cart_item_id` SERIAL,
	`cart_id` BIGINT(20) UNSIGNED NOT NULL, #In the future, this will be renamed to order_id
	`sku` VARCHAR(80) NOT NULL,
	`name` VARCHAR (80) NOT NULL,
	`price` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	`qty` INT(6) NOT NULL DEFAULT 1,
	`url` VARCHAR(256),
	`img` VARCHAR(256),
	PRIMARY KEY (`donorcart_cart_item_id`),
	FOREIGN KEY (`cart_id`) REFERENCES `#__donorcart_carts`(`donorcart_cart_id`)
);

CREATE TABLE IF NOT EXISTS `#__donorcart_orders` (
	`donorcart_order_id` SERIAL,
	`user_id` INT(11),
	`email` VARCHAR(80),
	`status` VARCHAR(50) NOT NULL DEFAULT 'cart', #In the future, this will be changed to an enumeration (including 'cart', 'checkout', 'submitted', and 'confirmed' values)
	`completed_on` DATETIME DEFAULT NULL,
	`cart_id` BIGINT(20) UNSIGNED,
	`shipping_address_id` BIGINT(20) UNSIGNED,
	`billing_address_id` BIGINT(20) UNSIGNED,
	`payment_name` VARCHAR(20),
	`payment_id` BIGINT(20) UNSIGNED,
	`order_total` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
	`recurring_frequency` VARCHAR(32) NOT NULL DEFAULT 'One Time', #This should be changed to an enumeration
	`recurring_details` VARCHAR(256) DEFAULT NULL, #May contain a json-encoded array with details about start and end dates and any other recurring donation information
	`dedication` TEXT DEFAULT NULL,
#	`recurring_start_date` DATE NOT NULL DEFAULT '0000-00-00',
#	`recurring_end_date` DATE NOT NULL DEFAULT '0000-00-00',
	`special_instr` TEXT,
	`viewtoken` VARCHAR(30),
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