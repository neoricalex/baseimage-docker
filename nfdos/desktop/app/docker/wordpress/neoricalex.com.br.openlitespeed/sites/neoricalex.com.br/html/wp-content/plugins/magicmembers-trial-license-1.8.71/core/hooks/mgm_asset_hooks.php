<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * asset hooks and callbacks
 *
 * @package MagicMembers
 * @since 2.7.0
 */

/**
 * enque screen wise front assets 
 *
 * @param string hook_suffix (file name)
 * @todo add all case
 */ 
function mgm_enqueue_scripts(){
	global $wp_version;	
}

/**
 * print styles
 */
function mgm_print_styles(){
	global $wp_styles;	
}

/**
 * enque screen wise admin assets 
 *
 * @param string hook_suffix (file name)
 * @todo add all case
 */
function mgm_admin_enqueue_scripts($hook_suffix){
	global $wp_version,$wp_styles,$wp_scripts;

	// check and disable jquery ui on mgm.admin
	if( $page = mgm_admin_page(false) ){
		// remove style by handler
		$ignore_style_handles = array(
			'jquery-ui-css','jquery-ui','aiow-plugin-css','smoothness','jqueryui',
            'sis_accordion_custom_style','jquery-ui-datepicker','slpcss-juiform',
            'jquery-ui-theme-latest','jquery-style','social-fans-admin-style-ui'
        );
		
		// remove by name
		foreach ($ignore_style_handles as $style_handle) {
			// check
			if( isset($wp_styles->registered[$style_handle]) ){
				unset($wp_styles->registered[$style_handle]);
			}
		}

		// remove by pattern	
		foreach( $wp_styles->registered as $style_handle => $wp_style ){
			$src = $wp_style->src;

			if( ! preg_match('/^mgm/', $style_handle) ){
				if( preg_match('/jquery-ui/', $src) || preg_match('/jquery.ui/', $src) ){
					unset($wp_styles->registered[$style_handle]);
				}
			}	
		}

		// remove script by handler
		$ignore_script_handles = array('jquery-ui','accordiantotab-js','1.11.4');
		// reset
		foreach ($ignore_script_handles as $script_handle) {
			// check
			if( isset($wp_scripts->registered[$script_handle]) ){
				unset($wp_scripts->registered[$script_handle]);
			}
		}

		// remove by pattern	
		/*foreach( $wp_scripts->registered as $script_handle => $wp_script ){
			$src = $wp_script->src;

			if( ! preg_match('/^mgm/', $script_handle) ){
				if( preg_match('/jquery-ui/', $src) || preg_match('/jquery.ui/', $src) ){
					unset($wp_scripts->registered[$script_handle]);
				}
			}
		}*/
	}// end admin ui

	// screen
	$screen_id = get_current_screen()->id;	
	// edit taxonomy
	if(preg_match('/^edit-/', $screen_id)){
		if( 'edit-tags.php' == $hook_suffix ){
			$screen_id = 'edit-taxonomy';
		}
	}

	//echo $screen_id;
	
	// post screen
	switch($screen_id){
		case 'post':// post edit,add
		case 'page':// page @todo custompage
		// case 'edit-post':// post listing
			// load respective jQueryUI	
			// $jqueryui_version =  sprintf('js/jquery/jquery.ui/jquery-ui-%s.min.js', mgm_get_jqueryui_version());
			// load ui
			// wp_enqueue_script('mgm-jquery-ui', (MGM_ASSETS_URL . $jqueryui_version), array('jquery'));

			// core
			wp_enqueue_script('jquery-ui-datepicker', null, array('jquery-ui-core','jquery-ui-widget')); 
			// custom scripts
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js'); 				
			
			// ui css	
			wp_enqueue_style('mgm-jqueryui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );	
			wp_enqueue_style('mgm-widgets-css', MGM_ASSETS_URL . 'css/admin/mgm.widgets.css' );				
		break;
		case 'edit-category':
		case 'edit-post_tag':
		case 'edit-taxonomy':
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// wp 3.3+ fix
			if ( mgm_compare_wp_version( '3.3', '>' ) ){
				wp_enqueue_style('mgm-adminui-wp3fix-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.wp3fix.css' );
			}
		break;
		case 'users':// list
		// case 'user':// add
		case 'user-edit':// edit
		case 'user-edit-network':// network edit		
			wp_enqueue_style('mgm-users-css', MGM_ASSETS_URL . 'css/admin/mgm.users.css' );
		case 'profile':
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js'); 	
			//wp_dequeue_script('jquery-blockui');
			//wp_dequeue_script('select2');	

			//wp_unregister_script('jquery-blockui');
			//wp_unregister_script('select2');

			//$wc_clashes = array('jquery-blockui','select2');

			/*foreach ($wc_clashes as $wc_clash) {
				if( isset($wp_scripts->registered[$wc_clashes]) ){
					unset($wp_scripts->registered[$wc_clash]);
				}
			}*/

		break;		
		case 'dashboard':
			wp_enqueue_style('mgm-dashboard-css', MGM_ASSETS_URL . 'css/admin/mgm.dashboard.css' );
		break;
		case 'profile_page_mgm/membership_details':
		case 'profile_page_mgm/membership_contents':
			// profile pages
			// ui css	
			wp_enqueue_style('mgm-ui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );								
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// styles
			wp_enqueue_style('mgm-wp-profile-css', MGM_ASSETS_URL . 'css/admin/mgm.wp-profile.css' );	
		break;
		case 'toplevel_page_mgm.admin':// mgm admin ui
			
			// load respective jQueryUI	
			//$jqueryui_version =  sprintf('js/jquery/jquery.ui/jquery-ui-%s.min.js', mgm_get_jqueryui_version());

			// load ui
			//wp_enqueue_script('mgm-jquery-ui', (MGM_ASSETS_URL . $jqueryui_version), array('jquery'));	

			// use core from wp
			wp_enqueue_script( 'jquery-ui-tabs', null, array('jquery','jquery-ui-core','jquery-ui-widget') );
			wp_enqueue_script( 'jquery-ui-datepicker' ); 
			wp_enqueue_script( 'jquery-ui-accordion' ); 
			wp_enqueue_script( 'jquery-ui-sortable' ); 
			wp_enqueue_script( 'jquery-form' ); 

			//
			
			wp_enqueue_script('mgm-jquery-browser', MGM_ASSETS_URL . 'js/jquery/browser/jquery.browser.min.js'); 	
			
			// helpers scripts		
			wp_enqueue_script('mgm-jquery-validate', MGM_ASSETS_URL . 'js/jquery/validate/jquery.validate.min.js');
			
			// wp_enqueue_script('mgm-jquery-form', (MGM_ASSETS_URL . 'js/jquery/jquery.form.js'));  
			//wp_enqueue_script('mgm-jquery-metadata', (MGM_ASSETS_URL . 'js/jquery/jquery.metadata.js'));
			
			// custom scripts
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js'); 		
			wp_enqueue_script('mgm-string', MGM_ASSETS_URL . 'js/string.js'); 						
			wp_enqueue_script('mgm-jquery-helpers', MGM_ASSETS_URL . 'js/jquery/jquery.helpers.js'); 		
				
			// helpers scripts		
			wp_enqueue_script('mgm-jquery-ajaxupload', MGM_ASSETS_URL . 'js/jquery/jquery.ajaxfileupload.js'); // ?? is used
			wp_enqueue_script('mgm-jquery-scrollto', MGM_ASSETS_URL . 'js/jquery/jquery.scrollTo-min.js');
			wp_enqueue_script('mgm-jquery-corner', MGM_ASSETS_URL . 'js/jquery/jquery.corner-2.13.js');	
			// wp_enqueue_script('mgm-jquery-tools', MGM_ASSETS_URL . 'js/jquery/jquery.tools.min.js');				
			// wp_enqueue_script('mgm-jquery-messi', MGM_ASSETS_URL . 'js/jquery/messi/messi.min.js');				
			wp_enqueue_script('mgm-nicedit', MGM_ASSETS_URL . 'js/nicedit/nicedit.js');		
			wp_enqueue_script('mgm-checkboxtree', MGM_ASSETS_URL . 'js/jquery/jquery.tree.js');			
			wp_enqueue_script('mgm-excanvas-min', MGM_ASSETS_URL . 'js/flot/excanvas.min.js');			
			wp_enqueue_script('mgm-jquery-flot', MGM_ASSETS_URL . 'js/flot/jquery.flot.js');					
			
			// ui css	
			wp_enqueue_style('mgm-ui-css', MGM_ASSETS_URL . 'css/default/mgm/jquery.ui.css' );								
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );	
			// wp 3.3+ fix
			if ( mgm_compare_wp_version( '3.3', '>' ) ){
				wp_enqueue_style('mgm-adminui-wp3fix-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.wp3fix.css' );
			}
			// other
			// wp_enqueue_style('mgm-admin-overlay-css', MGM_ASSETS_URL . 'css/admin/jquery.overlay.css' );	
			// wp_enqueue_style('mgm-admin-overlay-css', MGM_ASSETS_URL . 'css/admin/messi/messi.min.css' );	
			wp_enqueue_style('mgm-admin-checkboxtree-css', MGM_ASSETS_URL . 'css/admin/jquery.tree.css' );
		break;
		case 'widgets':
			// styles
			wp_enqueue_style('mgm-widgets-css', MGM_ASSETS_URL . 'css/admin/mgm.widgets.css' );	
		break;
		case 'profile_page_mgm/profile':
		case 'profile_page_mgm/membership/content':
			// styles
			wp_enqueue_style('mgm-adminui-css', MGM_ASSETS_URL . 'css/admin/mgm.adminui.css' );
			wp_enqueue_style('mgm-pages-css', MGM_ASSETS_URL . 'css/default/mgm.pages.css' );
		break;		
	}	
	// general
	wp_enqueue_style('mgm-general-css', MGM_ASSETS_URL . 'css/admin/mgm.general.css' );	
	wp_enqueue_script('mgm-general-js', MGM_ASSETS_URL . 'js/general.js');
}

/**
 * enque screen wise wp-login assets 
 *
 * @param void
 */
function mgm_login_enqueue_scripts(){
	global $wp_version;	
	
	// load ui	
	// wp_enqueue_script('mgm-login-js', MGM_ASSETS_URL . 'js/wp-login.js', array('jquery'));		
}

/**
 * login footer
 *
 * @param void
 */
function mgm_login_footer_scripts(){
	global $wp_version;	
	
	if(bool_from_yn(mgm_get_setting('enable_email_as_username'))):?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			if ( document.getElementById('loginform') )
				document.getElementById('loginform').childNodes[1].childNodes[1].childNodes[0].nodeValue = '<?php echo esc_js( __( 'Username or Email', 'email-login' ) ); ?>'; 		
			
	    });
	</script>
	<?php
	endif;
}

/**
 * enque screen wise wp site assets 
 *
 * @param void
 */
function mgm_wp_enqueue_scripts(){
	global $wp_version;	
	
	// jquery
	wp_enqueue_script('jquery');
	wp_enqueue_script('jquery-ui-datepicker', '', array('jquery','jquery-ui-core','jquery-ui-widget'));
}	 

/**
 * @todo
 */
function mgm_admin_print_footer_scripts(){
	global $hook_suffix;

	switch( $hook_suffix ){
		case 'user-edit.php':
		case 'profile.php':
		?>
		<script type="text/javascript">
			mgm_move_profile_blocks=function(){
				var after_id = 'mgm-additional-seperator';

				var hr = jQuery('#profile-page').after('<hr id="'+after_id+'"/>');

				jQuery('#your-profile').find("div[id^='mgm-additional']").each(function(){

					//console.log(after_id);

					var this_id = jQuery(this).attr('id');

					//console.log(this_id);
					

					cloned = jQuery(this).clone(true);
					jQuery(this).remove();
					
					jQuery('#'+after_id).after(cloned);

					jQuery(cloned).show();

					after_id = this_id;
				});
			}
			jQuery(document).ready(function(){
				// mgm_move_profile_blocks();

				jQuery('#your-profile').find("div[id^='mgm-additional']").show();
			});
		</script>
		<?php
		break;
	}
}  

/**
 * Load assets
 * @param void
 */
function mgm_load_assets(){
	// add action	
	add_action('wp_enqueue_scripts '   , 'mgm_enqueue_scripts', 10);
	add_action('wp_print_styles'       , 'mgm_print_styles', 10);
	add_action('admin_enqueue_scripts' , 'mgm_admin_enqueue_scripts', 10, 1); 
	add_action('login_enqueue_scripts' , 'mgm_login_enqueue_scripts', 10);
	add_action('wp_enqueue_scripts'    , 'mgm_wp_enqueue_scripts', 11);
	add_action('login_footer'          , 'mgm_login_footer_scripts');

	add_action('admin_print_footer_scripts', 'mgm_admin_print_footer_scripts');

	/*
	if (version_compare(get_bloginfo('version'), '3.3', '<')) {
		mgm_wp_enqueue_scripts();
	}else{
		add_action('wp_enqueue_scripts', 'mgm_wp_enqueue_scripts', 11);
	}*/

	/*if( has_action('admin_enqueue_scripts', 'WC_Admin_Assets::admin_scripts')){
		remove_action('admin_enqueue_scripts', 'WC_Admin_Assets::admin_scripts');
	}*/
}
// add
add_action('init', 'mgm_load_assets');	
// end of file