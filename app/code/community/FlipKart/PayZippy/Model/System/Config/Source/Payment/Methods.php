<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

class FlipKart_PayZippy_Model_System_Config_Source_Payment_Methods
{
    
    /*
    *Return available payment methods for payzippy
    */

    public function toOptionArray()
    {
        $methods = array();
		$methods[] = array('value' => 'PAYZIPPY' ,'label' => 'PayZippy');
        $methods[] = array('value' => 'CREDIT' ,'label' => 'Credit Card');
        $methods[] = array('value' => 'DEBIT' ,'label' => 'Debit Card');
        $methods[] = array('value' => 'EMI' ,'label' => 'Credit Card EMI');
        $methods[] = array('value' => 'NET' ,'label' => 'Net Banking');
        return $methods;
    }
}
