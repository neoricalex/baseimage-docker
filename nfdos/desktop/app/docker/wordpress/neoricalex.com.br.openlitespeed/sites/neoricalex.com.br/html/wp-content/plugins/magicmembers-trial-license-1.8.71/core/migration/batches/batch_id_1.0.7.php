<?php
/** 
 * Batch Upgrade
 * $Id 1.0.7
 */ 	
// current batch
$current_batch = '1.0.7';

// start
mgm_start_batch_upgrade( $current_batch );

// moved to upgrades due to object merge should run immediately

/** 
 * Objects merge/update
 * Update capability:  mgm_other , mgm_redirection  to admin role
 */ 

/*
$obj_role = new mgm_roles();

$obj_role->update_capability_role('administrator', 'mgm_redirection', true);
$obj_role->update_capability_role('administrator', 'mgm_other', true);

*/

// end
mgm_end_batch_upgrade( $current_batch );
// end batch $Id 1.0.7