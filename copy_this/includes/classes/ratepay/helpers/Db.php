<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('Data.php');
require_once('Session.php');

/**
 * Description of Db
 */
class rpDb
{

    /**
     * Retrieve the shop config entry for the given key
     *
     * @param string $key
     * @return strng
     */
    public static function getShopConfigEntry($key)
    {
        $query = xtc_db_query("SELECT configuration_value FROM configuration WHERE configuration_key = '" . $key . "'");
        $data = xtc_db_fetch_array($query);
        return $data['configuration_value'];
    }

    /**
     * Retrieve a complete order tupel for the given id
     *
     * @param int $orderId
     * @return array
     */
    public static function getShopOrderData($orderId)
    {
        $query = xtc_db_query("SELECT * FROM " . TABLE_ORDERS . " WHERE `orders_id` = " . (int) $orderId);
        $order = xtc_db_fetch_array($query);
        return $order;
    }

    /**
     * Retrieve a entry from a order tupel
     *
     * @param int $orderId
     * @param string $key
     * @return string
     */
    public static function getShopOrderDataEntry($orderId, $key)
    {
        $order = self::getShopOrderData($orderId);
        return $order[$key];
    }

    /**
     * Retrieve a complete ratepay order tupel for the given order id
     *
     * @param type $orderId
     * @return array
     */
    public static function getRatepayOrderData($orderId)
    {
        $oObject = new order($orderId);
        $query = xtc_db_query("SELECT * FROM `" . $oObject->info['payment_method'] . "_orders` WHERE `order_number` = " . (int) $orderId);
        $order = xtc_db_fetch_array($query);
        return $order;
    }

    /**
     * Retrieve a ratepay order entry for the given key
     *
     * @param int $orderId
     * @param type $key
     * @return string
     */
    public static function getRatepayOrderDataEntry($orderId, $key)
    {
        $order = self::getRatepayOrderData($orderId);
        return $order[$key];
    }

    /**
     * Retrieve the rate details for the given order id
     *
     * @param type $orderId
     * @return array
     */
    public static function getRatepayRateDetails($orderId)
    {
        $query = xtc_db_query("SELECT * FROM `ratepay_rate_details` WHERE `order_number` = " . (int) $orderId);
        return xtc_db_fetch_array($query);
    }

    /**
     * Insert rate details for the given order
     *
     * @param array $data
     */
    public static function setRatepayRateDetails(array $data)
    {
        $sql = "INSERT INTO ratepay_rate_details ("
                . "order_number, "
                . "total_amount, "
                . "amount, "
                . "interest_amount,"
                . "service_charge,"
                . "annual_percentage_rate,"
                . "monthly_debit_interest,"
                . "number_of_rates,"
                . "rate,"
                . "last_rate"
                . ") VALUES ('"
                . xtc_db_input($data['order_number']) . "', '"
                . xtc_db_input($data['total_amount']) . "', '"
                . xtc_db_input($data['amount']) . "','"
                . xtc_db_input($data['interest_amount']) . "','"
                . xtc_db_input($data['service_charge']) . "','"
                . xtc_db_input($data['annual_percentage_rate']) . "','"
                . xtc_db_input($data['monthly_debit_interest']) . "','"
                . xtc_db_input($data['number_of_rates']) . "','"
                . xtc_db_input($data['rate']) . "','"
                . xtc_db_input($data['last_rate'])
                . "')";
        xtc_db_query($sql);
    }

    /**
     * Insert ratepay order data
     *
     * @param order $order
     * @param int $orderId
     */
    public static function setRatepayOrderData(order $order, $orderId)
    {
        $payment = $order->info['payment_method'];
        $sql = "INSERT INTO " . $payment . "_orders ("
                . "order_number, "
                . "transaction_id, "
                . "transaction_short_id, "
                . "descriptor,"
                . "customers_birth,"
                . "fax,"
                . "customers_country_code,"
                . "gender"
                . ") VALUES ('"
                . xtc_db_input($orderId) . "', '"
                . xtc_db_input(rpSession::getRpSessionEntry('transactionId')) . "', '"
                . xtc_db_input(rpSession::getRpSessionEntry('transactionShortId')) . "','"
                . xtc_db_input(rpSession::getRpSessionEntry('descriptor')) . "','"
                . xtc_db_input(rpDb::getCustomersDob(null, rpSession::getSessionEntry('customer_id'))) . "','"
                . xtc_db_input(rpDb::getCustomersFax(null, rpSession::getSessionEntry('customer_id'))) . "','"
                . xtc_db_input(rpSession::getRpSessionEntry('customers_country_code')) . "','"
                . xtc_db_input(rpDb::getXtCustomerEntry(rpSession::getSessionEntry('customer_id'), 'customers_gender'))
                . "')";
        xtc_db_query($sql);

        self::setRpOrderItems($order, $orderId, $payment);
    }

    /**
     * Refund ratepay order item
     *
     * @param int $id
     * @param array $post
     * @param order $order
     */
    private static function _refundRpItem($id, $qty, order $order)
    {
        $payment = $order->info['payment_method'];
        xtc_db_query("UPDATE " . $payment . "_items"
                . " SET"
                . " returned = returned + " . (int) $qty . ","
                . " total_price = unit_price * (ordered - cancelled - returned),"
                . " total_price_with_tax = unit_price_with_tax * (ordered - cancelled - returned),"
                . " total_tax = unit_tax * (ordered - cancelled - returned)"
                . " WHERE `id` = " . (int) $id);
    }

    /**
     * Insert a ratepay order item
     *
     * @param array $data
     * @param int $orderId
     * @param string $payment
     */
    public static function setRpOrderItem($data, $orderId, $payment)
    {
        $unitTax = $data['tax'] / $data['qty'];
        $sql = "INSERT INTO " . $payment . "_items ("
                . "order_number, "
                . "article_number, "
                . "article_name, "
                . "ordered, "
                . "shipped, "
                . "cancelled,"
                . "returned,"
                . "unit_price,"
                . "unit_price_with_tax,"
                . "total_price,"
                . "total_price_with_tax,"
                . "unit_tax,"
                . "total_tax"
                . ") VALUES ('"
                . xtc_db_input($orderId) . "', '"
                . xtc_db_input($data['id']) . "', '"
                . xtc_db_input($data['name']) . "','"
                . xtc_db_input($data['qty']) . "', "
                . xtc_db_input(0) . ", "
                . xtc_db_input(0) . ", "
                . xtc_db_input(0) . ", "
                . xtc_db_input($data['unitPrice']) . ", "
                . xtc_db_input($data['unitPrice'] + $unitTax) . ", "
                . xtc_db_input($data['totalPrice']) . ", "
                . xtc_db_input($data['totalPrice'] + $data['tax']) . ", "
                . xtc_db_input($unitTax) . ", "
                . xtc_db_input($data['tax'])
                . ")";

        xtc_db_query($sql);
    }

    /**
     * Cancel ratepay order item
     *
     * @param int $id
     * @param array $post
     * @param order $order
     */
    private static function _cancelRpItem($id, $qty, order $order)
    {
        $payment = $order->info['payment_method'];
        xtc_db_query("UPDATE " . $payment . "_items"
                . " SET"
                . " cancelled = cancelled + " . (int) $qty . ","
                . " total_price = unit_price * (ordered - cancelled - returned),"
                . " total_price_with_tax = unit_price_with_tax * (ordered - cancelled - returned ),"
                . " total_tax = unit_tax * (ordered - cancelled - returned)"
                . " WHERE `id` = " . (int) $id);
    }

    /**
     * Retrieve the customer vat id
     *
     * @param int $orderId
     * @param int $customerId
     * @return string
     */
    public static function getCustomersVatId($orderId = null, $customerId = null)
    {
        if (!is_null($orderId)) {
            $vatId = self::getShopOrderDataEntry($orderId, 'customers_vat_id');
        } else {
            $vatId = self::getXtCustomerEntry($customerId, 'customers_vat_id');
        }

        return $vatId;
    }

    /**
     * Retrieve the customer date of birth
     *
     * @param int $orderId
     * @param int $customerId
     * @return string
     */
    public static function getCustomersDob($orderId = null, $customerId = null)
    {
        if (!is_null($orderId)) {
            $dob = self::getRatepayOrderDataEntry($orderId, 'customers_birth');
        } else {
            $dob = self::getXtCustomerEntry($customerId, 'customers_dob');
        }

        return $dob;
    }

    /**
     * Retrieve the customer fax number
     *
     * @param int $orderId
     * @param int $customerId
     * @return string
     */
    public static function getCustomersFax($orderId = null, $customerId = null)
    {
        if (!is_null($orderId)) {
            $fax = self::getRatepayOrderDataEntry($orderId, 'fax');
        } else {
            $fax = self::getXtCustomerEntry($customerId, 'customers_fax');
        }

        return $fax;
    }

    /**
     * Retrieve a customer array
     *
     * @param int $customerId
     * @return array
     */
    public static function getXtCustomer($customerId)
    {
        $query = xtc_db_query("SELECT customers_gender, DATE_FORMAT(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone, customers_fax, customers_vat_id from " . TABLE_CUSTOMERS . " WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
        return xtc_db_fetch_array($query);
    }

    /**
     * Retrieve a customer entry
     *
     * @param int $customerId
     * @param string $key
     * @return string
     */
    public static function getXtCustomerEntry($customerId, $key)
    {
        $customer = self::getXtCustomer($customerId);
        return $customer[$key];
    }

    /**
     * Update the shop customer with a customer entry
     *
     * @param int $customerId
     * @param string $key
     * @param string $value
     */
    public static function setXtCustomerEntry($customerId, $key, $value)
    {
        xtc_db_query("UPDATE " . TABLE_CUSTOMERS . " SET $key = '" . xtc_db_prepare_input($value) . "' WHERE customers_id ='" . xtc_db_input($customerId) . "' ");
    }

    /**
     * Retrieve a ratepay order item
     *
     * @param int $id
     * @param int $orderId
     * @return array
     */
    public static function getRpItem($id, $orderId)
    {
        $order = new order($orderId);
        $query = xtc_db_query("SELECT * FROM " . $order->info['payment_method'] . "_items WHERE `id` = " . (int) $id);
        return xtc_db_fetch_array($query);
    }

    /**
     * Set the history entrys for a operation
     *
     * @param array $post
     * @param string $method
     * @param string $subMethod
     */
    public static function setRpHistoryEntrys(array $post, $method, $subMethod)
    {
        foreach ($post['items'] as $key => $value) {
            $item = self::getRpItem($key, $post['order_number']);
            $itemData = array(
                'id' => $item['article_number'],
                'name' => $item['article_name'],
                'qty' => $value
            );

            self::setRpHistoryEntry($post['order_number'], $itemData, $method, $subMethod);
        }
    }

    /**
     * Set a ratepay history entry
     *
     * @param int $orderId
     * @param array $item
     * @param string $method
     * @param string $subMethod
     */
    public static function setRpHistoryEntry($orderId, array $item, $method, $subMethod)
    {
        if ($item['qty'] > 0) {
            $order = new order($orderId);
            $sql = "INSERT INTO " . $order->info['payment_method'] . "_history ("
                    . "order_number, "
                    . "article_number, "
                    . "article_name, "
                    . "quantity, "
                    . "method, "
                    . "submethod"
                    . ") VALUES ('"
                    . xtc_db_input($orderId) . "', '"
                    . xtc_db_input($item['id']) . "', '"
                    . xtc_db_input($item['name']) . "','"
                    . xtc_db_input($item['qty']) . "', '"
                    . xtc_db_input($method) . "', '"
                    . xtc_db_input($subMethod)
                    . "')";
            xtc_db_query($sql);
        }
    }

    /**
     * Update a shop item with the given item data
     *
     * @param array $item
     * @param int $orderId
     */
    private static function _updateShopItem(array $item, $orderId)
    {
        $qty = $item['ordered'] - $item['cancelled'] - $item['returned'];
        $sql = "UPDATE orders_products SET "
                . "products_quantity = " . (int) $qty . ", "
                . "final_price = " . (float) $item['total_price_with_tax']
                . " WHERE "
                . "orders_id = '" . xtc_db_input($orderId) . "' "
                . "AND "
                . "products_id = '" . xtc_db_input($item['article_number']) . "'";
        xtc_db_query($sql);
    }

    /**
     * Remove a credit form the shop order
     *
     * @param string $articleNumber
     * @param int $orderId
     */
    private static function _removeShopCredit($articleNumber, $orderId)
    {
        $sql = "DELETE FROM orders_total WHERE class = '" . xtc_db_input($articleNumber) . "' AND orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);
    }

    /**
     * Remove shipping from the shop order
     *
     * @param int $orderId
     */
    private static function _removeShopShipping($orderId)
    {
        $sql = "DELETE FROM orders_total WHERE class = 'ot_shipping' AND orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);
    }

    /**
     * Remove discount from the shop order
     *
     * @param int $orderId
     */
    private static function _removeShopDiscount($orderId)
    {
        $sql = "DELETE FROM orders_total WHERE class = 'ot_coupon' AND orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);
    }

    /**
     * Update the shop order totals for the give order id
     *
     * @param int $orderId
     */
    public static function updateShopOrderTotals($orderId)
    {
        $order = new order($orderId);
        $total = rpData::getBasketAmount($order, $orderId);
        $sql = "UPDATE orders_total SET value = " . (float) $total . " WHERE class = 'ot_total' AND orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);

        $tax = rpData::getTotalTaxAmount($orderId);
        $sql = "UPDATE orders_total SET value = " . (float) $tax . " WHERE class = 'ot_tax' AND orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);

        $subtotal = rpData::getSubtotal($orderId);
        $sql = "UPDATE orders_total SET value = " . (float) $subtotal . " WHERE class = 'ot_subtotal' and orders_id = '" . xtc_db_input($orderId) . "'";
        xtc_db_query($sql);

        $classes = array('ot_total', 'ot_tax', 'ot_subtotal');
        foreach ($classes as $class) {
            $sql = "SELECT value from orders_total WHERE orders_id = '" . xtc_db_input($orderId) . "' and class = '$class'";
            $query = xtc_db_query($sql);
            $entry = xtc_db_fetch_array($query);
            $text = rpData::getFormattedPrice($entry['value'], $order->info['language'], $order);
            if($class == 'ot_total') {
                $text = "<b>" . rpData::getFormattedPrice($entry['value'], $order->info['language'], $order) . "</b>";
            }
            $sql = "UPDATE orders_total SET text = '$text' WHERE orders_id = '" . xtc_db_input($orderId) . "' and class = '$class'";
            xtc_db_query($sql);
        }
    }

    /**
     * Insert a credit item to the shop order
     *
     * @param int $orderId
     * @param array $post
     */
    public static function addCreditToShop($orderId, array $post)
    {
        $order = new order($orderId);
        $credit = rpData::getCreditItem($post);
        $sql = "INSERT INTO  `orders_total` ("
                . "`orders_total_id` , "
                . "`orders_id` , "
                . "`title` , "
                . "`text` , "
                . "`value` , "
                . "`class` , "
                . "`sort_order` "
                . ") VALUES ("
                . "NULL, "
                . "'" . xtc_db_input($orderId) . "', "
                . "'" . xtc_db_input($credit['name']) . ":', "
                . "'" . xtc_db_input(rpData::getFormattedPrice($credit['totalPrice'], $order->info['language'], $order)) . "',  "
                . "'" . (float) $credit['totalPrice'] . "',  "
                . "'" . xtc_db_input($credit['id']) . "',  "
                . "'80'"
                . ")";
        xtc_db_query($sql);
    }

    /**
     * Cancel or refund items for the given order id
     *
     * @param array $post
     * @param int $orderId
     */
    public static function cancelOrRefundShopItems(array $post, $orderId)
    {
        foreach ($post['items'] as $id => $value) {
            if ($value > 0) {
                $item = self::getRpItem($id, $orderId);
                if ($item['article_number'] == 'SHIPPING') {
                    self::_removeShopShipping($orderId);
                } elseif ($item['article_number'] == 'DISCOUNT') {
                    self::_removeShopDiscount($orderId);
                } elseif ($item['article_name'] == 'Credit') {
                    self::_removeShopCredit($item['article_number'], $orderId);
                } else {
                    self::_updateShopItem($item, $orderId);
                }
            }
        }
    }

    /**
     * Retrieve the complete history for the given order id
     *
     * @param imt $orderId
     * @return array
     */
    public static function getRpHistory($orderId)
    {
        $order = new order($orderId);
        $entrys = array();
        $sql = 'SELECT * FROM ' . $order->info['payment_method'] . '_history WHERE order_number = ' . (int) $orderId;
        $query = xtc_db_query($sql);
        while ($entry = xtc_db_fetch_array($query)) {
            $entrys[] = $entry;
        }

        return $entrys;
    }

    /**
     * Retrieve all log entrys
     *
     * @param string $orderBy
     * @return array
     */
    public static function getLogEntrys($orderBy = 'date')
    {
        $sql = 'SELECT * FROM ratepay_log ORDER BY ' . xtc_db_input($orderBy) . ' ' . rpData::getLoggingLogical();
        return xtc_db_query($sql);
    }

    /**
     * Retrieve the request and the response from a single log entry
     *
     * @param int $id
     * @return array
     */
    public static function getLogEntry($id)
    {
        return xtc_db_fetch_array(xtc_db_query('SELECT request, response FROM ratepay_log WHERE id = ' . (int) $id));
    }

    /**
     * Retrieve all items for the given order id
     *
     * @param int $orderId
     * @return array
     */
    public static function getRpItems($orderId)
    {
        $order = new order($orderId);
        $items = array();
        $query = xtc_db_query("SELECT * FROM " . $order->info['payment_method'] . "_items WHERE order_number = " . (int) $orderId);
        while ($item = xtc_db_fetch_array($query)) {
            $items[] = array(
                'id' => $item['id'],
                'articleNumber' => $item['article_number'],
                'articleName' => $item['article_name'],
                'ordered' => $item['ordered'],
                'shipped' => $item['shipped'],
                'cancelled' => $item['cancelled'],
                'returned' => $item['returned'],
                'unitPrice' => $item['unit_price'],
                'unitPriceWithTax' => $item['unit_price_with_tax'],
                'totalPrice' => $item['total_price'],
                'totalPriceWithTax' => $item['total_price_with_tax'],
                'unitTax' => $item['unit_tax'],
                'totalTax' => $item['total_tax']
            );
        }

        return $items;
    }

    /**
     * Retrieve the different item types from the ratepay item table
     * if post params given the quantities and the prices get adjustet
     *
     * @param int $orderId
     * @param array $post
     * @return array
     */
    public static function getItemsByTable($orderId, array $post = array())
    {
        $items = self::getRpItems($orderId);
        $itemData = array();
        foreach ($items as $item) {
            $id = $item['id'];
            if (array_key_exists($id, $post) && array_key_exists('toShip', $post[$id])) {
                $itemData[] = rpData::getDeliverItemData($item, $post[$id]['toShip']);
            } elseif (array_key_exists($id, $post) && array_key_exists('toCancel', $post[$id])) {
                $itemData[] = rpData::getCancelItemData($item, $post[$id]['toCancel']);
            } elseif (array_key_exists($id, $post) && array_key_exists('toRefund', $post[$id])) {
                $itemData[] = rpData::getRefundItemData($item, $post[$id]['toRefund']);
            } else {
                $itemData[] = rpData::getCancelItemData($item);
            }
        }

        if (array_key_exists('voucherAmount', $post)) {
            $itemData[] = rpData::getCreditItem($post);
        }

        return $itemData;
    }

    /**
     * Retrieve the count of all credits
     *
     * @param int $orderId
     * @return int
     */
    public static function getLastCreditId($orderId)
    {
        $order = new order($orderId);
        $query = xtc_db_query('SELECT count(id) as "id" FROM `' . $order->info['payment_method'] . '_items` WHERE article_name = "Händler Gutschrift"');
        $data = xtc_db_fetch_array($query);
        return $data['id'];
    }

    /**
     * Ship ratepay order items
     *
     * @param array $post
     * @param order $order
     */
    public static function shipRpOrder($post, order $order)
    {
        foreach ($post as $key => $value) {
            if ($value['toShip'] > 0) {
                self::_shipRpItem($key, $value['toShip'], $order);
            }
        }
    }

    /**
     * Ship ratepay order item
     *
     * @param int $id
     * @param array $post
     * @param order $order
     */
    private static function _shipRpItem($id, $qty, order $order)
    {
        $payment = $order->info['payment_method'];
        xtc_db_query("UPDATE " . $payment . "_items"
                . " SET"
                . " shipped = shipped + " . (int) $qty
                . " WHERE `id` = " . (int) $id);
    }

    /**
     * Refund ratepay order items
     *
     * @param array $post
     * @param order $order
     */
    public static function refundRpOrder($post, order $order)
    {
        foreach ($post as $key => $value) {
            if ($value['toRefund'] > 0) {
                self::_refundRpItem($key, $value['toRefund'], $order);
            }
        }
    }

    /**
     * Cancel ratepay order items
     *
     * @param array $post
     * @param order $order
     */
    public static function cancelRpOrder($post, order $order)
    {
        foreach ($post as $key => $value) {
            if ($value['toCancel'] > 0) {
                self::_cancelRpItem($key, $value['toCancel'], $order);
            }
        }
    }

    /**
     * Set a ratepay credit item
     *
     * @param array $post
     */
    public static function setRpCreditItem($post)
    {
        $order = new order($post['order_number']);
        self::setRpOrderItem(rpData::getCreditItem($post), $post['order_number'], $order->info['payment_method']);
    }

    /**
     * Set all order items to the ratepay order
     *
     * @param order $order
     * @param int $orderId
     * @param string $payment
     */
    public static function setRpOrderItems(order $order, $orderId, $payment)
    {
        foreach ($order->products as $product) {
            self::setRpOrderItem(rpData::getItemData($product), $orderId, $payment);
        }

        foreach (rpData::getDiscounts() as $discountData) {
            $discount = rpData::getDiscountData($discountData);
            if (!empty($discount)) {
                self::setRpOrderItem($discount, $orderId, $payment);
            }
        }

        $shipping = rpData::getShippingData($order);
        if (!empty($shipping)) {
            self::setRpOrderItem($shipping, $orderId, $payment);
        }
    }

    /**
     * This method save's all necessary request and response informations in the database
     *
     * @param order $order
     * @param rpSimpleXmlExtended $request
     * @param string $orderId
     * @param SimpleXMLElement $response
     */
    public static function xmlLog($order, $request, $orderId = 'N/A', $response = null)
    {
        require_once(dirname(__FILE__) . '/../../../../lang/' . rpSession::getLang() . '/modules/payment/' . $order->info['payment_method'] . '.php');
        $payment = rpLoader::getRatepayPayment($order->info['payment_method']);
        if ($payment->logging) {
            $transactionId = 'N/A';
            $subType = 'N/A';
            if ($request->head->{'transaction-id'}) {
                $transactionId = (string) $request->head->{'transaction-id'};
            }

            if ($request->head->operation->attributes()->subtype) {
                $subType = (string) $request->head->operation->attributes()->subtype;
            }

            $operation = (string) $request->head->operation;

            $responseXml = 'N/A';
            if (!empty($response)) {
                $responseXml = $response->asXML();
                $result = (string) $response->head->processing->result;
                $resultCode = (string) $response->head->processing->result->attributes()->code;
                $reasonText = (string) $response->head->processing->reason;
                if ($response->head->{'transaction-id'}) {
                    $transactionId = (string) $response->head->{'transaction-id'};
                }
            } else {
                $result = "Service unavaible.";
                $resultCode = "Service unavaible.";
            }

            $sql = "INSERT INTO ratepay_log "
                    . "("
                    . "order_number, "
                    . "transaction_id, "
                    . "payment_method, "
                    . "payment_type,  "
                    . "payment_subtype, "
                    . "result, "
                    . "request, "
                    . "response, "
                    . "result_code, "
                    . "reason"
                    . ") "
                    . "VALUES ('"
                    . xtc_db_input($orderId) . "', '"
                    . xtc_db_input($transactionId) . "', '"
                    . xtc_db_input($payment->title) . "', '"
                    . xtc_db_input($operation) . "', '"
                    . xtc_db_input($subType) . "', '"
                    . xtc_db_input($result) . "','"
                    . xtc_db_input(utf8_decode($request->asXML())) . "','"
                    . xtc_db_input($responseXml) . "','"
                    . xtc_db_input($resultCode) . "','"
                    . xtc_db_input($reasonText)
                    . "')";

            xtc_db_query($sql);

            if ($operation == "PAYMENT_CONFIRM" && $transactionId != 'N/A') {
                $sql = "UPDATE ratepay_log SET order_number = '" . xtc_db_input($orderId) . "' WHERE transaction_id = '" . xtc_db_input($transactionId) . "';";
                xtc_db_query($sql);
            }
        }
    }
}
