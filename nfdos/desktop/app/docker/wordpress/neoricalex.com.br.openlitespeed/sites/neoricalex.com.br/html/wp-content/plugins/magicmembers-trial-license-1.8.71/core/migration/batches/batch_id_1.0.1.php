<?php
/** 
 * Batch Upgrade
 * $Id 1.0.1
 */ 
// current batch
$current_batch = '1.0.1';

// start
mgm_start_batch_upgrade( $current_batch );

// Restoring user _mgm_user_billing_num_cycles fields to user meta
$arr_users = mgm_get_all_userids();
// check
if(!empty($arr_users)) {
	
	foreach ($arr_users as $user_id) {			
		//member 
		$member = mgm_get_member($user_id);			

		if( mgm_get_user_option('_mgm_user_billing_num_cycles', $user_id)){
			// init
			$user_billing_num_cycles = 'ongoing';// ongoing
			// check limited
			if( isset($member->active_num_cycles) && (int)$member->active_num_cycles >= 1 ){
				// set			
				$user_billing_num_cycles = (int)$member->active_num_cycles;	
			}else{
				// check pack
				if( isset($member->pack_id) && (int)$member->pack_id > 0 ){
					// set		
					if( $pack = mgm_get_class('subscription_packs')->get_pack($member->pack_id) ){
						if ( isset($pack['num_cycles']) && (int)$pack['num_cycles'] >= 1 ){
							$user_billing_num_cycles = (int)$pack['num_cycles'];				
						}		
					}
				}
			}
			// update
			update_user_option($user_id, '_mgm_user_billing_num_cycles', $user_billing_num_cycles, true);						
		}
	}			
}

// end
mgm_end_batch_upgrade( $current_batch );	

// end batch $Id 1.0.1