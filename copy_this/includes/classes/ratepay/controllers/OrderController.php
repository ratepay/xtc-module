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

require_once('includes/classes/order.php');
require_once(dirname(__FILE__) . '/../../../../lang/' . rpSession::getLang() . '/admin/modules/payment/ratepay.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/mappers/RequestMapper.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Data.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Db.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Session.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Globals.php');
require_once(dirname(__FILE__) . '/../../../classes/ratepay/helpers/Loader.php');

/**
 * Order controller
 */
class rpOrderController
{
    /**
     * Call CONFIRMATION_DELIVER and updates order and item data
     */
    public static function deliverAction($deliverByCredit = false)
    {
        $post = (!$deliverByCredit) ? rpGlobals::getPost() : $deliverByCredit;
        $orderId = rpGlobals::getPostEntry('order_number');
        $order = new order($orderId);
        $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
        $transactionId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_id');
        $transactionShortId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_short_id');
        $data = array(
            'HeadInfo'   => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId),
            'BasketInfo' => rpRequestMapper::getBasketInfoModel($order, $orderId, self::getDeliverPostData($post))
        );
        $requestService = new rpRequestService($payment->sandbox, $data);
        $result = $requestService->callConfirmationDeliver();
        rpDb::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
        if (!array_key_exists('error', $result)) {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackSuccess');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_DELIVER_SUCCESS);
            rpDb::shipRpOrder(self::getDeliverPostData($post), $order);
            rpDb::setRpHistoryEntrys($post, 'CONFIRMATION_DELIVER', '');
        } else {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackError');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_DELIVER_ERROR);
        }
        
        xtc_redirect(xtc_href_link("ratepay_order.php", 'oID=' . $orderId, 'SSL'));
    }
    
    /**
     * Call PAYMENT_CHANGE with the subtype full or 
     * part cancellation and updates order and item data
     */
    public static function cancelAction()
    {
        $post = rpGlobals::getPost();
        $orderId = rpGlobals::getPostEntry('order_number');
        $order = new order($orderId);
        $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
        $transactionId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_id');
        $transactionShortId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_short_id');
        $subType = 'cancellation';
        $data = array(
            'HeadInfo'   => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId, $subType),
            'BasketInfo' => rpRequestMapper::getBasketInfoModel($order, $orderId, self::getCancelPostData($post), $subType),
            'PaymentInfo' => rpRequestMapper::getPaymentInfoModel($order, $orderId, self::getCancelPostData($post))
        );
        $requestService = new rpRequestService($payment->sandbox, $data);
        $result = $requestService->callPaymentChange();
        rpDb::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
        if ($result) {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackSuccess');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_CANCEL_SUCCESS);
            rpDb::cancelRpOrder(self::getCancelPostData($post), $order);
            rpDb::setRpHistoryEntrys($post, 'PAYMENT_CHANGE', $subType);
            rpDb::cancelOrRefundShopItems($post, $orderId);
            rpDb::updateShopOrderTotals($orderId);
        } else {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackError');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_CANCEL_ERROR);
        }
        
        xtc_redirect(xtc_href_link("ratepay_order.php", 'oID=' . $orderId, 'SSL'));
    }
    
    /**
     * Call PAYMENT_CHANGE with the subtype full or 
     * part return and updates order and item data
     */
    public static function refundAction()
    {
        $post = rpGlobals::getPost();
        $orderId = rpGlobals::getPostEntry('order_number');
        $order = new order($orderId);
        $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
        $transactionId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_id');
        $transactionShortId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_short_id');
        $subType = 'return';
        $data = array(
            'HeadInfo'   => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId, $subType),
            'BasketInfo' => rpRequestMapper::getBasketInfoModel($order, $orderId, self::getRefundPostData($post), $subType),
            'PaymentInfo' => rpRequestMapper::getPaymentInfoModel($order, $orderId, self::getRefundPostData($post))
        );
        $requestService = new rpRequestService($payment->sandbox, $data);
        $result = $requestService->callPaymentChange();
        rpDb::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
        if (!array_key_exists('error', $result)) {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackSuccess');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_REFUND_SUCCESS);
            rpDb::refundRpOrder(self::getRefundPostData($post), $order);
            rpDb::setRpHistoryEntrys($post, 'PAYMENT_CHANGE', $subType);
            rpDb::cancelOrRefundShopItems($post, $orderId);
            rpDb::updateShopOrderTotals($orderId);
        } else {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackError');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_REFUND_ERROR);
        }
        
        xtc_redirect(xtc_href_link("ratepay_order.php", 'oID=' . $orderId, 'SSL'));
    }
    
    /**
     * Call PAYMENT_CHANGE with the subtype credit
     * and add a credit item to the order
     */
    public static function creditAction()
    {
        $post = rpGlobals::getPost();
        $orderId = rpGlobals::getPostEntry('order_number');
        $price = floatval($post['voucherAmount'] . '.' . $post['voucherAmountKomma']);
        if ($price > 0) {
            $order = new order($orderId);
            $rate = ($order->info['payment_method'] == 'ratepay_rate') ? true : false;
            $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
            $transactionId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_id');
            $transactionShortId = rpDb::getRatepayOrderDataEntry($orderId, 'transaction_short_id');
            if ($rate) {
                $subType = 'return';
                $postCredit = self::getRefundPostData($post);
            } else {
                $subType = 'credit';
            }
            $postCredit['order_number'] = $post['order_number'];
            $postCredit['voucherAmount'] = $post['voucherAmount'];
            $postCredit['voucherAmountKomma'] = $post['voucherAmountKomma'];
            $data = array(
                'HeadInfo'   => rpRequestMapper::getHeadInfoModel($order, $transactionId, $transactionShortId, $orderId, $subType),
                'BasketInfo' => rpRequestMapper::getBasketInfoModel($order, $orderId, $postCredit, $subType),
                'PaymentInfo' => rpRequestMapper::getPaymentInfoModel($order, $orderId, $postCredit)
            );
            $requestService = new rpRequestService($payment->sandbox, $data);
            $result = $requestService->callPaymentChange();
            rpDb::xmlLog($order, $requestService->getRequest(), $orderId, $requestService->getResponse());
            if (!array_key_exists('error', $result)) {
                rpSession::setRpSessionEntry('message_css_class', 'messageStackSuccess');
                rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_CREDIT_SUCCESS);
                rpDb::setRpCreditItem(rpGlobals::getPost(), ($rate) ? 1 : 0);
                rpDb::setRpHistoryEntry($orderId, rpData::getCreditItem($post), 'PAYMENT_CHANGE', $subType);
                rpDb::addCreditToShop($orderId, $post);
                rpDb::updateShopOrderTotals($orderId);
            } else {
                rpSession::setRpSessionEntry('message_css_class', 'messageStackError');
                rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_CREDIT_ERROR);
            }

            if ($rate) {
                xtc_redirect(xtc_href_link("ratepay_order.php", 'oID=' . $orderId, 'SSL'));
            } else {
                self::deliverAction(self::getDeliverPostArray($orderId, $post['items']));
            }
        } else {
            rpSession::setRpSessionEntry('message_css_class', 'messageStackError');
            rpSession::setRpSessionEntry('message', RATEPAY_ORDER_MESSAGE_CREDIT_ERROR);
            xtc_redirect(xtc_href_link("ratepay_order.php", 'oID=' . $orderId, 'SSL'));
        }
    }

    private static function getDeliverPostArray($orderId, $items) {
        $items[rpDb::getMaxCreditId($orderId)] = 1;
        return array(
            'items' => $items,
            'order_number' => $orderId,
            'ship' => "versenden");
    }

    /**
     * Build the deliver post array
     * 
     * @param array $post
     * @return array
     */
    private static function getDeliverPostData(array $post)
    {
        $postData = array();
        $postData['deliver'] = true;
        foreach ($post['items'] as $key => $value) {
            $postData[$key]['toShip'] = $value;
        }
        
        return $postData;
    }
    
    /**
     * Build the refund post array
     * 
     * @param array $post
     * @return array
     */
    private static function getRefundPostData(array $post)
    {
        $postData = array();
        foreach ($post['items'] as $key => $value) {
            $postData[$key]['toRefund'] = $value;
        }
        
        return $postData;
    }

    /**
     * Build the cancel post array
     * 
     * @param array $post
     * @return array
     */
    private static function getCancelPostData(array $post)
    {
        $postData = array();
        foreach ($post['items'] as $key => $value) {
            $postData[$key]['toCancel'] = $value;
        }

        return $postData;
    }
}