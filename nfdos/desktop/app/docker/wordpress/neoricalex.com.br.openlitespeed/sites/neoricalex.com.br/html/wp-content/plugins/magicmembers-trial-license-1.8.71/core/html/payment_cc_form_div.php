<div id="mgm-billing-info-wrap" <?php if( isset($data['card_id']) and !empty($data['card_id']) ):?> style="display:none" <?php endif;?>>
	<div id ="mgm_creditard_info" class="mgm-billing-info">
		<p id ="mgm_pid_card_holder_name">
			<label for="mgm_card_holder_name"><?php _e('Name on Card','mgm');?> <span class="required">*</span></label><br />
			<!--<input autocomplete="off" type="text" value="<?php echo $data['name']?>" name="mgm_card_holder_name" id="mgm_card_holder_name" size="40" maxlength="150" class="input {required: true, minlength:5, maxlength:150}" />-->
				<input autocomplete="off" type="text" value="<?php echo $data['name']?>" name="mgm_card_holder_name" id="mgm_card_holder_name" size="40" minlength="5" maxlength="150" required />			
		</p>
		<p id ="mgm_pid_card_number">
			<label for="mgm_card_number"><?php _e('Card Number','mgm');?> <span class="required">*</span></label><br />
			<!--<input autocomplete="off" type="text" value="" name="mgm_card_number" id="mgm_card_number" size="40" maxlength="16" class="input {required: true, minlength:13, maxlength:16, digits:true}" />-->
				<input autocomplete="off" type="text" value="" name="mgm_card_number" id="mgm_card_number" size="40" number="true" minlength="13" maxlength="16" required />
		</p>	
		<p id ="mgm_pid_card_expiry">
		<label for="mgm_card_expiry"><?php _e('Card Expiry Date','mgm');?> <span class="required">*</span></label><br />							
			<select name="mgm_card_expiry_month" id="mgm_card_expiry_month" class="select width70px">
				<?php echo mgm_make_combo_options(array('01','02','03','04','05','06','07','08','09','10','11','12'), date('m'), MGM_VALUE_ONLY);?>
			</select>
			<select name="mgm_card_expiry_year" id="mgm_card_expiry_year" class="select width100px">
				<?php echo mgm_make_combo_options(range(date('Y')-1, date('Y')+10), date('Y'), MGM_VALUE_ONLY);?>
			</select>
		</p>		
		<p id ="mgm_pid_card_code">
			<label for="mgm_card_code"><?php _e('Card Security Code','mgm');?> <span class="required">*</span></label><br />
			<!--<input autocomplete="off" type="text" size="4" value="" name="mgm_card_code" id="mgm_card_code" maxlength="4" class="input {required: true, minlength:3, maxlength:4, digits:true}"/>-->
			<input autocomplete="off" type="text" value="" name="mgm_card_code" id="mgm_card_code" size="4" number="true" minlength="3" maxlength="4" required />
		</p>
		<p id ="mgm_pid_card_type">
			<span id="mgm_card_logo"></span>	
			<label for="mgm_card_type"><?php _e('Card Type','mgm');?> <span class="required">*</span></label><br />
			<select name="mgm_card_type" id="mgm_card_type" class="select width250px">
				<?php echo mgm_make_combo_options($data['card_types'], '', MGM_KEY_VALUE);?>
			</select>
		</p>
		<?php do_action('mgm_creditard_info_additional', $data['code']);?>
	</div>	
	<div id ="mgm_billing_info" class="mgm-billing-info">
		<?php echo $data['billing_info']?>
	</div>
</div>

<p class="btnwrap">
	<?php if( isset($data['card_id']) and !empty($data['card_id'])):?>
	<input class="button" type="button" value="<?php _e('Change Billing','mgm');?>" onClick="mgm_change_cc_billing_info('<?php echo $data['code']?>')">
	<input type="hidden" name="using_saved_card" id="using_saved_card" value="true">
	<?php endif;?>
	<input class="button" type="submit" value="<?php _e('Submit','mgm');?>" onClick="return mgm_submit_cc_payment('<?php echo $data['code']?>')">
	<input class="button" type="button" value="<?php _e('Cancel','mgm');?>" onClick="mgm_cancel_cc_payment('<?php echo $data['cancel_url']?>')">
</p>
<div id="mgm_cc_processing" class="mgm_displaynone">
	<img src="<?php echo includes_url('images') ?>/wpspin.gif" align="absmiddle" border="0" style="border:0"/> <?php _e('Processing payment, do not refresh page....', 'mgm');?>			
</div>