<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

class FlipKart_PayZippy_Block_Redirect extends Mage_Checkout_Block_Onepage_Abstract
{

    /*
    *Prepare request parameters for API request 
    */

    public function getRequestparams() {
        $_order                  = Mage::getSingleton('sales/order');
        $merchant_transaction_id = Mage::getSingleton('checkout/session')->getLastRealOrderId();
    	$_order->loadByIncrementId($merchant_transaction_id);
    	$payment_data            = $_order->getPayment()->getData();
        $shipping_address        = $_order->getShippingAddress();
        $billing_address         = $_order->getBillingAddress();
        $customerid              = $_order->getCustomerId();
    	$amount                  = $_order->getBaseGrandTotal();
    	$from_Currency           = Mage::app()->getStore()->getCurrentCurrencyCode();
        $orderItemDetails        = $this->getItemDetails($_order,$from_Currency);
    	
        /* Required Variables*/
        
        $params['currency']                = "INR";
        $params['merchant_transaction_id'] = $merchant_transaction_id;
    	$params['buyer_email_address']     = $_order->getBillingAddress()->getEmail();
        if($customerid != NULL && !isset($params['buyer_email_address'])) {
            $params['buyer_email_address'] = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
        }
    	$params['merchant_id']             = Mage::helper('payzippy')->getConfigData('merchant_id');
    	$params['transaction_type']        = "SALE";
    	$params['transaction_amount']      = $this->convertCurrency($from_Currency,$params['currency'],$amount); 
    	$params['ui_mode']                 = "REDIRECT";
    	$params['hash_method']             = "MD5";
    	$params['merchant_key_id']         = Mage::helper('payzippy')->getConfigData('merchant_key_id');
    	$params['callback_url']            = Mage::getUrl('payzippy/payment/response',array('_secure'=>true));
    	$params['payment_method']          = $payment_data['payzippy_payment_method'];
    	if(!empty ($payment_data['payzippy_bank_name'])):
    		$params['bank_name']           = $payment_data['payzippy_bank_name'];
    	endif;
    	if(!empty ($payment_data['payzippy_emi_months'])):
    		$params['emi_months']          = $payment_data['payzippy_emi_months'];
    	endif;

        /*Optional Variables*/

        $params['buyer_phone_no']          = $billing_address->getData('telephone');
        $params['shipping_address']        = str_replace(array("\r", "\n"), '', $shipping_address->getData('street'));
        $params['is_user_logged_in']       = 'false';
        if($customerid != NULL) {
            $params['buyer_unique_id']     = $customerid;
            $params['is_user_logged_in']   = 'true';
            $params['address_count']       = count(Mage::getSingleton('customer/session')->getCustomer()->getAddresses());
        }
        $params['shipping_city']           = $shipping_address->getData('city');
        $params['shipping_state']          = $shipping_address->getData('region');
        $params['shipping_zip']            = $shipping_address->getData('postcode');
        $params['shipping_country']        = Mage::getSingleton('directory/country')->load($shipping_address->getData('country_id'))->getName();
        $params['billing_address']         = str_replace(array("\r", "\n"), '', $billing_address->getData('street'));
        $params['billing_city']            = $billing_address->getData('city');
        $params['billing_state']           = $billing_address->getData('region');
        $params['billing_zip']             = $billing_address->getData('postcode');
        $params['billing_country']         = Mage::getSingleton('directory/country')->load($billing_address->getData('country_id'))->getName();
        if($this->isMobile()) {
            $params['source']              = 'mobile';    
        } else {
            $params['source']              = 'web';
        }
        $params['billing_name']            = $billing_address->getData('firstname');     
        $params['sales_channel']           = '';
        $params['item_total']              = $orderItemDetails['item_total'];
        $params['item_vertical']           = $orderItemDetails['item_vertical'];
        $params['udf1']                    = Mage::helper('payzippy')->getConfigData('udf1');
        $params['udf2']                    = Mage::helper('payzippy')->getConfigData('udf2');
        $params['udf3']                    = Mage::helper('payzippy')->getConfigData('udf3');
        $params['udf4']                    = Mage::helper('payzippy')->getConfigData('udf4');
        $params['udf5']                    = Mage::helper('payzippy')->getConfigData('udf5');

	   return $params;
    }

    /*
    *Return ordered items price and categories
    */
    
    public function getItemDetails($order,$currency) {
        $result = array();
        $category_names = array();
        foreach($order->getAllItems() as $item) {
            $result['item_total'][]    = $this->convertCurrency($currency,'INR',$item->getPrice());
            $categories                = Mage::getSingleton('catalog/product')->load($item->getProductId())->getCategoryCollection()->exportToArray();
            foreach($categories as $category) {
                $category_names[]   = Mage::getSingleton('catalog/category')->load($category['entity_id'])->getName();
            }
            $category_list = implode(',',$category_names);
            $result['item_vertical'] =  $category_list;
        }
        $result['item_total']        =  implode(',',$result['item_total']);
        return $result;
    }

    /*
    *Convert current currency to INR and finally rupees to paisa
    */

    public function convertCurrency($from_Currency,$to_Currency,$amount) {
        // $url = 'http://www.google.com/ig/calculator?hl=en&q=' . $amount . $from_Currency . '=?' . $to_Currency;
        // $ch = curl_init();
        // $timeout = 0;
        // curl_setopt ($ch, CURLOPT_URL, $url);
        // curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        // curl_setopt($ch,  CURLOPT_USERAGENT , "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1)");
        // curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        // $response = curl_exec($ch);
        // curl_close($ch);
        // $result_string = explode('"', $response);
        // $final_result = $result_string['3'];
        // $float_result = preg_replace("/[^0-9\.]/", '', $final_result);
        $float_result = $amount;
    	return round($float_result,2)*100;
    }

    /*
    *Detect device
    */
    public function isMobile()
    {   
        if(preg_match('/(alcatel|amoi|android|avantgo|blackberry|benq|cell|cricket|docomo|elaine|htc|iemobile|iphone|ipad|ipaq|ipod|j2me|java|midp|mini|mmp|mobi|motorola|nec-|nokia|palm|panasonic|philips|phone|sagem|sharp|sie-|smartphone|sony|symbian|t-mobile|telus|up\.browser|up\.link|vodafone|wap|webos|wireless|xda|xoom|zte)/i', $_SERVER['HTTP_USER_AGENT']))
            return true;
        else
            return false;
    }
}
?>
