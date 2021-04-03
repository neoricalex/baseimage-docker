<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members auth class
 * extends object to save options to database
 *
 * license spelling fixed
 * 
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_auth extends mgm_object{
	// vars	
	var $license_key     = '';
	var $activation_date = '';
	var $expire_date     = ''; 
	var $is_valid        = false;
	var $product_info    = array();
	var $cache_timeout   = 3600;// (60*60*1) - 1 hr in cache
	
	// construct
	function __construct(){
		// php4
		$this->mgm_auth();
	}
	
	// php4 construct
	function mgm_auth(){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults();			
		// read vars from db
		$this->read();// read and sync
	}
	
	// defaults
	function _set_defaults(){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Auth Lib';
		// description
		$this->description = 'Auth Lib';
		// valid
		$this->is_valid = false;
		// configure service properties
		$this->configure_service_properties();	
	}
	
	// re configure product info 	
	function reconfigure(){
		// check
		if(!$this->product_info){
			// reconfigure
			$this->configure_service_properties();
		}
	}
	
	// configure service properties
	function configure_service_properties(){
		// defined constants
		// $this->constants[] = get_defined_constants(true);
		
		// read ini configs
		$this->read_ini();
		
		// set product_version, used for distribution, required
		$this->set_service_property('product_version',array('ini'=>'product_version','const'=>'MGM_PRODUCT_VERSION','default'=>'1.5'));
		
		// set product_id, wpsc product id for reference, must set per product in wpsc for activation to work, required
		$this->set_service_property('product_id',array('ini'=>'product_id','const'=>'MGM_PRODUCT_ID','default'=>'1'));

		// set product_guid, wpsc product guid for reference, must set per product in wpsc for activation to work, required
		$this->set_service_property('product_guid',array('ini'=>'product_guid','const'=>'MGM_PRODUCT_GUID','default'=>'magic-members-single-license'));
		
		// set product_name, product name for reference, required
		$this->set_service_property('product_name',array('ini'=>'product_name','const'=>'MGM_PRODUCT_NAME','default'=>'Single License'));
		
		// set product_brand, uniquely identify each product brand, required
		$this->set_service_property('product_brand',array('ini'=>'product_brand','const'=>'MGM_PRODUCT_BRAND','default'=>'magic-members'));
		
		// set product_url, product url for renew, optional
		$this->set_service_property('product_url',array('ini'=>'product_url','const'=>'MGM_PRODUCT_URL','default'=>'products-page/plugins/magic-members-single-license/'));
		
		// set product_service_domain
		$this->set_service_property('product_service_domain',array('ini'=>'product_service_domain','const'=>'MGM_SERVICE_DOMAIN','default'=>'https://www.magicmembers.com/'));		
		
		// set license api version
		$this->set_service_property('license_api_version',array('ini'=>'license_api_version','const'=>'MGM_LICENSE_API_VERSION','default'=>'1.0'));		

		// set product_build, used for distribution, optional
		$this->set_service_property('product_build',array('ini'=>'product_build','const'=>'MGM_BUILD','default'=>'2.5.0'));
		
		// set product_stage, used for distribution, optional
		$this->set_service_property('product_stage',array('ini'=>'product_stage','const'=>'MGM_STAGE','default'=>'dev'));
		
		// intenral, not configurable

		// service site, v1
		$this->product_service_site = $this->product_service_domain . 'wp-content/plugins/mgms/mgms.php?action=';
		
		// service site, v2
		$this->product_service_site_api = $this->product_service_domain . 'mgmsapi/';
		
		// install host
		$this->product_install_host = site_url();
		
		// set commands
		$this->product_license_check_url       = $this->build_request_info('activate');
		$this->product_version_check_url       = $this->build_request_info('check_version');
		$this->product_message_check_url       = $this->build_request_info('get_message');
		$this->product_subscription_check_url  = $this->build_request_info('check_subscription');	
		$this->product_notify_deactivation_url = $this->build_request_info('notify_deactivation');		
		$this->product_upgrade_api_check_url   = $this->build_request_info('check_upgrade_api_status');

		// log
		// mgm_log( $this, __FUNCTION__);
	}
	
	// build_request_info
	function build_request_info($action){
		// info
		if(!$this->product_information){
			// info
			$info = $this->get_request_info();
			// set
			$this->product_information = _http_build_query( $info, null, '&', '', true );//mgm_http_build_query($info);
		}			
		
		// api version 2.0
		if( $this->get_license_api_version('2.0') ){
			return $this->product_service_site_api  . '?action=' . $action . '&' . $this->product_information;
		}else{
		// return 
			return $this->product_service_site . $action . '&' . $this->product_information;// in secure /wp-content
		}	
	}
	
	// get request info
	function get_request_info($license=false){
		// info
		$info = array(
			'product_id'=>$this->product_id, 'product_guid'=>$this->product_guid,'product_name'=>$this->product_name, 
			'product_brand'=>$this->product_brand,'version'=>$this->product_version, 'host'=>$this->product_install_host, 
			'build'=>$this->product_build, 'stage'=>$this->product_stage
		);
					  
		// get keys
		if($license){
			// query
			if($keys = $this->get_key('data')){
				// loop
				$query = array('email'=>$keys[0]);
				// set license
				if(isset($keys[1]) && !empty($keys[1])){
					$query['license'] = $keys[1];
				}
				// set
				$info = array_merge($info, $query);
			}		
		}	
			  
		// return
		return $info;				  
	}
	
	/**
	 * get api version
	 */
	function get_license_api_version($version='2.0'){
		return version_compare($this->license_api_version, $version, '=');
	}  

	// set
	function set_service_property($key, $property=array()){
		// check
		if(isset($this->product_info[$property['ini']])){
			// set
			$this->{$key} = stripslashes($this->product_info[$property['ini']]); 
		}elseif(defined($property['const'])){
			// set
			$this->{$key} = constant($property['const']);
		}else{
			// set
			$this->{$key} = $property['default'];
		}
	}
	
	// read ini
	function read_ini(){
		// init
		$this->product_info = array();				
		// check
		if(file_exists(MGM_BASE_DIR . 'config/product.ini.php')){
			// check support
			if(function_exists('parse_ini_file')){				
				// read
				if($info = parse_ini_file(MGM_BASE_DIR . 'config/product.ini.php')){								
					// set
					$this->product_info = $info;					
				}
			}
		}		
		// read constants
		if(empty($this->product_info) && file_exists(MGM_BASE_DIR . 'config/product.const.php')){
			// include
			@include_once(MGM_BASE_DIR . 'config/product.const.php');
		}		
		// return
		return $this->product_info;
	}
	
	// set key
	function set_key($email, $license){
		// set license
		$this->license_key = base64_encode(implode('|',array($email,$license,date('Ymd'),get_option('siteurl'))));		
		// activation
		$this->activation_date = date('Y-m-d H:i:s');
		// expire
		$this->expire_date = date('Y-m-d H:i:s', strtotime('+1 YEAR'));		
		// save
		$this->save();
	}
	
	// get key
	function get_key($return='check'){		
		// license not set
		if(empty($this->license_key)){
		// check history
			if($this->verify_history())
			// return found
				return true;	
			// default				
			return false;
		}
		
		// check parts		
		$auth_token = explode('|',base64_decode($this->license_key));
		
		// check
		if($return == 'data'){
			// data
			return $auth_token;
		}
		
		// single check
		if(preg_match('/\@/',$auth_token[0])){
			return true;
		}	
				
		// default
		return false;
	}
	
	// verify key
	function verify(){		
		// validate
		return $this->is_valid = $this->get_key();	
	}
	
	// verify history old key
	function verify_history(){
		// old key
		if(get_option('mgm_license_key') || get_option('v1_mgm_license_key')){
			// set as true
			return true;
			/***********************************************************************************************************************
			// license
			$license_key     = get_option('v1_mgm_license_key') ? get_option('v1_mgm_license_key') : get_option('mgm_license_key');
			$activation_date = get_option('v1_mgm_licensing_activation_date') ? get_option('v1_mgm_licensing_activation_date') : get_option('mgm_licensing_activation_date');
			// check parts		
			$auth_token = explode('|',base64_decode($license_key));
			$this->set_key($auth_token[0], $license);
			*************************************************************************************************************************/
		}
		// return
		return false;
	}
	
	// validate 
	function validate_subscription($email){		
		// request url	
		$request_url = add_query_arg(array('email'=>$email,'format'=>'plain'), $this->product_license_check_url);
		// log
		// mgm_log($request_url, __FUNCTION__);
		// validate
		$response = mgm_remote_get($request_url, NULL,  NULL, 'Could not connect');	

		// log
		//mgm_log($response, __FUNCTION__);

		// check data
		if(strpos($response, '|') !== FALSE){
			list($status, $license) = explode('|', $response);
		}else{
			$status  = $response;
			$license = md5(uniqid(mt_rand()));
		}
		// when equal
		if (trim($status) == trim('SUCCESSFUL')) {	
			// set license
			$this->set_key($email, $license);	
			// send true
			return true;
		}
		// send error message
		return $status;		
	}
	
	// check_version
	function check_version(){		
		// check cache
		if(!$current_version = get_transient('mgm_current_version')){		
			// url
			$request_url = $this->_get_subscription_request_url($this->product_version_check_url);		
			// log url
			// mgm_log($request_url, __FUNCTION__);
			// get
			$current_version = mgm_remote_get($request_url, NULL, NULL, 'No version information');
			// log url
			// mgm_log($current_version, __FUNCTION__);
			// set cache		
			set_transient('mgm_current_version', $current_version, $this->cache_timeout);
		}
		// return 
		return $current_version;
	}
	
	/**
	 * check upgrade api 2.0 availability
	 *
	 * @param bool $return
	 * @return void
	 */
	function check_auto_upgrader_api($return=false) {
		// return if already done
		if( 'Active' == get_option('mgm_auto_upgrader_api') ) return;

		// check cache
		if(!$api_status = get_transient('mgm_auto_upgrader_api_status')){			
			// url
			$request_url = $this->_get_subscription_request_url($this->product_upgrade_api_check_url);	
			
			// request
			$api_status = mgm_remote_get($request_url, NULL, NULL, 'Auto upgrader api not available');		
			
			// set cache		
			set_transient('mgm_auto_upgrader_api_status', $api_status, 60*60*1);// 1 hr in cache		
		}	

		// update
		if( 'Active' == trim($api_status) ){
			update_option('mgm_auto_upgrader_api', $api_status);
		}	
	}

	// check version via api 2.0
	function check_version_api($args){
		// args
		$args = array_merge($args, $this->get_request_info(true));
				
		// return
		return $this->api_request($args);
	}
	
	// get information via api 2.0 
	function get_information_api($args){
		// convert to array
		if(!is_array($args)) $args = (array)$args;
		
		// args
		$args = array_merge($args, $this->get_request_info(true));
				
		// return
		return $this->api_request($args);
	}
	
	// get messages
	function get_messages(){		
		// check cache
		if(!$current_messages = get_transient('mgm_current_messages')){
			// get
			$current_messages = mgm_remote_get($this->product_message_check_url, NULL, NULL, 'No messages');
			// set cache		
			set_transient('mgm_current_messages', $current_messages, $this->cache_timeout);
		}
		// return 
		return mgm_stripslashes_deep($current_messages);	
	}
	
	// get_subscription_status
	function get_subscription_status(){		
		// init
		$subscription_status = '';				
		// check cache
		if(!$subscription_status = get_transient('mgm_subscription_status')){			
			// @todo
			$request_url = $this->_get_subscription_request_url($this->product_subscription_check_url);
			// log
			// mgm_log($request_url, __FUNCTION__);
			// actual request
			$subscription_status = mgm_remote_get($request_url, null, null, 'No status found');
			// set cache		
			set_transient('mgm_subscription_status', $subscription_status, $this->cache_timeout);
		}
		
		// locked/expired ?
		if(trim($subscription_status) == 'LOCKED' || trim($subscription_status) == 'EXPIRED'){		
			// remove keys 
			delete_option('mgm_auth'); delete_option('mgm_auth_options');
			// set
			// admin url
			$admin_url = add_query_arg(array('page'=>'mgm.admin'), admin_url('admin.php'));

			// set
			$subscription_status  =  '<script type="text/javascript">window.location.href="'.$admin_url.'";</script>';
			$subscription_status .= __('Your Subscription has expired','mgm');			
		}
		// return 
		return $subscription_status;
	}
	
	// notify deactivation
	function notify_deactivation(){ 		
		// url
		$request_url = $this->_get_subscription_request_url($this->product_notify_deactivation_url);
		// get	
		return $deactivation = mgm_remote_get($request_url, NULL, NULL, 'Could not connect'); 		
	}	
	
	// get_product_info
	function get_product_info($key){						
		// read
		return $this->{$key};
	}
	
	// get_product_url
	function get_product_url(){
		return $this->get_product_info('product_service_domain') . $this->get_product_info('product_url');
	}
	
	// request      
	function api_request( $args ) {	  
		// Send request  
		$response = mgm_remote_post( $this->product_service_site_api, $args, NULL, 'Failed to connect' );  		
	  	
		// unserialize
		$response = maybe_unserialize( $response );  
	  	
		// verify
		if ( is_object( $response ) )  
			return $response;  
		else  
			return false;  	  
	}   
	
	// get subscription request url
	function _get_subscription_request_url($request_url){
		// get keys
		$keys = $this->get_key('data');
		// query
		if($keys){
			// loop
			$query = array('email'=>$keys[0]);
			// set license
			if(isset($keys[1]) && !empty($keys[1])){
				$query['license'] = $keys[1];
			}
			// set
			$request_url = add_query_arg($query, $request_url);
		}		
		// return
		return $request_url;
	}
	
	// internal fixtures ---------------------------------------------------------------------
	// fix
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('license_key','activation_date','expire_date');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}			
		// save
		$this->save();	
	}
	
	// prepare save, define the object vars to be saved
	// internally called by object->save()
	function _prepare(){		
		// init array
		$this->options = array();
		// to be saved vars
		$vars = array('license_key','activation_date','expire_date');
		// loop
		foreach($vars as $var){
			// set
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_auth.php