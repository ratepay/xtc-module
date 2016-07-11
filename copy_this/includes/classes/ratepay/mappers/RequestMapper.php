<?php

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * @category  PayIntelligent
 * @package   ratepay
 * @copyright (C) 2012 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 */

require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/HeadInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/CustomerInfo/AddressInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/CustomerInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/CustomerInfo/BankaccountInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/BasketInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/BasketInfo/ItemInfo.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/models/PaymentInfo.php');

require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Data.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Db.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Session.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Loader.php');

/**
 * RequestMapper class, fills all models which are needed for the ratepay calls
 */
class rpRequestMapper
{
    /**
     * Retrieve a headInfo model, filled with the merchant crdentials and some transaction data
     * 
     * @param order $order
     * @param string $transactionId
     * @param string $transactionShortId 
     * @param int $orderId
     * @param string $subtype
     * @return rpHeadInfo
     */
    public static function getHeadInfoModel(order $order, $transactionId = null, $transactionShortId = null, $orderId = null, $subtype = null)
    {
        rpSession::setRpSessionEntry('orderId', $orderId);
        $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
        
        $headInfo = new rpHeadInfo();
        $headInfo->setOrderId($orderId)
                ->setProfileId($payment->profileId)
                ->setSecurityCode($payment->securityCode)
                ->setSubtype($subtype)
                ->setTransactionId($transactionId)
                ->setTransactionShortId($transactionShortId)
                ->setShopSystem($payment->shopSystem)
                ->setShopVersion($payment->shopVersion)
                ->setModuleVersion($payment->version)
                ->setDeviceSite(rpSession::getRpSessionEntry('RATEPAY_DFP_SNIPPET_ID'))
                ->setDeviceToken(rpSession::getRpSessionEntry('RATEPAY_DFP_TOKEN'));

        return $headInfo;
    }

    /**
     * Retrieve a customerInfo model filled with the addresses and the customer data
     * 
     * @param order $order
     * @param int $orderId
     * @return CustomerInfo
     */
    public static function getCustomerInfoModel(order $order, $orderId = null)
    {
        $customerInfo = new CustomerInfo();
        $customerInfo->setBillingAddressInfo(self::getBillingAdressInfo($order, $orderId))
                ->setShippingAddressInfo(self::getShippingAdressInfo($order, $orderId));

        $customerInfo->setCreditInquiry('yes')
                ->setDateOfBirth(rpDb::getCustomersDob($orderId, rpSession::getSessionEntry('customer_id')))
                ->setEmail($order->customer['email_address'])
                ->setFax(rpDb::getCustomersFax($orderId, rpSession::getSessionEntry('customer_id')))
                ->setPhone($order->customer['telephone'])
                ->setFirstName(is_null($orderId) ? $order->customer['firstname'] : rpDb::getShopOrderDataEntry($orderId, 'customers_firstname'))
                ->setGender(is_null($orderId) ? $order->customer['gender'] : rpDb::getRatepayOrderDataEntry($orderId, 'gender'))
                ->setIp(is_null($orderId) ? rpData::getCustomerIp() : $order->customer['cIP'])
                ->setLastName(is_null($orderId) ? $order->customer['lastname'] : rpDb::getShopOrderDataEntry($orderId, 'customers_lastname'))
                ->setNationality(is_array($order->customer['country']) ? $order->customer['country']['iso_code_2'] : rpDb::getRatepayOrderDataEntry($orderId, 'customers_country_code'));
        $vatId = rpDb::getCustomersVatId($orderId, rpSession::getSessionEntry('customer_id'));
        if (!empty($order->customer['company']) && !empty($vatId)) {
            $customerInfo->setCompany($order->customer['company'])->setVatId($vatId);
        }
        
        if ($order->info['payment_method'] === 'ratepay_sepa' && is_null($orderId)) {
            $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
            $bankAccount = $payment->getBankData();
            $bankAccountInfo = new rpBankaccountInfo();
            $bankAccountInfo->setAccountNumber($bankAccount['accountnumber']);
            $bankAccountInfo->setBankName($bankAccount['bankname']);
            $bankAccountInfo->setOwner($bankAccount['owner']);
            if (!empty($bankAccount['bankcode'])) {
                $bankAccountInfo->setBankAccount($bankAccount['bankcode']);
            }
            
            $customerInfo->setBankAccount($bankAccountInfo);
        }

        return $customerInfo;
    }

    /**
     * Retrieve a billingAddressInfo model
     * 
     * @param order $order
     * @param int $orderId
     * @return rpAddressInfo
     */
    private static function getBillingAdressInfo(order $order, $orderId)
    {
        $countryId = is_array($order->billing['country']) ? $order->billing['country']['iso_code_2'] : rpDb::getShopOrderDataEntry($orderId, 'billing_country_iso_code_2');
        $addressInfo = new rpAddressInfo();
        $addressInfo->setCity($order->billing['city'])
                ->setCountryId($countryId)
                ->setStreet($order->billing['street_address'])
                ->setZip($order->billing['postcode']);
        $addressInfo->setType('BILLING');
        return $addressInfo;
    }

    /**
     * Retrieve a shippingAddressInfo model
     * 
     * @param order $order
     * @param int $orderId
     * @return rpAddressInfo
     */
    private static function getShippingAdressInfo(order $order, $orderId)
    {
        $countryId = is_array($order->delivery['country']) ? $order->delivery['country']['iso_code_2'] : rpDb::getShopOrderDataEntry($orderId, 'delivery_country_iso_code_2');
        $addressInfo = new rpAddressInfo();
        $addressInfo->setCity($order->delivery['city'])
                ->setCountryId($countryId)
                ->setStreet($order->delivery['street_address'])
                ->setZip($order->delivery['postcode']);
        $addressInfo->setType('DELIVERY');
        return $addressInfo;
    }

    /**
     * Retrieve a basketInfo model, filled with the items and basket data
     * 
     * @param order $order
     * @param int $orderId
     * @param array $post
     * @return rpBasketInfo
     */
    public static function getBasketInfoModel(order $order, $orderId = null, array $post = array(), $subType = false)
    {
        $basketInfo = new rpBasketInfo();
        $basketInfo->setAmount(rpData::getBasketAmount($order, $orderId, $post, $subType))
                ->setCurrency($order->info['currency'])
                ->setItems(self::getItems($order, $post, $orderId, $subType));
        return $basketInfo;
    }

    /**
     * Retrieve the items from the order
     * 
     * @param order $order
     * @return array
     */
    private static function getItemsByOrder(order $order)
    {
        $items = array();

        foreach ($order->products as $product) {
            $items[] = self::getItem(rpData::getItemData($product));

        }
        $shipping = rpData::getShippingData($order);
        if (!empty($shipping)) {
            $items[] = self::getItem($shipping);
        }

        foreach (rpData::getDiscounts() as $discountData) {
            $discount = rpData::getDiscountData($discountData);
            if (!empty($discount)) {
                $items[] = self::getItem($discount);
            }
        }
        return $items;
    }

    /**
     * Retrieve the items for the basketInfo model
     * 
     * @param order $order
     * @param array $post
     * @param int $orderId
     * @return array
     */
    private static function getItems(order $order, array $post, $orderId = null, $subType = false)
    {

        if (is_null($orderId)) {
            $items = self::getItemsByOrder($order);
        } else {
            $items = self::getItemInfoByTable($orderId, $post, $subType);
        }
        
        return $items;
    }

    /**
     * Retrieve the items by the ratepay tables
     * 
     * @param int $orderId
     * @param array $post
     * @return array
     */
    private static function getItemInfoByTable($orderId, array $post, $subType = false)
    {
        $itemInfos = array();
        $items = rpDb::getItemsByTable($orderId, $post, $subType);
        foreach ($items as $item) {
            if ($item['qty'] > 0) {
                $itemInfos[] = self::getItem($item);
            }
        }
        return $itemInfos;
    }

    /**
     * Retrieve itemInfo model
     * 
     * @param array $itemData
     * @return rpItemInfo
     */
    private static function getItem(array $itemData)
    {
        $item = new rpItemInfo();
        $item->setArticleName($itemData['name'])
                ->setArticleNumber(!empty($itemData['model']) ? $itemData['model'] : $itemData['id'])
                ->setUniqueArticleNumber($itemData['id'])
                ->setQuantity($itemData['qty'])
                ->setTaxRate($itemData['taxRate'])
                ->setUnitPriceGross($itemData['unitPriceGross']);
        return $item;
    }

    /**
     * Retrieve paymentInfo model 
     * 
     * @param order $order
     * @param int $orderId
     * @return rpPaymentInfo
     */
    public static function getPaymentInfoModel(order $order, $orderId = null, array $post = array(), $subType = false)
    {
        $paymentInfo = new rpPaymentInfo();
        $paymentInfo->setCurrency($order->info['currency']);
        $paymentInfo->setMethod(rpData::getRpPaymentMethod($order->info['payment_method']));
        if ($subType != 'credit' && $subType != 'return' && $subType != 'cancellation') {
            $paymentInfo->setAmount(rpData::getPaymentAmount($order, $orderId, $post));
            if ($order->info['payment_method'] == 'ratepay_rate') {
                if (is_null($orderId)) {
                    $paymentInfo->setDebitType('BANK-TRANSFER')
                        ->setInstallmentAmount(rpSession::getRpSessionEntry('ratepay_rate_rate'))
                        ->setInstallmentNumber(rpSession::getRpSessionEntry('ratepay_rate_number_of_rates'))
                        ->setInterestRate(rpSession::getRpSessionEntry('ratepay_rate_interest_rate'))
                        ->setLastInstallmentAmount(rpSession::getRpSessionEntry('ratepay_rate_last_rate'))
                        ->setPaymentFirstDay(rpSession::getRpSessionEntry('ratepay_payment_firstday'));
                } else {
                    $details = rpDb::getRatepayRateDetails($orderId);
                    $paymentInfo->setDebitType('BANK-TRANSFER')
                        ->setInstallmentAmount($details['rate'])
                        ->setInstallmentNumber($details['number_of_rates'])
                        ->setInterestRate($details['interest_amount'])
                        ->setLastInstallmentAmount($details['last_rate'])
                        ->setPaymentFirstDay($details['payment_firstday']);
                }
            }
        }
        
        return $paymentInfo;
    }

}
