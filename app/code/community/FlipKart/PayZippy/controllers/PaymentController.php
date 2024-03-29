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
            $validated        = htmlentities($response['transaction_response_code']);
            $hash_recievd     = $response['hash'];
            $payzippy_transid = $response['payzippy_transaction_id'];
            $payment_method   = $response['payment_method'];
            $payment_instrument   = $response['payment_instrument'];
            $bank_name        = $response['bank_name'];
            $emi_months       = $response['emi_months'];
            if($emi_months == '')
                $emi_months       = 'N/A';
            $trans_status     = htmlentities($response['transaction_status']);
            $orderId          = $response['merchant_transaction_id'];
            $message          = htmlentities($response['transaction_response_message']);
            $is_international = $response['is_international'];
            $fraud_action     = $response['fraud_action'];
            $fraud_detials    = $response['fraud_details'];
            if($fraud_details == '')
                $fraud_details = 'Accept';
            $allow            = array('SUCCESS','INITIATED','PENDING');
            $configured_order_status     = Mage::helper('payzippy')->getConfigData('order_status');
            $comment          = 'PayZippy Transaction Id : '.$payzippy_transid.'<br/>'.'Payment Method : '.$payment_method.'<br/>'.'Payment Instrument : '.$payment_instrument.'<br/>'.'Bank Name : '.$bank_name.'<br/>'.'EMI Months : '.$emi_months.'<br/>'.'Transaction Status : '.$trans_status.'<br/>'.'Transaction Response Code : '.$validated.'<br/>'.'Transaction Response Message : '.$message.'<br/>'.'Is_International : '.$is_international.'<br/>'.'Fraud Action : '.$fraud_action.'<br/>'.'Fraud Details : '.$fraud_details;
            unset($response['hash']);
            $hash_generated   = Mage::helper('payzippy')->getHash($response,Mage::helper('payzippy')->getConfigData('secret_key'));
        
            if (in_array($validated, $allow) && $hash_recievd == $hash_generated) {
                // Payment was successful, so update the order's state, send order email and move to the success page
                $order = Mage::getSingleton('sales/order');
                $order->loadByIncrementId($orderId);
                $order_status = Mage_Sales_Model_Order::STATE_PROCESSING;
                if($configured_order_status == 'pending') {
                    $order_status = Mage_Sales_Model_Order::STATE_PENDING_PAYMENT;
                }
                $order->setState($order_status, true, $comment);
                
                $order->sendNewOrderEmail();
                $order->setEmailSent(true);
                
                $order->save();
                
                Mage::getSingleton('checkout/session')->unsQuoteId();
                
                Mage_Core_Controller_Varien_Action::_redirect('checkout/onepage/success', array(
                    '_secure' => true
                ));
            } else {
                // There is a problem in the response we got
                Mage::getSingleton('core/session')->addError(htmlentities($message));
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
