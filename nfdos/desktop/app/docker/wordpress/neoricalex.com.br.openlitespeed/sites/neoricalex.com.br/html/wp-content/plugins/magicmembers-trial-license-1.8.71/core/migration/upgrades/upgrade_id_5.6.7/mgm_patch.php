<?php
	
/** 
 * Patch for updating the option: mgm_userids for mgm_update_userids() method failed users - issue #1913 - Fix
 * mgm_userids is to store the user IDs of all users in the DB
 * This is to optimize the performance of mgm_get_all_userids
 * 
 */  
/*
global $wpdb;
$fields = array('ID');
$result = array();
$limit = 1000;
$start = 0;
// Total records
$count  = $wpdb->get_var('SELECT count(*) from ' . $wpdb->users . ' WHERE ID <> 1');
if($count) {
	for( $i = $start; $i < $count; $i = $i + $limit ) {
		$result = array_merge($result, mgm_patch_partial_users($i, $limit, $fields, 'get_col'));
		//a small delay of 0.01 second
		usleep(10000);
	}
	// Update option with user ids
	update_option('mgm_userids', $result);
}*/