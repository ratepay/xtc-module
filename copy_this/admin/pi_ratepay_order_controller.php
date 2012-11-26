<?php

require 'includes/application_top.php';
require_once '../includes/modules/payment/ratepay_webservice/Ratepay_XML.php';
$language = $_SESSION['language'];
require_once '../lang/' . $language . '/admin/modules/payment/pi_ratepay.php';

require_once(DIR_FS_DOCUMENT_ROOT . 'includes/modules/payment/pi_ratepay_rechnung.php');


if (!empty($_POST['oID'])) {
    $shopOrderID = $_POST['oID'];
    $query = xtc_db_query("select * from orders a, orders_total b where a.orders_id = '" . xtc_db_input($shopOrderID) . "' and a.orders_id = b.orders_id and class = 'ot_total'");
    $order = xtc_db_fetch_array($query);
    $oID = $_POST['oID'];
    $paymentType = $_POST['payment'];
    require_once '../includes/modules/payment/' . $paymentType . '.php';
    if (!empty($_POST['stornieren'])) {
        $resultArr = cancelRequest($oID, $paymentType);
    } else if (!empty($_POST['versenden'])) {
        $resultArr = deliverRequest($oID, $paymentType);
    } else if (!empty($_POST['retournieren'])) {
        $resultArr = returnRequest($oID, $paymentType);
    } else if (!empty($_POST['gutschein'])) {
        $resultArr = voucherRequest($oID, $paymentType);
    }
    $url = "pi_ratepay_admin.php?oID=" . $oID . "&payment=" . $paymentType . "&result=" . urlencode($resultArr['result']) . "&message=" . urlencode($resultArr['message']);
    if (CURRENT_TEMPLATE == 'xtc5') {
        $url = "pi_ratepay_admin_xtc_modified.php?oID=" . $oID . "&payment=" . $paymentType . "&result=" . urlencode($resultArr['result']) . "&message=" . urlencode($resultArr['message']);
    }
    header("Location: $url");
}

/**
 * Add the shipping tax to the order object
 * 
 * @param float $shippingCost
 * @return float
 */
function getShippingTaxAmount($shippingCost) {
    global $order;
    
    $taxPercent = getShippingTaxRate();
    $shippingTaxAmount = $shippingCost * ($taxPercent / 100);
    return $shippingTaxAmount;
}

/**
 * Retrieve the shipping tax rate
 * 
 * @return float 
 */
function getShippingTaxRate() {
    global $order;
    $shipping_class_array = explode("_", $order['shipping_class']);
    $shipping_class = strtoupper($shipping_class_array[0]);
    if (empty($shipping_class)) {
        $shipping_tax_rate = 0;
    } else {
        $const = 'MODULE_SHIPPING_' . $shipping_class . '_TAX_CLASS';
        if (defined($const)) {
            $shipping_tax_rate = xtc_get_tax_rate(constant($const));
        } else {
            $shipping_tax_rate = 0;
        }
    }

    return $shipping_tax_rate;
}

/**
 * This functions send the CONFIRMATION_DELIVER request to the RatePAY API
 * and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function deliverRequest($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    $operation = 'CONFIRMATION_DELIVER';
    $subOperation = 'n/a';
    $pi_ratepay = null;
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_ratepay = new pi_ratepay_rechnung();
        $pi_table_prefix = 'pi_ratepay_rechnung';
        $pi_payment_type = 'INVOICE';
    } else {
        $pi_ratepay = new pi_ratepay_rate();
        $pi_table_prefix = 'pi_ratepay_rate';
        $pi_payment_type = 'INSTALLMENT';
    }

    $profileId = $pi_ratepay->profileId;
    $securityCode = $pi_ratepay->securityCode;
    $systemId = $_SERVER['SERVER_ADDR'];
    $query = xtc_db_query("select customers_id from " . TABLE_ORDERS . " where orders_id = '" . xtc_db_input($oID) . "'");
    $customerIdArray = xtc_db_fetch_array($query);
    $customerId = $customerIdArray['customers_id'];

    $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
    $customerXTC = xtc_db_fetch_array($query);
    $email = $customerXTC['customers_email_address'];

    $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
    $transactionArray = xtc_db_fetch_array($query);

    $ratepay = new Ratepay_XML;
    $ratepay->live = $pi_ratepay->testOrLive();

    $request = $ratepay->getXMLObject();

    $head = $request->addChild('head');
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transactionArray['transaction_id']);
    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
    $head->addChild('operation', $operation);

    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);

    $external = $head->addChild('external');
    $external->addChild('order-id', $oID);

    $content = $request->addChild('content');

    $content->addChild('shopping-basket');

    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $price = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        if ($_POST[$mItem['article_number']] > 0) {
            if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                $price = $price + ( ( $mItem['products_price'] * $_POST[$mItem['article_number']] ));
            } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                $price = $price + ( ( $mItem['article_netUnitPrice'] * $_POST[$mItem['article_number']] ));
            } elseif ($mItem['article_number'] == 'SHIPPING') {
                $price = $price + ( ( ( $mItem['article_netUnitPrice'] + getShippingTaxAmount($mItem['article_netUnitPrice']) ) * $_POST[$mItem['article_number']] ));
            } elseif ($mItem['article_number'] == 'DISCOUNT') {
                $price = $price + ( ( $mItem['article_netUnitPrice'] * $_POST[$mItem['article_number']] ));
            } elseif ($mItem['article_number'] == 'COUPON') {
                $price = $price + ( ( ($mItem['article_netUnitPrice']) * $_POST[$mItem['article_number']] ));
            }
        }
    }

    $shoppingBasket = $content->{'shopping-basket'};
    $shoppingBasket->addAttribute('amount', number_format($price, 2, '.', ''));
    $shoppingBasket->addAttribute('currency', 'EUR');
    $items = $shoppingBasket->addChild('items');
    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $i = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        $qty = ($mItem['ordered'] - $mItem['shipped'] - $mItem['cancelled']);
        if ($_POST[$mItem['article_number']] > 0) {
            if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                $items->addCDataChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['products_id']);
                $items->item[$i]->addAttribute('quantity', $_POST[$mItem['article_number']]);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['products_price'] / (100 + $mItem['products_tax']) * 100, 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($_POST[$mItem['article_number']] * ($mItem['products_price'] / (100 + $mItem['products_tax']) * 100)), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($_POST[$mItem['article_number']] * ($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax']), 2, '.', ''));
            } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                $items->addChild('item', PI_RATEPAY_VOUCHER);
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $_POST[$mItem['article_number']]);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } else if ($mItem['article_number'] == 'SHIPPING') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $_POST[$mItem['article_number']]);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($_POST[$mItem['article_number']] * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(getShippingTaxAmount(($_POST[$mItem['article_number']] * $mItem['article_netUnitPrice'])), 2, '.', ''));
            } else if ($mItem['article_number'] == 'DISCOUNT') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $_POST[$mItem['article_number']]);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($_POST[$mItem['article_number']] * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } else if ($mItem['article_number'] == 'COUPON') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $_POST[$mItem['article_number']]);
                $items->item[$i]->addAttribute('unit-price', number_format($cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($_POST[$mItem['article_number']] * $cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1))), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($cab->getCouponTaxAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
            }
            $i++;
        }
    }
    $response = $ratepay->paymentOperation($request);

    $query = xtc_db_query("select * from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $order = xtc_db_fetch_array($query);
    $first_name = $order['customers_firstname'];
    $last_name = $order['customers_lastname'];
    if ($response) {
        $resultCode = (string) $response->head->processing->result->attributes()->code;
        $result = (string) $response->head->processing->result;
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "404") {
            $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
            $query = xtc_db_query($sql);
            $i = 0;
            while ($mItem = xtc_db_fetch_array($query)) {
                $qty = ($mItem['ordered'] - $mItem['shipped'] - $mItem['cancelled']);
                if ($_POST[$mItem['article_number']] > 0) {
                    $sql = "update " . $pi_table_prefix . "_orderdetails set shipped = shipped + " . xtc_db_input($_POST[$mItem['article_number']]) . " where order_number = '" . xtc_db_input($oID) . "' and article_number = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "insert into " . $pi_table_prefix . "_history (order_number, article_number, quantity, method, submethod) values ('" . xtc_db_input($oID) . "', '" . xtc_db_input($mItem['article_number']) . "', '" . xtc_db_input($_POST[$mItem['article_number']]) . "', 'shipped', 'shipped')";
                    xtc_db_query($sql);
                }
            }
            $message = PI_RATEPAY_SUCCESSDELIVERY;
            return array('result' => 'SUCCESS', 'message' => $message);
        } else {
            $message = PI_RATEPAY_ERRORDELIVERY;
            return array('result' => 'ERROR', 'message' => $message);
        }
    } else {
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, false, $first_name, $last_name);
        $message = PI_RATEPAY_SERVICE;
        return array('result' => 'ERROR', 'message' => $message);
    }
}

/**
 * This functions calls the fullCancel($oID) or the partCancel($oID) function
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function cancelRequest($oID, $paymentType) {
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_table_prefix = 'pi_ratepay_rechnung';
    } else {
        $pi_table_prefix = 'pi_ratepay_rate';
    }
    $sql = "select * from " . $pi_table_prefix . "_orderdetails where order_number = '" . xtc_db_input($oID) . "'";
    $query = xtc_db_query($sql);
    $flag = array();
    $i = 0;
    while ($item = xtc_db_fetch_array($query)) {
        $qty = $item['ordered'] - $item['cancelled'] - $_POST[$item['article_number']];
        if ($qty == 0) {
            $flag[$i] = true;
        } else if ($qty > 0) {
            $flag[$i] = false;
        }
        $i++;
    }
    $full = true;
    for ($i = 0; $i < count($flag); $i++) {
        if ($flag[$i] == false) {
            $full = false;
        }
    }
    if ($full == true) {
        return fullCancel($oID, $paymentType);
    } else if ($full == false) {
        return partCancel($oID, $paymentType);
    }
}

/**
 * This functions send a PAYMENT_CHANGE request with the sub operation full-cancelation
 * to the RatePAY API and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function fullCancel($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    $operation = 'PAYMENT_CHANGE';
    $subOperation = 'full-cancellation';
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_ratepay = new pi_ratepay_rechnung();
        $pi_table_prefix = 'pi_ratepay_rechnung';
        $pi_payment_type = 'INVOICE';
    } else {
        $pi_ratepay = new pi_ratepay_rate();
        $pi_table_prefix = 'pi_ratepay_rate';
        $pi_payment_type = 'INSTALLMENT';
    }

    $profileId = $pi_ratepay->profileId;
    $securityCode = $pi_ratepay->securityCode;
    $systemId = $_SERVER['SERVER_ADDR'];

    $query = xtc_db_query("select customers_id,customers_country,billing_country,delivery_country from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $customerIdArray = xtc_db_fetch_array($query);
    $customerId = $customerIdArray['customers_id'];

    $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
    $customerXTC = xtc_db_fetch_array($query);
    $email = $customerXTC['customers_email_address'];
    $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
    $transactionArray = xtc_db_fetch_array($query);
    $query = xtc_db_query("select * from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $order = xtc_db_fetch_array($query);
    $ratepay = new Ratepay_XML;
    $ratepay->live = $pi_ratepay->testOrLive();
    $request = $ratepay->getXMLObject();

    $request->addChild('head');
    $head = $request->{'head'};
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transactionArray['transaction_id']);
    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
    $operation = $head->addChild('operation', $operation);
    $operation->addAttribute('subtype', $subOperation);

    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);

    $external = $head->addChild('external');
    $external->addChild('order-id', $oID);

    $content = $request->addChild('content');
    $content->addChild('customer');

    if (strtoupper($customerXTC['customers_gender']) == "F") {
        $gender = "F";
    } else if (strtoupper($customerXTC['customers_gender']) == "M") {
        $gender = "M";
    } else {
        $gender = "U";
    }

    $customer = $content->customer;
    $customer->addCDataChild('first-name', removeSpecialChars($order['customers_firstname']));
    $customer->addCDataChild('last-name', removeSpecialChars($order['customers_lastname']));
    $customer->addChild('gender', $gender);
    $customer->addChild('date-of-birth', (string) utf8_encode($customerXTC['customers_dob']));
    $customer->addChild('contacts');

    $contacts = $customer->contacts;
    $contacts->addChild('email', $email);
    $contacts->addChild('phone');

    $phone = $contacts->phone;
    $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

    $customer->addChild('addresses');
    $addresses = $customer->addresses;
    $addresses->addChild('address');
    $addresses->addChild('address');

    $billingAddress = $addresses->address[0];
    $shippingAddress = $addresses->address[1];

    $billingAddress->addAttribute('type', 'BILLING');
    $shippingAddress->addAttribute('type', 'DELIVERY');

    $billingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $billingAddress->addChild('zip-code', $order['delivery_postcode']);
    $billingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $billingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $shippingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $shippingAddress->addChild('zip-code', $order['delivery_postcode']);
    $shippingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $shippingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $customer->addChild('nationality', $order['delivery_country_iso_code_2']);
    $customer->addChild('customer-allow-credit-inquiry', 'yes');
    $content->addChild('shopping-basket');
    $shoppingBasket = $content->{'shopping-basket'};
    $shoppingBasket->addAttribute('amount', '0.00');
    $shoppingBasket->addAttribute('currency', 'EUR');
    $shoppingBasket->addChild('items');
    $content->addChild('payment');
    $payment = $content->payment;
    $payment->addAttribute('method', $pi_payment_type);
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', '0.00');
    if ($pi_payment_type == "INSTALLMENT") {
        $payment->addChild('installment-details');
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
    }
    $response = $ratepay->paymentOperation($request);

    $first_name = $order['customers_firstname'];
    $last_name = $order['customers_lastname'];

    if ($response) {
        $resultCode = (string) $response->head->processing->result->attributes()->code;
        $result = (string) $response->head->processing->result;
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "403") {
            $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
            $query = xtc_db_query($sql);
            while ($mItem = xtc_db_fetch_array($query)) {
                if ($_POST[$mItem['article_number']] > 0) {
                    $sql = "update " . $pi_table_prefix . "_orderdetails set cancelled = cancelled + " . xtc_db_input($_POST[$mItem['article_number']]) . " where order_number = '" . xtc_db_input($oID) . "' and article_number = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "insert into " . $pi_table_prefix . "_history (order_number, article_number, quantity, method, submethod) values ('" . xtc_db_input($oID) . "', '" . xtc_db_input($mItem['article_number']) . "', '" . xtc_db_input($_POST[$mItem['article_number']]) . "', 'cancelled', 'cancelled')";
                    xtc_db_query($sql);
                    $sql = "select products_quantity as qty from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    $query1 = xtc_db_query($sql);
                    $qty = xtc_db_fetch_array($query1);

                    $sql = "delete from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);

                    $sql = "delete from orders_total where class NOT LIKE 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);

                    $sql = "update orders_total set  text = '<b>0,00 EUR</b>' , value = 0 where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);
                }
            }
            $message = PI_RATEPAY_SUCCESSFULLCANCELLATION;
            return array('result' => 'SUCCESS', 'message' => $message);
        }
        else {
            $message = PI_RATEPAY_ERRORFULLCANCELLATION;
            return array('result' => 'ERROR', 'message' => $message);
        }
    }
    else {
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, false, $first_name, $last_name);
        $message = PI_RATEPAY_SERVICE;
        return array('result' => 'ERROR', 'message' => $message);
    }
}

/**
 * This functions send a PAYMENT_CHANGE request with the sub operation part-cancelation
 * to the RatePAY API and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function partCancel($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    $operation = 'PAYMENT_CHANGE';
    $subOperation = 'partial-cancellation';
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_ratepay = new pi_ratepay_rechnung();
        $pi_table_prefix = 'pi_ratepay_rechnung';
        $pi_payment_type = 'INVOICE';
    } else {
        $pi_ratepay = new pi_ratepay_rate();
        $pi_table_prefix = 'pi_ratepay_rate';
        $pi_payment_type = 'INSTALLMENT';
    }

    $profileId = $pi_ratepay->profileId;
    $securityCode = $pi_ratepay->securityCode;
    $systemId = $_SERVER['SERVER_ADDR'];

    $query = xtc_db_query("select customers_id,customers_country,billing_country,delivery_country from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $customerIdArray = xtc_db_fetch_array($query);
    $customerId = $customerIdArray['customers_id'];
    $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
    $customerXTC = xtc_db_fetch_array($query);
    $email = $customerXTC['customers_email_address'];
    $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
    $transactionArray = xtc_db_fetch_array($query);
    $query = xtc_db_query("select * from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $order = xtc_db_fetch_array($query);
    $ratepay = new Ratepay_XML;
    $ratepay->live = $pi_ratepay->testOrLive();
    $request = $ratepay->getXMLObject();

    $request->addChild('head');
    $head = $request->{'head'};
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transactionArray['transaction_id']);
    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
    $operation = $head->addChild('operation', $operation);
    $operation->addAttribute('subtype', $subOperation);

    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);

    $external = $head->addChild('external');
    $external->addChild('order-id', $oID);

    $content = $request->addChild('content');
    $content->addChild('customer');

    if (strtoupper($customerXTC['customers_gender']) == "F") {
        $gender = "F";
    } else if (strtoupper($customerXTC['customers_gender']) == "M") {
        $gender = "M";
    } else {
        $gender = "U";
    }

    $customer = $content->customer;
    $customer->addCDataChild('first-name', removeSpecialChars($order['customers_firstname']));
    $customer->addCDataChild('last-name', removeSpecialChars($order['customers_lastname']));
    $customer->addChild('gender', $gender);
    $customer->addChild('date-of-birth', (string) utf8_encode($customerXTC['customers_dob']));
    $customer->addChild('contacts');

    $contacts = $customer->contacts;
    $contacts->addChild('email', $email);
    $contacts->addChild('phone');

    $phone = $contacts->phone;
    $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

    $customer->addChild('addresses');
    $addresses = $customer->addresses;
    $addresses->addChild('address');
    $addresses->addChild('address');

    $billingAddress = $addresses->address[0];
    $shippingAddress = $addresses->address[1];

    $billingAddress->addAttribute('type', 'BILLING');
    $shippingAddress->addAttribute('type', 'DELIVERY');

    $billingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $billingAddress->addChild('zip-code', $order['delivery_postcode']);
    $billingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $billingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $shippingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $shippingAddress->addChild('zip-code', $order['delivery_postcode']);
    $shippingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $shippingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $customer->addChild('nationality', $order['delivery_country_iso_code_2']);
    $customer->addChild('customer-allow-credit-inquiry', 'yes');
    $content->addChild('shopping-basket');
    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $i = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        $qty = ($mItem['ordered'] - $mItem['returned'] - $mItem['cancelled']);
        $newQTY = $qty - $_POST[$mItem['article_number']];
        if ($_POST[$mItem['article_number']] < $qty) {
            if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                $price = $price + ( ( $mItem['products_price'] * $newQTY ));
            } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                $price = $price + ( ( $mItem['article_netUnitPrice'] * $newQTY ));
            } elseif ($mItem['article_number'] == 'SHIPPING') {
                $price = $price + ( ( ( $mItem['article_netUnitPrice'] + getShippingTaxAmount($mItem['article_netUnitPrice']) ) * $newQTY ));
            } elseif ($mItem['article_number'] == 'DISCOUNT') {
                $price = $price + ( ( $mItem['article_netUnitPrice'] * $newQTY ));
            } elseif ($mItem['article_number'] == 'COUPON') {
                $couponTax = (($mItem['article_netUnitPrice'] / (100 + $cab->getCouponTaxRate()) * 100) - $mItem['article_netUnitPrice']) * (-1);
                $price = $price + ( ( ($mItem['article_netUnitPrice']) * $newQTY ));
            }
        }
    }

    $shoppingBasket = $content->{'shopping-basket'};
    $shoppingBasket->addAttribute('amount', number_format($price, 2, ".", ""));
    $shoppingBasket->addAttribute('currency', 'EUR');
    $items = $shoppingBasket->addChild('items');
    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $i = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        $qty = ($mItem['ordered'] - $mItem['returned'] - $mItem['cancelled']);
        $newQTY = $qty - $_POST[$mItem['article_number']];
        if ($_POST[$mItem['article_number']] < $qty) {
            if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                $items->addCDataChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['products_id']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['products_price'] / (100 + $mItem['products_tax']) * 100, 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($mItem['products_price'] / (100 + $mItem['products_tax']) * 100) * $newQTY, 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($newQTY * ($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax']), 2, ".", ""));
            } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                $items->addChild('item', PI_RATEPAY_VOUCHER);
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format($newQTY * $mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } elseif ($mItem['article_number'] == 'SHIPPING') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(getShippingTaxAmount(($newQTY * $mItem['article_netUnitPrice'])), 2, '.', ''));
            } elseif ($mItem['article_number'] == 'DISCOUNT') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } elseif ($mItem['article_number'] == 'COUPON') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1))), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($cab->getCouponTaxAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
            }
            $i++;
        }
    }
    $content->addChild('payment');
    $payment = $content->payment;
    $payment->addAttribute('method', $pi_payment_type);
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', number_format($price, 2, ".", ""));
    if ($pi_payment_type == "INSTALLMENT") {
        $payment->addChild('installment-details');
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
    }
    $response = $ratepay->paymentOperation($request);
    $first_name = $order['customers_firstname'];
    $last_name = $order['customers_lastname'];
    
    if ($response) {
        $resultCode = (string) $response->head->processing->result->attributes()->code;
        $result = (string) $response->head->processing->result;
        
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "403") {
            $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
            $query = xtc_db_query($sql);
            while ($mItem = xtc_db_fetch_array($query)) {
                if ($_POST[$mItem['article_number']] > 0) {
                    $sql = "update " . $pi_table_prefix . "_orderdetails set cancelled = cancelled + " . xtc_db_input($_POST[$mItem['article_number']]) . " where order_number = '" . xtc_db_input($oID) . "' and article_number = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "insert into " . $pi_table_prefix . "_history (order_number, article_number, quantity, method, submethod) values ('" . xtc_db_input($oID) . "', '" . xtc_db_input($mItem['article_number']) . "', '" . xtc_db_input($_POST[$mItem['article_number']]) . "', 'cancelled', 'cancelled')";
                    xtc_db_query($sql);
                    $sql = "select products_quantity as qty from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    $query1 = xtc_db_query($sql);
                    $qty = xtc_db_fetch_array($query1);
                    if (($qty['qty'] - $_POST[$mItem['article_number']]) <= 0) {
                        $sql = "delete from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                        xtc_db_query($sql);
                    }

                    $sql = "update orders_products set products_quantity = products_quantity - " . xtc_db_input($_POST[$mItem['article_number']]) . ", final_price = products_price * (products_quantity) where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['products_price']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['products_price']) . ")) where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input(($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "select * from orders_total where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                        $gutscheinResult = xtc_db_query($sql);
                        $gutscheinResultArray = xtc_db_fetch_array($gutscheinResult);
                        if ($gutscheinResultArray['value'] == 0) {
                            $sql = "delete from orders_total where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);
                        } else {
                            $sql = "update orders_total set text = '<font color=\"ff0000\">" . number_format($gutscheinResultArray['value'], 2, ",", "") . " EUR</font>' where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);
                        }
                    } elseif ($mItem['article_number'] == 'SHIPPING') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice'] + getShippingTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input(getShippingTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                        
                        $sql = "delete from orders_total where class = 'ot_shipping' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } elseif ($mItem['article_number'] == 'DISCOUNT') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "delete from orders_total where class = 'ot_discount' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } elseif ($mItem['article_number'] == 'COUPON') {
                        
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                        
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($cab->getCouponTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "delete from orders_total where class = 'ot_coupon' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    }
                    $sql = "select value from orders_total where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);

                    $sql = "select value from orders_total where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);

                    $sql = "select value from orders_total where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);
                }
            }
            $message = PI_RATEPAY_SUCCESSPARTIALCANCELLATION;
            return array('result' => 'SUCCESS', 'message' => $message);
        }
        else {
            $message = PI_RATEPAY_ERRORPARTIALCANCELLATION;
            return array('result' => 'ERROR', 'message' => $message);
        }
    }
    else {
        
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, false, $first_name, $last_name);
        $message = PI_RATEPAY_SERVICE;
        return array('result' => 'ERROR', 'message' => $message);
    }
}

/**
 * This functions calls the fullCancel($oID) or the partCancel($oID) function
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function returnRequest($oID, $paymentType) {
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_table_prefix = 'pi_ratepay_rechnung';
    } else {
        $pi_table_prefix = 'pi_ratepay_rate';
    }
    $sql = "select * from " . $pi_table_prefix . "_orderdetails where order_number = '" . xtc_db_input($oID) . "'";
    $query = xtc_db_query($sql);
    $flag = array();
    $i = 0;
    while ($item = xtc_db_fetch_array($query)) {
        $qty = $item['ordered'] - $item['returned'] - $_POST[$item['article_number']];
        if ($qty == 0) {
            $flag[$i] = true;
        } else if ($qty > 0) {
            $flag[$i] = false;
        }
        $i++;
    }
    $full = true;
    for ($i = 0; $i < count($flag); $i++) {
        if ($flag[$i] == false) {
            $full = false;
        }
    }
    if ($full == true) {
        return fullReturn($oID, $paymentType);
    } else if ($full == false) {
        return partReturn($oID, $paymentType);
    }
}

/**
 * This functions send a PAYMENT_CHANGE request with the sub operation part-return
 * to the RatePAY API and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function partReturn($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    // Stuff for the request
    $operation = 'PAYMENT_CHANGE';
    $subOperation = 'partial-return';
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_ratepay = new pi_ratepay_rechnung();
        $pi_table_prefix = 'pi_ratepay_rechnung';
        $pi_payment_type = 'INVOICE';
    } else {
        $pi_ratepay = new pi_ratepay_rate();
        $pi_table_prefix = 'pi_ratepay_rate';
        $pi_payment_type = 'INSTALLMENT';
    }

    $profileId = $pi_ratepay->profileId;
    $securityCode = $pi_ratepay->securityCode;
    $systemId = $_SERVER['SERVER_ADDR'];

    $query = xtc_db_query("select customers_id,customers_country,billing_country,delivery_country from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $customerIdArray = xtc_db_fetch_array($query);
    $customerId = $customerIdArray['customers_id'];
    $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
    $customerXTC = xtc_db_fetch_array($query);
    $email = $customerXTC['customers_email_address'];
    $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
    $transactionArray = xtc_db_fetch_array($query);
    $query = xtc_db_query("select * from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $order = xtc_db_fetch_array($query);
    $ratepay = new Ratepay_XML;
    $ratepay->live = $pi_ratepay->testOrLive();
    $request = $ratepay->getXMLObject();

    $request->addChild('head');
    $head = $request->{'head'};
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transactionArray['transaction_id']);
    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
    $operation = $head->addChild('operation', $operation);
    $operation->addAttribute('subtype', $subOperation);

    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);

    $external = $head->addChild('external');
    $external->addChild('order-id', $oID);

    $content = $request->addChild('content');
    $content->addChild('customer');

    if (strtoupper($customerXTC['customers_gender']) == "F") {
        $gender = "F";
    } else if (strtoupper($customerXTC['customers_gender']) == "M") {
        $gender = "M";
    } else {
        $gender = "U";
    }

    $customer = $content->customer;
    $customer->addCDataChild('first-name', removeSpecialChars($order['customers_firstname']));
    $customer->addCDataChild('last-name', removeSpecialChars($order['customers_lastname']));
    $customer->addChild('gender', $gender);
    $customer->addChild('date-of-birth', (string) utf8_encode($customerXTC['customers_dob']));
    $customer->addChild('contacts');

    $contacts = $customer->contacts;
    $contacts->addChild('email', $email);
    $contacts->addChild('phone');

    $phone = $contacts->phone;
    $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

    $customer->addChild('addresses');
    $addresses = $customer->addresses;
    $addresses->addChild('address');
    $addresses->addChild('address');

    $billingAddress = $addresses->address[0];
    $shippingAddress = $addresses->address[1];

    $billingAddress->addAttribute('type', 'BILLING');
    $shippingAddress->addAttribute('type', 'DELIVERY');

    $billingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $billingAddress->addChild('zip-code', $order['delivery_postcode']);
    $billingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $billingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $shippingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $shippingAddress->addChild('zip-code', $order['delivery_postcode']);
    $shippingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $shippingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $customer->addChild('nationality', $order['delivery_country_iso_code_2']);
    $customer->addChild('customer-allow-credit-inquiry', 'yes');
    $content->addChild('shopping-basket');
    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $i = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        $qty = $mItem['ordered'] - $mItem['cancelled'] - $mItem['returned'];
        $newQTY = $qty - $_POST[$mItem['article_number']];
        if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
            $price = $price + ( ( $mItem['products_price'] * $newQTY ));
        } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
            $price = $price + ( ( $mItem['article_netUnitPrice'] * $newQTY ));
        } elseif ($mItem['article_number'] == 'SHIPPING') {
            $price = $price + ( ( ( $mItem['article_netUnitPrice'] + getShippingTaxAmount($mItem['article_netUnitPrice']) ) * $newQTY ));
        } elseif ($mItem['article_number'] == 'DISCOUNT') {
            $price = $price + ( ( $mItem['article_netUnitPrice'] * $newQTY ));
        } elseif ($mItem['article_number'] == 'COUPON') {
            $couponTax = (($mItem['article_netUnitPrice'] / (100 + $cab->getCouponTaxRate()) * 100) - $mItem['article_netUnitPrice']) * (-1);
            $price = $price + ( ( ($mItem['article_netUnitPrice']) * $newQTY ));
        }
    }


    $shoppingBasket = $content->{'shopping-basket'};
    $shoppingBasket->addAttribute('amount', number_format($price, 2, ".", ""));
    $shoppingBasket->addAttribute('currency', 'EUR');
    $items = $shoppingBasket->addChild('items');
    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
    $query = xtc_db_query($sql);
    $i = 0;
    while ($mItem = xtc_db_fetch_array($query)) {
        $qty = ($mItem['ordered'] - $mItem['returned'] - $mItem['cancelled']);
        $newQTY = $qty - $_POST[$mItem['article_number']];
        if ($_POST[$mItem['article_number']] < $qty) {
            if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                $items->addCDataChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['products_id']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['products_price'] / (100 + $mItem['products_tax']) * 100, 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($mItem['products_price'] / (100 + $mItem['products_tax']) * 100) * $newQTY, 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($newQTY * ($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax']), 2, '.', ''));
            } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                $items->addChild('item', PI_RATEPAY_VOUCHER);
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format($newQTY * $mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } elseif ($mItem['article_number'] == 'SHIPPING') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(getShippingTaxAmount(($newQTY * $mItem['article_netUnitPrice'])), 2, '.', ''));
            } elseif ($mItem['article_number'] == 'DISCOUNT') {
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $mItem['article_netUnitPrice']), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
            } elseif ($mItem['article_number'] == 'COUPON') {
                $couponTax = (($mItem['article_netUnitPrice'] / (100 + $cab->getCouponTaxRate()) * 100) - $mItem['article_netUnitPrice']) * (-1);
                $items->addChild('item', removeSpecialChars($mItem['article_name']));
                $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                $items->item[$i]->addAttribute('quantity', $newQTY);
                $items->item[$i]->addAttribute('unit-price', number_format($cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
                $items->item[$i]->addAttribute('total-price', number_format(($newQTY * $cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1))), 2, '.', ''));
                $items->item[$i]->addAttribute('tax', number_format($cab->getCouponTaxAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
            }
            $i++;
        }
    }
    $content->addChild('payment');
    $payment = $content->payment;
    $payment->addAttribute('method', $pi_payment_type);
    $payment->addAttribute('currency', 'EUR');
    $payment->addChild('amount', number_format($price, 2, ".", ""));
    if ($pi_payment_type == "INSTALLMENT") {
        $payment->addChild('installment-details');
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
    }
    $response = $ratepay->paymentOperation($request);

    $first_name = $order['customers_firstname'];
    $last_name = $order['customers_lastname'];
    if ($response) {
        $resultCode = (string) $response->head->processing->result->attributes()->code;
        $result = (string) $response->head->processing->result;
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "403") {
            $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
            $query = xtc_db_query($sql);
            $i = 0;
            while ($mItem = xtc_db_fetch_array($query)) {
                $qty = $mItem['ordered'] - $mItem['cancelled'] - $mItem['returned'];
                if ($_POST[$mItem['article_number']] > 0) {
                    $sql = "update " . $pi_table_prefix . "_orderdetails set returned = returned + " . xtc_db_input($_POST[$mItem['article_number']]) . " where order_number = '" . xtc_db_input($oID) . "' and article_number = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "insert into " . $pi_table_prefix . "_history (order_number, article_number, quantity, method, submethod) values ('" . xtc_db_input($oID) . "', '" . xtc_db_input($mItem['article_number']) . "', '" . xtc_db_input($_POST[$mItem['article_number']]) . "', 'returned', 'returned')";
                    xtc_db_query($sql);
                    $sql = "select products_quantity as qty from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    $query1 = xtc_db_query($sql);
                    $qty = xtc_db_fetch_array($query1);
                    if (($qty['qty'] - $_POST[$mItem['article_number']]) <= 0) {
                        $sql = "delete from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                        xtc_db_query($sql);
                    }

                    $sql = "update orders_products set products_quantity = products_quantity - " . xtc_db_input($_POST[$mItem['article_number']]) . ", final_price = products_price * (products_quantity) where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['products_price']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['products_price']) . ")) where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input(($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "select * from orders_total where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                        $gutscheinResult = xtc_db_query($sql);
                        $gutscheinResultArray = xtc_db_fetch_array($gutscheinResult);
                        if ($gutscheinResultArray['value'] == 0) {
                            $sql = "delete from orders_total where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);
                        } else {
                            $sql = "update orders_total set text = '<font color=\"ff0000\">" . number_format($gutscheinResultArray['value'], 2, ",", "") . " EUR</font>' where class = 'pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);
                        }
                    } elseif ($mItem['article_number'] == 'SHIPPING') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice'] + getShippingTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                        
                        $sql = "update orders_total set value = (value - (" . xtc_db_input(getShippingTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                        
                        $sql = "delete from orders_total where class = 'ot_shipping' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } elseif ($mItem['article_number'] == 'DISCOUNT') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "delete from orders_total where class = 'ot_discount' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    } elseif ($mItem['article_number'] == 'COUPON') {
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($_POST[$mItem['article_number']]) . " * " . xtc_db_input($mItem['article_netUnitPrice']) . ")) where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                        
                        $sql = "update orders_total set value = (value - (" . xtc_db_input($cab->getCouponTaxAmount($mItem['article_netUnitPrice'])) . ")) where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);

                        $sql = "delete from orders_total where class = 'ot_coupon' and orders_id = '" . xtc_db_input($oID) . "'";
                        xtc_db_query($sql);
                    }
                    $sql = "select value from orders_total where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);

                    $sql = "select value from orders_total where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_tax' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);

                    $sql = "select value from orders_total where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                    $totalq = xtc_db_query($sql);
                    $total = xtc_db_fetch_array($totalq);
                    $totalText = str_replace(",", ".", strval(number_format($total['value'], 2)));
                    $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_subtotal' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);
                }
            }
            $message = PI_RATEPAY_SUCCESSPARTIALRETURN;
            return array('result' => 'SUCCESS', 'message' => $message);
        }
        else {
            $message = PI_RATEPAY_ERRORPARTIALRETURN;
            return array('result' => 'ERROR', 'message' => $message);
        }
    }
    else {
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, false, $first_name, $last_name);
        $message = PI_RATEPAY_SERVICE;
        return array('result' => 'ERROR', 'message' => $message);
    }
}

/**
 * This functions send a PAYMENT_CHANGE request with the sub operation full-return
 * to the RatePAY API and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function fullReturn($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    $operation = 'PAYMENT_CHANGE';
    $subOperation = 'full-return';
    if ($paymentType == "pi_ratepay_rechnung") {
        $pi_ratepay = new pi_ratepay_rechnung();
        $pi_table_prefix = 'pi_ratepay_rechnung';
        $pi_payment_type = 'INVOICE';
    } else {
        $pi_ratepay = new pi_ratepay_rate();
        $pi_table_prefix = 'pi_ratepay_rate';
        $pi_payment_type = 'INSTALLMENT';
    }

    $profileId = $pi_ratepay->profileId;
    $securityCode = $pi_ratepay->securityCode;
    $systemId = $_SERVER['SERVER_ADDR'];

    $query = xtc_db_query("select customers_id,customers_country,billing_country,delivery_country from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $customerIdArray = xtc_db_fetch_array($query);
    $customerId = $customerIdArray['customers_id'];
    $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
    $customerXTC = xtc_db_fetch_array($query);
    $email = $customerXTC['customers_email_address'];
    $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
    $transactionArray = xtc_db_fetch_array($query);
    $query = xtc_db_query("select * from orders where orders_id = '" . xtc_db_input($oID) . "'");
    $order = xtc_db_fetch_array($query);
    $ratepay = new Ratepay_XML;
    $ratepay->live = $pi_ratepay->testOrLive();
    $request = $ratepay->getXMLObject();

    $request->addChild('head');
    $head = $request->{'head'};
    $head->addChild('system-id', $systemId);
    $head->addChild('transaction-id', $transactionArray['transaction_id']);
    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
    $operation = $head->addChild('operation', $operation);
    $operation->addAttribute('subtype', $subOperation);

    $credential = $head->addChild('credential');
    $credential->addChild('profile-id', $profileId);
    $credential->addChild('securitycode', $securityCode);

    $external = $head->addChild('external');
    $external->addChild('order-id', $oID);

    $content = $request->addChild('content');
    $content->addChild('customer');

    if (strtoupper($customerXTC['customers_gender']) == "F") {
        $gender = "F";
    } else if (strtoupper($customerXTC['customers_gender']) == "M") {
        $gender = "M";
    } else {
        $gender = "U";
    }

    $customer = $content->customer;
    $customer->addCDataChild('first-name', removeSpecialChars($order['customers_firstname']));
    $customer->addCDataChild('last-name', removeSpecialChars($order['customers_lastname']));
    $customer->addChild('gender', $gender);
    $customer->addChild('date-of-birth', (string) utf8_encode($customerXTC['customers_dob']));
    $customer->addChild('contacts');

    $contacts = $customer->contacts;
    $contacts->addChild('email', $email);
    $contacts->addChild('phone');

    $phone = $contacts->phone;
    $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

    $customer->addChild('addresses');
    $addresses = $customer->addresses;
    $addresses->addChild('address');
    $addresses->addChild('address');

    $billingAddress = $addresses->address[0];
    $shippingAddress = $addresses->address[1];

    $billingAddress->addAttribute('type', 'BILLING');
    $shippingAddress->addAttribute('type', 'DELIVERY');

    $billingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $billingAddress->addChild('zip-code', $order['delivery_postcode']);
    $billingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $billingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $shippingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
    $shippingAddress->addChild('zip-code', $order['delivery_postcode']);
    $shippingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
    $shippingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

    $customer->addChild('nationality', $order['delivery_country_iso_code_2']);
    $customer->addChild('customer-allow-credit-inquiry', 'yes');
    $content->addChild('shopping-basket');
    $shoppingBasket = $content->{'shopping-basket'};
    $shoppingBasket->addAttribute('amount', '0.00');
    $shoppingBasket->addAttribute('currency', 'EUR');
    $shoppingBasket->addChild('items');
    $content->addChild('payment');
    $payment = $content->payment;
    $payment->addAttribute('method', $pi_payment_type);
    $payment->addAttribute('currency', 'EUR');
    if ($pi_payment_type == "INSTALLMENT") {
        $payment->addChild('installment-details');
        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
    }
    $response = $ratepay->paymentOperation($request);
    $first_name = $order['customers_firstname'];
    $last_name = $order['customers_lastname'];
    if ($response) {
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "403") {
            $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
            $querySQL = xtc_db_query($sql);
            while ($mItem = xtc_db_fetch_array($querySQL)) {
                if ($_POST[$mItem['article_number']] > 0) {
                    $sql = "update " . $pi_table_prefix . "_orderdetails set returned = returned + " . xtc_db_input($_POST[$mItem['article_number']]) . " where order_number = '" . xtc_db_input($oID) . "' and article_number = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "insert into " . $pi_table_prefix . "_history (order_number, article_number, quantity, method, submethod) values ('" . xtc_db_input($oID) . "', '" . xtc_db_input($mItem['article_number']) . "', '" . xtc_db_input($_POST[$mItem['article_number']]) . "', 'returned', 'returned')";
                    xtc_db_query($sql);

                    $sql = "delete from orders_products where orders_id = '" . xtc_db_input($oID) . "' and orders_products_id = '" . xtc_db_input($mItem['article_number']) . "'";
                    xtc_db_query($sql);
                    $sql = "delete from orders_total where class NOT LIKE 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);
                    $sql = "update orders_total set  text = '<b>0,00 EUR</b>' , value = 0 where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                    xtc_db_query($sql);
                }
            }
            $message = PI_RATEPAY_SUCCESSFULLRETURN;
            return array('result' => 'SUCCESS', 'message' => $message);
        }
        else {
            $message = PI_RATEPAY_ERRORFULLRETURN;
            return array('result' => 'ERROR', 'message' => $message);
        }
    }
    else {
        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $first_name, $last_name);
        $message = PI_RATEPAY_SERVICE;
        return array('result' => 'ERROR', 'message' => $message);
    }
}

/**
 * This functions send a PAYMENT_CHANGE request with the sub operation goodwill
 * to the RatePAY API and saves all necessary informations in the DB
 * @param string $oID
 * @param string $paymentType
 *
 * @return array
 */
function voucherRequest($oID, $paymentType) {
    $cab = new pi_ratepay_rechnung();
    if (isset($_POST)) {
        $operation = 'PAYMENT_CHANGE';
        if ($paymentType == "pi_ratepay_rechnung") {
            $pi_ratepay = new pi_ratepay_rechnung();
            $pi_table_prefix = 'pi_ratepay_rechnung';
            $pi_payment_type = 'INVOICE';
        } else {
            $pi_ratepay = new pi_ratepay_rate();
            $pi_table_prefix = 'pi_ratepay_rate';
            $pi_payment_type = 'INSTALLMENT';
        }

        $profileId = $pi_ratepay->profileId;
        $securityCode = $pi_ratepay->securityCode;
        $systemId = $_SERVER['SERVER_ADDR'];

        $query = xtc_db_query("select customers_id,customers_country,billing_country,delivery_country from orders where orders_id = '" . xtc_db_input($oID) . "'");
        $customerIdArray = xtc_db_fetch_array($query);
        $customerId = $customerIdArray['customers_id'];

        $subOperation = 'credit';
        $query = xtc_db_query("select customers_gender, date_format(customers_dob, '%Y-%m-%d') as customers_dob, customers_email_address, customers_telephone from " . TABLE_CUSTOMERS . " where customers_id ='" . xtc_db_input($customerId) . "'");
        $customerXTC = xtc_db_fetch_array($query);
        $email = $customerXTC['customers_email_address'];
        $query = xtc_db_query("select transaction_id, transaction_short_id from " . $pi_table_prefix . "_orders where order_number = '" . xtc_db_input($oID) . "'");
        $transactionArray = xtc_db_fetch_array($query);
        $query = xtc_db_query("select * from orders a, orders_total b where a.orders_id = '" . xtc_db_input($oID) . "' and a.orders_id = b.orders_id and class = 'ot_total'");
        $order = xtc_db_fetch_array($query);

        if (isset($_POST['voucherAmount'])) {
            if (preg_match("/^[0-9]{1,4}$/", $_POST['voucherAmount'])) {
                $piRatepayVoucher = $_POST['voucherAmount'];
                if (isset($_POST['voucherAmountKomma']) && $_POST['voucherAmountKomma'] != '') {
                    if (preg_match("/^[0-9]{2}$/", $_POST['voucherAmountKomma'])) {
                        $piRatepayVoucher = $piRatepayVoucher . "." . $_POST['voucherAmountKomma'];
                    } else if (preg_match("/^[0-9]{1}$/", $_POST['voucherAmountKomma'])) {
                        $piRatepayVoucher = $piRatepayVoucher . "." . $_POST['voucherAmountKomma'] . "0";
                    } else {
                        $piRatepayVoucher = $piRatepayVoucher . ".00";
                        $message = PI_RATEPAY_ERRORVOUCHER;
                        return array('result' => 'ERROR', 'message' => $message);
                    }
                } else {
                    $piRatepayVoucher = $piRatepayVoucher . ".00";
                    $message = PI_RATEPAY_ERRORVOUCHER;
                    return array('result' => 'ERROR', 'message' => $message);
                }
                if ($piRatepayVoucher > $order['value']) {
                    $message = PI_RATEPAY_ERRORVOUCHER;
                    return array('result' => 'ERROR', 'message' => $message);
                } else {
                    $piRatepayVoucher = $piRatepayVoucher * (-1);

                    $ratepay = new Ratepay_XML;
                    $ratepay->live = $pi_ratepay->testOrLive();
                    $request = $ratepay->getXMLObject();

                    $request->addChild('head');
                    $head = $request->{'head'};
                    $head->addChild('system-id', $systemId);
                    $head->addChild('transaction-id', $transactionArray['transaction_id']);
                    $head->addChild('transaction-short-id', $transactionArray['transaction_short_id']);
                    $operation = $head->addChild('operation', $operation);
                    $operation->addAttribute('subtype', $subOperation);

                    $credential = $head->addChild('credential');
                    $credential->addChild('profile-id', $profileId);
                    $credential->addChild('securitycode', $securityCode);

                    $external = $head->addChild('external');
                    $external->addChild('order-id', $oID);

                    $content = $request->addChild('content');
                    $content->addChild('customer');

                    if (strtoupper($customerXTC['customers_gender']) == "F") {
                        $gender = "F";
                    } else if (strtoupper($customerXTC['customers_gender']) == "M") {
                        $gender = "M";
                    } else {
                        $gender = "U";
                    }

                    $customer = $content->customer;
                    $customer->addCDataChild('first-name', removeSpecialChars($order['customers_firstname']));
                    $customer->addCDataChild('last-name', removeSpecialChars($order['customers_lastname']));
                    $customer->addChild('gender', $gender);
                    $customer->addChild('date-of-birth', (string) utf8_encode($customerXTC['customers_dob']));
                    $customer->addChild('contacts');

                    $contacts = $customer->contacts;
                    $contacts->addChild('email', $email);
                    $contacts->addChild('phone');

                    $phone = $contacts->phone;
                    $phone->addChild('direct-dial', $customerXTC['customers_telephone']);

                    $customer->addChild('addresses');
                    $addresses = $customer->addresses;
                    $addresses->addChild('address');
                    $addresses->addChild('address');

                    $billingAddress = $addresses->address[0];
                    $shippingAddress = $addresses->address[1];

                    $billingAddress->addAttribute('type', 'BILLING');
                    $shippingAddress->addAttribute('type', 'DELIVERY');

                    $billingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
                    $billingAddress->addChild('zip-code', $order['delivery_postcode']);
                    $billingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
                    $billingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

                    $shippingAddress->addCDataChild('street', removeSpecialChars($order['delivery_street_address']));
                    $shippingAddress->addChild('zip-code', $order['delivery_postcode']);
                    $shippingAddress->addCDataChild('city', removeSpecialChars($order['delivery_city']));
                    $shippingAddress->addChild('country-code', $order['delivery_country_iso_code_2']);

                    $customer->addChild('nationality', $order['delivery_country_iso_code_2']);
                    $customer->addChild('customer-allow-credit-inquiry', 'yes');
                    $shoppingBasket = $content->addChild('shopping-basket');
                    $shoppingBasket->addAttribute('currency', 'EUR');
                    $items = $shoppingBasket->addChild('items');
                    $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($oID) . "' and  article_number != ''";
                    $query = xtc_db_query($sql);
                    $i = 0;
                    $shippingCost = 0;
                    $couponTax = 0;
                    while ($mItem = xtc_db_fetch_array($query)) {
                        $qty = ($mItem['ordered'] - $mItem['returned'] - $mItem['canceled']);

                        if ($mItem['article_name'] != 'pi-Merchant-Voucher' && $mItem['article_number'] != 'SHIPPING' && $mItem['article_number'] != 'DISCOUNT' && $mItem['article_number'] != 'COUPON') {
                            $items->addCDataChild('item', removeSpecialChars($mItem['article_name']));
                            $items->item[$i]->addAttribute('article-number', $mItem['products_id']);
                            $items->item[$i]->addAttribute('quantity', $qty);
                            $items->item[$i]->addAttribute('unit-price', number_format($mItem['products_price'] / (100 + $mItem['products_tax']) * 100, 2, '.', ''));
                            $items->item[$i]->addAttribute('total-price', number_format(($mItem['products_price'] / (100 + $mItem['products_tax']) * 100) * $qty, 2, '.', ''));
                            $items->item[$i]->addAttribute('tax', number_format($qty * ($mItem['products_price'] / (100 + $mItem['products_tax']) * $mItem['products_tax']), 2, '.', ''));
                        } else if ($mItem['article_name'] == 'pi-Merchant-Voucher') {
                            $items->addChild('item', PI_RATEPAY_VOUCHER);
                            $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                            $items->item[$i]->addAttribute('quantity', $qty);
                            $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                            $items->item[$i]->addAttribute('total-price', number_format($qty * $mItem['article_netUnitPrice'], 2, '.', ''));
                            $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
                        } elseif ($mItem['article_number'] == 'SHIPPING') {
                            $shippingCost = $mItem['article_netUnitPrice'];
                            $items->addChild('item', removeSpecialChars($mItem['article_name']));
                            $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                            $items->item[$i]->addAttribute('quantity', $qty);
                            $items->item[$i]->addAttribute('unit-price', number_format($shippingCost, 2, '.', ''));
                            $items->item[$i]->addAttribute('total-price', number_format(($qty * $shippingCost), 2, '.', ''));
                            $items->item[$i]->addAttribute('tax', number_format(getShippingTaxAmount(($qty * $shippingCost)), 2, '.', ''));
                        } elseif ($mItem['article_number'] == 'DISCOUNT') {
                            $items->addChild('item', removeSpecialChars($mItem['article_name']));
                            $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                            $items->item[$i]->addAttribute('quantity', $qty);
                            $items->item[$i]->addAttribute('unit-price', number_format($mItem['article_netUnitPrice'], 2, '.', ''));
                            $items->item[$i]->addAttribute('total-price', number_format(($qty * $mItem['article_netUnitPrice']), 2, '.', ''));
                            $items->item[$i]->addAttribute('tax', number_format(0, 2, '.', ''));
                        } elseif ($mItem['article_number'] == 'COUPON') {
                            $couponTax = (($mItem['article_netUnitPrice'] / (100 + $cab->getCouponTaxRate()) * 100) - $mItem['article_netUnitPrice']) * (-1);
                            $items->addChild('item', removeSpecialChars($mItem['article_name']));
                            $items->item[$i]->addAttribute('article-number', $mItem['article_number']);
                            $items->item[$i]->addAttribute('quantity', $qty);
                            $items->item[$i]->addAttribute('unit-price', number_format($cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
                            $items->item[$i]->addAttribute('total-price', number_format(($qty * $cab->getCouponAmount($mItem['article_netUnitPrice'] * (-1))), 2, '.', ''));
                            $items->item[$i]->addAttribute('tax', number_format($cab->getCouponTaxAmount($mItem['article_netUnitPrice'] * (-1)), 2, '.', ''));
                        }
                        $i++;
                    }
                    $sql = "SELECT count( * ) as nr FROM " . $pi_table_prefix . "_orderdetails WHERE article_name = 'pi-Merchant-Voucher'";
                    $query = xtc_db_query($sql);
                    $nr = xtc_db_fetch_array($query);
                    if (!empty($_POST['voucherAmount']) && !empty($_POST['voucherAmountKomma'])) {
                        $items->addChild('item', PI_RATEPAY_VOUCHER);
                        $items->item[$i]->addAttribute('article-number', "pi-Merchant-Voucher-" . $nr['nr']);
                        $items->item[$i]->addAttribute('quantity', '1');
                        $items->item[$i]->addAttribute('unit-price', number_format($piRatepayVoucher, 2, ".", ""));
                        $items->item[$i]->addAttribute('total-price', number_format($piRatepayVoucher, 2, ".", ""));
                        $items->item[$i]->addAttribute('tax', number_format(0, 2, ".", ""));
                    }
                    $content->addChild('payment');
                    $payment = $content->payment;
                    $payment->addAttribute('method', $pi_payment_type);
                    $payment->addAttribute('currency', 'EUR');
                    
                    $total = ($order['value'] + $piRatepayVoucher);

                    // Add the shopping basket amoutn later because we need the shipping cost
                    $shoppingBasket->addAttribute('amount', number_format(($total), 2, '.', ''));
                    $payment->addChild('amount', number_format(($total), 2, '.', ''));
                    if ($pi_payment_type == "INSTALLMENT") {
                        $payment->addChild('installment-details');
                        $payment->addChild('debit-pay-type', 'BANK-TRANSFER');
                    }
                    $response = $ratepay->paymentOperation($request);
                    $first_name = $order['customers_firstname'];
                    $last_name = $order['customers_lastname'];
                    if ($response) {
                        $resultCode = (string) $response->head->processing->result->attributes()->code;
                        $result = (string) $response->head->processing->result;
 
                        $pi_ratepay->piRatepayLog($oID, $transactionArray['transaction_id'], $operation, $subOperation, $request, $response, $first_name, $last_name);
                        if ((string) $response->head->processing->status->attributes()->code == "OK" && (string) $response->head->processing->result->attributes()->code == "403") {
                            $sql = "INSERT INTO " . $pi_table_prefix . "_orderdetails
										(order_number, article_number,
										article_name, ordered, article_netUnitPrice) VALUES
										('" . $oID . "', 'pi-Merchant-Voucher-" . xtc_db_input($nr['nr']) . "',
										'pi-Merchant-Voucher',1," . xtc_db_input($piRatepayVoucher) . ")";
                            xtc_db_query($sql);
                            $sql = "INSERT INTO " . $pi_table_prefix . "_history
										(order_number, article_number,
										quantity, method, submethod) VALUES
										('" . xtc_db_input($oID) . "', 'pi-Merchant-Voucher-" . xtc_db_input($nr['nr']) . "',
										'1',
										'Credit created', 'added')";
                            xtc_db_query($sql);

                            $discountSql = "SELECT * FROM `orders_total` WHERE class='pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                            $discountResult = xtc_db_query($discountSql);
                            $discountCount = xtc_db_num_rows($discountResult);
                            if ($discountCount > 0) {
                                $discountArray = xtc_db_fetch_array($discountResult);
                                $value = $discountArray['value'];
                                $value = $value + $piRatepayVoucher;
                                $value = number_format($value, 4, ".", "");
                                $discountTotalUpdate = "update orders_total set value = " . xtc_db_input($value) . " where class='pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                                xtc_db_query($discountTotalUpdate);
                                $value = number_format($value, 2, ",", "");
                                $discountTotalUpdate = "update orders_total set text = '<font color=\"ff0000\">" . xtc_db_input($value) . " EUR</font>' where class='pi_ratepay_voucher' and orders_id = '" . xtc_db_input($oID) . "'";
                                xtc_db_query($discountTotalUpdate);
                            } else {
                                $value = number_format($piRatepayVoucher, 4, ".", "");
                                $valueFormat = number_format($value, 2, ",", "");
                                $discountTotalInsert = "INSERT INTO `orders_total` (`orders_id`, `title`, `text`, `value`, `class`, `sort_order`) VALUES ('" . xtc_db_input($oID) . "', 'Gutschein:', '<font color=\"ff0000\"> " . xtc_db_input($valueFormat) . " EUR</font>', " . xtc_db_input($value) . ", 'pi_ratepay_voucher', 98)";
                                xtc_db_query($discountTotalInsert);
                            }

                            $sql = "update orders_total set value = value+$piRatepayVoucher where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);
                            $sql = "select value from orders_total where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                            $totalq = xtc_db_query($sql);
                            $total = xtc_db_fetch_array($totalq);
                            $totalText = number_format($total['value'], 2, ",", ".");
                            $sql = "update orders_total set text = '<b>" . xtc_db_input($totalText) . " EUR</b>' where class = 'ot_total' and orders_id = '" . xtc_db_input($oID) . "'";
                            xtc_db_query($sql);

                            $message = PI_RATEPAY_SUCCESSVOUCHER;
                            return array('result' => 'SUCCESS', 'message' => $message);
                        }
                        else {
                            $message = PI_RATEPAY_ERRORVOUCHER;
                            return array('result' => 'ERROR', 'message' => $message);
                        }
                    }
                    else {
                        $message = PI_RATEPAY_SERVICE;
                        return array('result' => 'ERROR', 'message' => $message);
                    }
                }
            }
            else {
                $message = PI_RATEPAY_ERRORVOUCHER;
                return array('result' => 'ERROR', 'message' => $message);
            }
        }
    }
}

/*
 * This method removes some special chars
 *
 * @return string
 */
function removeSpecialChars($str) {
    $search = array("–", "´", "‹", "›", "‘", "’", "‚", "“", "”", "„", "‟", "•", "‒", "―", "—", "™", "¼", "½", "¾");
    $replace = array("-", "'", "<", ">", "'", "'", ",", '"', '"', '"', '"', "-", "-", "-", "-", "TM", "1/4", "1/2", "3/4");
    return removeSpecialChar($search, $replace, $str);
}

/*
 * This method removes some special chars
 *
 * @return string
 */

function removeSpecialChar($search, $replace, $subject) {
    $str = str_replace($search, $replace, $subject);
    return utf8_encode($str);
}

function getCompanyAndVatId()
{
    
}

?>
