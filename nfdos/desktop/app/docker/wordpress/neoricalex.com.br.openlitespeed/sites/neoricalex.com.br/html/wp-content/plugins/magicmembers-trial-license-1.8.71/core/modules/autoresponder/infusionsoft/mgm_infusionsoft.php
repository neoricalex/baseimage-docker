<?php
/**
 * Magic Members infusionsoft autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_infusionsoft extends mgm_autoresponder{

	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_infusionsoft();
	}
	
	// construct
	public function mgm_infusionsoft(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Infusionsoft';
		// desc
		$this->description = __('Infusionsoft Desc','mgm');		
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
				$this->setting['api_key']       = $_POST['setting']['api_key'];
				$this->setting['app_name']      = $_POST['setting']['app_name'];
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
				$this->setting['api_key'] 	= $_POST['setting']['infusionsoft']['api_key'];
				$this->setting['app_name']	= $_POST['setting']['infusionsoft']['app_name'];
				
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
		if(!isset($this->setting['api_key']) && !isset($this->setting['app_name'])){
			return false;
		}
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		// set
		$this->postfields = array('Email'=> $userdata['email']);
		
		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'id');

		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'id');
		// set list id change only on unsubscribe		
		}else if( 'unsubscribe' == $this->method ){
			$this->_set_provider_listids($userdata, 'id');
		}
		
		//return
		return true;
	}

	//get post fields
	public function _get_postfields($user_id){
		// new filter
		$this->postfields = apply_filters( 'mgm_autoresponder_get_postfields', $this->postfields, $this->code, $user_id );
		// return as it is
		return $this->postfields;
	}	

	//transport
	public function _transport($user_id){
		// method
		$method = $this->get_method();		
		// post url
		$post_url = $this->get_endpoint($method);
		// post data
		$post_data = $this->_get_postfields($user_id);
		//tag or group
		$tag_id = 0;
		//check
	 	if(isset($post_data['id']) && $post_data['id'] > 0){
			$tag_id  = $post_data['id'];
			unset($post_data['id']);
		}
		//check if user upgrades his membership then unsubscribe old membership
		$this->_check_unsubscribe_previous_membership_upgrade($user_id);				
		//if email exists directly update tag
		if($cid = $this->_check_email_exists($post_data['Email'])) {
		 	return $this->_update_tag($tag_id,$cid);			
		}		
	 	// post data
	 	$params =array($this->setting['api_key'],$post_data);
	 	// post xml
	 	$post_xml = xmlrpc_encode_request('ContactService.add', $params);		
	 	// headers
		$http_headers = array('Content-Type' => 'application/xml');
		// get result	
		$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
		//decode xml rpc response
	 	$response = xmlrpc_decode ($result);
	 	//check
	 	if(!isset($response['faultCode']) && $tag_id >0){
	 		return $this->_update_tag($tag_id,$response);
	 	}else {
			// log
	 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
			// log
	 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
	 	}
	 	//return
	 	return false;		
	}

	// update tag
	public function _update_tag($tag_id,$contact_id){
		// method
		$method = $this->get_method();		
		// post url
		$post_url = $this->get_endpoint($method);		
		// post data
		$params = array($this->setting['api_key'],$contact_id,$tag_id);
		// post xml	 	
	 	$post_xml = xmlrpc_encode_request('ContactService.addToGroup', $params);
	 	// headers
		$http_headers = array('Content-Type' => 'application/xml');
		// get result	
		$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
		//decode xml rpc response
	 	$response = xmlrpc_decode ($result);
	 	//check
	 	if(!isset($response['faultCode'])){
			// log
	 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
	 		//return
			return $response;	 		
	 	}else {
			// log
	 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
			// log
	 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
	 	}
		//return
		return false;
	}		
		
	// validate
	public function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['infusionsoft']['api_key'])){
			$errors[] = __('Api key is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['infusionsoft']['app_name'])){
			$errors[] = __('Application Name is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// get endpoint
	public function get_endpoint($method='subscribe'){
		//appliction name
 		$app_name = $this->setting['app_name'];
		//must be include application name in end point
 		$post_url = "https://{$app_name}.infusionsoft.com/api/xmlrpc/";
		// set 
		$this->set_endpoint($method, $post_url);	
		// return		
		return parent::get_endpoint($method);
	}
	
	// check if email exists in IS contact list	
	public function _check_email_exists($email=''){
		// method
		$method = $this->get_method();		
		// post url
		$post_url = $this->get_endpoint($method);		
		// post data
		$params = array($this->setting['api_key'],$email,array('Id'));
		// post xml	 	
		$post_xml = xmlrpc_encode_request('ContactService.findByEmail', $params);
		// headers
		$http_headers = array('Content-Type' => 'application/xml');
		// get result	
		$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
		//decode xml rpc response
	 	$response = xmlrpc_decode ($result);
		//check
	 	if(!isset($response['faultCode'])){		
			// log
	 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
	 		//check
	 		if(!empty($response)){
				//loop
				foreach ($response as $contact_data){
					$contact_id = $contact_data['Id'];
				}
				//return
				return ($contact_id > 0 ) ? $contact_id : false;
			}
		}else {
			// log
	 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
			// log
	 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));			
		}
		return false;
	}	
	
	// user unsubscribe from the AR list
	public function unsubscribe($user_id){	
		// userdata	
		$userdata = $this->_get_userdata($user_id);			
		//check if email exists remove tag
		if($cid = $this->_check_email_exists($userdata['email'])) {
			//init
			$tag_id = 0;
			// set list
			if(isset($userdata['membership_type']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap'])>0){
				// loop
				foreach($this->setting['membershipmap'] as $listid=>$ms_type){
					// check
					if($userdata['membership_type'] == $ms_type){
						// set
						$tag_id = $listid;// update matched tag
					}
				}
			}		
			//check
			if($cid > 0 && $tag_id > 0) {
				// method
				$method = $this->get_method();		
				// post url
				$post_url = $this->get_endpoint($method);		
				// post data
				$params = array($this->setting['api_key'],$cid,$tag_id);
				// post xml	 	
				$post_xml = xmlrpc_encode_request('ContactService.removeFromGroup', $params);
				// headers
				$http_headers = array('Content-Type' => 'application/xml');
				// get result	
				$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
				//decode xml rpc response
			 	$response = xmlrpc_decode ($result);
				//check
			 	if(!isset($response['faultCode'])){		
					// log
			 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
			 		//return
			 		return $response;
				}else {
					// log
			 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
					// log
			 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));			
				}				
			}
		}
		//return
		return false;
	}
	
	//unsubscribe previous membership before subscribe to new membership	
	public function _check_unsubscribe_previous_membership_upgrade($user_id) {		
		// member
		$member = mgm_get_member($user_id);
		//transactions
		$transactions = array();
		//add
		$transactions[$member->pack_id] = (isset($member->transaction_id))? $member->transaction_id : 0 ;
		//check for other membership_types
		if (isset($member->other_membership_types) && ! empty($member->other_membership_types)){			
			// loop
			foreach ($member->other_membership_types as $key => $memtypes) {
				// convet
				if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes,$user_id);
				
				$transactions[$memtypes->pack_id] = (isset($memtypes->transaction_id))? $memtypes->transaction_id : 0 ;
				
			}
		}
		//init
		$upgrade_packs = array();
		//loop
		foreach ($transactions as $pack_id => $tran_id) {
			//check
			if(!empty($tran_id) && $tran_id > 0 ){
				//transaction data
				$data = mgm_get_transaction($tran_id);
				//check
				if(	isset($data['data']['subscription_option']) && $data['data']['subscription_option'] =='upgrade' && isset($data['data']['upgrade_prev_pack']) && $data['data']['upgrade_prev_pack'] > 0 ) {
					
					$prev_pack_data  = mgm_get_class('subscription_packs')->get_pack($data['data']['upgrade_prev_pack']);
					$upgrade_packs[] = array('prev_pack' =>$prev_pack_data['membership_type'],'new_pack'=>$data['data']['membership_type']);
					
				}
			}
		}
		//upgraded packs and previous packs
		if(!empty($upgrade_packs))	{
			// userdata	
			$userdata = $this->_get_userdata($user_id);	
			//check if email exists remove tag
			if($cid = $this->_check_email_exists($userdata['email'])) {
				//loop
				foreach ($upgrade_packs as $upgrade_pack) {					
					// set list
					if(isset($upgrade_pack['prev_pack']) && isset($this->setting['membershipmap']) && count($this->setting['membershipmap'])>0){
						//init
						$tag_id = 0;
						// loop
						foreach($this->setting['membershipmap'] as $listid=>$ms_type){
							// check
							if($upgrade_pack['prev_pack'] == $ms_type){
								// set
								$tag_id = $listid;// update matched tag
							}
						}

						//check
						if($cid > 0 && $tag_id > 0) {
							// method
							$method = $this->get_method();		
							// post url
							$post_url = $this->get_endpoint($method);		
							// post data
							$params = array($this->setting['api_key'],$cid,$tag_id);
							// post xml	 	
							$post_xml = xmlrpc_encode_request('ContactService.removeFromGroup', $params);
							// headers
							$http_headers = array('Content-Type' => 'application/xml');
							// get result	
							$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
							//decode xml rpc response
						 	$response = xmlrpc_decode ($result);
							//check
						 	if(!isset($response['faultCode'])){		
								// log
						 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));
							}else {
								// log
						 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
								// log
						 		mgm_log('response: '.print_r($response,1), $this->get_context( __FUNCTION__ ));			
							}				
						}
						
					}
					//small delay
					sleep(5);					
				}
			}
		}
		//return		
		return true;
	}
	// get all lists
	public function _get_all_lists(){		
		// check
		if( ! $this->is_enabled() )
			return ;
		// check
		if( ! isset($this->setting['api_key']) ||! isset($this->setting['app_name']))
			return;			
		// method
		$method = $this->get_method();		
		// post url
		$post_url = $this->get_endpoint($method);
		// post data - privateKey,table,limit,page,queryData,selectedFields
		$params = array($this->setting['api_key'],'ContactGroup',1000,0,array('GroupName'=>'%'),array('Id','GroupName'));
		// post xml	 	
		$post_xml = xmlrpc_encode_request('DataService.query', $params);
		// headers
		$http_headers = array('Content-Type' => 'application/xml');
		// get result	
		$result = mgm_remote_post($post_url, $post_xml, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false),false);
		//decode xml rpc response
		$_lists = xmlrpc_decode ($result);			
		//check
	 	if(!isset($_lists['faultCode']) && !empty($_lists)){		
			//init
			$lists = array();
			//loop
			foreach ($_lists as $_list) {
				$list_id = $_list['Id'];
				$list_name = (string)$_list['GroupName'];	
				$lists [$list_id] = $list_name;		
			}
	 		// log
	 		mgm_log('response: '.print_r($_lists,1), $this->get_context( __FUNCTION__ ));			
	 		// log
	 		mgm_log('lists: '.print_r($lists,1), $this->get_context( __FUNCTION__ ));			
			//return
			return $lists;

		}else {
			// log
	 		mgm_log('result: '.print_r($result,1), $this->get_context( __FUNCTION__ ));
			// log
	 		mgm_log('response: '.print_r($_lists,1), $this->get_context( __FUNCTION__ ));			
		}
		//return					
		return false;		
	}
}
// end of file core/modules/autoresponder/mgm_infusionsoft.php