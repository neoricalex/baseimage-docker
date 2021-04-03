<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * batch upgrader hooks and callbacks
 *
 * @package MagicMembers
 * @since 2.6.1
 */

/**
 * check batch upgrades
 * 
 * @param string $return ( count|list|stats )
 * @return mixed
 * @since 2.6.1
 */ 
function mgm_get_batch_upgrades( $return ){	
	// check if exists
	if( $stats = get_option('mgm_batch_upgrade_stats') ){
		// return
		return mgm_return_batch_upgrade( $stats, $return );			
	}	

 	// get last upgrade version 
	$batch_upgrade_id = get_option('mgm_batch_upgrade_id');	
	// log
	/*if ( ! defined('DOING_AJAX') ){
		mgm_log( 'batch check start, last batch_upgrade_id: ' . $batch_upgrade_id, __FUNCTION__);
	}*/	

	// init
	$upgrades = array();
	// batches
	if( $batches = glob( MGM_BATCH_UPGRADE_DIR . 'batch_id_*.php' ) ){
		// filter ids
		$batch_ids = array_map('mgm_filter_batch_ids', $batches);
		// log
		/*if ( ! defined('DOING_AJAX') ){
			mgm_log( 'list all batch_ids: ' . mgm_pr($batch_ids, true), __FUNCTION__);	
		}*/	
		// loop
		foreach( $batch_ids as $batch_id ){
			// compare
			if( version_compare($batch_id, $batch_upgrade_id, '>') ){// fix for minor version checking	
			// in	
				$upgrades[] = $batch_id;
			}	
		}
		// log
		/*if ( ! defined('DOING_AJAX') ){
			mgm_log( 'pending upgrade batch_ids: ' . mgm_pr($upgrades, true), __FUNCTION__);	
		}*/	
	}	

	// init
	$stats = array('status'=>'error','message'=>__('No upgrades','mgm'),'upgrades'=>$upgrades);
	// check has upgrades
	if( ! empty($upgrades) ){	
		// current
		$current_batch = $upgrades[0];
		// stats
		$stats['status']        = 'waiting';
		$stats['message']       = sprintf(__('Batch %s waiting...','mgm'), $current_batch);
		$stats['percent_done']  = 0;
		$stats['percent_unit']  = ceil(100 / count($upgrades));	
		$stats['current_batch'] = $current_batch;		
		// update
		update_option( 'mgm_batch_upgrade_stats', $stats );	
		// log
		/*if ( ! defined('DOING_AJAX') ){
			mgm_log( 'init batch upgrade stats: ' . mgm_pr($stats, true), __FUNCTION__);	
		}*/	
	}

	// return
	return mgm_return_batch_upgrade( $stats, $return );	
}

/**
 * return values for batch upgrade
 * 
 * @param array $stats
 * @param string $return
 * @return mixed
 */ 
function mgm_return_batch_upgrade( $stats, $return ){
	// return list
	if( $return == 'list' ){
		return $stats['upgrades'];
	}
	// return count
	if( $return == 'count' ){
		return count($stats['upgrades']);
	}
	// return current_batch
	if( $return == 'current_batch' ){
		return count($stats['current_batch']);
	}
	// return all
	return $stats;
}

/**
 * filter batch ids
 * 
 * @param string $f
 * @return string
 */
function mgm_filter_batch_ids( $f ){
 // return	
 	return str_replace( 'batch_id_', '', basename($f, '.php') );
} 

/**
 * process batch upgrade
 * 
 * @param string $return (json|array|void)
 * @return string
 * @since 2.6.1
 */
function mgm_process_batch_upgrades( $return='void' ){
  	// init
	// $counter = (int)mgm_post_var('counter');
	// get stats
	$stats = mgm_get_batch_upgrades( 'stats' );
	
	// check percent
	if( isset($stats['percent_done']) && $stats['percent_done'] >= 100 ){
		// done
		$stats['percent_done'] = 100;
		// message
		$stats['message'] = __('Batch upgrade complete.','mgm');
		// delete
		delete_option( 'mgm_batch_upgrade_stats' );
		// clear event
		if( $background = get_option('mgm_batch_upgrade_in_background') ){
			// remove
			// mgm_remove_schedule( 'every15minute', 'batch_upgrade' );
			// delete
			delete_option('mgm_batch_upgrade_in_background');
		}
	}	

	// run
	if( isset( $stats['current_batch'] ) && ! mgm_is_batch_upgrade_running( $stats['current_batch'] ) ){		
		// path
		$batch_filepath = MGM_BATCH_UPGRADE_DIR . sprintf('batch_id_%s.php', $stats['current_batch']);
		// check
		if( file_exists( $batch_filepath ) ){
			// time
			@set_time_limit(300);//300s
			@ini_set('memory_limit', 134217728);// 128M
			// include upgrade
			@include_once($batch_filepath);					
		}		
	}

	// log
	// mgm_log( 'batch upgrade process stats: ' . mgm_pr($stats, true), __FUNCTION__);	

	// return
	if( $return == 'json' ){
		// response
		return json_encode( $stats );
	}	
} 

/**
 * add schedule
 */
function mgm_add_cron_for_batch_upgrades(){
	// add
	// mgm_add_schedule( 'every15minute', 'batch_upgrade' );
	// track
	update_option('mgm_batch_upgrade_in_background', time());
	// log
	// mgm_log( 'Batch upgrade started in background.', __FUNCTION__);	
	// response
	return json_encode( array('status'=>'success', 'message'=>__('Batch upgrade started in background.', 'mgm')) );
}

/**
 * cancel upgrade
 */
function mgm_cancel_batch_upgrades(){
	// set cache		
	set_transient('mgm_batch_upgrade_cancelled', time(), 60*60*24);// 24 hr in cache

	// response
	return json_encode( array('status'=>'success', 'message'=>__('Batch upgrade cancelled for today.', 'mgm')) );
}

/**
 * start batch upgrade
 * 
 * @param string $batch_id
 * @return void
 */
function mgm_start_batch_upgrade( $batch_id ){
	// get stats
	$stats = mgm_get_batch_upgrades( 'stats' );
	// stats
	$stats['status'] = 'running';	
	$stats['message'] = sprintf(__('Batch %s %s...','mgm'), $batch_id, $stats['status']);
	$stats['current_batch'] = $batch_id;
	// update
	update_option( 'mgm_batch_upgrade_stats', $stats ); 

	// log
	// mgm_log( $stats['message'], 'batch_' . $batch_id . '_' . __FUNCTION__);	
}   

/**
 * end batch upgrade
 * 
 * @param string $batch_id
 * @return void
 */
function mgm_end_batch_upgrade( $batch_id ){
	// get
	$stats = mgm_get_batch_upgrades( 'stats' );
	// stats
	$stats['status'] = 'finished';	
	$stats['message'] = sprintf(__('Batch %s %s.','mgm'), $batch_id, $stats['status']);
	// upgrades
	$upgrades = $stats['upgrades'];	
	$pending = array();
	// loop
	foreach( $upgrades as $upgrade_id ){
		// done
		if( $batch_id == $upgrade_id ) continue;
		// pending
		$pending[] = $upgrade_id;
	}
		
	// check
	if( empty($pending) ){
		// done
		$stats['percent_done'] = 100;
		// unset
		unset($stats['current_batch']);
	}else{
		// take first
		$stats['percent_done'] = $stats['percent_done'] + $stats['percent_unit'];
		$stats['current_batch'] = $pending[0];
	}

	// set
	$stats['upgrades'] = $pending;
	
 	// update
	update_option( 'mgm_batch_upgrade_stats', $stats );
	// batch id
	update_option( 'mgm_batch_upgrade_id', $batch_id );

	// log
	// mgm_log( $stats['message'], 'batch_' . $batch_id . '_' . __FUNCTION__);	
}   

/**
 * check if ruiing
 * 
 * @param string $batch_id
 * @return bool
 */
 function mgm_is_batch_upgrade_running( $batch_id ) {
 	// get
	$stats = mgm_get_batch_upgrades( 'stats' );

	// return
	if( isset($stats['current_batch']) && $stats['current_batch'] == $batch_id ){
		// check status
		if( 'running' == $stats['status'] ){
			return true;
		}
	}

	// return
	return false;	
 }
// end of file /core/libs/helpers/mgm_batch_upgrader.php