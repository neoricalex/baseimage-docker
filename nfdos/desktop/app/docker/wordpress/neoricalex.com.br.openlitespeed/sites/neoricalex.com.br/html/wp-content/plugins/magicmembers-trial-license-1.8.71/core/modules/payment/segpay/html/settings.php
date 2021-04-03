<!--paypal main settings-->

<?php header('Content-Type: text/html; charset=UTF-8');?>


<div id="module_settings_<?php echo $data['module']->code?>">

	<?php mgm_box_top($data['module']->name. ' Settings');?>

	
		<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm/admin/payments&method=module_settings&module=<?php echo $data['module']->code?>">

		<div class="table">
			<div class="row">
				<div class="cell">
				<span class="label"><b><?php _e('Segpay Price Type:','mgm'); ?></b></span>
				<span class="val">

					<input type="radio" name="setting[type]" id="setting_type_<?php echo $data['module']->code?>" value="static" size="50" <?php echo ($data['module']->setting['type']=='static')? 'checked' : ''?> /> Static

					<input type="radio" name="setting[type]" id="setting_type_<?php echo $data['module']->code?>" value="dynamic" size="50" <?php echo ($data['module']->setting['type']=='dynamic') ? 'checked' : ''?>/> Dynamic
					<p><div class="tips"><a href="<?php echo admin_url(); ?>admin.php?page=mgm_product_list"><?php _e('If you choose Static, click here to add eticketID for each of your products','mgm'); ?></a></div></p>
				</span>
				</div>
			</div>

			<div class="row">
				<div class="cell">
				<span class="label"><b><?php _e('Title:','mgm'); ?></b></span>
				<span class="val">
					<input type="text" name="setting[title]" id="setting_title_<?php echo $data['module']->code?>" value="<?php echo esc_html($data['module']->setting['title']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Payment gateway title','mgm'); ?></div></p>
				</span>
				</div>
			</div>
			

			<div class="row">
				<div class="cell">
				<span class="label"><b><?php _e('Description:','mgm'); ?></b></span>
				<span class="val"><textarea name="setting[description]" id="setting_description_<?php echo $data['module']->code?>" rows='2' cols='75' class="width750px height100px"><?php echo mgm_stripslashes_deep(esc_html($data['module']->setting['description'])); ?></textarea>	</span>
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
				<span class="label"><b><?php _e('Dynamic price E-ticket id:','mgm'); ?></b></span>
				<span class="val">	 <input type="text" name="setting[eticketid]" id="setting_eticketid" value="<?php echo esc_html($data['module']->setting['eticketid']); ?>" size="50"/>
					<p><div class="tips"><?php _e('Payment gateway eticketid for direct posts payment','mgm'); ?></div></p>
				</span>
				</div>

			</div>
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Live Payment Url:','mgm'); ?></b> </span>
					<span class="val"> <input type="text" name="setting[livePaymentUrl]" id="setting_livePaymentUrl" value="<?php echo esc_html($data['module']->setting['livePaymentUrl']); ?>" size="50"/>	
					</span>				
				</div>
			</div>
			
			
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Direct post - Price Hash Url:','mgm'); ?></b> </span>
					<span class="val"> <input type="text" name="setting[priceHashUrlDirect]" id="setting_priceHashUrlDirect" value="<?php echo esc_html($data['module']->setting['priceHashUrlDirect']); ?>" size="50"/>	</span>				
				</div>
			</div>
		
			
			
			<div class="row heading_row">
				<div class="cell">
					<b>Recurring Payments : essentials </b>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Dynamic Recurring-ticket id:','mgm'); ?></b></span>
				<span class="val"> <input type="text" name="setting[rticketid]" id="setting_rticketid" value="<?php echo esc_html($data['module']->setting['rticketid']); ?>" size="50"/>
					<p><div class="tips"><?php _e('E-ticketid for recurring payments','mgm'); ?></div></p>
</span>
				</div>
			</div>
		
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Recurring User access key:','mgm'); ?></b> </span>
					<span class="val"><input type="text" name="setting[username]" id="setting_username" value="<?php echo esc_html($data['module']->setting['username']); ?>" size="50"/>	
				<p><div class="tips"><?php _e('Used for getting hash in reccuring payments','mgm'); ?></div></p>
</span>					
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Recurring User access password:','mgm'); ?></b> </span>
<span class="val"><input type="text" name="setting[password]" id="setting_password" value="<?php echo esc_html($data['module']->setting['password']); ?>" size="50"/>	
					<p><div class="tips"><?php _e('Used for getting hash in reccuring payments','mgm'); ?></div></p>	</span>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<span class="label"><b> Recurring Merchant ID: </b> </span>
<span class="val"><input type="text" name="setting[merchantid]" id="setting_merchantid" value="<?php echo esc_html($data['module']->setting['merchantid']); ?>" size="50"/>	
					<p><div class="tips"><?php _e('Used for getting hash in reccuring payments','mgm'); ?></div></p></span>						
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Recurring Price Hash Url:','mgm'); ?>:</b></span>
<span class="val"> <input type="text" name="setting[priceHashUrl]" id="setting_priceHashUrl" value="<?php echo esc_html($data['module']->setting['priceHashUrl']); ?>" size="50"/>		</span>			
				</div>
			</div>

			<div class="row">
				<div class="cell">
					<span class="label"><b><?php _e('Cancel Url:','mgm'); ?></b></span>
<span class="val"> <input type="text" name="setting[refundUrl]" id="setting_refundUrl" value="<?php echo esc_html($data['module']->setting['refundUrl']); ?>" size="50"/>	</span>				
				</div>
			</div>
			<!-- <div class="row">
				<div class="cell">
					<b> Initial amount: </b> <input type="text" name="setting[initialamount]" id="setting_initialamount" value="<?php echo esc_html($data['module']->setting['initialamount']); ?>" size="50"/>					
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<b> Initial length: </b> <input type="text" name="setting[intiallength]" id="setting_intiallength" value="<?php echo esc_html($data['module']->setting['intiallength']); ?>" size="50"/>					
				</div>
			</div>
			
			<div class="row">
				<div class="cell">
					<b> Recurring Amount: </b> <input type="text" name="setting[recurringamount]" id="setting_recurringamount" value="<?php echo esc_html($data['module']->setting['recurringamount']); ?>" size="50"/>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<b> Recurring Length: </b> <input type="text" name="setting[recurringlength]" id="setting_recurringlength" value="<?php echo esc_html($data['module']->setting['recurringlength']); ?>" size="50"/>					
				</div>
			</div>
			-->
			

			<?php if(in_array('buypost', $data['module']->supported_buttons)):?>			

			<div class="row">

				<div class="cell">

					<span class="label"><p><b><?php _e('Default Post Purchase Price','mgm'); ?>:</b></p>		</span>		

				</div>

			</div>

			<div class="row">

				<div class="cell">

				<span class="label">	<input type="text" name="setting[purchase_price]" id="setting_purchase_price" value="<?php echo $data['module']->setting['purchase_price']; ?>" size="10"/>
</span><span class="label">
					<p><div class="tips"><?php _e('Post purchase price. Only available in modules which supports buypost.','mgm'); ?></div></p>
</span>
				</div>

			</div>

			<?php endif;?>

			
			<div class="row">

				<div class="cell">

					<span class="label"><p><b><?php _e('Button/Logo','mgm'); ?>:</b></p>	</span>			

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

					<p><b><?php _e('Test/Live Switch','mgm'); ?>:</b></p>				

				</div>

			</div>

			<div class="row">

				<div class="cell">

					<select name="status" class="width100px">

						<?php echo mgm_make_combo_options(array('test'=>__('TEST','mgm'),'live'=>__('LIVE','mgm')), $data['module']->status, MGM_KEY_VALUE)?>

					</select>						

					<p><div class="tips"><?php _e('Switch between TEST/LIVE mode to test your payments. Not all modules supports this feature.','mgm'); ?></div></p>

				</div>

			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Authorized Text:','mgm'); ?>:</b></p>	
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[authText]" id="setting_authText" value="<?php echo esc_html($data['module']->setting['authText']); ?>" size="50"/>					
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<p><b><?php _e('Declined Text:','mgm'); ?>:</b></p>	
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<input type="text" name="setting[decText]" id="setting_decText" value="<?php echo esc_html($data['module']->setting['decText']); ?>" size="50"/>					
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

			


			

			<div class="row">

				<div class="cell">

					<p><b><?php _e('Supported Buttons','mgm'); ?>:</b></p>

				</div>

			</div>

			<div class="row">

				<div class="cell">

					<?php echo implode(', ', $data['module']->supported_buttons)?>												

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

					<?php echo ( $data['module']->is_trial_supported() ) ? __('Yes', 'mgm') : __('No', 'mgm')?>												

					<p><div class="tips"><?php _e('Supports trial setup. READONLY, only for information.','mgm'); ?></div></p>

				</div>

			</div>

			<?php endif;?>

		</div>

		<p>					

			<input class="button" type="submit" name="btn_save" value="<?php _e('Update Settings', 'mgm') ?>" />

		</p>

		<input type="hidden" name="update" value="true" />

		<input type="hidden" name="setting_form" value="main" />

		</form>

	<?php mgm_box_bottom();?>

</div>
<style>
.cell .label{float: left;
    width: 30%;}
.cell .val{float: left;}
.heading_row{ background: #ddd none repeat scroll 0 0;
    float: left;
    line-height: 40px;
    margin: 20px 0;
    text-align: center;}
</style>

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

					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", {status: "running", message: "<?php _e('Processing','mgm')?>..."}, true);						

				  },

				  success: function(data){							

					// show status  

					mgm_show_message("#module_settings_<?php echo $data['module']->code?>", data);													

				  }}); // end   		

				  return false;											

			},

			

			errorClass: 'invalid'

		});			

		// attach uploader

		mgm_file_uploader('#module_settings_<?php echo $data['module']->code?>', mgm_upload_logo);			

	});	

	//-->	

</script>
