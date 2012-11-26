/**
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @package pi_ratepay_rate_calculator
 * Code by PayIntelligent GmbH  <http://www.payintelligent.de/>
 */

function switchRateOrRuntime(mode) {
	if (mode == 'rate') {
		document.getElementById('pirptabfirst').className = 'pirptabfirst pirpleft pirpbgblue';
		document.getElementById('pirptabsecond').className = 'pirptabsecond pirpright';
		
		document.getElementById('pirpspanrate').className = '';
		document.getElementById('pirpspanruntime').className = 'pirpactive';

		document.getElementById('pirpcontent').style.display = 'none';
		document.getElementById('pirpcontent2').style.display = 'block';
		
		document.getElementById('pirptop-text-runtime').style.display = 'none';
		document.getElementById('pirptop-text-rate').style.display = 'block';

	} else if (mode == 'runtime') {

		document.getElementById('pirptabfirst').className = 'pirptabfirst pirpleft';
		document.getElementById('pirptabsecond').className = 'pirptabsecond pirpbgblue pirpright';
		
		document.getElementById('pirpspanrate').className = 'pirpactive';
		document.getElementById('pirpspanruntime').className = '';
		
		document.getElementById('pirpcontent').style.display = 'block';
		document.getElementById('pirpcontent2').style.display = 'none';
		
		document.getElementById('pirptop-text-runtime').style.display = 'block';
		document.getElementById('pirptop-text-rate').style.display = 'none';
	}

}