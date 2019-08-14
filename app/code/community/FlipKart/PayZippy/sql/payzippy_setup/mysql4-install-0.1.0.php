<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

$installer = $this;
$installer->startSetup();
$installer->run("
 
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `payzippy_payment_method` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `payzippy_bank_name` VARCHAR( 255 ) ;
ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `payzippy_emi_months` VARCHAR( 255 ) ;
 
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `payzippy_payment_method` VARCHAR( 255 ) NOT NULL ;
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `payzippy_bank_name` VARCHAR( 255 );
ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `payzippy_emi_months` VARCHAR( 255 );
 
");
$installer->endSetup();