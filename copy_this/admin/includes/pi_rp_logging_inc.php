<?php
if(CURRENT_TEMPLATE == 'xtc5'){
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['csv_backend'] == '1')) echo '<li><a href="' . xtc_href_link('pi_rp_logging_xtc_modified.php') . '" class="menuBoxContentLink"> -RatePAY Logging</a></li>';
}
if(CURRENT_TEMPLATE == 'xtc4'){
	if (($_SESSION['customers_status']['customers_status_id'] == '0') && ($admin_access['csv_backend'] == '1')) echo '<a href="' . xtc_href_link('pi_rp_logging.php') . '" class="menuBoxContentLink"> -RatePAY Logging</a><br>';
}
?>