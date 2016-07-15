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

require_once('Db.php');
require_once('Session.php');
require_once('Globals.php');
require_once('Loader.php');

/**
 * Data helper, contians all helper methods
 * (DB operations, session access, access on globals
 */
class rpData
{

    /**
     * Retrieve the credntials for the requets
     *
     * @param string $payment
     * @return array
     */
    public static function getCredentials($payment)
    {
        $payment = rpLoader::getRatepayPayment($payment);
        return array('profileId' => $payment->profileId, 'securityCode' => $payment->securityCode);
    }

    /**
     * This method replaces all chars which can produce problems with the zoot risk managment
     *
     * @param string $str
     * @return string
     */
    public static function removeSpecialChars($str)
    {
        $search = array("–", "´", "‹", "›", "‘", "’", "‚", "“", "”", "„", "‟", "•", "‒", "―", "—", "™", "¼", "½", "¾");
        $replace = array("-", "'", "<", ">", "'", "'", ",", '"', '"', '"', '"', "-", "-", "-", "-", "TM", "1/4", "1/2", "3/4");
        return str_replace($search, $replace, $str);
    }

    /**
     * This method returns the IP of the Customer
     *
     * @return string
     */
    public static function getCustomerIp()
    {
        $systemId = "";
        if (isset($_SERVER['HTTP_CLIENT_IP']) && !empty($_SERVER['HTTP_CLIENT_IP'])) {
            $systemId = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && !empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $systemId = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $systemId = $_SERVER['REMOTE_ADDR'];
        }

        return $systemId;
    }

    /**
     * Retrieve a credit item builded from the post data
     *
     * @param array $post
     * @return array
     */
    public static function getCreditItem($post)
    {
        $price = empty($post['voucherAmountKomma']) ? floatval($post['voucherAmount']) : floatval($post['voucherAmount'] . '.' . $post['voucherAmountKomma']);
        $credit = array();
        $credit['id'] = 'CREDIT[' . rpDb::getNextCreditId($post['order_number']) . ']';
        $credit['name'] = utf8_decode('Händler Gutschrift');
        $credit['qty'] = 1;
        $credit['unitPriceGross'] = $price * -1;
        $credit['taxRate'] = 19;

        return $credit;
    }

    /**
     * Subtrahate the quantitie from the given item with the quantitie to cancel
     *
     * @param array $item
     * @param int $toCancel
     * @return array
     */
    public static function getRemainingItemData(array $item, $subType = false)
    {
        $qty = 0;
        switch ($subType) {
            case 'cancellation':
                $qty = $item['ordered'] - ($item['shipped'] + $item['cancelled']);
                break;
            case 'return':
                $qty = $item['shipped'] - $item['returned'];
                break;
            case 'credit':
                $qty = $item['ordered'] - ($item['shipped'] + $item['cancelled']);
                break;
            default:
                $qty = $item['ordered'] - ($item['cancelled'] + $item['returned']);
                break;
        }

        $entry = array();
        $entry['id'] = $item['articleNumber'];
        $entry['name'] = $item['articleName'];
        $entry['qty'] = $qty;
        $entry['unitPriceGross'] = $item['unitPriceGross'];
        $entry['taxRate'] = $item['taxRate'];

        return $entry;
    }

    /**
     * Subtrahate the quantitie from the given item with the quantitie to refund
     *
     * @param array $item
     * @param int $toRefund
     * @return array
     */
    public static function getRefundItemData(array $item, $toRefund)
    {
        $entry = array();
        $qty = $item['shipped'] - $item['returned'] - $toRefund;
        $entry['id'] = $item['articleNumber'];
        $entry['name'] = $item['articleName'];
        $entry['qty'] = $qty;
        $entry['unitPriceGross'] = $item['unitPriceGross'];
        $entry['taxRate'] = $item['taxRate'];
        return $entry;
    }

    /**
     * Subtrahate the quantitie from the given item with the quantitie to cancel
     *
     * @param array $item
     * @param int $toCancel
     * @return array
     */
    public static function getCancelItemData(array $item, $toCancel = 0)
    {
        $entry = array();
        $qty = $item['ordered'] - $item['shipped'] - $item['cancelled'] - $item['returned'] - $toCancel;
        $entry['id'] = $item['articleNumber'];
        $entry['name'] = $item['articleName'];
        $entry['qty'] = $qty;
        $entry['unitPriceGross'] = $item['unitPriceGross'];
        $entry['taxRate'] = $item['taxRate'];

        return $entry;
    }

    /**
     * Build a shipping item from the given item and the quantitie to ship
     *
     * @param array $item
     * @param int $toShip
     * @return array
     */
    public static function getDeliverItemData(array $item, $toShip)
    {
        $entry = array();
        $entry['id'] = $item['articleNumber'];
        $entry['name'] = $item['articleName'];
        $entry['qty'] = $toShip;
        $entry['unitPriceGross'] = $item['unitPriceGross'];
        $entry['taxRate'] = $item['taxRate'];

        return $entry;
    }

    /**
     * Retrieve a request item from a given shop order product
     *
     * @param array $product
     * @return array
     */
    public static function getItemData($product)
    {
        $item = array();
        $item['id'] = $product['id'];
        $item['name'] = $product['name'];
        $item['qty'] = intval($product['qty']);
        $item['unitPriceGross'] = floatval($product['price']);
        $item['taxRate'] = floatval($product['tax']);
        $item['model'] = $product['model'];
        return $item;
    }

    /**
     * Retrieve a request shipping item from the given order
     *
     * @param order $order
     * @return array
     */
    public static function getShippingData(order $order)
    {
        $shipping = array();
        $session = rpSession::getSessionEntry('piRP');
        if (array_key_exists('shipping_cost', $order->info) && $order->info['shipping_cost'] > 0) {
            $shipping['qty'] = 1;
            $shipping['name'] = $order->info['shipping_method'];
            $shipping['id'] = 'SHIPPING';
            $shipping['unitPriceGross'] = $order->info['shipping_cost'] + self::getShippingTaxAmount($order);
            $shipping['taxRate'] = self::getShippingTaxRate($order);
            rpSession::setRpSessionEntry('shipping', $shipping);
        } else if (array_key_exists('shipping', $session)) {
            $shipping = rpSession::getRpSessionEntry('shipping');
        }

        return $shipping;
    }

    /**
     * Retrieve the payment amount for the request
     *
     * @param order $order
     * @param int $orderId
     * @param array $post
     * @return float
     */
    public static function getPaymentAmount(order $order, $orderId = null, array $post = array())
    {
        $amount = self::getBasketAmount($order, $orderId, $post);
        if ($order->info['payment_method'] === 'ratepay_rate' && is_null($orderId)) {
            $amount = rpSession::getRpSessionEntry('ratepay_rate_total_amount');
        }
        
        return $amount;
    }

    /**
     * Retrieve the basket amount
     *
     * @param order $order
     * @param int $orderId
     * @param array $post
     * @return float
     */
    public static function getBasketAmount(order $order, $orderId = null, array $post = array(), $subType = false)
    {
        $discountPrice = 0;
        foreach (self::getDiscounts() as $discountData) {
            $discount = self::getDiscountData($discountData);
            $discountPrice += $discount['unitPriceGross'];
        }

        $amount = $order->info['total'] + self::getShippingTaxAmount($order) + $discountPrice;

        if (!is_null($orderId)) {
            $amount = 0;
            $items = rpDb::getItemsByTable($orderId, $post, $subType);
            foreach ($items as $item) {
                $amount += floatval($item['unitPriceGross']) * $item['qty'];
            }
        }

        return $amount;
    }

    /**
     * Retrieve the current item amount
     *
     * @param array $item
     * @return float
     */
    public static function getItemAmount($item) {
        return $item['unitPriceGross'] * ($item['ordered'] - $item['cancelled'] - $item['returned']);
    }

    /**
     * Retrieve the subtotal
     *
     * @param int $orderId
     * @return float
     */
    public static function getSubtotal($orderId)
    {
        $amount = 0;
        $items = rpDb::getItemsByTable($orderId);
        foreach ($items as $item) {
            if (!strstr($item['id'], 'DISCOUNT') && !strstr($item['id'], 'SHIPPING') && !strstr($item['id'], 'CREDIT')) {
                $amount += $item['unitPriceGross'] * $item['qty'];
            }
        }

        return $amount;
    }

    /**
     * Retrieve the total taxt amount
     *
     * @param int $orderId
     * @return float
     */
    public static function getTotalTaxAmount($orderId)
    {

        $amount = 0;
        $items = rpDb::getItemsByTable($orderId);
        foreach ($items as $item) {
            $amount += $item['unitPriceGross'] * ($item['taxRate'] / 100) * $item['qty'];
        }

        return $amount;
    }

    /**
     * Add the shipping tax to the order object
     *
     * @param order $order
     * @return float
     */
    public static function getShippingTaxAmount(order $order)
    {
        return $order->info['shipping_cost'] * (self::getShippingTaxRate($order) / 100);
    }

    /**
     * Retrieve the shipping tax rate
     *
     * @param order $order
     * @return float
     */
    public static function getShippingTaxRate(order $order)
    {
        $shippingClassArray = explode("_", $order->info['shipping_class']);
        $shippingClass = strtoupper($shippingClassArray[0]);
        if (empty($shippingClass)) {
            $shippingTaxRate = 0;
        } else {
            $const = 'MODULE_SHIPPING_' . $shippingClass . '_TAX_CLASS';
            if (defined($const)) {
                $shippingTaxRate = xtc_get_tax_rate(constant($const));
            } else {
                $shippingTaxRate = 0;
            }
        }

        return $shippingTaxRate;
    }

    /**
     * Retrieve discount data if available
     *
     * @param array $discountData
     * @return array
     */
    public static function getDiscountData($discountData = null)
    {
        $discount = array();
        if (!is_null($discountData)) {
            $discountValue = number_format($discountData['value'], 2, ".", "");
            $discount['qty'] = 1;
            $discount['name'] = $discountData['title'];
            $discount['id'] = 'DISCOUNT';
            $discount['unitPriceGross'] = ($discountValue > 0) ? $discountValue * (-1) : $discountValue;
            $discount['taxRate'] = self::getCouponTaxRate();
        }

        return $discount;
    }

    /**
     * Retrieve discounts from session if available
     *
     * @return array
     */
    public static function getDiscounts()
    {
        $discounts = rpSession::getRpSessionEntry('coupon');

        if (!is_null($discounts)) {
            return $discounts;
        }

        return array();
    }

    /**
     * Retrive the coupon tax rate
     *
     * @return float
     */
    public static function getCouponTaxRate()
    {
        $const = 'MODULE_ORDER_TOTAL_COUPON_TAX_CLASS';
        if (defined($const)) {
            $couponTaxClass = xtc_get_tax_rate(constant($const));
        } else {
            $couponTaxClass = 0;
        }

        return $couponTaxClass;
    }

    /**
     * Is payment ratepay_rechnung or ratepay_rate
     *
     * @param string $code
     * @return boolean
     */
    public static function isRatepayPayment($code)
    {
        $payments = array('ratepay_rate', 'ratepay_rechnung', 'ratepay_sepa');
        return in_array($code, $payments);
    }

    /**
     * Retrieve payment type by the given code
     *
     * @param string $code
     * @return string
     */
    public function getRpPaymentMethod($code)
    {
        if (self::isRatepayPayment($code)) {
            $methods = array(
                'ratepay_rechnung' => 'invoice',
                'ratepay_rate' => 'installment',
                'ratepay_sepa' => 'elv'
            );
            return $methods[$code];
        }

        trigger_error('Not a RatePAY payment');
    }

    /**
     * Disable RatePAY for a customer
     */
    public static function disableRatepay()
    {
        rpSession::setRpSessionEntry('disabled', true);
    }

    /**
     * Is RatePAY disabled
     *
     * @return boolean
     */
    public static function isRatepayAvailable()
    {
        return !rpSession::getRpSessionEntry('disabled');
    }

    /**
     * Retrieve if the result is SUCCESS or ERROR
     *
     * @param string $result
     * @return string
     */
    public static function getRpResult($result)
    {
        $success = array(
            'Confirmation deliver successful',
            'Transaction initialized',
            'Payment change successful',
            'Transaction result successful',
            'Transaction result pending'
        );

        return in_array($result, $success) ? 'SUCCESS' : 'ERROR';
    }

    /**
     * Retrieve the full name of an order's customer
     *
     * @param int $orderId
     * @return string
     */
    public static function getFullName($orderId)
    {
        // For some reasons i can't require this at the head of the file
        require_once(dirname(__FILE__) . '/../../../../admin/includes/classes/order.php');
        $order = new order($orderId);
        return $order->customer['name'];
    }

    /**
     * Retrieve the logging logical
     *
     * @return string
     */
    public static function getLoggingLogical()
    {
        $logicals = array('desc' => 'asc', 'asc' => 'desc');
        $logical = 'desc';
        if (rpGlobals::hasParam('logical')) {
            $logical = $logicals[rpGlobals::getParam('logical')];
        }
        return $logical;
    }

    /**
     * Add line breaks to xml
     *
     * @param string $xml
     * @return string
     */
    public static function addXmlLineBreak($xml)
    {
        return trim(str_replace("&gt;&lt;", "&gt;\n&lt;", $xml));
    }

    /**
     * Retrieve the available item quantitie to ship or cancel
     *
     * @param array $item
     * @return int
     */
    public static function getAvailableItemQtyToShipOrCancel($item)
    {
        return $item['ordered'] - $item['cancelled'] - $item['shipped'];
    }

    /**
     * Retrieve the available item quantitie to refund
     *
     * @param array $item
     * @return int
     */
    public static function getAvailableItemQtyToRefund($item)
    {
        return $item['shipped'] - $item['returned'];
    }

    /**
     * Is full cancel
     *
     * @param array $post
     * @param int $orderId
     * @return boolean
     */
    public static function isFullCancel($post, $orderId)
    {
        foreach (rpDb::getRpItems($orderId) as $item) {
            if (!($item['ordered'] == $post[$item['id']]['toCancel'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Is full return
     *
     * @param array $post
     * @param int $orderId
     * @return boolean
     */
    public static function isFullReturn($post, $orderId)
    {
        foreach (rpDb::getRpItems($orderId) as $item) {
            if (!($item['ordered'] == $post[$item['id']]['toRefund'])) {
                return false;
            }
        }

        return true;
    }

    /**
     * Retrieve a price formatted by the given lang
     *
     * @param float $price
     * @param string $lang
     * @param order $order
     * @return string
     */
    public static function getFormattedPrice($price, $lang, order $order)
    {
        $output = '';
        if ($lang == 'german') {
            $output = number_format($price, 2, ',', '.') . '&nbsp;' . $order->info['currency'];
        } else {
            $output = number_format($price, 2, '.', ',') . '&nbsp;' . $order->info['currency'];
        }

        return $output;
    }

    /**
     * empty() for return values
     *
     * @param mixed $var
     * @return boolean
     */
    public static function betterEmpty($var)
    {
        return empty($var);
    }
}
