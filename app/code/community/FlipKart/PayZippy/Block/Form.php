<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

class FlipKart_PayZippy_Block_Form extends Mage_Payment_Block_Form {
    
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('payzippy/form.phtml')->setRedirectMessage(
                Mage::helper('payzippy')->__('You will be redirected to the PayZippy website when you place an order.')
            );
    }

    /*
    *Return all allowed payment methods for payzippy like EMI,Debit Card etc
    */

    // public function getAllowedMethods() {
    // 	$available_methods = array();
    // 	$methods  = Mage::getStoreConfig('payment/payzippy/payment_methods');
    // 	$methods = explode(',',$methods);
    // 	$label_codes = Mage::getSingleton('payzippy/system_config_source_payment_methods')->toOptionArray();
    //     $availables = array();
    //     foreach($methods as $method) {
    //         foreach($label_codes as $label) {
    //             if($label['value'] == $method) {
    //                 $availables[] = $label;
    //             }
    //         }
    //     }
    //     return $availables;	
    // }
    
    
    // *Return allowed bank names for either payment method Net banking or EMI 
    

    // public function getBankNames($paymentMethod) {        
    //     $bankCodes = Mage::getStoreConfig('payment/payzippy/'.$paymentMethod);
    //     $bankCodes =  explode(',', $bankCodes);
    //     $bankLabels = Mage::getSingleton('payzippy/system_config_source_payment_bank_names')->toOptionArray();
    //     $availables = "";
    //     foreach($bankCodes as $code) {
    //         foreach($bankLabels as $label) {
    //             if($label['value'] == $code) {
    //                 $availables.= '<option value="'.$label["value"].'">'.$label["label"].'</option>';
    //             }
    //         }
    //     }
    //     return $availables;
    // }
}
?>
