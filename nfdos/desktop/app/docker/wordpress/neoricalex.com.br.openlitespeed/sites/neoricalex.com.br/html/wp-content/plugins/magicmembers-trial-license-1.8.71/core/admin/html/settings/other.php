<!--redirection-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<form name="frmsetother" id="frmsetother" method="post" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.settings&method=other">
	<?php mgm_box_top(__('Other Settings', 'mgm'));?>
	<div class="table">
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Would you like to enable magic members logs ?','mgm');  ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="enable_debug_log" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('enable_debug_log'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="enable_debug_log" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('enable_debug_log'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90"><?php _e('Easy way to stop magic member logs i.e.( uploads/mgm/logs) ','mgm'); ?></div></p>
			</div>
		</div>
  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Http request timeout','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="get_http_request_timeout" value="<?php echo esc_attr($data['system_obj']->get_setting('get_http_request_timeout')); ?>" size="10" />
				<p><div class="tips width90"><?php _e('It will control the http request time out in seconds.','mgm'); ?></div></p>
    		</div>
		</div> 		
 	</div>
 	<?php mgm_box_bottom();?>
	<?php mgm_box_top(__('3rd Party Plugin Settings', 'mgm'));?>
	<div class="table">
		<div class="row">
    		<div class="cell">
				<p><b><?php _e('Would you like to enable register or login with Social Login plugin ?','mgm');  ?></b></p>
			</div>
		</div>
  		<div class="row">
  			<div class="cell">
				<input type="radio" name="oa_social_login_assign" value="Y" <?php if (bool_from_yn($data['system_obj']->get_setting('oa_social_login_assign'))) { echo 'checked="true"'; } ?>/> <?php _e('Yes','mgm'); ?>
				<input type="radio" name="oa_social_login_assign" value="N"  <?php if (!bool_from_yn($data['system_obj']->get_setting('oa_social_login_assign'))) { echo 'checked="true"'; } ?>/> <?php _e('No','mgm'); ?>					
				<p><div class="tips width90">
					<?php 
						_e('Enable third party social login plugin for mm register/login . ','mgm'); 
						_e('Plugin referecnce url : https://wordpress.org/plugins/oa-social-login/','mgm');
					?>
				</div></p>
			</div>
		</div>

  		<div class="row">
    		<div class="cell">
    			<p><b><?php _e('Default social login / register pack id','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="default_social_pack_id" value="<?php echo esc_attr($data['system_obj']->get_setting('default_social_pack_id')); ?>" size="10" />
				<p><div class="tips width90"><?php _e('Set here default social login or register pack id for mm registration','mgm'); ?></div></p>
    		</div>
		</div> 			

		<div class="row">
    		<div class="cell">
				<p><b><?php  _e('Would you like to enable woocommerce register with magic members ?','mgm');  ?></b></p>
			</div>
		</div>

  		<div class="row">
  			<div class="cell">
				<input type="radio" name="woocommerce_register_assign" value="Y" <?php  if (bool_from_yn($data['system_obj']->get_setting('woocommerce_register_assign'))) { echo 'checked="true"'; } ?>/> <?php  _e('Yes','mgm'); ?>
				<input type="radio" name="woocommerce_register_assign" value="N"  <?php  if (!bool_from_yn($data['system_obj']->get_setting('woocommerce_register_assign'))) { echo 'checked="true"'; } ?>/> <?php  _e('No','mgm'); ?>					
				<p><div class="tips width90">
					<?php 
						 _e('Enable third party woocommerce register with magic members. ','mgm'); 
					?>
				</div></p>
			</div>
		</div>

  		<div class="row">
    		<div class="cell">
    			<p><b><?php  _e('Default woocommerce register pack id','mgm'); ?>:</b></p>
    		</div>
		</div>
  		<div class="row">
    		<div class="cell">
				<input type="text" name="default_woocommerce_pack_id" value="<?php  echo esc_attr($data['system_obj']->get_setting('default_woocommerce_pack_id')); ?>" size="10" />
				<p><div class="tips width90"><?php  _e('Set here default woocommerce register pack id for magic members','mgm'); ?></div></p>
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
		jQuery("#frmsetother").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmsetother").ajaxSubmit({type: "POST",										  
				  dataType: 'json',			
				  iframe: false,								 
				  beforeSubmit: function(){	
				  	// show message
					mgm_show_message('#frmsetother', {status:'running', message:'<?php echo esc_js(__('Processing','mgm'));?>...'}, true);							
				  },
				  success: function(data){	
				  	// show message
				  	mgm_show_message('#frmsetother', data);																			
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