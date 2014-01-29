ALTER TABLE `#__checkout_orders` CHANGE `id` `checkout_order_id` SERIAL;

ALTER TABLE `#__checkout_orders` ADD `address2` VARCHAR(80) NOT NULL DEFAULT '';
ALTER TABLE `#__checkout_orders` ADD `reference` VARCHAR(80) NOT NULL DEFAULT '';
ALTER TABLE `#__checkout_orders` ADD `order_code` VARCHAR(80) NOT NULL DEFAULT '';
ALTER TABLE `#__checkout_orders` ADD `order_number` VARCHAR(80) NOT NULL DEFAULT '';
ALTER TABLE `#__checkout_orders` ADD `viewtoken` VARCHAR(30) NOT NULL DEFAULT '';
ALTER TABLE `#__checkout_orders` ADD `created_by` BIGINT(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__checkout_orders` ADD `created_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';
ALTER TABLE `#__checkout_orders` ADD `modified_by` BIGINT(20) NOT NULL DEFAULT '0';
ALTER TABLE `#__checkout_orders` ADD `modified_on` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00';

ALTER TABLE `#__checkout_orders` DROP `profile_id`;
ALTER TABLE `#__checkout_orders` DROP `nameoncard`;
ALTER TABLE `#__checkout_orders` DROP `cardnum_last4`;
ALTER TABLE `#__checkout_orders` DROP `cardbrand`;
ALTER TABLE `#__checkout_orders` DROP `cardexp`;
ALTER TABLE `#__checkout_orders` DROP `authorizationnumber`;
ALTER TABLE `#__checkout_orders` DROP `transaction_id`;
ALTER TABLE `#__checkout_orders` DROP `create_date`;
ALTER TABLE `#__checkout_orders` DROP `terminal`;
ALTER TABLE `#__checkout_orders` DROP `testing`;
ALTER TABLE `#__checkout_orders` DROP `check_number`;
ALTER TABLE `#__checkout_orders` DROP `account_number`;