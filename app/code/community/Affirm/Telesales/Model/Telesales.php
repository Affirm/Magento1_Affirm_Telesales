<?php

/**
 * Class Affirm_Telesales_Model_Telesales
 */
class Affirm_Telesales_Model_Telesales extends Affirm_Affirm_Model_Payment
{

    protected $_affirmHelperClass = 'affirm_telesales';

    public function getBaseApiUrl()
    {
        return Mage::helper('affirm_telesales')->getApiUrl();
    }

    public function sendCheckout($checkoutObj)
    {
        return $this->_apiRequest(Varien_Http_Client::POST, "store", $checkoutObj, null ,self::API_CHECKOUT_PATH);
    }

    public function getCheckoutFromToken($token)
    {
        return $this->_apiRequest(Zend_Http_Client::GET, $token, null, null,self::API_CHECKOUT_PATH);
    }

    /**
     * @param Mage_Sales_Model_Order $order
     * @param string $checkoutToken
     */
    public function processConfirmOrder($order, $checkoutToken)
    {
        $payment = $order->getPayment();
        $methodInst = $payment->getMethodInstance();
        $methodInst->setHelperClass('affirm_telesales');
        $payment->setAdditionalInformation(self::CHECKOUT_TOKEN, $checkoutToken);
        $payment->setAdditionalInformation('affirm_telesales', true);
        $action = Mage::helper('affirm_telesales')->getPaymentAction();
        //authorize the total amount.
        $payment->authorize(true, self::_affirmTotal($order));
        $payment->setAmountAuthorized(self::_affirmTotal($order));
        $order->save();
        //can capture as well..
        if ($action == self::ACTION_AUTHORIZE_CAPTURE) {
            $payment->setAmountAuthorized(self::_affirmTotal($order));
            $payment->setBaseAmountAuthorized($order->getBaseTotalDue());
            $payment->capture(null);
            $order->save();
        }
    }

    /**
     * Api request
     *
     * @param  mixed  $method
     * @param  string $path
     * @param  null|array $data
     * @param  string $resourcePath
     * @return string
     * @throws Affirm_Affirm_Exception
     */
    protected function _apiRequest($method, $path, $data = null, $storeId = null, $resourcePath = self::API_CHARGES_PATH)
    {
        $url = trim($this->getBaseApiUrl(), '/') . $resourcePath . $path;
        $client = new Zend_Http_Client($url);
        if ($method == Zend_Http_Client::POST && $data) {
            $json = json_encode($data);
            $client->setRawData($json, 'application/json');
        }
        $helperClass = $this->_affirmHelperClass;
        $client->setAuth(Mage::helper($helperClass)->getApiKey($storeId),
            Mage::helper($helperClass)->getSecretKey($storeId), Zend_Http_Client::AUTH_BASIC
        );
        $rawResult = $client->request($method)->getRawBody();
        try {
            $retJson = Zend_Json::decode($rawResult, Zend_Json::TYPE_ARRAY);
        } catch (Zend_Json_Exception $e) {
            throw new Affirm_Affirm_Exception(Mage::helper('affirm')->__('Invalid affirm response: '. $rawResult));
        }
        //validate to make sure there are no errors here
        if (isset($retJson['status_code'])) {
            throw new Affirm_Affirm_Exception(Mage::helper('affirm')->__('Affirm error code:'.
                $retJson['status_code'] . ' error: '. $retJson['message']));
        }
        return $retJson;
    }

    public function getCheckoutObject($orderId)
    {
        $order = Mage::getModel('sales/order')->load($orderId);
        $shippingAddress = $order->getShippingAddress();
        $shipping = null;
        if ($shippingAddress) {
            $shipping = array(
                'name' => array('full' => $shippingAddress->getName()),
                'phone_number' => $shippingAddress->getTelephone(),
                'phone_number_alternative' => $shippingAddress->getAltTelephone(),
                'address' => array(
                    'line1' => $shippingAddress->getStreet(1),
                    'line2' => $shippingAddress->getStreet(2),
                    'city' => $shippingAddress->getCity(),
                    'state' => $shippingAddress->getRegion(),
                    'country' => $shippingAddress->getCountryModel()->getIso2Code(),
                    'zipcode' => $shippingAddress->getPostcode(),
                ));
        }

        $billingAddress = $order->getBillingAddress();
        $billing = array(
            'name' => array('full' => $billingAddress->getName()),
            'email' => $order->getCustomerEmail(),
            'phone_number' => $billingAddress->getTelephone(),
            'phone_number_alternative' => $billingAddress->getAltTelephone(),
            'address' => array(
                'line1' => $billingAddress->getStreet(1),
                'line2' => $billingAddress->getStreet(2),
                'city' => $billingAddress->getCity(),
                'state' => $billingAddress->getRegion(),
                'country' => $billingAddress->getCountryModel()->getIso2Code(),
                'zipcode' => $billingAddress->getPostcode(),
            ));

        $items = array();
        $productIds = array();
        $productItemsMFP = array();
        $categoryItemsIds = array();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $productIds[] = $orderItem->getProductId();
        }
        $products = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect(
                array('affirm_product_mfp', 'affirm_product_mfp_type', 'affirm_product_mfp_priority')
            )
            ->addAttributeToFilter('entity_id', array('in' => $productIds));
        $productItems = $products->getItems();
        foreach ($order->getAllVisibleItems() as $orderItem) {
            $product = $productItems[$orderItem->getProductId()];
            if (Mage::helper('affirm')->isPreOrder() && $orderItem->getParentItem() &&
                ($orderItem->getParentItem()->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
            ) {
                continue;
            }

            try{
                $baseImageUrl = Mage::helper('catalog/image')->init($product, 'image');
            }
            catch(Exception $e) {
                $baseImageUrl = Mage::getDesign()->getSkinUrl('images/catalog/product/placeholder/image.jpg',array('_area'=>'frontend'));
            }

            $items[] = array(
                'sku' => $orderItem->getSku(),
                'display_name' => $orderItem->getName(),
                'item_url' => $product->getProductUrl(),
                'item_image_url' => $baseImageUrl,
                'qty' => intval($orderItem->getQtyOrdered()),
                'unit_price' => Mage::helper('affirm/util')->formatCents($orderItem->getPrice())
            );

            $start_date = $product->getAffirmProductMfpStartDate();
            $end_date = $product->getAffirmProductMfpEndDate();
            if (empty($start_date) || empty($end_date)) {
                $mfpValue = $product->getAffirmProductMfp();
            } else {
                if (Mage::app()->getLocale()->isStoreDateInInterval(null, $start_date, $end_date)) {
                    $mfpValue = $product->getAffirmProductMfp();
                } else {
                    $mfpValue = "";
                }
            }

            $productItemsMFP[] = array(
                'value' => $mfpValue,
                'type' => $product->getAffirmProductMfpType(),
                'priority' => $product->getAffirmProductMfpPriority() ?
                    $product->getAffirmProductMfpPriority() : 0
            );

            $categoryIds = $product->getCategoryIds();
            if (!empty($categoryIds)) {
                $categoryItemsIds = array_merge($categoryItemsIds, $categoryIds);
            }
        }

        $storeId = $order->getStoreId(); // we need to send url of store for which order was placed
        $checkout = array(
            'checkout_id' => $order->getIncrementId(),
            'currency' => $order->getOrderCurrencyCode(),
            'shipping_amount' => Mage::helper('affirm/util')->formatCents($order->getShippingAmount()),
            'shipping_type' => $order->getShippingMethod(),
            'tax_amount' => Mage::helper('affirm/util')->formatCents($order->getTaxAmount()),
            'merchant' => array(
                'name' => Mage::helper('affirm_telesales')->getMerchantName(),
                'public_api_key' => Mage::helper('affirm_telesales')->getApiKey(),
                'user_confirmation_url' => Mage::app()->getStore($storeId)->getUrl('telesales/payment/confirm', array('_secure' => true)),
                'user_cancel_url' => Mage::app()->getStore($storeId)->getUrl('telesales/payment/cancel', array('_secure' => true)),
                'user_confirmation_url_action' => 'POST',
                'charge_declined_url' => Mage::app()->getStore($storeId)->getUrl('telesales/payment/decline', array('_secure' => true))
            ),
            'config' => array('required_billing_fields' => 'name,address,email'),
            'items' => $items,
            'billing' => $billing
        );

        // By convention, Affirm expects positive value for discount amount. Magento provides negative.
        $discountAmtAffirm = (-1) * $order->getDiscountAmount();
        if ($discountAmtAffirm > 0.001) {
            $discountCode = $this->_getDiscountCode($order);
            $checkout['discounts'] = array(
                $discountCode => array(
                    'discount_amount' => Mage::helper('affirm/util')->formatCents($discountAmtAffirm)
                )
            );
        }

        if ($shipping) {
            $checkout['shipping'] = $shipping;
        }
        $checkout['total'] = Mage::helper('affirm/util')->formatCents($order->getTotalDue());
        if (method_exists('Mage', 'getEdition')) {
            $platform_edition = Mage::getEdition();
        }
        $platform_version = Mage::getVersion();
        $platform_version_edition = isset($platform_edition) ? $platform_version . ' ' . $platform_edition : $platform_version;
        $checkout['metadata'] = array(
            'shipping_type' => $order->getShippingDescription(),
            'platform_type' => 'Magento Telesales',
            'platform_version' => $platform_version_edition,
            'platform_affirm' => Mage::helper('affirm_telesales')->getExtensionVersion()
        );
        $checkout['send_link'] = array(
            'sms' => true,
            'email' => true
        );
        $affirmMFPValue = Mage::helper('affirm/mfp')->getAffirmMFPValue($productItemsMFP, $categoryItemsIds, $order->getBaseGrandTotal());
        if ($affirmMFPValue) {
            $checkout['financing_program'] = $affirmMFPValue;
        }

        $checkoutObject = new Varien_Object($checkout);
        Mage::dispatchEvent('affirm_get_checkout_object_after', array('checkout_object' => $checkoutObject));
        $checkout = $checkoutObject->getData();
        return $checkout;
    }
}