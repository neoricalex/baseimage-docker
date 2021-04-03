<?php
/** 
 * Objects merge/update
 * Add New capability: enable admin access and admin bar options to admin role
 */ 

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