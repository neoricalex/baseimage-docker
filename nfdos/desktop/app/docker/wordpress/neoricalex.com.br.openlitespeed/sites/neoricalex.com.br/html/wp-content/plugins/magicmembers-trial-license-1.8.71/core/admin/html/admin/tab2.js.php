<script type="text/javascript">
	var mgm_primary_tabs = null;
	var mgm_secondary_tabs = null;
	// load
	mgm_load_twin_tabs=function(){
		// attach loading mask
		mgm_ajax_loader();

		// tabs
		mgm_primary_tabs = jQuery( "#mgm-panel-content" ).tabs({
			// before load
			beforeLoad: function( event, ui ) {	
				// ui.jqXHR.error(function() {
				// wp 5.6 fix
				ui.jqXHR.fail(function() {
					ui.panel.html(
						"<?php _e('Couldn\'t load this tab. We\'ll try to fix this as soon as possible.','mga');?> " );
				});
			},

			load: function( event, ui ) {
				// create secondary tabs										
				mgm_secondary_tabs = jQuery(ui.panel).find('.content-div').tabs({ 				
					beforeActivate: function( event, ui ){
						jQuery('#message').remove();
					},
					load: function(event, ui){ 

						mgm_attach_tips();

						//mgm_tab_hash();

						mgm_tab_hash_reload();
					} 											  
				}); 
			}
		});
	}

	jQuery(function() {
		// add last item css
		jQuery('#mgm-panel-mainmenu li:last').addClass('last');	

		// load
		mgm_load_twin_tabs();
	});
</script>