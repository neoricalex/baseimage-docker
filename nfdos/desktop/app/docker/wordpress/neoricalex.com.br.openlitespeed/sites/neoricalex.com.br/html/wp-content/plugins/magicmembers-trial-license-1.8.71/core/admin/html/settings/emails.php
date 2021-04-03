<!--messages-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<form name="frmemailtpls" id="frmemailtpls" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.settings&method=emails">
	<?php mgm_box_top(__('Email Configuration & Settings', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email From','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="from_email" value="<?php echo esc_attr($data['system_obj']->get_setting('from_email')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Email of the sender ','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email From Name','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="from_name" value="<?php echo esc_attr($data['system_obj']->get_setting('from_name')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Name of the sender (you or your site&rsquo;s name)','mgm'); ?></div></p>
    		</div>
		</div>

		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Email Content Type and Charset','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="email_content_type" class="width150px;">
					<?php echo mgm_make_combo_options(array('text/html','text/plain'), $data['system_obj']->get_setting('email_content_type'), MGM_VALUE_ONLY);?>
				</select>
				<select name="email_charset">
					<?php echo mgm_make_combo_options(array('UTF-8','ISO-8859-1','ISO-8859-9','windows-1254'), $data['system_obj']->get_setting('email_charset'), MGM_VALUE_ONLY);?>
				</select>										
				<p><div class="tips width90"><?php _e('Content Type and Charset of emails','mgm'); ?></div></p>
    		</div>
		</div>
		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Apply Changes Globally','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<select name="email_headers_global" class="width150px;">
					<?php echo mgm_make_combo_options(array('Y'=>__('Yes','mgm'),'N'=>__('No','mgm')), $data['system_obj']->get_setting('email_headers_global'), MGM_KEY_VALUE);?>
				</select>					
				<p><div class="tips width90"><?php _e('Apply Email settings behavior, use with caution, if selected as "Yes", it will apply the settings to all outgoing emails, otherwise to Magic Members emails only','mgm'); ?></div></p>
    		</div>
		</div>
		
 		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable emails from payment gateways?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_gateway_emails" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_gateway_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_gateway_emails" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_gateway_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off gateway emails. When "On", stops payment notification emails to the administrator.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable payment notification emails from modules?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_payment_notify_emails" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_payment_notify_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_payment_notify_emails" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_payment_notify_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off payment notification emails. When "On", stops payment notification emails to users.','mgm'); ?></div></p>
			</div>
		</div>
  		
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable remote post connection error emails notification ?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_remote_post_emails" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_remote_post_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_remote_post_emails" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_remote_post_emails'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off remote post connection error notification emails. When "On", stops remote post connection error notification emails to administrator.','mgm'); ?></div></p>
			</div>
		</div>
				
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Disable registration email if Buddypress is enabled?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="disable_registration_email_bp" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('disable_registration_email_bp'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="disable_registration_email_bp" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('disable_registration_email_bp'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off registration notification email if Buddypress plugin is enabled.','mgm'); ?></div></p>
			</div>
		</div>
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable new user email notification after user is active?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_new_user_email_notifiction_after_user_active" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_after_user_active'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_new_user_email_notifiction_after_user_active" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_after_user_active'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off new user email notification after user is active, When "On", receives the registration email when user become active only, When "Off" receives the registration email when user clicks the register button.','mgm'); ?></div></p>
			</div>
		</div>


		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Please check to receive password via email','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_new_user_email_notifiction_password" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_password'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_new_user_email_notifiction_password" value="N" <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_new_user_email_notifiction_password'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Please choose Yes/No to receive password via email. If "Yes" password will be sent via email. If "No" then no password will be sent via email.','mgm'); ?></div></p>
			</div>
		</div>




	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top(__('Account Expiration Reminder Email Configuration & Settings', 'mgm'));?>
	
	<div class="table">
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Days to Start','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="reminder_days_to_start" value="<?php echo esc_attr($data['system_obj']->get_setting('reminder_days_to_start')); ?>" size="50" />
				<p><div class="tips width90"><?php _e('Days to start the email i.e. 5 Days','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Incremental','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="checkbox" name="reminder_days_incremental" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('reminder_days_incremental'))) ? 'checked' : '';?>/>
				<input type="text" name="reminder_days_incremental_ranges" value="<?php echo esc_attr($data['system_obj']->get_setting('reminder_days_incremental_ranges')); ?>" <?php echo (bool_from_yn($data['system_obj']->get_setting('reminder_days_incremental')))?'':'disabled=true';?> />
				<p><div class="tips width90"><?php _e('Days range i.e. 5,3,1. With wrong value provided, default will be used','mgm'); ?></div></p>
    		</div>
		</div>
	</div>		
	<?php mgm_box_bottom();?>	
		<input class="button" type="submit" name="msgs_update" value="<?php _e('Save Email Templates & Settings','mgm'); ?>"/>
	<?php mgm_box_top(__('General Email Templates', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Subscription Expiration Email Reminder Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[reminder_email_template_subject]" id="setting_reminder_email_template_subject" size="100" value="<?php echo esc_attr(mgm_print_template_content('reminder_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for subscription expiration email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[reminder_email_template_body]" id="setting_reminder_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('reminder_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for subscription expiration email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Registration Email Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[registration_email_template_subject]" id="setting_registration_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('registration_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for registration email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[registration_email_template_body]" id="setting_registration_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('registration_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for registration email.','mgm'); ?></div></p>
  			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Admin New User Notification Email Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[new_user_notification_email_template_subject]" id="setting_new_user_notification_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('new_user_notification_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for admin new user notification email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[new_user_notification_email_template_body]" id="setting_new_user_notification_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('new_user_notification_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for admin new user notification email.','mgm'); ?></div></p>
  			</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Admin User Upgrade Notification Email Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[user_upgrade_notification_email_template_subject]" id="setting_user_upgrade_notification_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('user_upgrade_notification_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for admin user upgrade notification email.','mgm'); ?></div></p>				
    		</div>
		</div>		
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[user_upgrade_notification_email_template_body]" id="setting_user_upgrade_notification_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('user_upgrade_notification_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for admin user upgrade notification email.','mgm'); ?></div></p>
  			</div>
		</div>		
  	</div>	
	<?php mgm_box_bottom();?>
	<input class="button" type="submit" name="msgs_update" value="<?php _e('Save Email Templates & Settings','mgm'); ?>"/>	
	<?php mgm_box_top(__('Email Templates Short Codes', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Email Shortcodes','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<div>
					<ul>
						<li><strong>[name]</strong> = <?php _e('Displays user name.','mgm'); ?></li>
						<li><strong>[username]</strong> = <?php _e('Displays user username.','mgm'); ?></li>
						<li><strong>[password]</strong> = <?php _e('Displays user password.','mgm'); ?></li>
						<li><strong>[first_name]</strong> = <?php _e('Displays user first name.','mgm'); ?></li>
						<li><strong>[last_name]</strong> = <?php _e('Displays user last name.','mgm'); ?></li>
						<li><strong>[email]</strong> = <?php _e('Displays the user email.','mgm'); ?></li>
						<li><strong>[admin_email]</strong> = <?php _e('Displays the admin email.','mgm'); ?></li>
						<li><strong>[blogname]</strong> = <?php _e('Displays the blog anme.','mgm'); ?></li>							
						<li><strong>[siteurl]</strong> = <?php _e('Displays site url.','mgm'); ?></li>
						<li><strong>[loginurl]</strong> = <?php _e('Displayslogin url.','mgm'); ?></li>
						<li><strong>[login_url]</strong> = <?php _e('Displays login url.','mgm'); ?></li>
						<li><strong>[passwordlink]</strong> = <?php _e('Displays password reset link.','mgm'); ?></li>
						<li><strong>[reason]</strong> = <?php _e('Displays the reason.','mgm'); ?></li>
						<li><strong>[expire_date]</strong> = <?php _e('Displays user expire date.','mgm'); ?></li>
						<li><strong>[post_title]</strong> = <?php _e('Displays the purchase post title.','mgm'); ?></li>
						<li><strong>[purchase_cost]</strong> = <?php _e('Displays the purchase post cost.','mgm'); ?></li>
						<li><strong>[amount]</strong> = <?php _e('Displays the membership amount.','mgm'); ?></li>
						<li><strong>[currency_sign]</strong> = <?php _e('Displays the currency symbol.','mgm'); ?></li>
						<li><strong>[membership_type]</strong> = <?php _e('Displays the membership type.','mgm'); ?></li>
						<li><strong>[date]</strong> = <?php _e('Displays the date.','mgm'); ?></li>	
						<li><strong>[ip_address]</strong> = <?php _e('Displays the member ip_address.','mgm'); ?></li>	
					</ul>
				</div>
				<p>
					<div class="tips width95"><?php _e('In this section you can change how the messages will display inside the email templates . You are free to use HTML coding and special tags.','mgm'); ?>:</div>
				</p>

    		</div>
		</div>
  	</div>	
	<?php mgm_box_bottom();?>
	
	<?php mgm_box_top(__('Payment Email Templates', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Success Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_success_email_template_subject]" id="setting_payment_success_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_success_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment success email.','mgm'); ?></div></p>				
    		</div>
		</div>
		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body (Post Purchase Payment)','mgm'); ?>:</b><br />
				<textarea name="setting[payment_success_email_template_body]" id="setting_payment_success_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_success_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for post purchase payment success email.','mgm'); ?></div></p>
    		</div>
		</div>
		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body (Subscription Payment)','mgm'); ?>:</b><br />
				<textarea name="setting[payment_success_subscription_email_template_body]" id="setting_payment_success_subscription_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_success_subscription_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for subscription payment success email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Failed Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_failed_email_template_subject]" id="setting_payment_failed_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_failed_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment failed email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[payment_failed_email_template_body]" id="setting_payment_failed_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_failed_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for payment failed email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Active Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_active_email_template_subject]" id="setting_payment_active_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_active_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment active email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[payment_active_email_template_body]" id="setting_payment_active_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_active_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for payment active email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Pending Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_pending_email_template_subject]" id="setting_payment_pending_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_pending_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment pending email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[payment_pending_email_template_body]" id="setting_payment_pending_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_pending_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for payment pending email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Error Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_error_email_template_subject]" id="setting_payment_error_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_error_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment error email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[payment_error_email_template_body]" id="setting_payment_error_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_error_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for payment error email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Payment Status Unknown Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[payment_unknown_email_template_subject]" id="setting_payment_unknown_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('payment_unknown_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for payment status unknown email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[payment_unknown_email_template_body]" id="setting_payment_unknown_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('payment_unknown_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for payment status unknown email.','mgm'); ?></div></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Subscription Cancelled Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[subscription_cancelled_email_template_subject]" id="setting_subscription_cancelled_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('subscription_cancelled_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for subscription cancelled email.','mgm'); ?></div></p>				
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[subscription_cancelled_email_template_body]" id="setting_subscription_cancelled_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('subscription_cancelled_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for subscription cancelled email.','mgm'); ?></div></p>
    		</div>
		</div>
	</div>	

	<?php mgm_box_bottom();?>
	<input class="button" type="submit" name="msgs_update" value="<?php _e('Save Email Templates & Settings','mgm'); ?>"/>
	<?php mgm_box_top(__('Retrieve Password Email Templates', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Password Link Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[retrieve_password_email_template_subject]" id="retrieve_password_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('retrieve_password_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for password link email.','mgm'); ?></div></p>							
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[retrieve_password_email_template_body]" id="retrieve_password_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('retrieve_password_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for password email.','mgm'); ?></div></p>			
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Retrieve Password Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[lost_password_email_template_subject]" id="lost_password_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('lost_password_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for retrieve password email.','mgm'); ?></div></p>							
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[lost_password_email_template_body]" id="lost_password_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('lost_password_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for retrieve password email.','mgm'); ?></div></p>			
    		</div>
		</div>
  		<div class="row">
    		<div class="cell"></div>
		</div>
	</div>

	<?php mgm_box_bottom();?>
	<?php mgm_box_top(__('Gift a Post/Page Email Templates', 'mgm'));?>
	<div class="table">
  		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Gift a Post/Page Template','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<b><?php _e('Subject','mgm'); ?>:</b><br />
				<input type="text" name="setting[gift_post_email_template_subject]" id="gift_post_email_template_subject"  size="100" value="<?php echo esc_attr(mgm_print_template_content('gift_post_email_template_subject','emails')); ?>" />
				<p><div class="tips width95"><?php _e('Subject for gift a post/page link email.','mgm'); ?></div></p>							
    		</div>
		</div>
  		<div class="row">
    		<div class="cell paddingtop10px">
				<b><?php _e('Body','mgm'); ?>:</b><br />
				<textarea name="setting[gift_post_email_template_body]" id="gift_post_email_template_body" class="height200px width820px"><?php echo mgm_print_template_content('gift_post_email_template_body','emails'); ?></textarea>
				<p><div class="tips width95"><?php _e('Body for gift a post/page email.','mgm'); ?></div></p>			
    		</div>
		</div>
	</div>
	<?php mgm_box_bottom();?>		
	<p class="submit">
		<input class="button" type="submit" name="msgs_update" value="<?php _e('Save Email Templates & Settings','mgm'); ?>"/>
	</p>
</form>
<script type="text/javascript">
<!--
	jQuery(document).ready(function(){
		
		// check bind
		jQuery("#frmemailtpls :checkbox[name='reminder_days_incremental']").bind('click',function(){
			jQuery("#frmemailtpls :input[name='reminder_days_incremental_ranges']").attr('disabled',!jQuery(this).prop('checked'));
		});		
				
		// editor
		jQuery("#frmemailtpls textarea[id]").each(function(){				
			new nicEditor({fullPanel : true, iconsPath: '<?php echo MGM_ASSETS_URL?>js/nicedit/nicEditorIcons.gif'}).panelInstance(jQuery(this).attr('id')); 			
		});
		// add : form validation
		jQuery("#frmemailtpls").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmemailtpls").ajaxSubmit({type: "POST",										  
				  dataType: 'json',		
				  iframe: false,		
				  beforeSerialize: function($form) { 					
					// only on IE
					if(jQuery.browser.msie){
						jQuery($form).find("textarea[id]").each(function(){								
							jQuery(this).val(nicEditors.findEditor(jQuery(this).attr('id')).getContent()); 
						});										
					}
				  },							 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmemailtpls', {status:'running', message:'<?php echo esc_js(__('Processing','mgm'));?>...'});							
					// focus
					jQuery.scrollTo('#frmemailtpls',400);	
				  },
				  success: function(data){	
					// message																				
					mgm_show_message('#frmemailtpls', data);																					
				  }}); // end   		
				return false;											
			},			
			errorClass: 'invalid'
		});							  
	});	
//-->
</script>