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
 * @package   PayIntelligent_RatePAY
 * @copyright (C) 2010 PayIntelligent GmbH  <http://www.payintelligent.de/>
 * @license   GPLv2
 */

require('includes/application_top.php');
require_once(DIR_FS_DOCUMENT_ROOT . 'includes/modules/payment/pi_ratepay_rechnung.php');

$cab = new pi_ratepay_rechnung();


/**
 * Add the shipping tax to the order object
 * 
 * @param order $order
 * @return float
 */
function getShippingTaxAmount($shippingCost)
{
    global $order;
    $taxPercent = getShippingTaxRate();
    $shippingTaxAmount = $shippingCost * ($taxPercent/100);

    return $shippingTaxAmount;
}

/**
 * Retrieve the shipping tax rate
 * 
 * @param order $order
 * @return float order 
 */
function getShippingTaxRate() 
{
    global $order;
    //Class name from shipping
    $shipping_class_array = explode("_", $order['shipping_class']);
    $shipping_class = strtoupper($shipping_class_array[0]);
    if (empty($shipping_class)) {
        $shipping_tax_rate = 0;
    } else {
        $const = 'MODULE_SHIPPING_' . $shipping_class . '_TAX_CLASS';
        //Shipping tax rate
        if (defined($const)) {
            $shipping_tax_rate = xtc_get_tax_rate(constant($const));
        } else {
            $shipping_tax_rate = 0;
        }
    }

    return $shipping_tax_rate;
}

$language = $_SESSION['language'];
require_once '../lang/' . $language . '/admin/modules/payment/pi_ratepay.php';
!empty($_GET['oID']) ? $shopOrderID = $_GET['oID'] : $shopOrderID = $_POST['oID'];
!empty($_GET['payment']) ? $pi_table_prefix = $_GET['payment'] : $pi_table_prefix = $_POST['payment'];
$query = xtc_db_query("select * from orders a, orders_total b where a.orders_id = '" . xtc_db_input($shopOrderID) . "' and a.orders_id = b.orders_id and class = 'ot_total'");
$order = xtc_db_fetch_array($query);

function showMessageSuccess($message) {
    $messageBox = '<tr>
                        <td>
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td class="messageStackSuccess"><img border="0" title="" alt="" src="images/icons/success.gif">' . $message . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
    return $messageBox;
}

function showMessageError($message) {
    $messageBox = '<tr>
                        <td>
                            <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                <tr>
                                    <td class="messageStackError"><img border="0" title="" alt="" src="images/icons/error.gif">' . $message . '</td>
                                </tr>
                            </table>
                        </td>
                    </tr>';
    return $messageBox;
}

$sql = "select * from pi_ratepay_rate_details where orderid='" . xtc_db_input($shopOrderID) . "'";
$query = xtc_db_query($sql);
$ratedetails = xtc_db_fetch_array($query);

$pirptotalamountvalue = $ratedetails['totalamount'];
$pirpcashpaymentpricevalue = $ratedetails['amount'];
$pirpamountofinterestvalue = $ratedetails['interestamount'];
$pirpservicechargevalue = $ratedetails['servicecharge'];
$pirpeffectiveratevalue = $ratedetails['annualpercentagerate'];
$pirpdebitratevalue = $ratedetails['monthlydebitinterest'];
$pirpamountofratesvalue = $ratedetails['numberofrates'] - 1;
$pirpdurationtimevalue = $ratedetails['numberofrates'];
$pirpdurationmonthvalue = $ratedetails['rate'];
$pirplastratevalue = $ratedetails['lastrate'];

$pirptotalamountvalue = str_replace(".", ",", number_format($pirptotalamountvalue, 2, ".", "")) . " EUR";
$pirpcashpaymentpricevalue = str_replace(".", ",", number_format($pirpcashpaymentpricevalue, 2, ".", "")) . " EUR";
$pirpamountofinterestvalue = str_replace(".", ",", number_format($pirpamountofinterestvalue, 2, ".", "")) . " EUR";
$pirpservicechargevalue = str_replace(".", ",", number_format($pirpservicechargevalue, 2, ".", "")) . " EUR";
$pirpeffectiveratevalue = str_replace(".", ",", number_format($pirpeffectiveratevalue, 2, ".", "")) . "%";
$pirpdebitratevalue = str_replace(".", ",", number_format($pirpdebitratevalue, 2, ".", "")) . "%";
$pirpdurationtimevalue = str_replace(".", ",", number_format($pirpdurationtimevalue, 0)) . " Monate";
$pirpamountofratesvalue = number_format($pirpamountofratesvalue, 0);
$pirpdurationmonthvalue = str_replace(".", ",", number_format($pirpdurationmonthvalue, 2, ".", "")) . " EUR";
$pirplastratevalue = str_replace(".", ",", number_format($pirplastratevalue, 2, ".", "")) . " EUR";
?>
<doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
         <html <?php echo HTML_PARAMS; ?>>
        <head>
            <meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
            <meta name="robots" content="noindex,nofollow">
            <title><?php echo TITLE; ?></title>
            <link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
            <script language="javascript" src="includes/general.js"></script>
            <script language="javascript">
                function hideMessageBox() {
                    document.getElementById('message').style.visibility = 'hidden';
                }
                function check_voucher(totalamount) {
                    var vouchertotal = 0;
                    var sub = document.getElementById('voucherAmount').value;
                    var subKomma = document.getElementById('voucherAmountKomma').value;
                    if (sub.match(/^[0-9]{1,4}$/i)) {
                        vouchertotal = parseInt(sub);
                        if (vouchertotal > totalamount) {
                            document.getElementById('voucherAmount').value = "0";
                        }
                        else {
                            if (subKomma.match(/^[0-9]{1,2}$/i)) {
                                vouchertotal = sub + "." + subKomma;
                                vouchertotal = parseFloat(vouchertotal);
                                totalamount = parseFloat(totalamount);
                                if (vouchertotal > totalamount) {
                                    document.getElementById('voucherAmountKomma').value = "00";
                                }
                            }
                            else {
                                document.getElementById('voucherAmountKomma').value = "00";
                            }
                        }
                    }
                    else {
                        document.getElementById('voucherAmount').value = "0";
                    }
                }
                function check(artid, totalamount, amount) {
                    var sub = document.getElementsByName(artid)[0].value;
                    if(sub.match(/^[0-9]{1,4}$/i)) {
                        if(totalamount < amount) {
                            document.getElementsByName(artid)[0].value = totalamount;
                            document.getElementById('message').style.visibility = 'visible';
                        }
                    }
                    else {
                        document.getElementsByName(artid)[0].value = totalamount;
                        document.getElementById('message').style.visibility = 'visible';
                    }
                }
                function checkShipped(artid, totalamount, amount) {
                    var sub = document.getElementsByName(artid)[1].value;
                    if(sub.match(/^[0-9]{1,4}$/i)) {
                        if(totalamount < amount) {
                            document.getElementsByName(artid)[1].value = totalamount;
                            document.getElementById('message').style.visibility = 'visible';
                        }
                    }
                    else {
                        document.getElementById('message').style.visibility = 'visible';
                        document.getElementsByName(artid)[1].value = totalamount;
                    }
                }
            </script>
            <style>
                .piRpRight{
                    text-align:right;
                }
            </style>
        </head>
        <body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF" onload="SetFocus();">
            <!-- header //-->
            <?php require(DIR_WS_INCLUDES . 'header.php'); ?>
            <!-- header_eof //-->
            <!-- body //-->
            <table border="0" width="100%" cellspacing="2" cellpadding="2">
                <tr>
                    <!-- body_text //-->
                    <td width="100%" valign="top" class="boxCenter">
                        <table border="0" width="100%" cellspacing="0" cellpadding="2">
                            <?php
                            if (isset($_GET['result'])) {
                                $result = $_GET['result'];
                                $message = $_GET['message'];
                                if ($result == 'SUCCESS') {
                                    $showMessage = showMessageSuccess($message);
                                    echo $showMessage;
                                } elseif ($result == 'ERROR') {
                                    $showMessage = showMessageError($message);
                                    echo $showMessage;
                                }
                            }
                            if ($pi_table_prefix == 'pi_ratepay_rate') {
                                ?>
                               <tr>
                                    <td>
                                        <table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
                                            <tr>
                                                <td class="pageHeading"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RATE_DETAILS; ?></td>
                                            </tr>
                                            <tr>
                                                <td><img width="100%" height="1" border="0" alt="" src="images/pixel_black.gif"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td valign="top">
                                        <!-- RatePAY Details START -->
                                          <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                            <tr>
                                                <td valign="top">
                                                    <!-- RatePAY Details START -->
                                                    <table style="width: 30%">
                                                        <tr>
                                                            <td class="main"><b>Gesamtbetrag:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirptotalamountvalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Barzahlungspreis:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpcashpaymentpricevalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Zinsbetrag:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpamountofinterestvalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Vertragsabschlussgeb&uuml;hr:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpservicechargevalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Effektiver Jahreszins:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpeffectiveratevalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Sollzinssatz pro Monat:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpdebitratevalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>Laufzeit:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpdurationtimevalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b><?php echo $pirpamountofratesvalue; ?>&nbsp;monatliche Raten a:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirpdurationmonthvalue; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="main"><b>zzgl. einer Abschlussrate a:</b></td>
                                                            <td class="main piRpRight"><?php echo $pirplastratevalue; ?></td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <?php } ?>
                                <tr>
                                    <td>
                                        <table border="0" width="100%" cellspacing="0" cellpadding="2" height="40">
                                            <tr>
                                                <td class="pageHeading"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DELIVER_CANCEL; ?></td>
                                            </tr>
                                            <tr>
                                                <td><img width="100%" height="1" border="0" alt="" src="images/pixel_black.gif"></td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                <td>
                                    <table border="0" width="100%" cellspacing="0" cellpadding="2">
                                        <form action="pi_ratepay_order_controller.php" method="POST"
                                              name="shipmentOrCancellation">
                                            <input type="hidden" name="payment" value="<?php echo $pi_table_prefix; ?>"/>
                                            <tr>
                                                <td valign="top"><!-- RatePAY Details START -->
                                                    <div id="message" style="visibility: hidden;"></div>
                                                    <table style="border: 1px solid #CCCCCC; width: 100%">
                                                        <tr class="dataTableHeadingRow">
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_QTY; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ART_ID; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_PRODUCT_NAME; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_PRICE_NETTO; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_TAX_AMOUNT; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_ROW_PRICE; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_ORDERED; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERED; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_CANCELED; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RETURNED; ?></th>
                                                        </tr>

                                                        <?php
                                                        $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($shopOrderID) . "' and  a.article_number != ''";
                                                        $query = xtc_db_query($sql);
                                                        $disableButtonsDevStorno = true;
                                                        $i = 0;
                                                        $price = 0;
                                                        while ($item = xtc_db_fetch_array($query)) {

                                                            $qty = ($item['ordered'] - $item['shipped'] - $item['cancelled']);
                                                            if ($qty > 0) {
                                                                $disableButtonsDevStorno = false;
                                                            }
                                                            $tax = ($item['products_tax']);
                                                            if ($item['article_name'] != 'pi-Merchant-Voucher' && $item['article_number'] != 'SHIPPING' && $item['article_number'] != 'DISCOUNT' && $item['article_number'] != 'COUPON') {
                                                                $price = $price + number_format($item['final_price'], 2, ".", "");
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="check('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['real_article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['products_price'] * 100 / (100 + $item['products_tax']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($tax, 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['final_price'], 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['ordered']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['cancelled']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_name'] == 'pi-Merchant-Voucher') {
                                                                $price = $price + number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", "");
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="check('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo PI_RATEPAY_VOUCHER; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format(0, 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['ordered']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['cancelled']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_number'] == 'SHIPPING') {
                                                                $shippingTaxAmount = getShippingTaxAmount($item['article_netUnitPrice'] + $shippingTaxAmount);
                                                                $price = $price + number_format(($item['article_netUnitPrice'] + $shippingTaxAmount) * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", "");
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="check('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'], 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format(getShippingTaxRate(), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format(($item['article_netUnitPrice'] + $shippingTaxAmount) * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['ordered']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['cancelled']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_number'] == 'DISCOUNT') {
                                                                $price = $price + number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", "");
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="check('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format(0, 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['ordered']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['cancelled']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                                $i++;
                                                            } elseif ($item['article_number'] == 'COUPON') {
                                                                $couponTax = (($item['article_netUnitPrice'] / (100 + $cab->getCouponTaxRate()) * 100) - $item['article_netUnitPrice']) * (-1);
                                                                $price = $price + number_format(($item['article_netUnitPrice'] + $couponTax) * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", "");
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="check('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($item['article_netUnitPrice'] * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format($cab->getCouponTaxRate() * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo number_format(($item['article_netUnitPrice'] + $couponTax) * ($item['ordered'] - $item['cancelled'] - $item['returned']), 2, ".", ""); ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['ordered']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['cancelled']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                                $i++;
                                                            }
                                                        }
                                                        ?>
                                                        <tr class="dataTableRow">
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent piRpRight"><?php echo number_format($price, 2, ".", ""); ?></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                            <td class="dataTableContent"></td>
                                                        </tr>
                                                    </table>
                                                    <div style="margin-top: 10px;"><?php if ($disableButtonsDevStorno) { ?>
                                                            <input type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERY; ?>"
                                                                   name="versenden" class="button" disabled="disabled">&nbsp; <input
                                                                   type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_CANCELLATION; ?>"
                                                                   name="stornieren" class="button" disabled="disabled"> <?php } else { ?>
                                                            <input type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERY; ?>"
                                                                   name="versenden" class="button">&nbsp; <input type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_CANCELLATION; ?>"
                                                                   name="stornieren" class="button"> <?php } ?></div>
                                                    <input type="hidden" name="oID"
                                                           value="<?php echo $shopOrderID; ?>"> 
                                                    <!-- RatePAY Details END -->
                                                </td>
                                            </tr>
                                        </form>
                                    </table>
                                    <br />
                                    <br />
                                    <table border="0" width="100%" cellspacing="0" cellpadding="2"
                                           style="width: 100%">
                                        <form action="pi_ratepay_order_controller.php" method="POST"
                                              name="retour">
                                            <input type="hidden" name="payment" value="<?php echo $pi_table_prefix; ?>"/>
                                            <tr>
                                                <td valign="top">
                                                    <table border="0" width="100%" cellspacing="0" cellpadding="2"
                                                           height="40">
                                                        <tr>
                                                            <td class="pageHeading"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RETOUR; ?></td>
                                                        </tr>
                                                        <tr>
                                                            <td><img width="100%" height="1" border="0" alt=""
                                                                     src="images/pixel_black.gif"></td>
                                                        </tr>
                                                    </table>
                                                    <!-- RatePAY Details START -->
                                                    <table style="border: 1px solid #CCCCCC; width: 100%">
                                                        <tr class="dataTableHeadingRow">
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_QTY; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ART_ID; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_PRODUCT_NAME; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DELIVERED; ?></th>
                                                            <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RETURNED; ?></th>
                                                        </tr>

                                                        <?php
                                                        $sql = "select * from " . $pi_table_prefix . "_orderdetails a left join orders_products b on b.orders_id = a.order_number and a.article_number = b.orders_products_id where  a.order_number = '" . xtc_db_input($shopOrderID) . "' and  article_number != ''";
                                                        $query = xtc_db_query($sql);
                                                        $disableButtonsRetoure = true;
                                                        while ($item = xtc_db_fetch_array($query)) {
                                                            $qty = $item['shipped'] - $item['returned'];
                                                            if ($qty > 0) {
                                                                $disableButtonsRetoure = false;
                                                            }
                                                            //if($item['shipped'] > 0){
                                                            if ($item['article_number'] == 'SHIPPING') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="checkShipped('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_number'] == 'DISCOUNT') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="checkShipped('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_number'] == 'COUPON') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="checkShipped('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_name'] != 'pi-Merchant-Voucher') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="checkShipped('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['real_article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_name']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } elseif ($item['article_name'] == 'pi-Merchant-Voucher') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><input type="text" size="3"
                                                                                                        maxlength="4" value="<?php echo $qty; ?>"
                                                                                                        name="<?php echo $item['article_number']; ?>"
                                                                                                        <?php
                                                                                                        if ($qty <= 0) {
                                                                                                            echo 'disabled';
                                                                                                        }
                                                                                                        ?>
                                                                                                        onkeyup="checkShipped('<?php echo $item['article_number']; ?>',<?php echo $qty; ?>,this.value);"
                                                                                                        onFocus="this.select();" /></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo PI_RATEPAY_VOUCHER; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['shipped']; ?></td>
                                                                    <td class="dataTableContent piRpRight"><?php echo $item['returned']; ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                            //}
                                                        }
                                                        ?>
                                                    </table>
                                                    <div style="margin-top: 10px;"><?php if ($disableButtonsRetoure) { ?>
                                                            <input type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RETOURE_BUTTON; ?>"
                                                                   name="retournieren" class="button" disabled="disabled"> <?php } else { ?>
                                                            <input type="submit"
                                                                   value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_RETOURE_BUTTON; ?>"
                                                                   name="retournieren" class="button"> <?php } ?></div>
                                                    <input type="hidden" name="oID"
                                                           value="<?php echo $shopOrderID; ?>"> <!-- RatePAY Details END -->
                                                </td>
                                            </tr>
                                        </form>
                                    </table>
                                    <br />
                                    <br />
                                    <form action="pi_ratepay_order_controller.php" method="POST"
                                          name="voucher">
                                        <input type="hidden" name="payment" value="<?php echo $pi_table_prefix; ?>"/>
                                        <table cellspacing="0" style="border: 1px solid #CCCCCC; width: 15%">
                                            <tbody>
                                                <tr class="dataTableHeadingRow">
                                                    <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_GOODWILL; ?></th>
                                                </tr>
                                                <tr class="dataTableHeadingRow">
                                                    <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_GOODWILL_AMOUNT; ?></th>
                                                </tr>
                                                <tr class="dataTableHeadingRow">
                                                    <td class="dataTableHeadingContent"><input id="voucherAmount"
                                                                                               type="text" value="0" maxlength="4" size="2"
                                                                                               name="voucherAmount"
                                                                                               onKeyUp="check_voucher(<?php echo $order['value']; ?>)"
                                                                                               onFocus="this.select();"> &nbsp;,&nbsp; <input
                                                                                               id="voucherAmountKomma" type="text" value="00" maxlength="2"
                                                                                               size="2" name="voucherAmountKomma"
                                                                                               onKeyUp="check_voucher(<?php echo $order['value']; ?>)"
                                                                                               onFocus="this.select();"> &nbsp; EUR</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <br>
                                        <input type="hidden" name="oID" value="<?php echo $shopOrderID; ?>">
                                        <input type="submit"
                                               value="<?php echo RATEPAY_ORDER_RATEPAY_ADMIN_CREATE_GOODWILL; ?>"
                                               name="gutschein" class="button"></form>
                                    <br />
                                    <br />
                                    <table border="0" width="100%" cellspacing="0" cellpadding="2"
                                           style="width: 100%">
                                        <tr>
                                            <td valign="top">
                                                <table border="0" width="100%" cellspacing="0" cellpadding="2"
                                                       height="40">
                                                    <tr>
                                                        <td class="pageHeading"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_HISTORY; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><img width="100%" height="1" border="0" alt=""
                                                                 src="images/pixel_black.gif"></td>
                                                    </tr>
                                                </table>
                                                <table style="border: 1px solid #CCCCCC; width: 100%">
                                                    <tr class="dataTableHeadingRow">
                                                        <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_QTY; ?></th>
                                                        <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ART_ID; ?></th>
                                                        <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_PRODUCT_NAME; ?></th>
                                                        <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_ACTION; ?></th>
                                                        <th class="dataTableHeadingContent"><?php echo RATEPAY_ORDER_RATEPAY_ADMIN_DATE; ?></th>
                                                    </tr>
                                                    <?php
                                                    $sql = "select * from " . $pi_table_prefix . "_history a, " . $pi_table_prefix . "_orderdetails b where a.order_number = '" . xtc_db_input($shopOrderID) . "' and a.order_number = b.order_number and a.article_number = b.article_number order by a.date asc";
                                                    $query = xtc_db_query($sql);
                                                    while ($item = xtc_db_fetch_array($query)) {
                                                        $sql = "select article_name from " . $pi_table_prefix . "_orderdetails where order_number = '" . xtc_db_input($shopOrderID) . "' and article_number = '" . xtc_db_input($item['article_number']) . "'";
                                                        $query1 = xtc_db_query($sql);
                                                        $name = xtc_db_fetch_array($query1);
                                                        if (!empty($item['article_number'])) {
                                                            if ($item['article_number'] == "SHIPPING" || $item['article_number'] == "DISCOUNT" || $item['article_number'] == "COUPON" || $item['article_name'] == 'pi-Merchant-Voucher') {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><?php echo $item['quantity']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['article_number']; ?></td>
                                                                    <td class="dataTableContent">
                                                                        <?php if ($item['article_name'] == 'pi-Merchant-Voucher'): ?>
                                                                            <?php echo PI_RATEPAY_VOUCHER; ?>
                                                                        <?php else: ?>
                                                                            <?php echo $item['article_name']; ?>
                                                                        <?php endif; ?>
                                                                    </td>

                                                                    <td class="dataTableContent">
                                                                        <?php
                                                                        if ($item['method'] == "shipped") {
                                                                            echo PI_RATEPAY_SHIPPED;
                                                                        } else if ($item['method'] == "returned") {
                                                                            echo PI_RATEPAY_RETURNED;
                                                                        } else if ($item['method'] == "cancelled") {
                                                                            echo PI_RATEPAY_CANCELLED;
                                                                        } else {
                                                                            echo PI_RATEPAY_CREDIT;
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td class="dataTableContent"><?php echo $item['date']; ?></td>
                                                                </tr>
                                                                <?php
                                                            } else {
                                                                ?>
                                                                <tr class="dataTableRow">
                                                                    <td class="dataTableContent"><?php echo $item['quantity']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $item['real_article_number']; ?></td>
                                                                    <td class="dataTableContent"><?php echo $name['article_name']; ?></td>
                                                                    <td class="dataTableContent">
                                                                        <?php
                                                                        if ($item['method'] == "shipped") {
                                                                            echo PI_RATEPAY_SHIPPED;
                                                                        } else if ($item['method'] == "returned") {
                                                                            echo PI_RATEPAY_RETURNED;
                                                                        } else if ($item['method'] == "cancelled") {
                                                                            echo PI_RATEPAY_CANCELLED;
                                                                        } else {
                                                                            echo PI_RATEPAY_CREDIT;
                                                                        }
                                                                        ?>
                                                                    </td>
                                                                    <td class="dataTableContent"><?php echo $item['date']; ?></td>
                                                                </tr>
                                                                <?php
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </table>
                                            </td>
                                        </tr>
                                        <!-- RatePAY Details END -->
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- body_eof //-->

            <!-- footer //-->
            <?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
            <!-- footer_eof //-->
            <br>
        </body>
    </html>
    <?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>