<?php
/** 
 * Batch Upgrade
 * $Id 1.0.9
 */ 	
// current batch
$current_batch = '1.0.9';

// start
mgm_start_batch_upgrade( $current_batch );

// moved to upgrades due to object merge should run immediately

/** 
 * Objects merge/update
 * Update capability:  mgm_setting_enable_admin_access , mgm_setting_enable_admin_bar  to admin role
 */ 

/*

$obj_role = new mgm_roles();

$obj_role->update_capability_role('administrator', 'mgm_setting_enable_admin_access', true);
$obj_role->update_capability_role('administrator', 'mgm_setting_enable_admin_bar', true);

global $wp_roles;
//clean unwanted caps
$delete_caps = array('mgm_setting_block_admin_access', 'mgm_setting_disable_admin_bar');
//loop
foreach ($delete_caps as $cap) {
	//loop
	foreach (array_keys($wp_roles->roles) as $role) {
		//remove
		$wp_roles->remove_cap($role, $cap);
	}
}

*/
// end
mgm_end_batch_upgrade( $current_batch );
// end batch $Id 1.0.9