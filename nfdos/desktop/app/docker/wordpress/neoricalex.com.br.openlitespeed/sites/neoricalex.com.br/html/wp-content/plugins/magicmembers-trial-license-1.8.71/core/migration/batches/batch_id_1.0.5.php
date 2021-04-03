<?php
/** 
 * Batch Upgrade
 * $Id 1.0.5
 */ 	
// current batch
$current_batch = '1.0.5';

// start
mgm_start_batch_upgrade( $current_batch );
/** 
 * Patch for updating the option: mgm_userids
 * mgm_userids is to store the user IDs of all users in the DB
 * This is to optimize the performance of mgm_get_all_userids
 * 
 */  
	
// Simply run the below function
// This will make sure that the option 'mgm_userids' is created and in syn with user IDs
// The option: 'mgm_userids' will be updated when user insert, delete and import happens
mgm_get_all_userids();

// end
mgm_end_batch_upgrade( $current_batch );
// end batch $Id 1.0.5