
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

var RpCheckout = {  
    ratepayOnLoad : function()
    {
        var paymentRadioButtons = document.getElementsByName("payment");
        for (var i = 0; i < paymentRadioButtons.length; i++) {
            paymentRadioButtons[i].setAttribute("onclick","RpCheckout.checkRpPayment();");
        }

        if(paymentRadioButtons.length == 1) {
            paymentRadioButtons[0].checked = true;
        }

        RpCheckout.checkRpPayment();
    },
    checkRpPayment : function()
    {
        var paymentRadioButtons = document.getElementsByName("payment");
        var element = document.getElementsByClassName("conditions")[0].parentNode.parentNode.parentNode.parentNode;
        for (var i = 0; i < paymentRadioButtons.length; i++) {
            var payment = paymentRadioButtons[i].value;
            if (paymentRadioButtons[i].checked && RpCheckout.isRpPayment(payment)) {
                element.style.display = "none";
                document.getElementById("checkout_payment").conditions.checked = true;
            } else if(paymentRadioButtons[i].checked && !RpCheckout.isRpPayment(payment)){
                element.style.display = "block";
                document.getElementById("checkout_payment").conditions.checked = false;
            }
        }
    },
    isRpPayment : function (payment)
    {
        var payments = new Array('ratepay_rate', 'ratepay_rechnung');
        return RpCheckout.inArray(payment, payments)
    },
    inArray : function(item, arr) 
    {
        for(p = 0; p < arr.length; p++){ 
            if (item == arr[p]) {
                return true;
            }
        }
        
        return false;
    }
}