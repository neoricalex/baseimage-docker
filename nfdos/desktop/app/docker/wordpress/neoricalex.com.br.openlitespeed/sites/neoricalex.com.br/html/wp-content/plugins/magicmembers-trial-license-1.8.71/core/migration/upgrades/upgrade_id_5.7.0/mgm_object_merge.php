<?php
/** 
 * Objects merge/update
 */ 
$wp_version_check = mgm_compare_wp_version( '4.2', '>=' );

//check
if($wp_version_check) {
	// saved object
	$system_obj_cached = mgm_get_option('system');	
	// disable testcookie
	if(isset($system_obj_cached->setting['disable_testcookie'])){
		//update
		$system_obj_cached->setting['disable_testcookie'] = 'Y';
	}
	// update
	update_option('mgm_system', $system_obj_cached);
}
 
// ends