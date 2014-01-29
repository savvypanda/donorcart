CREATE TABLE IF NOT EXISTS `#__checkout_addresses` {
	`checkout_address_id` SERIAL,
	`user_id` BIGINT(20),
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
	`created_by` BIGINT(20) NOT NULL DEFAULT '0',
	`created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` BIGINT(20) NOT NULL DEFAULT '0',
	`modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`checkout_order_id` BIGINT(20),
	PRIMARY KEY (`checkout_address_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
};
INSERT INTO `#__checkout_addresses` (user_id, first_name, middle_name, last_name, address1, address2, city, state, zip, country, created_by, created_on, modified_by, modified_on, checkout_order_id)
SELECT user_id,  first_name, middle_name, last_name, address, address2, city, state, zip, country, created_by, created_on, modified_by, modified_on, checkout_order_id
FROM `#__checkout_orders`;


CREATE TABLE IF NOT EXISTS `#__checkout_payments` {
	`checkout_payment_id` SERIAL,
	`user_id` BIGINT(20),
	`payment_type` VARCHAR(80),
	`infohash` TEXT NOT NULL,
	`checkout_order_id` BIGINT(20),
	PRIMARY KEY (`checkout_payment_id`),
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`)
};
INSERT INTO `#__checkout_payments` (user_id, payment_type, infohash, checkout_order_id)
SELECT user_id, 'old_payment', CONCAT('transaction_type:',transaction_type,', reference:',reference,', order_code:',order_code,', order_number:',order_number), checkout_order_id
FROM `#__checkout_orders`;


CREATE TABLE IF NOT EXISTS `#__checkout_carts` {
	`checkout_cart_id` SERIAL,
	`checkout_order_id` BIGINT(20) DEFAULT -1,
	PRIMARY KEY (`checkout_cart_id`)
};
INSERT INTO `#__checkout_cart` (checkout_cart_id, checkout_order_id) VALUES (0,-1);
INSERT INTO `#__checkout_cart` (checkout_order_id)
SELECT checkout_order_id
FROM `#__checkout_orders`;


CREATE TABLE IF NOT EXISTS `#__checkout_cart_items` {
	`checkout_cart_item_id` SERIAL,
	`cart_id` BIGINT(20) NOT NULL,
	`sku` VARCHAR(80) NOT NULL,
	`name` VARCHAR (80) NOT NULL,
	`price` VARCHAR(8) NOT NULL,
	`qty` INT(6) NOT NULL DEFAULT 1,
	`url` VARCHAR(80),
	`created_by` BIGINT(20) NOT NULL DEFAULT '0',
	`created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` BIGINT(20) NOT NULL DEFAULT '0',
	`modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`checkout_cart_item_id`),
	FOREIGN KEY (`cart_id`) REFERENCES `#__checkout_carts`(`checkout_cart_id`)
};


ALTER TABLE IF EXISTS `#__checkout_orders`
	ALTER COLUMN `status` SET DEFAULT 'cart',
	ADD COLUMN `checkout_cart_id` BIGINT(20) DEFAULT 0,
	ADD COLUMN `checkout_shipping_address_id` BIGINT(20) DEFAULT NULL,
	ADD COLUMN `checkout_billing_address_id` BIGINT(20) DEFAULT NULL,
	ADD COLUMN `checkout_payment_id` BIGINT(20) DEFAULT NULL,
	ADD COLUMN `notes` TEXT,
	ADD FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`),
	ADD FOREIGN KEY (`checkout_cart_id`) REFERENCES `#__checkout_carts`(`checkout_cart_id`),
	ADD FOREIGN KEY (`checkout_shipping_address_id`) REFERENCES `#__checkout_addresses`(`checkout_address_id`),
	ADD FOREIGN KEY (`checkout_billing_address_id`) REFERENCES `#__checkout_addresses`(`checkout_address_id`),
	ADD FOREIGN KEY (`checkout_payment_id`) REFERENCES `#__checkout_payments`(`checkout_payment_id`)
;


CREATE TABLE IF NOT EXISTS `#__checkout_custom_fields` {
	`checkout_custom_field_id` SERIAL,
	`checkout_order_id` BIGINT(20),
	`title` VARCHAR(80),
	`setting` VARCHAR(80),
	PRIMARY KEY (`checkout_custom_field_id`),
	FOREIGN KEY (`checkout_order_id`) REFERENCES `#__checkout_orders`(`checkout_order_id`)
};
INSERT INTO `#__checkout_custom_fields` (checkout_order_id, title, setting)
SELECT checkout_order_id, 'transaction_type', transaction_type
FROM `#__checkout_orders`;
INSERT INTO `#__checkout_custom_fields` (checkout_order_id, title, setting)
SELECT checkout_order_id, 'phone', phone
FROM `#__checkout_orders`;


UPDATE `#__checkout_orders` o SET o.checkout_cart_id = (SELECT c.checkout_cart_id FROM `#__checkout_cart` c WHERE c.checkout_order_id = o.checkout_order_id);
UPDATE `#__checkout_orders` o SET o.checkout_shipping_address_id = (SELECT a.checkout_address_id FROM `#__checkout_addresses` a WHERE a.checkout_order_id = o.checkout_order_id);
UPDATE `#__checkout_orders` o SET o.checkout_billing_address_id = (SELECT a.checkout_address_id FROM `#__checkout_addresses` a WHERE a.checkout_order_id = o.checkout_order_id);
UPDATE `#__checkout_orders` o SET o.checkout_payment_id = (SELECT p.checkout_payment_id FROM `#__checkout_payments` p WHERE p.checkout_order_id = o.checkout_order_id);
UPDATE `#__checkout_orders` o SET o.notes = o.cart_items;

ALTER TABLE IF EXISTS `#__checkout_orders`
	DROP COLUMN `transaction_type`,
	DROP COLUMN `first_name`,
	DROP COLUMN `middle_name`,
	DROP COLUMN `last_name`,
	DROP COLUMN `phone`,
	DROP COLUMN `address`,
	DROP COLUMN `address2`,
	DROP COLUMN `city`,
	DROP COLUMN `state`,
	DROP COLUMN `zip`,
	DROP COLUMN `country`,
	DROP COLUMN `cart_items`,
	DROP COLUMN `reference`,
	DROP COLUMN `order_code`,
	DROP COLUMN `order_number`,
	MODIFY COLUMN `checkout_cart_id` BIGINT(20) NOT NULL,
;


ALTER TABLE `#__checkout_payments` DROP COLUMN `checkout_order_id`;
ALTER TABLE `#__checkout_addresses` DROP COLUMN `checkout_order_id`;
ALTER TABLE `#__checkout_cart` DROP COLUMN `checkout_order_id`;

COMMIT;