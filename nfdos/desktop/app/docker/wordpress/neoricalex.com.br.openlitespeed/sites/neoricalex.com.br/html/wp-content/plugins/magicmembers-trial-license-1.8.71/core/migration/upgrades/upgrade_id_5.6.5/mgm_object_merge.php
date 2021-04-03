<?php
/** 
 * Objects merge/update
 */ 
 // saved object
$system_obj_cached = mgm_get_option('system');

// set new vars
if(!isset($system_obj_cached->setting['enable_debug_log'])){
	$system_obj_cached->setting['enable_debug_log'] = 'Y'; 	
}	
// update
update_option('mgm_system', $system_obj_cached);