<?php
/**
 * Magic Members mailchimp autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_mailchimp extends mgm_autoresponder{
	
	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_mailchimp();
	}
	
	// construct
	public function mgm_mailchimp(){
		// parent
		parent::mgm_autoresponder();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'MailChimp';
		// desc
		$this->description = __('MailChimp Desc.','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','https://[domain].api.mailchimp.com/1.3/?output=php&method=listSubscribe');// subscribe
		$this->set_endpoint('unsubscribe','https://[domain].api.mailchimp.com/1.3/?output=php&method=listUnsubscribe');// unsubscribe
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
		// contact lists
		$data['contact_lists'] = $this->_get_all_lists();		
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
				$this->setting['unique_id']     = (isset($_POST['setting']['unique_id'])) ? $_POST['setting']['unique_id'] : '';
				//apikey
				$this->setting['apikey']        = $_POST['setting']['apikey'];
				//double or single opt-in
				$this->setting['double_optin']     = $_POST['setting']['double_optin'];
				
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
				$this->setting['unique_id'] = $_POST['setting']['mailchimp']['unique_id'];
				$this->setting['apikey']    = $_POST['setting']['mailchimp']['apikey'];		
				$this->setting['double_optin'] = $_POST['setting']['mailchimp']['double_optin'];		

				
				// save
				$this->save();							
				// message
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'),$this->name)));
			break;			
		}		
	}
		
	// set postfields
	public function set_postfields($user_id){
		// validate		
		if(!isset($this->setting['unique_id']) && !isset($this->setting['apikey'])){			
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set		
		$this->postfields = array(
			  'id'                => trim($this->setting['unique_id']),			
			  'apikey'            => trim($this->setting['apikey']),								  
			  'email_address'     => $userdata['email'],
			  'double_optin'      => $this->setting['double_optin'],
			  'submit'            => 'Submit'	
		);			
		
		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'id', 'merge_vars[%s]');
		
		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'id', 'merge_vars[%s]');
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
		if(empty($_POST['setting']['mailchimp']['unique_id'])){
			$errors[] = __('Unique List Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['mailchimp']['apikey'])){
			$errors[] = __('API Key is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// get endpoint
	public function get_endpoint($method='subscribe'){
		// key
		$apikey = $this->setting['apikey'];
		// mailchipmp url must be configured
		list($key, $domain) = explode('-', $apikey, 2);
		// default
		if (!$domain) $domain = 'us1';
		// return		
		return parent::get_endpoint($method, array('domain'=>$domain));
	}

	// user subscribe
	public function subscribe($user_id){	
		// debug
		// define('DEBUG_AUTORESPONDER_TRANSPORT', true);

		return parent::subscribe($user_id);
	}
		
	// user unsubscribe
	public function unsubscribe($user_id){	
		// debug
		// define('DEBUG_AUTORESPONDER_TRANSPORT', true);

		return parent::unsubscribe($user_id);
	}
	
	// get all lists
	public function _get_all_lists(){
		// check
		if( ! $this->is_enabled() )
			return ;
		// check
		if( ! isset($this->setting['apikey']) )
			return;		
		// key
		$apikey = $this->setting['apikey'];		
		// mailchipmp url must be configured
		list($key, $domain) = explode('-', $apikey, 2);
		// default
		if (!$domain) $domain = 'us1';				
		//end url
		$post_url = 'http://'.$domain.'.api.mailchimp.com/1.3/?method=lists';
		//init
		$lists = array();	
		// proxy submit
		if(!$result = $this->get_proxy($post_url)){
			// post fields
			$fields = "apikey={$apikey}";			
			// curl handle
			$ch = curl_init($post_url);					
			// http request headers
			$headers = $this->get_transport_headers($fields);	
			// curl options		
			$curl_options = $this->get_transport_curl_options($fields, $headers);		
			// set 
			$this->_set_curl_setopt_array($ch, $curl_options);				
			// get result			
			$result = curl_exec($ch);						   
			// close			
			curl_close($ch);
			//convertion
			$_lists = json_decode($result);	
			//check
			if(isset($_lists->data) && !empty($_lists->data)){
				//loop
				foreach ($_lists->data as $_list) {
					$list_id = $_list->id;
					$list_name = (string)$_list->name;
					//$lists [] = array('id'=> $list_id, 'name'=> $list_name);		
					$lists [$list_id] = $list_name;		
				}
			}
					
		}
		//return
		return $lists;
	}
}
// end of file core/modules/autoresponder/mgm_mailchimp.php