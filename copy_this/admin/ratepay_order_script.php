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
 * RatePAY order script calls the right controller action for RatePAY order operations
 */
require_once ('includes/application_top.php');
require_once ('../includes/classes/ratepay/helpers/Data.php');
require_once ('../includes/classes/ratepay/helpers/Globals.php');
require_once ('../includes/classes/ratepay/controllers/OrderController.php');
if (rpGlobals::hasPostEntry('order_number')) {
    if (rpGlobals::hasPostEntry('ship')) {
        rpOrderController::deliverAction();
    } elseif (rpGlobals::hasPostEntry('cancel')) {
        rpOrderController::cancelAction();
    } elseif (rpGlobals::hasPostEntry('refund')) {
        rpOrderController::refundAction();
    } elseif(rpGlobals::hasPostEntry('credit')){
        rpOrderController::creditAction();
    } else {
        die('Operation not found!');
    }
} else {
    die('Missing post param "order_number"!');
}