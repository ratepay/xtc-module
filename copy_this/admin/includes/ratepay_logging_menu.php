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
 * Include script to extend the admin side bar with a RatePAY Logging entry
 */
if (CURRENT_TEMPLATE == 'xtc4') {
    if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['csv_backend'] == '1')) {
        echo '<a href="' . xtc_href_link('ratepay_logging.php') . '" class="menuBoxContentLink"> -RatePAY Logging</a><br>';
    }
}
if (CURRENT_TEMPLATE == 'xtc5') {
    if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['csv_backend'] == '1')) {
        echo '<li><a href="' . xtc_href_link('ratepay_logging.php') . '" class="menuBoxContentLink"> -RatePAY Logging</a></li>';
    }
}