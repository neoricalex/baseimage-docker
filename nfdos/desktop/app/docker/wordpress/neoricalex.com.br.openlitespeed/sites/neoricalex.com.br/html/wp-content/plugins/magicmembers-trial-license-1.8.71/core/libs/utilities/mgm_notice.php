<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members notice handler utility class 
 *  
 * @package MagicMembers
 * @since 2.5.1
 */
class mgm_notice{	
	/**
	 * daily schedule not setup
	 * 
	 * @param void
	 * @return string
	 * @since 2.5.0
	 */
	public static function daily_schedule_not_setup(){					
		// message
		$message = __('MagicMembers schedular is not properly setup, this is required to run periodical updates and membership expiration.'. 
					  'Please deactivate and reactivate the plugin using plugin management screen, this will reinstall the schedular.','mgm');
		// show
		mgm_notice(__('Error!','mgm'), $message, 'error');				
	}
	
	/**
	 * file storage not writable 
	 * 
	 * @param void
	 * @return string
	 * @since 2.5.0
	 */
	public static function file_stotage_not_writable(){
		// folders
		$folders = array(sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads'),
		                 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/downloads'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/exports'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/modules'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/images'),
						 sprintf('<li>%s</li>', WP_CONTENT_DIR.'/uploads/mgm/logs'));
        // str
		$folder_structure = sprintf('<ul>%s</ul>', implode(' ',$folders));		
						
		// message
		$message = sprintf( __('MagicMembers files storage folder is not writable, please make sure "%s" is writable.<br>'. 
		                       'You can also manually create the file structure:<br> %s','mgm'), WP_CONTENT_DIR, $folder_structure);
		// show
		mgm_notice(__('Error!','mgm'), $message, 'error');
	}
	
	/**
	 * default permalink  error
	 * 
	 * @param void
	 * @return string
	 * @since 2.5.0
	 */
	public static function default_permalink_error(){	
		// message
		$message = __('Please update your permalink structure for MagicMembers to operate normally.'. 
			          'We recommend using "Post name" option which works better with MagicMembers.', 'mgm');
		// current
		if( 'options-permalink.php' != basename($_SERVER['PHP_SELF']) ){
			$message .= ' ' . sprintf( __('Use this <a href="%s">link</a> to update your permalink structure.','mgm'), 
			                admin_url('options-permalink.php') );	
		}

		// show
		mgm_notice( __('Information','mgm'), $message, 'error');				
	}

	/**
	 * show batch upgrade message
	 * 
	 * @param void
	 * @return string
	 * @since 2.6.1
	 */ 
	public static function batch_upgrade_required(){
		// message
		$message = __('MagicMembers database upgrade required! Please use the <b>Start Upgrade</b> button to upgrade now.'.
		              'You can also use <b>Queue Upgrade</b> option which will run and complete the upgrade in background.','mgm');
		// loading 
		$waiting = mgm_get_loading_icon( __('Starting Batch Upgrade...','mgm'));
		
		// buttons	        
		$buttons = sprintf('<p id="upgrade-buttons">'.
						   '<input type="button" class="button" value="%s" onclick="mgm_batch_upgrade_start()"> '.
		                   '<input type="button" class="button" value="%s" onclick="mgm_batch_upgrade_queue()"> '.
		                   '<input type="button" class="button" value="%s" onclick="mgm_batch_upgrade_cancel()"> '.
		                   '</p>
		                   <p>%s</p>
		                   <div class="clear"></div>', 
		                   __('Start Upgrade','mgm'), 
		                   __('Queue Upgrade','mgm'), 
		                   __('Not Today','mgm'), 
		                   $waiting);
		// show
		mgm_notice(__('Information','mgm'), $message . $buttons, 'error');		
	}	
}
// end of file core/libs/utilities/mgm_notice.php