<!--aweber box settings -->
<div id="module_settings_box_<?php echo $data['module']->code?>" class="module_settings_box box_max">	
	<div class="name">
		<input type="radio" name="module" value="<?php echo $data['module']->code?>" <?php echo ( $data['module']->is_enabled() ) ? 'checked' : ''; ?>/>
		<?php echo $data['module']->name?>
	</div>	
	<div class="clearfix"></div>
	<div class="description">
		<?php echo $data['module']->description?>
	</div>			
	<div class="fields">
		<div class="table">
	  		<div class="row">
	    		<div class="cell width26 textalignleft">
		    		<?php _e('Web Form Id','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
	    		<div class="cell textalignleft">
		    		<input type="text" name="setting[<?php echo $data['module']->module?>][form_id]" value="<?php echo $data['module']->setting['form_id']; ?>" size="40" />
				</div>
			</div>
			<div class="row">
				<div class="cell width26 textalignleft">
					<b><?php _e('Consumer Key','mgm'); ?></b>
				</div>
	    		<div class="cell width2 textaligncenter">
		    		<b>:</b>
				</div>
				<div class="cell textalignleft">
					<input type="text" name="setting[<?php echo $data['module']->module?>][consumer_key]" value="<?php echo $data['module']->setting['consumer_key']; ?>" size="40" />
				</div>
			</div>
			<div class="row">
				<div class="cell width26 textalignleft">
					<b><?php _e('Consumer Secret','mgm'); ?></b>
				</div>
				<div class="cell textalignleft">
					<input type="text" name="setting[<?php echo $data['module']->module?>][consumer_secret]" value="<?php echo $data['module']->setting['consumer_secret']; ?>" size="40" />
				</div>
			</div>
			<div class="row">
				<div class="cell width26 textalignleft">
					<b><?php _e('Access Key','mgm'); ?></b>
				</div>
				<div class="cell textalignleft">
					<input type="text" name="setting[<?php echo $data['module']->module?>][access_key]" value="<?php echo $data['module']->setting['access_key']; ?>" size="40" />
				</div>
			</div>
			
			<div class="row">
				<div class="cell width26 textalignleft">
					<b><?php _e('Access Secret','mgm'); ?></b>
				</div>
				<div class="cell textalignleft">
					<input type="text" name="setting[<?php echo $data['module']->module?>][access_secret]" value="<?php echo $data['module']->setting['access_secret']; ?>" size="40" />
				</div>
			</div>
		</div>	
		<p>	
			<div class="tips width95">
				<?php _e('After updating all keys only AWeber contact list is available.','mgm'); ?>
			</div>
		</p>
		<div class="table">			
			<div class="row">
				<div class="cell width26 textalignleft">
					<b><?php _e('Unit/List Name','mgm'); ?></b>
				</div>
				<div class="cell textalignleft">
					<select  name="setting[<?php echo $data['module']->module?>][unit]" class="width200px">
						<?php echo mgm_make_combo_options($data['contact_lists'], $data['module']->setting['unit'], MGM_KEY_VALUE);?>
					</select>
				</div>
			</div>
		</div>
	</div>
</div>