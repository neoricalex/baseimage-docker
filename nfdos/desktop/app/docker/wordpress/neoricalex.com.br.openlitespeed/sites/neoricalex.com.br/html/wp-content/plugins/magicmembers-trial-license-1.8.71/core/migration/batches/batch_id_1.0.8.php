<?php
/** 
 * Batch Upgrade
 * $Id 1.0.8
 */ 	
// current batch
$current_batch = '1.0.8';

// start
mgm_start_batch_upgrade( $current_batch );

/** 
 * Objects merge/update
 * Update packs:   don't expire the user after the last billing cycle setting.
 */

// moved to upgrades due to object merge should run immediately

/*
	//pack obj
	$obj_packs = mgm_get_option('subscription_packs');
	//init
	$arr_packs = array();
	//log
	mgm_log("Before : ".mgm_pr($obj_packs->packs,true),"batch_upgrade");
	//loop
	foreach ($obj_packs->packs as $pack) {
		//init
		$pack['allow_expire']=1;
		//assign
		$arr_packs[] = $pack;	
	}
	//log
	mgm_log("arr packs : ".mgm_pr($arr_packs,true),"batch_upgrade");
	//check
	if(!empty($arr_packs)) {
		//assign
		$obj_packs->packs = $arr_packs;
		//update
		update_option('mgm_subscription_packs', $obj_packs);
	}
	//log
	mgm_log("After : ".mgm_pr($obj_packs->packs,true),"batch_upgrade");


*/

// end
mgm_end_batch_upgrade( $current_batch );
// end batch $Id 1.0.8