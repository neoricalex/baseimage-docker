<?php
/** 
 * Batch Upgrade
 * $Id 1.0.0
 */  
// current batch
$current_batch = '1.0.0';

// start
mgm_start_batch_upgrade( $current_batch );

// Restoring custom fields to user meta
$arr_users = mgm_get_all_userids();
// check
if(!empty($arr_users)) {	
	foreach ($arr_users as $user_id) {			
		//member 
		$member = mgm_get_member($user_id);			
		// save
		if(isset($member->custom_fields)){	
			// loop		
			foreach ($member->custom_fields as $key => $value) {				
				// verify
				if( $user_id ){
					// key 
					$options_key = '_mgm_cf_' . $key;
					// update
					update_user_option($user_id, $options_key, $value, true);	
				}				
			}
		}			
	}			
}

// end
mgm_end_batch_upgrade( $current_batch );	
// end batch $Id 1.0.0