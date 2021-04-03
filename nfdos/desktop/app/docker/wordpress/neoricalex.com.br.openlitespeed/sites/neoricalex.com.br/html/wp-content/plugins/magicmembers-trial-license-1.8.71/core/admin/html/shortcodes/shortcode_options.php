<?php
// url
// $url = MGM_ASSETS_URL.'js/editor/plugins/shortcodes/shortcodes.php';
$main = MGM_CORE_DIR . 'assets/js/editor/plugins/shortcodes/php/main.php';
// html	
$html = '';
	
$html .= '
	<fieldset>
		<legend>' . __('Content Protection Shortcodes', 'mgm') . '</legend>
		<table cellpadding="0" cellspacing="0" border="0">
			<tr id="protectshortcode">
				<td>' . __('Select Shortcode : ','mgm') . '</td>
				<td>
					<select name="protect_shortcode" id="protect_shortcode" class="width130px;">' . mgm_make_combo_options($data['protect_shortcodes'],'',2) . '</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			
			<tr id="protectargument" style="display:none;">
				<td>' . __('Shortcode Argument : ','mgm') . '</td>
				<td>
					<input type="text" name="protect_shortcode_argument" value="" id="protect_shortcode_argument"/>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input class="button" type="button" id="protect_shortcode_insert" name="protect_shortcode_insert" value="' . __('Insert','mgm') . '" /></td>
			</tr>						
		</table>
	</fieldset>
	<hr/>
	<fieldset>
		<legend>' . __('Purchase Shortcodes', 'mgm') . '</legend>
		<table cellpadding="0" cellspacing="0" border="0">
			<tr  id="purchaseshortcode">
				<td>' . __('Select Shortcode : ','mgm') . ':</td>
				<td>
					<select name="purchase_shortcode" id="purchase_shortcode" class="width130px;">' . mgm_make_combo_options($data['purchase_shortcodes'],'',2) . '</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			
			<tr id="purchaseargument" style="display:none;">
				<td>' . __('Shortcode Argument : ','mgm') . '</td>
				<td>
					<input type="text" name="purchase_shortcode_argument" value="" id="purchase_shortcode_argument"/>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input class="button" type="button" id="purchase_shortcode_insert" name="purchase_shortcode_insert" value="' . __('Insert','mgm') . '" /></td>
			</tr>						
		</table>
	</fieldset>
	<hr/>
	<fieldset>
		<legend>' . __('Other Shortcodes', 'mgm') . '</legend>
		<table cellpadding="0" cellspacing="0" border="0">
			<tr id="othershortcode">
				<td>' . __('Select Shortcode : ','mgm') . ':</td>
				<td>
					<select name="other_shortcode" id="other_shortcode" class="width130px;">' . mgm_make_combo_options($data['other_shortcodes'],'',2) . '</select>
				</td>
			</tr>
			<tr>
				<td colspan="2">&nbsp;</td>
			</tr>
			
			<tr id="otherargument" style="display:none;">
				<td>' . __('Shortcode Argument : ','mgm') . '</td>
				<td>
					<input type="text" name="other_shortcode_argument" value="" id="other_shortcode_argument"/>
				</td>
			</tr>
			<tr>
				<td>&nbsp;</td>
				<td><input class="button" type="button" id="other_shortcode_insert" name="other_shortcode_insert" value="' . __('Insert','mgm') . '" /></td>
			</tr>						
		</table>
	</fieldset><br/>';

	// set
	$mgm_assets_url = MGM_ASSETS_URL;
	$includes_url = includes_url();
	$admin_url = admin_url();
	$blog_version = get_bloginfo('version');
	$shortcodes = $html;
	// include
	include_once($main);