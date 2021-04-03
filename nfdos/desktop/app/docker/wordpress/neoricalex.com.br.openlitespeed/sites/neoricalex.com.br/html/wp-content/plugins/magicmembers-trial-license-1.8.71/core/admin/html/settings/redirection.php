<!--redirection-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<form name="frmsetrd" id="frmsetrd" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.settings&method=redirection">
	<?php mgm_box_top(__('Redirection Settings', 'mgm'));?>
	<div class="table">
		<div class="row">
    		<div class="cell">
				<input type="checkbox" id="enable_login_redirect_url" value="Y" <?php echo ($data['system_obj']->get_setting('login_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect after login','mgm'); ?>:</b>		
			</div>
		</div>
  		<div class="row">
    		<div class="cell <?php echo ($data['system_obj']->get_setting('login_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm');?>: <input type="text" name="login_redirect_url" id="login_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('login_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect after successful login, URL short codes : [username]-Displays user username.','mgm'); ?></div></p>
			</div>
		</div>

		<div class="row">
    		<div class="cell">
				<p><input type="checkbox" id="enable_logout_redirect_url" value="Y"  <?php echo ($data['system_obj']->get_setting('logout_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect after logout','mgm'); ?>:</b></p>			
			</div>
		</div>
  		<div class="row">
    		<div class="cell <?php echo ($data['system_obj']->get_setting('logout_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm');?>: <input type="text" name="logout_redirect_url" id="logout_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('logout_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect after successful logout.','mgm'); ?></div></p>
			</div>
		</div>

  		<div class="row">
    		<div class="cell">
				<input type="checkbox" id="enable_category_access_redirect_url" value="Y" <?php echo ($data['system_obj']->get_setting('category_access_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect if category access denied','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo ($data['system_obj']->get_setting('category_access_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm');?>: <input type="text" name="category_access_redirect_url" id="category_access_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('category_access_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect if access denied to a category.','mgm'); ?></div></p>
			</div>
		</div>
		
		<div class="row">
    		<div class="cell">
				<input type="checkbox" id="enable_buddypress_access_redirect_url" value="Y" <?php echo ($data['system_obj']->get_setting('buddypress_access_redirect_url') != '') ? 'checked' : '';?>/>
				<b><?php _e('Redirect if buddypress pages access denied','mgm'); ?>:</b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo ($data['system_obj']->get_setting('buddypress_access_redirect_url') == '') ? 'displaynone' : '';?>">
				<?php _e('URL', 'mgm');?>: <input type="text" name="buddypress_access_redirect_url" id="buddypress_access_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('buddypress_access_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect if access denied to a buddypress pages.','mgm'); ?></div></p>
			</div>
		</div>
		
  		<div class="row">
    		<div class="cell">
				<input type="checkbox" name="enable_autologin" id="enable_autologin" value="Y" <?php echo (bool_from_yn($data['system_obj']->get_setting('enable_autologin'))) ? 'checked' : '';?>/>
				<b><?php _e('Enable auto login after register','mgm'); ?>:</b>
				<p><div class="tips width90"><?php _e('Turn On/Off auto login after register. When "On", Auto login and redirect users to profile page after registration is complete.','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo (!bool_from_yn($data['system_obj']->get_setting('enable_autologin'))) ? 'displaynone' : '';?>">
				<?php _e('Redirect URL', 'mgm');?>: <input type="text" name="autologin_redirect_url" id="autologin_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('autologin_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect if after register auto login enabled. By default redirects to profile.URL short codes : [username]-Displays user username','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell <?php echo (!bool_from_yn($data['system_obj']->get_setting('enable_guest_lockdown'))) ? 'displaynone' : '';?>">
				<?php _e('Redirect URL', 'mgm');?>: <input type="text" name="guest_lockdown_redirect_url" id="guest_lockdown_redirect_url" value="<?php echo esc_attr($data['system_obj']->get_setting('guest_lockdown_redirect_url')); ?>" size="100" />
				<p><div class="tips width90"><?php _e('Url to redirect if guest lockdown enabled. By default redirects to login.','mgm'); ?></div></p>
			</div>
		</div>		
 		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Enable redirection to post/page url after content purchase or login?','mgm'); ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_post_url_redirection" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_post_url_redirection'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_post_url_redirection" value="N" <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_post_url_redirection'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Turn On/Off post url redirection. When "On", redirect users to the last visited post url.','mgm'); ?></div></p>
			</div>
		</div>
 		
 	</div>
	
	<?php mgm_box_bottom();?>


	<p class="submit floatleft">
		<input class="button" type="submit" name="settings_update" value="<?php _e('Save Settings','mgm') ?>" />
	</p>
	<div class="clearfix"></div>	
</form>
<script type="text/javascript">
	<!--
	jQuery(document).ready(function(){
		
		jQuery.validator.addMethod('checkSpecialChar', function(value, element) {
			return (value).match(/^[A-Za-z0-9_,]+$/);
		}, '<?php _e('Please remove space/special characters','mgm') ?>');
		// add : form validation
		jQuery("#frmsetrd").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmsetrd").ajaxSubmit({type: "POST",										  
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmsetrd', {status:'running', message:'<?php echo esc_js(__('Processing','mgm'));?>...'}, true);							
				  },
				  success: function(data){	
				  	// show message
				  	mgm_show_message('#frmsetrd', data);																			
				  }}); // end   		
				return false;											
			},
			rules:{download_slug:{required:true,checkSpecialChar:true}},	
			messages: {	},		
			errorClass: 'invalid',
			errorPlacement:function(error, element) {										
				error.insertAfter(element);
			}
		});			
		
		
		// enable bind
		jQuery("#frmsetrd :checkbox[id^='enable_']").click(function(e){
			var elmid = jQuery(this).attr('id').replace('enable_','');
			// autologin
			if(elmid == 'autologin') elmid = 'autologin_redirect_url';
			// check
			if(jQuery(this).prop('checked')){
				jQuery('#'+elmid).attr('disabled', false).val('');
				jQuery('#'+elmid).parent('div').fadeIn();
			}else{
			// uncheck
				jQuery('#'+elmid).attr('disabled', true);
				jQuery('#'+elmid).parent('div').fadeOut();
			}
		});

	});
	//-->
</script>