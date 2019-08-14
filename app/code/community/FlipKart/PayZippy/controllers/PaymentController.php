<?php

/**
 * Description of ExtensionsStatusManager
 * @package   FlipKart_PayZippy
 * @company   CueBlocks - http://www.cueblocks.com/
 * @author    Ravinder <ravinder.singh@cueblocks.com>
 */

class FlipKart_PayZippy_PaymentController extends Mage_Core_Controller_Front_Action
{
    
    /*
    *Triggered when place order button is clicked
    */

    public function redirectAction()
    {
        $this->getResponse()->setBody($this->getLayout()->createBlock('payzippy/redirect', 'payzippy', array(
            'template' => 'payzippy/redirect.phtml'
        ))->toHtml());
        ;
    }
    
    /*
    *Handle response from API
    */
    
    public function responseAction()
    {     
        $response = $this->getRequest()->getParams();
    
        if(Mage::helper('payzippy')->getConfigData('debug')) {
            Mage::log("Response:- ".print_r($response, true), Zend_Log::DEBUG, 'payzippy.log', true);
        }
        if (isset($response)) {
            $validated        = $response['transaction_response_code'];
            $hash_recievd     = $response['hash'];
            $payzippy_transid = $response['payzippy_transaction_id'];
            $payment_method   = $response['payment_method'];
            $trans_status     = $response['transaction_status'];
            $orderId          = $response['merchant_transaction_id'];
            $message          = $response['transaction_response_message'];
            $is_international = $response['is_international'];
            $fraud_action     = $response['fraud_action'];
            $allow            = array('SUCCESS','INITIATED','PENDING');
            $comment          = 'PayZippy Transaction Id : '.$payzippy_transid.'<br/>'.'Payment Method : '.$payment_method.'<br/>'.'Transaction Status : '.$trans_status.'<br/>'.'Transaction Response Code : '.$validated.'<br/>'.'Transaction Response Message : '.$message.'<br/>'.'Is_International : '.$is_international.'<br/>'.'Fraud Action : '.$fraud_action;
            unset($response['hash']);
            $hash_generated   = Mage::helper('payzippy')->getHash($response,Mage::helper('payzippy')->getConfigData('secret_key'));
        
            if (in_array($validated, $allow) && $hash_recievd == $hash_generated) {
                // Payment was successful, so update the order's state, send order email and move to the success page
                $order = Mage::getSingleton('sales/order');
                $order->loadByIncrementId($orderId);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, $comment);
                
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                
                $order->save();
                
                Mage::getSingleton('checkout/session')->unsQuoteId();
                
                Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array(
                    '_secure' => true
                ));
            } else {
                // There is a problem in the response we got
                Mage::getSingleton('core/session')->addError($message);
                $this->cancelAction($comment);
                Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/failure', array(
                    '_secure' => true
                ));
            }
        } else {
            Mage_Core_Controller_Varien_Action::_redirect('');
        }
    }
    
    
    /*
    *Triggered to cancel the order
    */

    public function cancelAction($reason)
    {
        if (Mage::getSingleton('checkout/session')->getLastRealOrderId()) {
            $order = Mage::getSingleton('sales/order')->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
            if ($order->getId()) {
                // Flag the order as 'cancelled' and save it
                $order->cancel()->setState(Mage_Sales_Model_Order::STATE_CANCELED, true, $reason)->save();
            }
        }
    }
}
