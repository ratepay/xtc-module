<?php
/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */

include_once 'includes/application_top.php';
require_once 'ext/modules/payment/pi_ratepay_rate/ratenrechner/php/path.php';
require_once $pi_ratepay_rate_calc_path . 'php/PiRatepayRateCalc.php';
$pi_calculator = new PiRatepayRateCalc();
$pi_calculator->unsetData();
$pi_config = $pi_calculator->getRatepayRateConfig();
$pi_monthAllowed = $pi_config['month_allowed'];
$pi_monthAllowedArray = explode(',',$pi_monthAllowed);

$pi_amount = $pi_calculator->getRequestAmount();
$pi_language = $pi_calculator->getLanguage();
if($pi_language == "DE") {
	require_once $pi_ratepay_rate_calc_path . 'php/languages/german.php';
	$pi_currency = 'EUR';
	$pi_decimalSeperator = ',';
	$pi_thousandSeperator = '.';
} else {
	require_once $pi_ratepay_rate_calc_path . 'php/languages/english.php';
	$pi_currency = 'EUR';
	$pi_decimalSeperator = '.';
	$pi_thousandSeperator = ',';
}
$pi_amount = number_format($pi_amount,2,$pi_decimalSeperator,$pi_thousandSeperator);
if($pi_calculator->getErrorMsg() != '') {
	if($pi_calculator->getErrorMsg() == 'serveroff') {
		echo "<div>" . $pi_lang_server_off . "</div>";
	} else {
		echo "<div>" . $pi_lang_config_error_else . "</div>";
	}
} else {
	?>

<div id="pirpmain-cont">
<div class="pirpheader"><img
	src="<?php echo $pi_ratepay_rate_calc_path;?>images/ratepay-logo.png"
	class="pirpleft" width="183" height="39" alt="" />
<div class="pirptop-right"><span class="pirpinput-text"><?php echo $pi_lang_cash_payment_price;?></span>
<input name="" class="pirpinput-amount pirpleft" id="pirpamount"
	value="<?php echo $pi_amount;?>" type="text" /> <span
	class="pirpcurrency-text">&euro;</span></div>
</div>

<p id='pirptop-text-runtime' class="pirptop-text" style='display: none'><?php echo $pi_lang_hint_runtime_1;?><br />
	<?php echo $pi_lang_hint_runtime_2;?></p>

<p id='pirptop-text-rate' class="pirptop-text" ><?php echo $pi_lang_hint_rate_1;?><br />
<?php echo $pi_lang_hint_rate_2;?></p>

<div class="pirpcontent-tabs">
<div class="pirptabfirst pirpleft" id='pirptabfirst'
	onClick="switchRateOrRuntime('rate');"><img
	src="<?php echo $pi_ratepay_rate_calc_path;?>images/text-icon-blue.jpg"
	class="pirpleft" width="15" height="16" alt="" /> <span
	id='pirpspanrate'><?php echo $pi_lang_insert_wishrate;?><br />
	<?php echo $pi_lang_calculate_runtime;?></span> <input name=""
	value="<?php echo $pi_lang_calculate_runtime;?>" type="button"
	class="pirpinp-btn" /></div>
<div class="pirptabsecond pirpbgblue pirpright" id='pirptabsecond'
	onClick="switchRateOrRuntime('runtime');"><img
	src="<?php echo $pi_ratepay_rate_calc_path;?>images/text-icon-blue.jpg"
	class="pirpleft" width="15" height="16" alt="" /> <span
	id='pirpspanruntime' class="pirpactive"><?php echo $pi_lang_choose_runtime;?><br />
	<?php echo $pi_lang_calculate_rate;?></span> <input name=""
	value="<?php echo $pi_lang_calculate_rate;?>" type="button"
	class="pirpinp-btn" /></div>
</div>

<div class='pirpcontenttop' id='pirpcontent' style='display: none;'>
<div class="pirparrow-right"><img
	src="<?php echo $pi_ratepay_rate_calc_path;?>images/arrow-right.png"
	width="12" height="39" alt="" /></div>
<p><?php echo $pi_lang_hint_runtime_1;?><br />
	<?php echo $pi_lang_hint_runtime_2;?></p>
<div class="pirpsubmit-area"><span class="pirpinput-text"><?php echo $pi_lang_please . " " . $pi_lang_insert_runtime;?>:</span>
<select id="runtime" class="pirpselect-list">
<?php foreach($pi_monthAllowedArray as $pi_month) {
	echo '<option value="' . $pi_month . '">' . $pi_month . ' ' . $pi_lang_months . '</option>';
}?>
</select><input name=""
	onclick="piRatepayRateCalculatorAction('runtime')"
	value="<?php echo $pi_lang_calculate_rate;?>" type="button"
	class="pirpinp-btn" /></div>
</div>

<div class='pirpcontenttop' id='pirpcontent2'>
<div class="pirparrow"><img
	src="<?php echo $pi_ratepay_rate_calc_path;?>images/arrow.png"
	width="12" height="39" alt="" /></div>
<p><?php echo $pi_lang_hint_rate_1;?><br />
<?php echo $pi_lang_hint_rate_2;?></p>
<div class="pirpmain-submit-section pirpleft">
<div class="pirpsubmit-area"><span class="pirpinput-text"><?php echo $pi_lang_please . " " . $pi_lang_insert_wishrate;?>:</span>
<input name="" id="rate" class="pirpinput-amount left" type="text" /> <span
	class="pirpcurrency-text"></span>&euro;</div>
<input name="" onclick="piRatepayRateCalculatorAction('rate')"
	value="<?php echo $pi_lang_calculate_runtime;?>" class="pirpsubmit"
	type="button" /></div>
</div>

<div id="pirpresult" class="pirpcontentbot"></div>
<?php
}
?>
