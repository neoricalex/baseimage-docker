	<?php $recaptcha = mgm_get_class('recaptcha');?>
	<p><div class="table">
		<div class="row headrow">
			<div class="cell theadDivCell">
				<?php _e('reCaptcha configuration','mgm');?>
			</div>
		</div>	
  		<div class="row">
    		<div class="cell width25">
				<p><b><?php _e('reCaptcha Public Key','mgm');?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell width75">
				<p><input type="text" name="recaptcha_public_key" value="<?php echo esc_attr($data['system_obj']->get_setting('recaptcha_public_key')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA Public Key. Generate your key at <br /><b>'.$recaptcha->recaptcha_get_signup_url().'</b>','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCaptcha Private Key','mgm');?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_private_key" value="<?php echo esc_attr($data['system_obj']->get_setting('recaptcha_private_key')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA Private Key. Generate your key at <br /><b>'.$recaptcha->recaptcha_get_signup_url().'</b>','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA API Server Url','mgm');?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_api_server" value="<?php echo esc_attr($data['system_obj']->get_setting('recaptcha_api_server')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA API Server Url.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA API Secure Server Url','mgm');?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_api_secure_server" value="<?php echo esc_attr($data['system_obj']->get_setting('recaptcha_api_secure_server')); ?>" size="60" /></p>
				<p><div class="tips width90"><?php _e('reCAPTCHA API Secure Server Url.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('reCAPTCHA Verify Server Url','mgm');?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><input type="text" name="recaptcha_verify_server" value="<?php echo esc_attr($data['system_obj']->get_setting('recaptcha_verify_server')); ?>" size="60" /></p>	
				<p><div class="tips width90"><?php _e('reCAPTCHA Verify Server Url','mgm'); ?></div></p>
    		</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable NO CAPTCHA reCAPTCHA','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="no_captcha_recaptcha" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('no_captcha_recaptcha'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="no_captcha_recaptcha" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('no_captcha_recaptcha'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off NO CAPTCHA reCAPTCHA.','mgm'); ?></div></p>
			</div>
		</div>
	</div>
	</p>
