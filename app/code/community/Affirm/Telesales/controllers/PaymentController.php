<?php
/**
 * Class Affirm_Telesales_PaymentController
 */
class Affirm_Telesales_PaymentController extends Mage_Checkout_Controller_Action
{
    /**
     * Confirm checkout
     */
    public function confirmAction()
    {
        $checkoutToken = $this->getRequest()->getParam('checkout_token');
        if (!$checkoutToken) {
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');

            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $failureUrl = Mage::getUrl('telesales/payment/failure');
            $this->_redirectUrl($failureUrl);
            return;
        } else {
            $response = Mage::getModel('affirm_telesales/telesales')->getCheckoutFromToken($checkoutToken);
            $order = Mage::getModel('sales/order');
            $incrementId = $response['merchant_external_reference'];

            if($incrementId) {
                try {
                    $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
                    if ($order->getId()) {
                        $storeId = $order->getStoreId();
                        $paymentStatus = $order->getStatus();
                        if($paymentStatus == Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
                            Mage::getModel('affirm_telesales/telesales')->processConfirmOrder($order, $checkoutToken);
                            Mage::getSingleton('checkout/session')->setLastQuoteId($order->getId())
                                ->setLastSuccessQuoteId($order->getId())
                                ->clearHelperData();
                            Mage::getSingleton('checkout/session')->setLastOrderId($order->getId())
                                 ->setLastRealOrderId($order->getIncrementId());
                            $order->sendNewOrderEmail();
                            $this->_redirect('checkout/onepage/success');
                            return;
                        } else {
                            Mage::getSingleton('core/session')->addError($this->__('Your order has already been processed. Please contact us for more information about your order.'));
                            $redirectUrl = Mage::getUrl('telesales/payment/failure');
                            $this->_redirectUrl($redirectUrl);
                            return;
                        }
                    }
                } catch (Affirm_Affirm_Exception $e) {
                    Mage::logException($e);
                    Mage::throwException(
                        Mage::helper('affirm_telesales')->__('We couldn’t find your order. Please contact us or try again later.')
                    );
                    Mage::getSingleton('core/session')->addError($e->getMessage());
                    $this->getResponse()->setRedirect(Mage::getUrl('telesales/payment/failure'))->sendResponse();
                    return;
                } catch (Mage_Core_Exception $e) {
                    Mage::logException($e);
                    Mage::getSingleton('core/session')->addError($this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.'));
                    $this->getResponse()->setRedirect(Mage::getUrl('telesales/payment/failure'))->sendResponse();
                    return;
                }
            }  else {
                Mage::throwException(
                    Mage::helper('affirm_telesales')->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.')
                );
                $redirectUrl = Mage::getUrl('telesales/payment/failure');
                $this->_redirectUrl($redirectUrl);
                return;
            }
        }
    }

    public function cancelAction()
    {
        $result['success']  = false;
        $result['error']    = true;
        $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        $redirectUrl = Mage::getUrl('telesales/payment/failure');
        $this->_redirectUrl($redirectUrl);
        return;
    }

    public function declineAction()
    {
        $result['success']  = false;
        $result['error']    = true;
        $result['error_messages'] = $this->__('We weren’t able to process your order either because of a processing error or an issue with your loan application. We’re sorry for the inconvenience. Please contact us or try again later.');

        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
        $redirectUrl = Mage::getUrl('telesales/payment/failure');
        $this->_redirectUrl($redirectUrl);
        return;
    }

    public function failureAction()
    {
        $this->loadLayout();
        $this->renderLayout();
    }
}
