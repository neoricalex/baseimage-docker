<?php 
//if( ! function_exists('mgm_daily_cron_batch_call2') ){
	/**
	 * get response confirmation users mail to campaign admin
	 * 
	 * @deprecated
	 */
	// function mgm_daily_getresponse_confirmed_users() {
	/*function mgm_daily_cron_batch_call2() {
	
		// call
		// mgm_send_getresponse_confirmed_users();		

		// return as done
		return true;
	}*/
//}

	
/**
 * Check and update dataplus transactions (runs hourly)
 *
 * @deprecated moved to hourly batch call
 */
/*function mgm_hourly_epoch_dataplus_transactions() {
	// run
	// mgm_epoch_update_dataplus_transactions();	

	// return as done
	return true;
}*/

/**
 * Calculate and update widget data (runs hourly)
 * 
 * @deprecated, overuse of cron, utilizing wp transient cache
 */
/*function mgm_hourly_update_widget_data() {
	// run
	// mgm_update_dashboard_widget_data();

	// return as done
	return true;
}*/
	
/**
 * update  missing transaction date for Authorize.Net and other module
 * 
 * @deprecated moved to hourly batch call
 */
/*function mgm_hourly_update_transaction_data(){
	// run
	// mgm_update_transaction_data();

	// return as done
	return true;
}*/

/**
 * check limited memberships and extend or expire (runs every 2nd hourly)
 *
 * @deprecated moved to every15minute batch call
 */
/*function mgm_every15minute_batch_upgrade(){		
	// run
	mgm_process_batch_upgrades();

	// return as done
	return true;
}*/

/**
 * @todo
 * 
 * @deprecated
 */
/*function mgm_send_getresponse_confirmed_users(){
	// send
	return mgm_get_module('getresponse', 'autoresponder')->send_confirmation();
}*/
