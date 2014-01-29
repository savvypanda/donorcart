DROP TABLE IF EXISTS `#__checkout_orders`;
CREATE TABLE `#__checkout_orders` {
	`checkout_order_id` SERIAL,
	`user_id` INT(11),
	`transaction_type` VARCHAR(50) NOT NULL DEFAULT 'onetime',
	`status` VARCHAR(50) NOT NULL DEFAULT 'pending',
	`order_total` DOUBLE NOT NULL DEFAULT 0,
	`first_name` VARCHAR(32) NOT NULL DEFAULT '',
	`middle_name` VARCHAR(32) NOT NULL DEFAULT '',
	`last_name` VARCHAR(32) NOT NULL DEFAULT '',
	`email` VARCHAR(80) NOT NULL DEFAULT '',
	`phone` VARCHAR(16) NOT NULL DEFAULT '',
	`address` VARCHAR(80) NOT NULL DEFAULT '',
	`address2` VARCHAR(80) NOT NULL DEFAULT '',
	`city` VARCHAR(80) NOT NULL DEFAULT '',
	`state` VARCHAR(80) NOT NULL DEFAULT '',
	`zip` VARCHAR(16) NOT NULL DEFAULT '',
	`country` VARCHAR(32) NOT NULL DEFAULT '',
	`cart_items` TEXT NOT NULL DEFAULT '{}',
	`special_instr` TEXT,
	`reference` VARCHAR(80) NOT NULL DEFAULT '',
	`order_code` VARCHAR(80) NOT NULL DEFAULT '',
	`order_number` VARCHAR(80) NOT NULL DEFAULT '',
	`viewtoken` VARCHAR(30) NOT NULL DEFAULT '',
	`errors` TEXT,
	`created_by` BIGINT(20) NOT NULL DEFAULT '0',
	`created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	`modified_by` BIGINT(20) NOT NULL DEFAULT '0',
	`modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
	PRIMARY KEY (`checkout_order_id`)
};