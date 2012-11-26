<?php

if (CURRENT_TEMPLATE == 'xtc5') {
    if ($oInfo->payment_method == 'pi_ratepay_rechnung') {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_admin_xtc_modified.php?oID=' . $oInfo->orders_id . '&payment=' . $oInfo->payment_method . '">RatePAY</a>');
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_rechnung_print_order.php?oID=' . $oInfo->orders_id . '" target="_blanc">RatePAY Rechnung</a>');
    } else if ($oInfo->payment_method == 'pi_ratepay_rate') {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_admin_xtc_modified.php?oID=' . $oInfo->orders_id . '&payment=' . $oInfo->payment_method . '">RatePAY</a>');
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_rate_print_order.php?oID=' . $oInfo->orders_id . '" target="_blanc">RatePAY Rechnung</a>');
    }
}
if (CURRENT_TEMPLATE == 'xtc4') {
    if ($oInfo->payment_method == 'pi_ratepay_rechnung') {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_admin.php?oID=' . $oInfo->orders_id . '&payment=' . $oInfo->payment_method . '">RatePAY</a>');
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_rechnung_print_order.php?oID=' . $oInfo->orders_id . '" target="_blanc">RatePAY Rechnung</a>');
    } else if ($oInfo->payment_method == 'pi_ratepay_rate') {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_admin.php?oID=' . $oInfo->orders_id . '&payment=' . $oInfo->payment_method . '">RatePAY</a>');
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="pi_ratepay_rate_print_order.php?oID=' . $oInfo->orders_id . '" target="_blanc">RatePAY Rechnung</a>');
    }
}
?>