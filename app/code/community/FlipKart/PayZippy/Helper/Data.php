<?php
class FlipKart_PayZippy_Helper_Data extends Mage_Core_Helper_Abstract
{

    /*
    *Return system configuration values
    */

    public function getConfigData($value) {
        return Mage::getStoreConfig('payment/payzippy/'.$value,Mage::app()->getStore());
    }
    
    /*
    *Generate Hash
    */

    public function gethash($params,$secret_key) {
        ksort($params);
        $str = implode("|", $params);
        $str.= '|'.$secret_key;
        $hash = md5($str);
        return $hash;
    }
}
