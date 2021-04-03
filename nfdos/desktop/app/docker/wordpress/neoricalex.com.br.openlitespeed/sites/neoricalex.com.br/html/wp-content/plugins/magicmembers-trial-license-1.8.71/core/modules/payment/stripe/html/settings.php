<!--stripe settings-->
<?php header('Content-Type: text/html; charset=UTF-8');?>
<div id="module_settings_<?php echo $data['module']->code?>">
	<?php mgm_box_top($data['module']->name. ' Settings');?>
	<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.payments&method=module_settings&module=<?php echo $data['module']->code?>">
		<div class="table">
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Stripe API Secret Key','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[secretkey]" id="setting_secretkey" value="<?php echo $data['module']->setting['secretkey']; ?>" size="50"/>
					<p><div class="tips"><?php _e('Stripe Secret Key.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Stripe API Publishable Key','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[publishable_key]" id="setting_publishable_key" value="<?php echo $data['module']->setting['publishable_key']; ?>" size="50"/>
					<p><div class="tips"><?php _e('Stripe Publishable Key.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row dp_field">
				<div class="cell">
					<p><b><?php _e('Webhook Signing Secret','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row dp_field">
				<div class="cell">
					<input type="text" name="setting[webhook_secret]" id="setting_webhook_secret" value="<?php echo esc_html($data['module']->setting['webhook_secret']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Optional, Webhook Signing Secret obtained from Stripe Webhooks Settings. Adds extra layer of Security when provided.','mgm'); ?></div></p>
				</div>
			</div>

			<div class="row">
				<div class="cell">
					<p><b><?php _e('Rebill Status Query','mgm'); ?>:</b></p>		
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[rebill_status_query]" id="setting_rebill_status_query" class="width100px">
						<?php echo mgm_make_combo_options(array('enabled'=>__('Enabled','mgm'),'disabled'=>__('Disabled','mgm')), $data['module']->setting['rebill_status_query'], MGM_KEY_VALUE);?>
					</select>						
					<p><div class="tips"><?php _e('Enable/Disable Rebill Status Query via Stripe API.','mgm'); ?></div></p>		
				</div>
			</div>

			<div class="row rsq_field">
				<div class="cell">
					<p><b><?php _e('Rebill status check delay','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row rsq_field">
				<div class="cell">
					<select name="rebill_check_delay" class="width100px">
						<?php $rcd = array(
							'-0 HOUR'=>__('0 HOURS','mgm'),'-6 HOUR'=>__('6 HOURS','mgm'),
							'-12 HOUR'=>__('12 HOURS','mgm'),'-24 HOUR'=>__('24 HOURS','mgm'),
							'-36 HOUR'=>__('36 HOURS','mgm'),'-48 HOUR'=>__('48 HOURS','mgm'),
							'-72 HOUR'=>__('72 HOURS','mgm'),'-288 HOUR'=>__('288 HOURS','mgm'),
						);?>
						<?php echo mgm_make_combo_options($rcd, $data['module']->setting['rebill_check_delay'], MGM_KEY_VALUE);?>
					</select>						
					<p><div class="tips"><?php _e('Rebill status  will check after the mentioned delay of hours.','mgm'); ?></div></p>
				</div>
			</div>		
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Subscription Cancellation Mode','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[subs_cancel]" id="setting_subs_cancel" class="width150px">
						<?php echo mgm_make_combo_options(array('instant'=>__('INSTANT', 'mgm'),'period_end'=>__('PERIOD END','mgm')), $data['module']->setting['subs_cancel'], MGM_KEY_VALUE);?>
					</select>						
					<p>
						<div class="tips">
							<?php _e('After subscription cancellation, deactivation mode, with INSTANT, members will be instantly deactivated when cancel request received. '.
									 'With PERIOD END, member will be deactivated at the end of current subscription period. Reactivation only works with cancellations with PERIOD END.','mgm'); ?>
						</div>
					</p>
				</div>
			</div>	
			
			<div class="row">
				<div class="cell">
					<p><b><?php _e('The Currency used for the payments','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="setting[currency]" id="setting_currency" class="width200px">
						<?php echo mgm_make_combo_options(mgm_get_currencies(), $data['module']->setting['currency'], MGM_KEY_VALUE);?>
					</select>							
					<p><div class="tips"><?php _e('Currency to use, update primary currency in General Settings page.','mgm'); ?></div></p>
				</div>
			</div>
			
			<?php if(in_array('buypost', $data['module']->supported_buttons)):?>				
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Default Post Purchase Price','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[purchase_price]" id="setting_purchase_price" value="<?php echo $data['module']->setting['purchase_price']; ?>" size="10"/>
					<p><div class="tips"><?php _e('Post purchase price. Only available in modules which supports buypost.','mgm'); ?></div></p>
				</div>
			</div>
			<?php endif;?>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supported Credit Card Types','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo mgm_make_checkbox_group('card_types[]', $data['module']->card_types, $data['module']->setting['supported_card_types'], 2, '', 'div' );?>
					<p><div class="tips"><?php _e('Enable/Disable the credit card types to be shown in Credit Card Form.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Success Title','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[success_title]" id="setting_success_title" value="<?php echo mgm_stripslashes_deep($data['module']->setting['success_title']); ?>" size="100"/>
					<p><div class="tips"><?php _e('Payment success page title.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Success Message','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="setting[success_message]" id="setting_success_message_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['success_message'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Payment success page message.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Failed Title','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[failed_title]" id="setting_failed_title" value="<?php echo mgm_stripslashes_deep($data['module']->setting['failed_title']); ?>" size="100"/>
					<p><div class="tips"><?php _e('Payment failed page title.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Callback Failed Message','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="setting[failed_message]" id="setting_failed_message_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['failed_message'])); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Payment failed page message.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Button/Logo','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php if (! empty($data['module']->logo)) :?>
						<img src="<?php echo $data['module']->logo ?>" id="logo_image_<?php echo $data['module']->code?>" alt="<?php echo sprintf(__('%s Logo', 'mgm'),$data['module']->name) ?>" border="0"/><br />
					<?php endif;?> 
					<input type="file" name="logo_<?php echo $data['module']->code?>" id="logo_<?php echo $data['module']->code?>" size="50"/>						
					<p><div class="tips"><?php _e('Button/logo image.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Description','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<textarea name="description" id="setting_description_<?php echo $data['module']->code?>" rows='4' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->description)); ?></textarea>						
					<div class="clearfix"></div>
					<p><div class="tips"><?php _e('Description shown on payment page.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Test/Live Switch','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<select name="status" class="width100px">
						<?php echo mgm_make_combo_options(array('test'=>__('TEST','mgm'),'live'=>__('LIVE','mgm')), $data['module']->status, MGM_KEY_VALUE);?>
					</select>						
		
					<p><div class="tips"><?php _e('Switch between TEST/LIVE mode to test your payments. Not all modules supports this feature.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Custom Thankyou URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[thankyou_url]" id="setting_thankyou_url" value="<?php echo $data['module']->setting['thankyou_url']; ?>" size="100"/>											
					<p><div class="tips"><?php _e('Custom Thankyou URL for redirecting user to payment success/failed page. This URL is meant to be updated inside your site, you can create a Wordpress post/page and paste the page url here.<br><u><b>Tag</b></u>: <br> <b>[transactions]</b> : Shows Transaction Details<br>','mgm'); ?></div></p>
				</div>
			</div>
				
			<?php /*?><div class="row">
				<div class="cell">
					<p><b><?php _e('Return URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['return_url']?>												
					<p><div class="tips"><?php _e('Retrun URL for capturing payment post data returned from gateway. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div><?php */?>
	
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Push Notify/Webhook URL','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['status_notify_url']?>												
					<p><div class="tips"><?php _e('Push Notify/Webhook URL for capturing silent post data sent from Stripe. Set this url as the Silent Post Url in Stripe Merchant Settings Panel. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supported Buttons','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo implode(', ', $data['module']->supported_buttons);?>											
					<p><div class="tips"><?php _e('Supported buttons. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<?php if(in_array('subscription', $data['module']->supported_buttons)):?>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supports Trial','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo ( $data['module']->is_trial_supported() ) ? __('Yes', 'mgm') : __('No', 'mgm');?>											
					<p><div class="tips"><?php _e('Supports trial setup. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Supports Rebill Status Checking','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo ( $data['module']->is_rebill_status_check_supported() ) ? __('Yes', 'mgm') : __('No', 'mgm');?>											
					<p><div class="tips"><?php _e('Supports rebill status check via API query. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<?php endif;?>	
	
			<?php if($data['module']->dependency_check() == true):?>				
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Dependency','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo implode(', <br>',$data['module']->dependency);?>												
					<p><div class="tips"><?php _e('Dependency Check. READONLY, only for information.','mgm'); ?></div></p>
				</div>
			</div>
			<?php endif;?>	

			<div class="row">
				<div class="cell">
					<p><b><?php _e('Stripe API Version','mgm'); ?>:</b></p>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<?php echo $data['module']->setting['stripe_version'];?>												
					<p>
						<div class="tips">
							<?php 
							printf(__('Stripe API Version, see <a href="%s" target="_blank">changelog</a>. READONLY, only for information.','mgm'), 'https://stripe.com/docs/upgrades#'.$data['module']->setting['stripe_version']); 
							?>
						</div>
					</p>
				</div>
			</div>
		</div>
		<p>					
			<input class="button" type="submit" name="btn_save" value="<?php _e('Update Settings', 'mgm') ?>" />
		</p>			
		<input type="hidden" name="update" value="true" />
		<input type="hidden" name="setting_form" value="main" />
	</form>
	<?php mgm_box_bottom();?>
</div>
<script type="text/javascript">
	<!--	
	// onready
	jQuery(document).ready(function(){   
		// editor
		jQuery("#frmmod_<?php echo $data['module']->code?> textarea[id]").each(function(){						
			new nicEditor({fullPanel : true, iconsPath: '<?php echo MGM_ASSETS_URL?>js/nicedit/nicEditorIcons.gif'}).panelInstance(jQuery(this).attr('id')); 			
		}); 
		// add : form validation
		jQuery("#frmmod_<?php echo $data['module']->code?>").validate({
			submitHandler: function(form) {					    					
				jQuery("#frmmod_<?php echo $data['module']->code?>").ajaxSubmit({type: "POST",				  
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
				  	// show processing 
					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", {status: "running", message: "<?php _e('Processing','mgm');?>..."}, true);						
				  },
				  success: function(data){							
					// show status  
					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", data);													
				  }}); // end  		
				  return false;											
			},
			rules: {			
				'setting[secretkey]': "required",						
				'setting[publishable_key]': "required"
			},
			messages: {			
				'setting[secretkey]': "<?php _e('Please enter Stripe api secret key.','mgm');?>",
				'setting[publishable_key]': "<?php _e('Please enter Stripe api publishable key.','mgm');?>"
			},
			errorClass: 'invalid'
		});	

		// rebill status query
		jQuery('#module_settings_<?php echo $data['module']->code?>	#setting_rebill_status_query').bind('change', function(){
			if(jQuery(this).val() == 'enabled'){
				jQuery('#module_settings_<?php echo $data['module']->code?> .rsq_field').fadeIn('slow');
			}else{
				jQuery('#module_settings_<?php echo $data['module']->code?> .rsq_field').fadeOut('slow');
			}
		}).change();
		
		// attach uploader
		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);				
		// attach endpoint toggle
		mgm_module_endpoints_toggle('<?php echo $data['module']->code?>');
	});	
	//-->	
</script>