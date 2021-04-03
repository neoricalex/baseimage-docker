<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members autoresponders admin module
 *
 * @package MagicMembers
 * @since 2.0
 */
 class mgm_admin_autoresponders extends mgm_controller{
 	
	// construct
	function __construct(){
		// php4
		$this->mgm_admin_autoresponders();
	}
	
	// construct php4
	function mgm_admin_autoresponders()
	{		
		// load parent
		parent::__construct();
	}
	
	// index
	function index(){
		// data
		$data = array();		
		// load template view
		$this->loader->template('autoresponders/index', array('data'=>$data));		
	}		
	
	// modules
	function autoresponder_modules(){
		// data
		$data = array();		
		// get modules
		$data['autoresponder_modules'] = mgm_get_modules('autoresponder');		
		// autoresponders
		foreach($data['autoresponder_modules'] as $module){				
			// get module
			$module_object = mgm_get_module('mgm_' . $module, 'autoresponder');
			// check	
			if(is_object($module_object)){										
				// get html
				$data['modules'][$module]['html'] = $module_object->settings();
				// get code
				$data['modules'][$module]['code'] = $module_object->code;	
				// get name
				$data['modules'][$module]['name'] = $module_object->name;	
			}
		}		
		// membership types
		$data['membership_types'] = mgm_get_class('membership_types')->membership_types;		
		// active
		$data['active_module']    = mgm_get_class('system')->active_modules['autoresponder'];
		// load template view
		$this->loader->template('autoresponders/modules', array('data'=>$data));	
	}
	
	// module settings
	function module_settings(){		
		// make local
		extract($_REQUEST);				
		// get module
		$module_class = mgm_get_module($module, 'autoresponder');	
		// update
		if(isset($update) && $update=='true'){
			// settings update
			echo $module_class->settings_update();
		}else{		
			// load settings form
			$module_class->settings();
		}				
	}
	//aweber access token & secret key help
	function aweber_help(){
		//init aweber help
		$aweber_obj = mgm_get_module('mgm_aweber', 'autoresponder');
		//data
		$data = $aweber_obj->setting; 

		if( ! isset($data['consumer_key']) ){
			$data['consumer_key'] = mgm_get_var('consumer_key');
		}

		if( ! isset($data['consumer_secret']) ){
			$data['consumer_secret'] = mgm_get_var('consumer_secret');
		}
		
		// load template view
		$this->loader->template('autoresponders/help', array('data'=>$data));			
	}

	// autoresponders settings
	function autoresponder_settings(){
		// local
		extract($_POST);
		// update
		if(isset($settings_update) && !empty($settings_update)){
			// get system object	
			$system_obj = mgm_get_class('system');
			// update if set			
			foreach($system_obj->setting as $k => $v){
				// set var
				if(isset($_POST[$k])){
					// array
					if(is_array($_POST[$k])){
						$system_obj->setting[$k] = (array)$_POST[$k];
					}else{
						$system_obj->setting[$k] = addslashes($_POST[$k]);		
					}
				}								
			}
			// update
			$system_obj->save();
			//response
			$reponse = array('status'=>'success','message'=>__('Autoresponders settings successfully updated.','mgm'));			
			// message
			echo json_encode($reponse);			
			// return
			return;			
		}
		// data
		$data = array();
		// system
		$data['system_obj'] = mgm_get_class('system');
		// load template view
		$this->loader->template('autoresponders/settings', array('data'=>$data));			
	}		
		
 }
// return name of class 
return basename(__FILE__,'.php');
// end file /core/admin/mgm_admin_autoresponders.php