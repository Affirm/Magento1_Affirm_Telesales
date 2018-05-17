<?php
class Affirm_Telesales_Adminhtml_TelesalesController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Checkout Page On Affirm Payment Action
     *
     */
    public function checkoutAction()
    {
        Mage::log(__METHOD__);
        $this->loadLayout();
        $this->_setActiveMenu('sales/orders');
        $this->_addContent($this->getLayout()->createBlock('affirm_telesales/adminhtml_checkout'));
        $this->renderLayout();
        return;
    }

    public function createCheckoutAction()
    {
        Mage::log(__METHOD__);
        $result = new Varien_Object();
        try {
            $orderId = $this->getRequest()->getParam('order_id');
            $order = Mage::getModel('sales/order')->load($orderId);
            $payment = $order->getPayment()->getMethodInstance();
            if($payment->getCheckoutToken()){
                Mage::log(__METHOD__.' Token already exist, resend checkout from existing token: '.$payment->getCheckoutToken()); //pending api implementation
            }
            $checkoutObj = Mage::getModel('affirm_telesales/telesales')->getCheckoutObject($orderId);
            $response = $this->sendCheckoutAction($order, $checkoutObj);
            if($response['checkout_id']) {
                $result = array(
                    'success' => true,
                    'error' => false,
                    'checkout_id' => $response['checkout_id'],
                    'redirect_url' => $response['redirect_url']
                );
                if($response['checkout_id']) {
                    $this->_getSession()->addSuccess($this->__('Affirm Checkout link was sent to user.'));
                    $payment->setAffirmCheckoutToken($response['checkout_id']);
                    $order->addStatusHistoryComment(Mage::helper('affirm_telesales')->__('Affirm Telesales checkout link was sent to user. Checkout token:'.$response['checkout_id']))->save();
                    $order->save();
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result->setSuccess(false);
        };
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    public function sendCheckoutAction($order, $checkoutObj){
        Mage::log(__METHOD__);
        $response = Mage::getModel('affirm_telesales/telesales')->sendCheckout($checkoutObj);
        return $response;
    }
}