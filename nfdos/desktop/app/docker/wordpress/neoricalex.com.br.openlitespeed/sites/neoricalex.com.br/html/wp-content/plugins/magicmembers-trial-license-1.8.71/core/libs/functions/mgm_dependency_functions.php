<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members dependency functions
 *
 * @package MagicMembers
 * @subpackage tools
 * @since 2.6
 */

/**
 * check curl support
 */
function mgm_check_curl_support($key=NULL){
	// return
	return extension_loaded('curl');
}
/**
 * check simplexml support
 */
function mgm_check_simplexml_support($key=NULL){
	// return
	return extension_loaded('simplexml');
}

/**
 * check hash support
 */
function mgm_check_hash_support($key=NULL){
	// return
	return extension_loaded('hash');
}

/**
 * check mbstring support
 */
function mgm_check_mbstring_support($key=NULL){
	// return
	return extension_loaded('mbstring');
}

/**
 * check mysq support
 */
function mgm_check_mysql_support($key=NULL){
	global $wpdb;
	// version
	$db_version = $wpdb->db_version();
	// return
	return ((version_compare($db_version, '5.0.0')) >= 0) ? true : false;	

	// return
	// return ((version_compare(mysql_get_server_info(), '5.0.0')) >= 0) ? true : false;	
}

/**
 * check wordpress version support
 */
function mgm_check_wp_version_support($key=NULL){
	// return
	return mgm_compare_wp_version( '3.1.0', '>=' ) ? true : false;

	# ((version_compare($GLOBALS['wp_version'], '3.1.0')) >= 0) ? true : false;	
}

/**
 * check php version support
 */
function mgm_check_php_version_support($key=NULL){
	// return
	return ((version_compare(phpversion(), '5.2.0')) >= 0) ? true : false;	
}

/**
 * check http range support for heavy download
 */
function mgm_check_http_range_support($key=NULL){
	// url    
    $url = MGM_ASSETS_URL.'images/logo.png';
	// headers
	$h = wp_remote_head($url, array('timeout'=>30));	

	if(isset($h) && is_array($h)) {
		// return	
		return ( isset($h['headers']['accept-ranges']) 
			&& $h['headers']['accept-ranges'] == 'bytes') ? true : false;
	}	

	/*if ( is_wp_error($h) ) {

	}	*/

	return false;
}

/**
 * check https post support for payment gateways
 */
function mgm_check_https_post_support($key=NULL){
	// post url
	$post_url = 'https://httpbin.org/post';

	// post data
	$post_data = array('source'=>'Magic Members Test', 'timestamp'=>time());

	// version
	$product_version = mgm_get_class('auth')->get_product_info('product_version');
	
	// headers
	$http_headers = array (
		'Content-Type: application/x-www-form-urlencoded\r\n',
		'User-Agent : MagicMembers V.'.$product_version.'; '.home_url().'\r\n',
		'Connection: close\r\n\r\n'
	);		

	// args
	$http_args = array(
		'headers'=>$http_headers,'timeout'=>30,'sslverify'=>false, 'httpversion'=>'1.1',
		'user-agent'=>'MagicMembers V.'.$product_version.'; '.home_url()
	);		

	// args
	$args = array('body' => $post_data);
	
	// merge		
	if(is_array($http_args)) $args = array_merge($args, $http_args);	
	
	// request
	$request = wp_remote_post($post_url, $args);

	// log
	// mgm_log($request, __FUNCTION__);	

	// status
	$status_code = wp_remote_retrieve_response_code( $request );

	// log
	// mgm_log($status_code, __FUNCTION__);

	// wp error
	/*if( is_wp_error( $request ) ){
		$error = $request->get_error_message();

		// log
		// mgm_log($error, __FUNCTION__);
	}else{
		$body = wp_remote_retrieve_body( $request ); 

		// log
		// mgm_log($body, __FUNCTION__);
	}*/

	// return	
	return ((int)$status_code == 200);
}
/**
 * check tables loaded
 */
function mgm_check_dbtables_loaded($key=NULL){
	global $wpdb;		
	// wp tables
	$wp_tables  = mgm_get_wp_tables();
	// check
	$mgm_tables = mgm_get_tables();
	// deprecated tables not removed from tables define
	$mgm_deprecated_tables = array(TBL_MGM_DOWNLOAD_ATTRIBUTE,TBL_MGM_DOWNLOAD_ATTRIBUTE_TYPE);
	// return 
	$return = true;
	// prefix
	$t_prefix = $wpdb->prefix . MGM_TABLE_PREFIX;
	// check
	foreach($mgm_tables as $mgm_table){
		// only tables matching "wp_mgm_" at beginging
		if(preg_match('/^' . $t_prefix . '/i', $mgm_table)){
			// skip
			if(in_array($mgm_table, $mgm_deprecated_tables)) continue;
			// check
			if(!in_array($mgm_table, $wp_tables)){
				// log
				mgm_log(' missing table: '.$mgm_table, __FUNCTION__);
				// break
				$return = false; break;
			}
		}
	}
	
	// reload
	if( ! $return ){
		require_once(MGM_CORE_DIR . 'migration/install/mgm_schema.php');
	}	
	
	// return	
	return $return;
}
/**
 * check paypal ipn server port support
 */
function mgm_check_paypal_ipn_port_support($key=NULL){
	// return
	return (($_SERVER['SERVER_PORT'] == 80) ? true : false);	
}
/**
 * check xmlrpc support
 */
function mgm_check_xmlrpc_support($key=NULL){
	// return
	return extension_loaded('xmlrpc');
}
/**
 * check mcrypt support
 */
function mgm_check_mcrypt_support($key=NULL){
	// return
	return extension_loaded('mcrypt');
}
// end file