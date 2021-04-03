<!--autoresponders lists-->
<?php mgm_box_top(__('Autoresponder List/Group Settings', 'mgm'),'autoresponder-lists-settings');?>
	<div id="autoresponders_list" class="mgm_font_size12px">
		<div class="table">
			<div class="row`">
				<div class="cell">
					&nbsp;&nbsp;<b><?php _e('Autoresponder Lists/Groups','mgm');?></b>
				</div>
			</div>
			<div class="row">
				<div class="cell">
					<div class='mgm'>
						<div id="autoresponders_lists_panel">
							<?php foreach($data['modules'] as $module) : ?>		
							<h3><a href="#" id="armod_form_tab_<?php echo $module['code']?>"><b><?php echo $module['name']?></b> <?php if($module['code'] == $data['active_module']):?> <img src="<?php echo MGM_ASSETS_URL?>images/icons/16-em-check.png" class="enabled_symbol"/><?php endif?></a> </h3>
							<div>
								<p>							
									<!-- new ar-->
									<div id="armod_form_cont_<?php echo $module['code']?>" >
										<?php echo $module['html']?>
									</div>											
									<div class="clearfix"></div>
								</p>
							</div>
							<?php endforeach?>		
						</div>			
					</div>
				</div>
			</div>	
		</div>		
	</div>	
<?php mgm_box_bottom();?>
<?php mgm_box_top(__('Unsubscribe Settings', 'mgm'));?>
<div id="autoresponder_settings"></div>
<?php mgm_box_bottom();?>	
<script type="text/javascript">
	<!--
	jQuery(document).ready(function(){	
		// create
		mgm_create_row = function(selector){
			// get size
			var size = jQuery(selector).find("div[id^='layer']").size();			
			var last_layer = jQuery(selector).find('div.row:last');
			// cloned
			var last_layer_cloned = last_layer.clone();	
			// update index
			jQuery(last_layer_cloned).attr('id','layer'+size);
			jQuery(last_layer_cloned).attr('rel','layer-last');
			// new trigger
			r_trig = "<a href=\"javascript:mgm_remove_row('"+selector+"','"+(size-1)+"')\"><img src=\"<?php echo MGM_ASSETS_URL?>images/icons/16-em-cross.png\" /></a>";
			// remove trigger
			last_layer.find('a.layer-trig').replaceWith(r_trig);
			// change index
			last_layer.attr('id','layer'+(size-1));
			last_layer.attr('rel','layer-'+(size-1));
			// clear text
			jQuery(last_layer_cloned).find(":input[type='text']").val('');
			// append
			jQuery(selector).append(last_layer_cloned);
		}	
		
		// remove
		mgm_remove_row = function(selector, id){		
			// get size
			var size = jQuery(selector).find("div[id^='layer']").size();		
			// remove
			if(size>1){
				// get layer
				var layer = jQuery(selector).find('div.row#layer' + id);
				// check last
				if(layer.attr('rel') != 'layer-last'){
					layer.remove();
				}else{
					alert("<?php echo esc_js(__('This is last layer, can not remove last layer','mgm'));?>");
				}
			}else{
				alert("<?php echo esc_js(__('One layer should exist','mgm'));?>");
			}
			// send ajax
		}	
		// symbol change
		mgm_active_module_symbol=function(code){
			// check
			var enabled = jQuery('#module_settings_' + code).find("select[name='enabled']").val();
			if( enabled == 'Y'){
				// prev enabled
				var prev_code = jQuery("#autoresponders_lists_panel a img.enabled_symbol").parent().attr('id').replace('armod_form_tab_','');
				// the symbol
				var symbol = jQuery("#autoresponders_lists_panel a img.enabled_symbol").clone(true);
				// remove symbol
				jQuery("#autoresponders_lists_panel a img.enabled_symbol").remove();
				// previous select
				jQuery('#module_settings_' + prev_code).find("select[name='enabled'] option[value=N]").attr('selected',true);
				// present select
				jQuery('#module_settings_' + code).find("select[name='enabled'] option[value=N]").attr('selected',false);
				// present select
				jQuery('#module_settings_' + code).find("select[name='enabled'] option[value=Y]").attr('selected',true);
				// add new
				jQuery('#armod_form_tab_' + code).append(symbol);							
			}
		}
		// setup autoresponders list
		mgm_setup_autoresponder_lists_ui=function(){			
			// set up accordian
			jQuery("#autoresponders_lists_panel").accordion({
				collapsible: true,
				active: false,
				<?php if( mgm_compare_wp_version('3.6', '>=') ):?>
				heightStyle: 'content'
				<?php else:?>
				autoHeight: true,
				fillSpace: false,
				clearStyle: true
				<?php endif;?>	
			});	
			// wp3.6+
			jQuery( "#subs_pkgs_panel" ).accordion( "refresh" );
		}
		// load settings
		mgm_autoresponder_settings=function(){
			jQuery('#autoresponder_settings').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.autoresponders&method=autoresponder_settings'); 
		}	
		// setup autoresponder lists ui
		mgm_setup_autoresponder_lists_ui();	
		//load settings
		mgm_autoresponder_settings();	
	});
	//-->
</script>