<?php
/**
 * Magic Members autoresponseplus autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_autoresponseplus extends mgm_autoresponder{

	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_autoresponseplus();
	}
	
	// construct
	public function mgm_autoresponseplus(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'AutoResponse Plus';
		// desc
		$this->description = __('AutoResponse Plus Desc','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();
	}
	
	// settings api hook
	public function settings(){
		global $wpdb;
		// data
		$data = array();		
		// set 
		$data['module']           = $this;
		// fields
		$data['custom_fields']    = $this->_get_custom_fields();
		// membership types
		$data['membership_types'] = $this->_get_membership_types();		
		// load template view
		return $this->loader->template('settings', array('data'=>$data), true);
	}	
	
	// settings_box
	public function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;
		// load template view
		return $this->loader->template('settings_box', array('data'=>$data), true);		
	}
	
	// update
	public function settings_update(){
		// form type 
		switch($_POST['setting_form']){
			case 'main':
			// form main
				// set fields
				$this->setting['list_id']       = $_POST['setting']['list_id'];
				$this->setting['post_url']      = $_POST['setting']['post_url'];
				$this->setting['unsubscribe_post_url']      = $_POST['setting']['unsubscribe_post_url'];
				// fieldmap
				$this->setting['fieldmap']      = $this->_make_assoc($_POST['setting']['fieldmap']);
				// membershipmap
				$this->setting['membershipmap'] = $this->_membership_assoc($_POST['setting']['membershipmap']);
				// update enable/disable
				$this->enabled                  = $_POST['enabled']; 
				// enable/disable method
				$activate_method = bool_from_yn($this->enabled) ? 'activate_module' : 'deactivate_module';				
				// update
				$ret = call_user_func_array(array(mgm_get_class('system'),$activate_method),array($this->code,$this->type));					
				// save object options
				$this->save();								
				// message				
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'),$this->name)));
			break;			
			case 'box':
			default:
			// from box
				// set fields
				$this->setting['list_id'] 	= $_POST['setting']['autoresponseplus']['list_id'];
				$this->setting['post_url']	= $_POST['setting']['autoresponseplus']['post_url'];				
				$this->setting['unsubscribe_post_url']	= $_POST['setting']['autoresponseplus']['unsubscribe_post_url'];				
				
				// save
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
		}		
	}
	
	// set postfields
	public function set_postfields($user_id){
		// validate
		if(!isset($this->setting['list_id']) && !isset($this->setting['post_url'])){
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	

		// set
		$this->postfields = array(
			'id'     			=> $this->setting['list_id'], // default
			'email'             => $userdata['email'],
			'subscription_type' => 'E',								
			'submit'            => 'Submit'
		);	
		
		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'id');

		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'id');
		// set list id change only on unsubscribe		
		}else if( 'unsubscribe' == $this->method ){
			$this->_set_provider_listids($userdata, 'id');
		}
				
		// return
		return true;
	}
	
	// validate
	public function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['autoresponseplus']['list_id'])){
			$errors[] = __('List Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['autoresponseplus']['post_url'])){
			$errors[] = __('Post Url is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// get endpoint
	public function get_endpoint($method='subscribe'){
		// by method post url
		if(isset($this->setting[$method . '_post_url'])) $post_url = $this->setting[$method . '_post_url'];
		else $post_url = $this->setting['post_url'];
		// set 
		$this->set_endpoint($method, $post_url);	
		// return		
		return parent::get_endpoint($method);
	}
	
	// user unsubscribe from the AR list
	public function unsubscribe($user_id){	
		/*// set method
		$this->set_method('unsubscribe');
		// set params
		if($this->set_postfields($user_id)){			
			// transport
			return $this->_transport($user_id);
		}
		// return 
		return false;*/

		return parent::unsubscribe($user_id);
	}	
}
// end of file core/modules/autoresponder/mgm_autoresponseplus.php