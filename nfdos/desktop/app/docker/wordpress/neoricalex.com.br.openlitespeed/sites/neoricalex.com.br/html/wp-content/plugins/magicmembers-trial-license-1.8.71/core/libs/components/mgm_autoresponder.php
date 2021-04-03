<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members autoresponder modules parent class
 *
 * @package MagicMembers
 * @since 2.5.0
 */
class mgm_autoresponder extends mgm_component{	
	// type
	public $type           = 'autoresponder';	
	// name
	public $name           = 'Magic Members Autoresponder Module';
	// internal name
	public $code           = 'mgm_autoresponder';
	// dir
	public $module         = 'autoresponder';
	// description
	public $description    = '';
	// enabled/disabled : Y/N
	public $enabled        = 'N';	
	// end_points
	public $end_points     = array();	
	// settings
	public $setting        = array();
	// postfields
	public $postfields     = array();
	// status, deprecated
	// public $status      = 'live';
	// method
	public $method         = 'subscribe'; 
	
	/** 
	 * construct
	 */	
	public function __construct(){
		// php4 construct
		$this->mgm_autoresponder();
	}
	
	/** 
	 * php4 construct
	 */	
	public function mgm_autoresponder(){
		// call parent
		parent::__construct();		
		// set code
		$this->code = __CLASS__; 
		// desc
		$this->description = __('<p>Autoresponder module description</p>','mgm');		
	}
	
	/**
	 * set template
	 */
	public function set_tmpl_path($module_base_dir=''){
		// set module
		$this->set_module();
		// set base dir
		$this->set_module_base_dir($module_base_dir);		
		// set dir
		$this->set_module_dir();
		// set path	mgm_module_paypal_tmpl_path
		$tmpl_path = ($this->get_module_base_dir() . $this->get_module_dir() . 'html' . MGM_DS);
		//check
		if(!file_exists($tmpl_path)) {			
			// set extend base dir	
			$this->set_extend_module_base_dir($module_base_dir);
			// set extend module path		
			$extend_tmpl_path = ($this->get_extend_module_base_dir() . $this->get_module_dir() . 'html' . MGM_DS);
			//check and set
			if(file_exists($extend_tmpl_path)) $tmpl_path = $extend_tmpl_path;
		}			
		// filter
		$tmpl_path = apply_filters('mgm_module_tmpl_path_' . $this->module, $tmpl_path);	
		// set		
		$this->loader->set_tmpl_path($tmpl_path);
		/*// dir/module		
		$this->module = str_replace($prefix, '', $this->code) ;
		// set path		
		$tmpl_path = ($basedir . implode(DIRECTORY_SEPARATOR, array($this->type, $this->module, 'html')) . DIRECTORY_SEPARATOR);		
		// set		
		$this->loader->set_tmpl_path($tmpl_path);*/
	}
	
	/**
	 * set module base
	 *
	 * @param string $dir
	 * @param string $url
	 * @return void
	 * @since 2.7
	 */
	public function set_module_base($dir, $url){
		// set dir
		$this->set_module_base_dir($dir);
		// set url
		$this->set_module_base_url($url);		
	}

	/**
	 * set module from code
	 *
	 * @param string $prefix
	 * @return void
	 * @since 2.7
	 */
	public function set_module($prefix='mgm_'){
		// dir/module		
		if(!$this->module) $this->module = str_replace($prefix, '', $this->code);	
	}

	/**
	 * set module base directory
	 *
	 * @param string $module_base_dir
	 * @return void
	 * @since 2.7
	 */	
	public function set_module_base_dir($module_base_dir=''){
		// set
		if($module_base_dir) return $this->module_base_dir = $module_base_dir;		
		// default
		if(!$this->module_base_dir)	$this->module_base_dir = MGM_MODULE_BASE_DIR;				
	}

	/**
	 * set module base url
	 *
	 * @param string $module_base_url
	 * @return void
	 * @since 2.7
	 */	
	public function set_module_base_url($module_base_url=''){
		// set
		if($module_base_url) return $this->module_base_url = $module_base_url;
		// default		
		if(!$this->module_base_url)	$this->module_base_url = MGM_MODULE_BASE_URL;			
	}

	/**
	 * set module directory
	 *
	 * @param string $module_dir
	 * @return void
	 * @since 2.7
	 */	
	public function set_module_dir($module_dir=''){
		// set
		if($module_dir) return $this->module_dir = $module_dir;
		// default
		if(!$this->module_dir) $this->module_dir = (implode(MGM_DS, array($this->type, $this->module)) . MGM_DS);		
	}

	/**
	 * set module url
	 *
	 * @param string $module_url
	 * @return void
	 * @since 2.7
	 */	
	public function set_module_url($module_url=''){
		// set
		if($module_url) return $this->module_url = $module_url;
		// default
		if(!$this->module_url) $this->module_url = (implode('/', array($this->type, $this->module)) . '/');		
	}	

	/**
	 * module url by path
	 *
	 * @param string $path
	 * @return string $url
	 * @since 2.7
	 */
	public function module_url($path){
		// return
		return $this->get_module_base_url() . $this->get_module_url() . $path;
	}

	/**
	 * get module base directory
	 *
	 * @param void
	 * @return string $module_base_dir
	 * @since 2.7
	 */
	public function get_module_base_dir(){
		// check
		if(!$this->module_base_dir) $this->set_module_base_dir();
		// set
		return apply_filters('mgm_module_base_dir_' . $this->module, $this->module_base_dir);
	}

	/**
	 * get module base url
	 *
	 * @param void
	 * @return string $module_base_url
	 * @since 2.7
	 */
	public function get_module_base_url(){
		// check
		if(!$this->module_base_url) $this->set_module_base_url();
		// set
		return apply_filters('mgm_module_base_url_' . $this->module, $this->module_base_url);
	}

	/**
	 * get module directory
	 *
	 * @param void
	 * @return string $module_dir
	 * @since 2.7
	 */	
	public function get_module_dir(){		
		// check
		if(!$this->module_dir) $this->set_module_dir();
		// return 
		return apply_filters('mgm_module_dir_' . $this->module, $this->module_dir);
	}

	/**
	 * get module url
	 *
	 * @param void
	 * @return string $module_url
	 * @since 2.7
	 */	
	public function get_module_url(){
		// check
		if(!$this->module_url) $this->set_module_url();
		// return 
		return apply_filters('mgm_module_url_' . $this->module, $this->module_url);
	}
	/**
	 * set extend module base directory
	 *
	 * @param string $module_base_dir
	 * @return void
	 * @since 2.7
	 */	
	public function set_extend_module_base_dir($module_base_dir=''){
		// set
		if($module_base_dir) return $this->module_base_dir = $module_base_dir;		
		// set default
		$this->module_base_dir = MGM_EXTEND_MODULE_BASE_DIR;	
	}	
	/**
	 * set extend module base url
	 *
	 * @param string $module_base_url
	 * @return void
	 * @since 2.7
	 */	
	public function set_extend_module_base_url($module_base_url=''){
		// set
		if($module_base_url) return $this->module_base_url = $module_base_url;
		// set default		
		$this->module_base_url = MGM_EXTEND_MODULE_BASE_URL;			
	}

	/**
	 * get extend module base directory
	 *
	 * @param void
	 * @return string $module_base_dir
	 * @since 2.7
	 */
	public function get_extend_module_base_dir(){
		// check
		if(!$this->module_base_dir) $this->set_extend_module_base_dir();
		// set
		return apply_filters('mgm_module_base_dir_' . $this->module, $this->module_base_dir);
	}
	
	/**
	 * get module name
	 *
	 * @param void
	 * @return string $name
	 */
	public function get_name(){
		// return
		return $this->name;
	}

	/**
	 * get module description
	 *
	 * @param void
	 * @return string $description
	 */
	public function get_description(){
		// return
		return $this->description;
	}

	/**
	 * enable module
	 * 
	 * @param bool $activate 
	 * @return none
	 */
	public function enable($activate=false){
		// activate
		if($activate) mgm_get_class('system')->activate_module($this->code,$this->type);					
		// update state
		$this->enabled = 'Y'; 				
		// update options
		$this->save();
	}
	
	/**
	 * disable module
	 * 
	 * @param bool $deactivate
	 * @return none
	 */
	public function disable($deactivate=false){
		// deactivate
		if($deactivate) mgm_get_class('system')->deactivate_module($this->code,$this->type);
		// update state
		$this->enabled = 'N'; 		
		// update options
		$this->save();
	}
	
	/**
	 * install module
	 * 
	 * @param none
	 * @return none
	 */
	public function install(){				
		// enable
		$this->enable(true);
	}
	
	/**
	 * uninstall module
	 * 
	 * @param none
	 * @return none
	 */
	public function uninstall(){							
		// disable
		$this->disable(true);			
	}
	
	/**
	 * check module enabled status
	 * 
	 * @param string $return_type
	 * @return bool
	 */
	public function is_enabled($return_type='bool'){
		// return true|false on enabled
		$return = (bool_from_yn($this->enabled) && mgm_get_class('system')->is_active_module($this->code,$this->type)) ? true : false;
		// return
		return ($return_type == 'bool') ? $return : ( $return ? 'Y' : 'N' );// needed for selection bug
	}
	
	/**
	 * set module method
	 * 
	 * @param string $method (subscribe|unsubscribe)
	 * @return none
	 */
	public function set_method($method){
		// set
		$this->method = $method;
	}
	
	/**
	 * get module method
	 * 
	 * @param none
	 * @return string $method (subscribe|unsubscribe)
	 */
	public function get_method(){
		// set
		return $this->method;
	}
	
	/**
	 * invoke module method
	 * 
	 * @param string $method
	 * @param array @args
	 * @return public function output
	 */
	public function invoke($method, $args=false){
		// check
		if(method_exists($this,$method)){
			return $this->$method($args);
		}else{
			die(sprintf(__('No such method: %s','mgm'),$method));
		}
	} 	
	
	/**
	 * API method settings ui, callback main settings page
	 *
	 * must be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	public function settings(){
		// return
		return false;
	}
	
	/**
	 * API method quick settings ui, callback quick/box settings page
	 *
	 * must be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	public function settings_box(){
		// return
		return false;
	}	
		
	/**
	 * API method save settings, callback main and quick settings data save
	 *
	 * must be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 */
	public function settings_update(){
		// form type 
		switch(mgm_post_var('setting_form')){
			case 'box':
			// from box	
			break;
			case 'main':
			// form main
			break;
		}	
		// return 
		return true;
	}
	
	/**
	 * API method send, callback for send post
	 *
	 * must be overriden in module	 
	 *
	 * @param none
	 * @return none	 
	 * @deprecated, use subscribe()
	 */
	public function send($user_id){		
		// @see subscribe
		$this->subscribe($user_id);
	}
	
	/**
	 * API method subscribe, callback for subscribe to AR
	 *
	 * must be overriden in module	 
	 *
	 * @param int $user_id
	 * @return bool 	 	 
	 */
	public function subscribe($user_id){	
		// set method
		$this->set_method('subscribe');	
		// set params, to be overridden by child class		
		if($this->set_postfields($user_id)){
		// transport			
			return $this->_transport($user_id);
		}
		// return
		return false;
	}
	
	/**
	 * API method unsubscribe, callback for unsubscribe from AR
	 *
	 * must be overriden in module	 
	 *
	 * @param int $user_id
	 * @return bool 	 
	 * @todo test	 
	 */
	public function unsubscribe($user_id){		
		// set method
		$this->set_method('unsubscribe');	
		// set params, to be overridden by child class
		if($this->set_postfields($user_id)){
		// transport
			return $this->_transport($user_id);
		}
		// return
		return false;
	}		
	
	/**
	 * API method set postfields, callback for set postfields
	 *
	 * must be overriden in module	 
	 *
	 * @param int $user_id
	 * @return bool 	
	 */
	public function set_postfields($user_id){		
		// userdata	
		$userdata = $this->_get_userdata($user_id);		
		// set
		$this->postfields = array('email'=>$userdata['email'],'name'=>$userdata['full_name']);
		// return 
		return true;
	}	
	
	/**
	 * API method set endpoint, callback for set endpoint
	 *
	 * must be overriden in module	 
	 *
	 * @param string $method
	 * @param string $endpoint 
	 * @return none 	
	 */
	public function set_endpoint($method, $endpoint){
		// status
		if($endpoint) $this->end_points[$method] = $endpoint;	
	}
	
	/**
	 * API method get endpoint, callback for getendpoint
	 *
	 * must be overriden in module	 
	 *
	 * @param string $method
	 * @param array $data 
	 * @return string $endpoint 	
	 */
	public function get_endpoint($method='', $data=NULL){
		// force
		$method = (!empty($method)) ? $method : $this->method;
		// default subscribe
		if(!isset($this->end_points[$method])){			
			$this->end_points[$method] = $this->end_points['subscribe'];
		}
		// if data
		if(is_array($data)){
			// loop
			foreach($data as $k=>$v){
				// update
				$this->end_points[$method] = str_replace('['.$k.']', $v, $this->end_points[$method]);
			}
		}
		// return 
		return $this->end_points[$method];
	}			
	
	/**
	 * API method get transport headers, callback for get transport headers
	 *
	 * must be overriden in module	 
	 *
	 * @param string $fields
	 * @return array $headers 	
	 */
	public function get_transport_headers($fields){
		// set headers
		$headers   = array();
		
		// set
		$headers[] = "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11";
		$headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$headers[] = "Accept-Language: en-us,en;q=0.5";
		$headers[] = "Accept-Charset: UTF-8,ISO-8859-1;q=0.7,*;q=0.7";
		$headers[] = "Keep-Alive: 300";
		$headers[] = "Connection: keep-alive";
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		$headers[] = "Content-Length: " . strlen($fields);
		
		// apply filter
		return $headers = apply_filters('mgm_autoresponder_headers', $headers, $this->code);
	}
	
	/**
	 * API method get transport curl options, callback for get curl options
	 *
	 * can be overriden in module for curtom curl options	 
	 *
	 * @param string $fields
	 * @param array $headers 	
	 * @return array $curl_options 
	 */
	public function get_transport_curl_options($fields, $headers){
		// set options
		$curl_options = array(
			CURLOPT_TIMEOUT        => 30, 
			CURLOPT_POST           => true, 
			CURLOPT_POSTFIELDS     => $fields, 
			CURLOPT_HEADER         => false, // do not return headers in response	
			CURLOPT_HTTPHEADER     => $headers,	// set request headers
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => false,	
			CURLOPT_SSL_VERIFYHOST => false, // new						
			CURLOPT_REFERER        => home_url() // home url	
		);
		
		// return
		return apply_filters('mgm_autoresponder_curl_options', $curl_options, $this->code);
	}
	
	/**
	 * API method get proxy, callback for get proxy
	 *
	 * can be overriden in module for custom proxy post 
	 *
	 * @param string $url		
	 * @return bool 
	 */
	public function get_proxy($url){
		// return
		return false;
	}
	
	/**
	 * API method validate settings post data
	 *
	 * can be overriden in module for custom validate
	 *
	 * @param none		
	 * @return bool 
	 */
	public function validate(){
		// return
		return false;
	}
	
	/**
	 * API method default setting
	 *
	 * can be overriden in module for custom default_setting
	 *
	 * @param none		
	 * @return bool 
	 */
	public function default_setting(){
		// return
		return array();
	}
	
	// internal private methods --------------------------------------------------------------
	
	/**
	 * API helper method transport
	 *
	 * @param int $$user_id
	 * @return bool
	 */
	public function _transport($user_id){
		// method
		$method = $this->get_method();
		
		// post url
		$post_url = $this->get_endpoint($method);		
		
		// log context
		$log_context = $this->get_context( __FUNCTION__ );

		// log
		if( defined('DEBUG_AUTORESPONDER_TRANSPORT') ){
			mgm_log('post_url: '.$post_url, $log_context);
		}	
		
		// proxy submit
		if( ! $result = $this->get_proxy($post_url) ){
			// post fields
			$fields = $this->_get_postfields($user_id);
			
			// curl handle
			$ch = curl_init($post_url);		
			
			// http request headers
			$headers = $this->get_transport_headers($fields);	
			
			// log
			if( defined('DEBUG_AUTORESPONDER_TRANSPORT') ){
				mgm_log('headers: '.mgm_pr($headers, true), $log_context);
			}	
			
			// curl options		
			$curl_options = $this->get_transport_curl_options($fields, $headers);	
			
			// log
			if( defined('DEBUG_AUTORESPONDER_TRANSPORT') ){
				mgm_log('curl_options: '.mgm_pr($curl_options, true), $log_context);
			}	
			
			// set 
			$this->_set_curl_setopt_array($ch, $curl_options);		
				
			// get result			
			$result = curl_exec($ch);	
					   
			// close			
			curl_close($ch);			
		}
		
		// log
		if( defined('DEBUG_AUTORESPONDER_TRANSPORT') ){
			mgm_log('result: '.mgm_pr($result, true), $log_context);
		}
			
		// default action
		do_action('mgm_autoresponder_result', $result, $this->code, $method, $user_id);			
		
		// return 
		return true;
	}
	
	/**
	 * API helper method transport
	 *
	 * @param none
	 * @return bool
	 * @deprecated
	 */
	public function _transport_old(){
		// urls
		$url    = $this->get_endpoint('live');
		$fields = $this->_get_postfields();
		
		// curl handle
		$ch = curl_init($url);		
		
		// set headers
		$headers   = array();
		$headers[] = "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11";
		$headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
		$headers[] = "Accept-Language: en-us,en;q=0.5";
		$headers[] = "Accept-Charset: UTF-8,ISO-8859-1;q=0.7,*;q=0.7";
		$headers[] = "Keep-Alive: 300";
		$headers[] = "Connection: keep-alive";
		$headers[] = "Content-Type: application/x-www-form-urlencoded";
		$headers[] = "Content-Length: " . strlen($fields);
		// apply filter
		$headers = apply_filters('mgm_autoresponder_headers', $headers, $this->code);
		
		// set options
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);		
		curl_setopt($ch, CURLOPT_POST, true);				
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);		
		curl_setopt($ch, CURLOPT_HEADER, $headers);		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);// new			
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl'));
			
		// get result			
		$result = curl_exec($ch);			   
		// close			
		curl_close($ch);			
		
		// return action
		do_action('mgm_autoresponder_result', $result, $this->code);	
		
		// return 
		return true;
	}	
	
	/**
	 * API helper method get post fields
	 *
	 * @param none
	 * @return string $postfields
	 */
	public function _get_postfields($user_id){
		// new filter
		$this->postfields = apply_filters( 'mgm_autoresponder_get_postfields', $this->postfields, $this->code, $user_id );
		// check
		if( is_array($this->postfields) ){
			// return 
			return _http_build_query( $this->postfields, null, '&', '', true );			
		}elseif( is_string($this->postfields) ){
			// return
			return $this->postfields;
		}	
		// return as it is
		return $this->postfields;
	}	
	
	/**
	 * API helper method get custom fields, used in field mapping
	 *
	 * @param none
	 * @return array $custom_fields
	 */
	public function _get_custom_fields(){
		// init, email and full name
		$custom_fields = array( 'full_name' => __('Full Name','mgm') );
		// to_autoresponder fields
		$cf_to_autoresponders = mgm_get_class('member_custom_fields')->get_fields_where(array('attributes'=>array('to_autoresponder'=>true)));
		// check
		if(count($cf_to_autoresponders)>0){
			// loop
			foreach($cf_to_autoresponders as $to_autoresponder){
				// skip email
				if($to_autoresponder['name'] != 'email'){
					$custom_fields[$to_autoresponder['name']] = $to_autoresponder['label'];
				}
			}
		}			
		// return
		return $custom_fields;
	}
	
	/**
	 * API helper method get membership types, used in field mapping
	 *
	 * @param none
	 * @return array $membership_types
	 */
	public function _get_membership_types(){
		// member types
		$membership_types = array();
		// loop
		foreach (mgm_get_class('membership_types')->membership_types as $code => $name){
			$membership_types[$code] = mgm_stripslashes_deep($name); 
		}
		// set	
		return $membership_types;	
	}
	
	/**
	 * API helper method get userdata
	 *
	 * @param int $user_id
	 * @return array $userdata
	 */
	public function _get_userdata($user_id){
		// get userdata
		$user       = mgm_get_userdata($user_id);	
		// user data		
		$email      = stripslashes($user->user_email);	
		$first_name = !empty($user->first_name) ? stripslashes($user->first_name) : '';
		$last_name  = !empty($user->last_name) ? stripslashes($user->last_name) : '';	
		$full_name  = !empty($first_name) ? mgm_str_concat($first_name,$last_name) : $user->display_name;
		
		// return 
		$userdata = array(
			'email'      => $email,
			'full_name'  => $full_name,
			'first_name' => $first_name,
			'last_name'  => $last_name
		);
		
		// member
		$member = mgm_get_member($user_id);
		
		// custom fields
		if($member->custom_fields){
			// get vars
			$custom_fields = get_object_vars($member->custom_fields);
			// check
			if(count($custom_fields)>0){
				// loop
				foreach($custom_fields as $custom_field=>$value){
					// check
					if(!isset($userdata[$custom_field])){// ensure fields already set are not overwritten
						$userdata[$custom_field] = $value;
					}
				}
			}
		}
		
		//other membership types -issue #1073
		if($member->other_membership_types){
			$other_membership_types=array();
			foreach ($member->other_membership_types as $o_membership_type){
				if(!empty($o_membership_type)){
					$other_membership_types[] = $o_membership_type['membership_type'];
				}
			}
			$userdata['other_membership_types']=$other_membership_types;
		}
		
		// membership type
		if($member->membership_type){
			$userdata['membership_type'] = $member->membership_type;
		}		
		
		// return 
		return $userdata;
	}
		
	/**
	 * API helper method set curl options
	 *
	 * @param resource $ch curl handle
	 * @param array $curl_options
	 * @return none
	 */
	public function _set_curl_setopt_array(&$ch, $curl_options){
		// check
		if (!function_exists('curl_setopt_array')) {
			curl_setopt_array($ch, $curl_options);
		}else{
			// loop
			foreach ($curl_options as $option => $value) {
				// set
				if(!curl_setopt($ch, $option, $value)) {
					// halt
					return false;
				} 
			}
		}
	}
	
	/**
	 * API helper method make assoc data, used in mapping
	 *
	 * @param array $data
	 * @return array $assoc
	 */	 
	public function _make_assoc($data){
		// assoc
		$assoc = array();
		// check
		if(count($data)>0){
			// loop
			for($i=0; $i<count($data); $i=$i+2){
				// check
				if(isset($data[$i]) && !empty($data[$i]) && isset($data[$i+1]) && !empty($data[$i+1])){
					$assoc[$data[$i]] = $data[$i+1];
				}				
			}
		}
		// return
		return $assoc;
	}

	/**
	 * API helper method membership assoc data, used in membership mapping
	 *
	 * @param array $data
	 * @return array $assoc
	 */	 
	public function _membership_assoc($data){
		// assoc
		$assoc = array();
		// check
		if(count($data)>0){
			// loop
			for($i=0; $i<count($data); $i=$i+2){
				// check
				if(isset($data[$i]) && !empty($data[$i]) && isset($data[$i+1]) && !empty($data[$i+1])){
					$assoc[$data[$i+1]] = $data[$i];
				}				
			}
		}
		// return
		return $assoc;
	}
	
	/**
	 * set provider fields
	 */ 
	public function _set_provider_fields($userdata, $listfield='id', $wrap_format=null){
		// check
		if(isset($this->setting['fieldmap']) && count($this->setting['fieldmap']) > 0){
			// loop
			foreach($this->setting['fieldmap'] as $modulefld => $mgmfld){
				// check
				if(isset($userdata[$mgmfld]) && !empty($userdata[$mgmfld])){
					// wrap format
					if( $wrap_format ){				
						// set		
						$modulefld = sprintf($wrap_format, $modulefld);						
					}
					// set, handle array
					$this->postfields[$modulefld] = is_array($userdata[$mgmfld]) ? current($userdata[$mgmfld]) : $userdata[$mgmfld];
				}
			}
		}

		// filter
		$this->postfields = apply_filters('mgm_autoresponder_set_provider_fields', $this->postfields, $this->code, $this->setting, $userdata);
	}	

	/**
	 * set provider listids
	 */ 
	public function _set_provider_listids($userdata, $listfield='id'){
		// set list
		if(isset($userdata['membership_type']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap']) > 0){
			// loop
			foreach($this->setting['membershipmap'] as $ms_type=>$listid){
				// check
				if($userdata['membership_type'] == $ms_type){
					// set
					$this->postfields[$listfield] = $listid;// update default per membership type
				}
			}
		}
		
		//set list other membership check - #issue 1073
		if(isset($userdata['other_membership_types']) && !empty($userdata['other_membership_types']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap']) > 0) {
			//other membership types count		   	
			$o_count = count($userdata['other_membership_types']);
			//check
			if(array_key_exists($userdata['other_membership_types'][$o_count-1], $this->setting['membershipmap'])){
			   	//check each other membership type
				for ($i=0;$i< $o_count ;$i++){
					// loop
					foreach($this->setting['membershipmap'] as $ms_type=>$listid){
						// check
						if($userdata['other_membership_types'][$i] == $ms_type ){
							// set
							$this->postfields[$listfield] = $listid;// update default per membership type
						}
					}
				}
			}
		}

		// filter
		$this->postfields = apply_filters('mgm_autoresponder_set_provider_listids', $this->postfields, $this->code, $this->setting, $userdata);
	}

	/**
	 * API helper method set extra post fields, push extra mapping fields
	 *
	 * @param array $userdata
	 * @param array $listfield list/group field
	 * @param array $wrap_format field wrap as php array name
	 * @return none
	 */
	public function _set_extra_postfields($userdata, $listfield='id', $wrap_format=null){
		// set extra fields
		$this->_set_provider_fields($userdata, $listfield, $wrap_format);
		
		// switch listid by membership type
		$this->_set_provider_listids($userdata, $listfield);
		
		// filter
		$this->postfields = apply_filters('mgm_autoresponder_set_extra_postfields', $this->postfields, $this->code, $userdata);
	}
	
	// private for module restructuring --------------------------------
	
	 /**
	  * Apply sync fix
	  *
	  * @param object $old_obj
	  * @return none
	  */
	public function apply_fix($old_obj){
		// to be copied vars
		$vars = array('description', 'enabled', 'end_points', 'setting');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}			
		// save
		$this->save();	
	}
	 
	/**
	  * prepare save, define the object vars to be saved
	  *
	  * @param none
 	  * @return none
	  */
	public function _prepare(){		
		// init array
		$this->options = array();
		// to be saved
		$vars = array('description', 'enabled', 'end_points', 'setting');//,'status' deprecated
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}		
	}
	
	/**
	  * serialize
	  *
	  * @param none
	  * @return none
	  */
	public function __toString(){
		// return
		return serialize($this);
	}

	/**
	 * get context
	 */
	public function get_context(){

		$args = func_get_args();

		return ($this->module . '_' . implode('_', $args));
	}
}
// end of file core/libs/components/mgm_autoresponder.php