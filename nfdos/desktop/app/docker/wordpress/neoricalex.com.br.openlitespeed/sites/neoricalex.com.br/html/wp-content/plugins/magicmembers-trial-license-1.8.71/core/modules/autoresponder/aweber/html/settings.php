<!--aweber main settings-->
<div id="module_settings_<?php echo $data['module']->code?>">
	<form name="frmmod_<?php echo $data['module']->code?>" id="frmmod_<?php echo $data['module']->code?>" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.autoresponders&method=module_settings&module=<?php echo $data['module']->code?>">
		<h3><b><?php _e('Enable/Disable Settings','mgm');?></b></h3>
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft ">
		    		<b><?php _e('Setting','mgm');?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('Value','mgm');?></b>
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width50 textalignleft ">
					<p><b><?php _e('Enable','mgm'); ?>?</b></p>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="enabled" class="width100px">
							<?php echo mgm_make_combo_options(array('Y'=>__('Yes','mgm'),'N'=>__('No','mgm')), $data['module']->is_enabled('string'), MGM_KEY_VALUE);?>
						</select>
				</div>
			</div>	
		</div>		

		<p><div class="tips width95"><?php _e('Enable/Disable the Aweber module.','mgm'); ?></div></p>
		<p>&nbsp;</p>
				
		<h3><b><?php _e('Primary Settings','mgm');?></b></h3>
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('Aweber Field','mgm');?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('Value','mgm');?></b>
				</div>
			</div>
				<!--			
				<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php //_e('Web Form Id','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[form_id]" value="<?php //echo $data['module']->setting['form_id']; ?>" size="40" />
				</div>
			</div>-->
			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php _e('Consumer Key','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[consumer_key]" value="<?php echo $data['module']->setting['consumer_key']; ?>" size="40" />
				</div>
			</div>
			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php _e('Consumer Secret','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[consumer_secret]" value="<?php echo $data['module']->setting['consumer_secret']; ?>" size="40" />
				</div>
			</div>

			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input class="button" type="button" name="btn_authorize" value="<?php _e('Authorize', 'mgm') ?>" onclick="mgm_aweber_authorize()"/>
				</div>
			</div>
		</div>	
		<p>	
			<div class="tips width95">
				<?php $url = admin_url("admin.php?page=mgm.admin.autoresponders&method=aweber_help"); ?>			
				<?php printf(__('After updating Consumer & Secret keys,  <a href="%s" target="_blank">Click here</a> to get  your aweber account Access Token & Access Token Secret keys.','mgm'),$url); ?>				
				
			</div>
		</p>
		<div class="table widefatDiv">			
			
			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php _e('Access Token Key','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[access_key]" value="<?php echo $data['module']->setting['access_key']; ?>" size="40" />
				</div>
			</div>		
			<div class="row brBottom">
				<div class="cell width50 textalignleft">
					<b><?php _e('Access Token Secret','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<input type="text" name="setting[access_secret]" value="<?php echo $data['module']->setting['access_secret']; ?>" size="40" />
				</div>
			</div>
		</div>	
		<p>	
			<div class="tips width95">
				<?php _e('After updating all keys only AWeber contact list is available.','mgm'); ?>
			</div>
		</p>
		<div class="table widefatDiv">			
			<div class="row brBottom">
				<div class="cell width50 textalignleft ">
					<b><?php _e('Unit/List Name ( default )','mgm'); ?></b>
				</div>
				<div class="cell textalignleft width50 brCellBorderLeft">
					<select  name="setting[unit]" class="width200px">
						<?php echo mgm_make_combo_options($data['contact_lists'], $data['module']->setting['unit'], MGM_KEY_VALUE);?>
					</select>
				</div>
			</div>
		</div>		

		<p>&nbsp;</p>		
		
		<h3><b><?php _e('Field Mappings','mgm');?></b></h3>
		
		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('Aweber Field','mgm');?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('MagicMembers Field','mgm');?></b>
				</div>
			</div>
			
			<div class="tbodyDiv" id="fieldmap_layers_<?php echo $data['module']->code?>">	
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
					<div class="cell width50 textalignleft ">
						<b><?php _e('email','mgm');?></b>
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<b><?php _e('E-mail ( default )','mgm');?></b>
					</div>
				</div>
				<?php if(count($data['module']->setting['fieldmap'])>0): $layer=1; foreach($data['module']->setting['fieldmap'] as $modulefld=>$mgmfld):?>					
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer<?php echo $layer?>">	
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[fieldmap][]" value="<?php echo $modulefld?>" size="40" />
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[fieldmap][]" class="width200px">
							<?php if(is_array($data['custom_fields']) && count($data['custom_fields'])>0): foreach($data['custom_fields'] as $field_name=>$field_label):?>
							<option value="<?php echo $field_name?>" <?php echo ($field_name==$mgmfld) ? 'selected' : ''?>><?php _e($field_label,'mgm');?></option>
							<?php endforeach; endif;?>
						</select>	
						<?php if($layer == count($data['module']->setting['fieldmap'])):?>	
						<a class='layer-trig' href="javascript:mgm_create_row('#fieldmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>												
						<?php else:?>
						<a class='layer-trig' href="javascript:mgm_remove_row('#fieldmap_layers_<?php echo $data['module']->code?>', <?php echo $layer?>)"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png"/></a>
						<?php endif;?>
					</div>
				</div>
				<?php $layer++; endforeach; else:?>
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer">
					<div class="cell width50 textalignleft ">
						<input type="text" name="setting[fieldmap][]" value="" size="40" />	
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[fieldmap][]" class="width200px">
							<?php if(is_array($data['custom_fields']) && count($data['custom_fields'])>0): foreach($data['custom_fields'] as $field_name=>$field_label):?>
							<option value="<?php echo $field_name?>"><?php _e($field_label,'mgm');?></option>
							<?php endforeach; endif;?>
						</select>		
						<a class='layer-trig' href="javascript:mgm_create_row('#fieldmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>						
					</div>
				</div>
				<?php endif;?>
			</div>
		</div>
		<p>&nbsp;</p>
		
		<h3><b><?php _e('Membership Mappings','mgm');?></b></h3>

		<div class="table widefatDiv">
			<div class="row headrow">
				<div class="cell theadDivCell width50 textalignleft">
		    		<b><?php _e('Aweber List/Group','mgm');?></b>
				</div>
				<div class="cell theadDivCell width50 textalignleft brCellBorderLeft">
		    		<b><?php _e('MagicMembers Membership Type','mgm');?></b>
				</div>
			</div>
			
			<div class="tbodyDiv" id="membershipmap_layers_<?php echo $data['module']->code?>">	
				<?php if(count($data['module']->setting['membershipmap'])>0): $layer=1; foreach($data['module']->setting['membershipmap'] as $ms_type=>$listid):?>
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer<?php echo $layer?>">
					<div class="cell width50 textalignleft ">
						<select  name="setting[membershipmap][]" class="width200px">
							<?php echo mgm_make_combo_options($data['contact_lists'], $listid, MGM_KEY_VALUE);?>
						</select>
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[membershipmap][]" class="width200px">
							<?php if(is_array($data['membership_types']) && count($data['membership_types'])>0): foreach($data['membership_types'] as $ms_code=>$ms_name):?>
							<option value="<?php echo $ms_code?>" <?php echo ($ms_code==$ms_type) ? 'selected' : ''?>><?php _e($ms_name,'mgm');?></option>
							<?php endforeach; endif;?>
						</select>		
						<?php if($layer == count($data['module']->setting['membershipmap'])):?>	
						<a class='layer-trig' href="javascript:mgm_create_row('#membershipmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>												
						<?php else:?>
						<a class='layer-trig' href="javascript:mgm_remove_row('#membershipmap_layers_<?php echo $data['module']->code?>', <?php echo $layer?>)"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png"/></a>
						<?php endif;?>	
					</div>
				</div>
				<?php $layer++; endforeach; else:?>	
				<div class="row brBottom <?php echo ($alt = ($alt=='') ? 'alternate': '');?>" id="layer">
					<div class="cell width50 textalignleft ">
						<select  name="setting[membershipmap][]" class="width200px">
							<?php echo mgm_make_combo_options($data['contact_lists'],'',MGM_KEY_VALUE); ?>
						</select>
					</div>
					<div class="cell textalignleft width50 brCellBorderLeft">
						<select name="setting[membershipmap][]" class="width200px">							
							<?php if(is_array($data['membership_types']) && count($data['membership_types'])>0): foreach($data['membership_types'] as $ms_code=>$ms_name):?>
							<option value="<?php echo $ms_code?>"><?php _e($ms_name,'mgm');?></option>
							<?php endforeach; endif;?>
						</select>		
						<a class='layer-trig' href="javascript:mgm_create_row('#membershipmap_layers_<?php echo $data['module']->code?>')"><img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-plus.png"/></a>
					</div>
				</div>
				<?php endif;?>
				
			</div>
		</div>
		<p>&nbsp;</p>			
		<p class="submit">					
			<input class="button" type="submit" name="btn_save" value="<?php _e('Update Settings', 'mgm') ?>" />
		</p>
		<input type="hidden" name="update" value="true" />
		<input type="hidden" name="setting_form" value="main" />
	</form>
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
					// update current sysmbol
					mgm_active_module_symbol('<?php echo $data['module']->code?>');													
				  }}); // end   		
				  return false;											
			},
			rules: {			
				//'setting[form_id]': 'required',
				'setting[consumer_key]': 'required',
				'setting[consumer_secret]': 'required',
				//'setting[access_key]': 'required',
				//'setting[access_secret]': 'required',
				//'setting[unit]': 'required'				
			},
			messages: {		
				//'setting[form_id]': '<?php _e('Please enter aweber form id.','mgm');?>',
				'setting[consumer_key]': '<?php _e('Please enter aweber consumer key.','mgm');?>',
				'setting[consumer_secret]': '<?php _e('Please enter aweber consumer secret.','mgm');?>',
				//'setting[access_key]': '<?php _e('Please enter aweber access key.','mgm');?>',
				//'setting[access_secret]': '<?php _e('Please enter aweber access secret.','mgm');?>',
				//'setting[unit]': '<?php _e('Please enter aweber form unit.','mgm');?>'
			},
			errorClass: 'invalid'
		});			

		mgm_aweber_authorize=function(){
			var code = "<?php echo $data['module']->code?>";
			var url = "<?php echo admin_url("admin.php?page=mgm.admin.autoresponders&method=aweber_help"); ?>";	
			var context = jQuery("#frmmod_"+code);
			var consumer_key = context.find(":input[name$='[consumer_key]']");
			var consumer_secret = context.find(":input[name$='[consumer_secret]']");

			if( consumer_key.val().is_empty() ){
				alert('Must enter consumer key');
				consumer_key.focus();
				return;
			}

			if( consumer_secret.val().is_empty() ){
				alert('Must enter consumer secret');
				consumer_secret.focus();
				return;
			}

			window.open(url+'&consumer_key='+consumer_key.val()+'&consumer_secret='+consumer_secret.val());
		}				
	});	
	//-->	
</script>