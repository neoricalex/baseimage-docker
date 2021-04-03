<?php
/**
 * Magic Members aweber autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_aweber extends mgm_autoresponder{

	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_aweber();
	}
	
	// construct
	public function mgm_aweber(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Aweber';
		// desc
		$this->description = __('Aweber Desc','mgm');		
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();	
		// endpoints, not saved
		$this->set_endpoint('subscribe','http://www.aweber.com/scripts/addlead.pl');//subscribe		
		$this->set_endpoint('unsubscribe','http://www.aweber.com/scripts/removelead.pl');//unsubscribe, dummy			
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
	
	// settings box api hook
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
				// primary
				//$this->setting['form_id']       	= $_POST['setting']['form_id'];
				$this->setting['unit']          	= (isset($_POST['setting']['unit'])) ? $_POST['setting']['unit'] : '';
				//api keys
				$this->setting['consumer_key']      = $_POST['setting']['consumer_key'];
				$this->setting['consumer_secret']   = $_POST['setting']['consumer_secret'];
				$this->setting['access_key']       	= $_POST['setting']['access_key'];
				$this->setting['access_secret']   	= $_POST['setting']['access_secret'];
				
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
				//$this->setting['form_id']           = $_POST['setting']['aweber']['form_id'];
				$this->setting['unit']              = $_POST['setting']['aweber']['unit'];
				//api keys
				$this->setting['consumer_key']      = $_POST['setting']['consumer_key'];
				$this->setting['consumer_secret']   = $_POST['setting']['consumer_secret'];
				$this->setting['access_key']       	= $_POST['setting']['access_key'];
				$this->setting['access_secret']   	= $_POST['setting']['access_secret'];
				
				// save object options		
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
		}		
	}
	
	// set postfields - Deprecated
	public function _set_postfields($user_id){	
		// validate
		if(!isset($this->setting['form_id']) && !isset($this->setting['unit'])){
			return false;
		}
			
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// set
		$this->postfields = array(
			'meta_web_form_id'     => $this->setting['form_id'],
			'meta_split_id'        => '',
			'unit'                 => $this->setting['unit'],
			'redirect'             => 'http://www.aweber.com/form/thankyou_vo.html',
			'meta_redirect_onlist' => '',
			'meta_adtracking'      => '',
			'meta_message'         => '1',
			'meta_required'        => 'from',
			'meta_forward_vars'    => '0',
			'from'                 => $userdata['email'],
			//'name'                 => $userdata['full_name'],
			'submit'               => 'Submit'
		);	
		
		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'unit');

		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'unit');
		// set list id change only on unsubscribe		
		}else if( 'unsubscribe' == $this->method ){
			$this->_set_provider_listids($userdata, 'unit');
		}
		
		// return 
		return true;
	}		
	//updated - set postfields	
	public function set_postfields($user_id){
		// validate
		if(!isset($this->setting['unit'])){
			return false;
		}		
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	

		// set
		$this->postfields = array(
			'unit'=> $this->setting['unit'],
			'email'=> $userdata['email'],
			'name'=> $userdata['full_name']
		);		

		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'unit');

		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'unit');
		// set list id change only on unsubscribe		
		}else if( 'unsubscribe' == $this->method ){
			$this->_set_provider_listids($userdata, 'unit');
		}
		
		// return 
		return true;	
	}	
	// validate
	public function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['aweber']['form_id'])){
			$errors[] = __('Web Form Id is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['aweber']['unit'])){
			$errors[] = __('Unit/List Name is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	// user unsubscribe from the AR list
	public function unsubscribe($user_id){	
		// check
		if( ! $this->is_enabled() )
			return ;
		// check consumer keys
		if( ! isset($this->setting['consumer_key']) || ! isset($this->setting['consumer_secret']))
			return;	
		// check access keys
		if( ! isset($this->setting['access_key']) ||! isset($this->setting['access_secret']))
			return;
					
		//init api
		@require_once(MGM_LIBRARY_DIR.'third_party/aweber_api/aweber_api.php');		
		
		$consumerKey    = $this->setting['consumer_key'];
		$consumerSecret = $this->setting['consumer_secret'];
		$accessKey      = $this->setting['access_key'];
		$accessSecret   = $this->setting['access_secret'];
		$membershipmap   = $this->setting['membershipmap'];	
		//init
		$listName ='';
		//user
		$user 	=  get_userdata( $user_id );		
		//member	
		$member = 	mgm_get_member($user_id);
		//check
		if(!empty($membershipmap)) {
			//check
			if(array_key_exists($member->membership_type, $membershipmap)){
				$listName = $membershipmap[$member->membership_type];
			}
		}		
		
		//check
		if(empty($listName)) $listName = $this->setting['unit'];
		//aweber obj
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);		

		try {
			//init
			$account = $aweber->getAccount($accessKey, $accessSecret);		
			//lists
			$lists = $account->lists->find(array('unique_list_id' => $listName));			
			//check
			if(count($lists)) {	
				//matched list
				//$list = $lists[0];
				//loop
				foreach ($lists as $key =>$l_data) {
					//check
					if($l_data->data['unique_list_id'] != $listName) continue;
					//matched list
					$list = $lists[$key];	
				}						
				//subscribers 
				$subscribers = $list->subscribers;		
				
				try {
					//check
				    $params = array('status' => 'subscribed');
				    //list subscribers
				    $found_subscribers = $subscribers->find($params);
				    //loop
				    foreach($found_subscribers as $subscriber) {
				    	//check
						if ($subscriber->email == $user->user_email) {
							//change status
							$subscriber->status = 'unsubscribed';
							//save
							$subscriber->save();
							//return
							return true;							
						}
			
				    }
				} catch(AWeberAPIException $exc) {
					mgm_log('AWeberAPIException : '.print_r($exc,true),$this->get_context( __FUNCTION__ ));
				}		
				unset($list);
				unset($subscribers);
			
			} else {
				mgm_log('AWeberAPI Did not find list',$this->get_context( __FUNCTION__ ));
			}			
		}
		catch(AWeberAPIException $exc) {
			mgm_log('AWeberAPIException : '.print_r($exc,true),$this->get_context( __FUNCTION__ ));
		}
		// return 
		return false;
	}

	// get all lists
	public function _get_all_lists(){
		// check
		if( ! $this->is_enabled() )
			return ;
		// check consumer keys
		if( ! isset($this->setting['consumer_key']) || ! isset($this->setting['consumer_secret']))
			return;	
		// check access keys
		if( ! isset($this->setting['access_key']) ||! isset($this->setting['access_secret']))
			return;
					
		//init api
		require_once(MGM_LIBRARY_DIR.'third_party/aweber_api/aweber_api.php');		
		
		$consumerKey    = $this->setting['consumer_key'];
		$consumerSecret = $this->setting['consumer_secret'];
		$accessKey      = $this->setting['access_key'];
		$accessSecret   = $this->setting['access_secret'];

		//aweber obj
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);		
		
		try {
			//init
			$account = $aweber->getAccount($accessKey, $accessSecret);
			//lists
			$_lists =  $account->lists->data['entries'];
			//check
			if(count($_lists) > 0 && !empty($_lists)){
				//init
				$lists = array();
				//loop
				foreach ($_lists as $list) {
					$lists [$list['unique_list_id']] = $list['name'];
				}
				//return
				return $lists;
			}
		} catch(AWeberAPIException $exc) {
			mgm_log('AWeberAPIException : '.print_r($exc,true),$this->get_context( __FUNCTION__ ));
		}
		//return
		return false;
	}
	// user subscribe to AR list
	public function subscribe($user_id){	
		// check
		if( ! $this->is_enabled() )
			return ;
		// check consumer keys
		if( ! isset($this->setting['consumer_key']) || ! isset($this->setting['consumer_secret']))
			return;	
		// check access keys
		if( ! isset($this->setting['access_key']) ||! isset($this->setting['access_secret']))
			return;
		
		// set method
		$this->set_method('subscribe');
		// set postfields
		$this->set_postfields($user_id);	
		
		//init api
		require_once(MGM_LIBRARY_DIR.'third_party/aweber_api/aweber_api.php');		
		
		$consumerKey    = $this->setting['consumer_key'];
		$consumerSecret = $this->setting['consumer_secret'];
		$accessKey      = $this->setting['access_key'];
		$accessSecret   = $this->setting['access_secret'];
		//init
		$unique_list_id = $this->postfields ['unit'];
		//aweber obj
		$aweber = new AWeberAPI($consumerKey, $consumerSecret);		

		try {
			//init
			$account = $aweber->getAccount($accessKey, $accessSecret);			
			//log
			//mgm_log('unique list id : '.$unique_list_id,$this->get_context( __FUNCTION__ ));
			//list id
			$list_id = str_replace('awlist','',$unique_list_id);
			//log
			//mgm_log('list id : '.$list_id,$this->get_context( __FUNCTION__ ));
			
			//chec
			if($list_id){
				
				try {
					
					$listURL = "/accounts/{$account->id}/lists/{$list_id}";
					
					//mgm_log('listURL : '.$listURL,$this->get_context( __FUNCTION__ ));
					
				    $list = $account->loadFromUrl($listURL);
				    
				    //mgm_log('list : '.print_r($list,true),$this->get_context( __FUNCTION__ ));
				    	
				    $custom_fields = array();
				    //check
				    foreach ($this->postfields as $key => $val) {
				    	if(!in_array($key,array('email','name','unit'))) $custom_fields[$key] =  $val;
				    }
	
				    // create a subscriber
				    $params = array(
				        'email' => $this->postfields['email'],
				        'name' => $this->postfields['name']			     
				    );
					//check
				    if(!empty($custom_fields)) $params['custom_fields'] =  $custom_fields; 			    
				    //log
				    //mgm_log('params : '.print_r($params,true),$this->get_context( __FUNCTION__ ));
				    
				    $subscribers = $list->subscribers;
				    $new_subscriber = $subscribers->create($params);
					//log
				    //mgm_log('new_subscriber : '.print_r($new_subscriber,true),$this->get_context( __FUNCTION__ ));
				    
					//return
					return true;

				}catch(AWeberAPIException $exc) {
					mgm_log('1. AWeberAPIException : '.print_r($exc,true),$this->get_context( __FUNCTION__ ));
				}			    
			}else {
				mgm_log('2. AWeber list id not found : ',$this->get_context( __FUNCTION__ ));
			}		
	
		}catch(AWeberAPIException $exc) {
			mgm_log('3. AWeberAPIException : '.print_r($exc,true),$this->get_context( __FUNCTION__ ));
		}			
	}	
}
// end of file core/modules/autoresponder/mgm_aweber.php