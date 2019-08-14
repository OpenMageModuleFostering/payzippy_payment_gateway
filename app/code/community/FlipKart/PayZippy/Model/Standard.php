<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

class FlipKart_PayZippy_Model_Standard extends Mage_Payment_Model_Method_Abstract {
	
        protected $_code = 'payzippy';
    	protected $_formBlockType = 'payzippy/form';
        protected $_infoBlockType = 'payzippy/info';
    	protected $_isInitializeNeeded      = true;
    	protected $_canUseInternal          = true;
    	protected $_canUseForMultishipping  = false;
    	
    	public function getOrderPlaceRedirectUrl() {
    		return Mage::getUrl('payzippy/payment/redirect', array('_secure' => true));
    	}
        
        /*
        *Assign payment method data to info object
        */

        public function assignData($data)
        {
           
            if (!($data instanceof Varien_Object)) {
                $data = new Varien_Object($data);
            }
            $info = $this->getInfoInstance();
           
            $payment_method = $data->getPayzippyPaymentMethod();
            
            $info->setPayzippyPaymentMethod($payment_method);
           
            if($payment_method == 'NET' || $payment_method == 'EMI') {
                $info->setPayzippyBankName($data->getPayzippyBankName());
            }
            
            if($payment_method == 'EMI') {
                $info->setPayzippyEmiMonths($data->getPayzippyEmiMonths());
            }
            
            return $this;
        }

        /*
        *Validate payment method's form fields
        */

 
        public function validate()
        {
            parent::validate();
            
            $errorMsg = "";
            
            $info = $this->getInfoInstance();

            $payment_method = $info->getPayzippyPaymentMethod();
           
            if(empty($payment_method)){
                $errorCode = 'invalid_data';
                $errorMsg = $this->_getHelper()->__('Payment Method is required field');
            }

            if($errorMsg){
                Mage::throwException($errorMsg);
            }
            return $this;
        }
}
?>
