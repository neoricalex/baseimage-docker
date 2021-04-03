<form name="frmsetar" id="frmsetar" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.autoresponders&method=autoresponder_settings">
<div class="table">
	<div class="row">
		<div class="cell">
			<p><b><?php _e('Enable autoresponder unsubscribe','mgm'); ?>:</b></p>
		</div>
	</div>
		<div class="row">
			<div class="cell">
			<input type="radio" name="autoresponder_unsubscribe" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('autoresponder_unsubscribe'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
			<input type="radio" name="autoresponder_unsubscribe" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('autoresponder_unsubscribe'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
			<p><div class="tips width90"><?php _e('Turn On/Off autoresponder unsubscribe form the mailing list, when user unsubscribes from the site.' ,'mgm'); ?></div></p>
		</div>
	</div>
	
	<div class="row">
		<div class="cell">
			<p><b><?php _e('Unsubscribe Autoresponder on Subscription Expiration','mgm'); ?>:</b></p>
		</div>
	</div>
	
	<div class="row">
			<div class="cell">
			<input type="radio" name="unsubscribe_autoresponder_on_expire" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('unsubscribe_autoresponder_on_expire'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
			<input type="radio" name="unsubscribe_autoresponder_on_expire" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('unsubscribe_autoresponder_on_expire'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
			<p><div class="tips width90"><?php _e('Unsubcribe from Autoresponder when Subscription expired.' ,'mgm'); ?></div></p>
		</div>
	</div>

</div>
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
		jQuery("#frmsetar").validate({		
			submitHandler: function(form) {					    					
				jQuery("#frmsetar").ajaxSubmit({type: "POST",										  
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmsetar', {status:'running', message:'<?php echo esc_js(__('Processing','mgm'));?>...'}, true);							
				  },
				  success: function(data){	
				  	// show message
				  	mgm_show_message('#frmsetar', data);																			
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
	});
	//-->
</script>