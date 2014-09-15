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



/**
 * Include script to extend the admin order overview with some RatPAY specific buttons
 */
if ($oInfo->payment_method == 'ratepay_rechnung' || $oInfo->payment_method == 'ratepay_rate' || $oInfo->payment_method == 'ratepay_sepa') {
    if (preg_match('/modified/', strtolower(PROJECT_VERSION))) {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="' . xtc_href_link('ratepay_order.php', 'oID=' . $oInfo->orders_id) . '">RatePAY</a>');
    } else {
        $contents[] = array(
            'align' => 'center',
            'text' => '<a class="button" href="' . xtc_href_link('ratepay_order.php', 'oID=' . $oInfo->orders_id) . '">RatePAY</a>');
    }
}