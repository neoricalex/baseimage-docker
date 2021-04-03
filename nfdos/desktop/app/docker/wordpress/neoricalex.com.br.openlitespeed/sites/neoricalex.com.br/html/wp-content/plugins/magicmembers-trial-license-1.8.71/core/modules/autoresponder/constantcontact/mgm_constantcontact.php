<?php
/**
 * Magic Members constantcontact autoresponder module
 *
 * @package MagicMembers
 * @since 1.0
 */
class mgm_constantcontact extends mgm_autoresponder{

	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_constantcontact();
	}
	
	// construct
	public function mgm_constantcontact(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Constant Contact';
		// desc
		$this->description = __('Constant Contact Desc','mgm');				
		// set path
		parent::set_tmpl_path();	
		// read settings
		$this->read();			
		// endpoints, not saved
		$this->set_endpoint('subscribe','https://api.constantcontact.com/ws/customers/[user_name]/contacts');//subscribe		
		$this->set_endpoint('unsubscribe','https://api.constantcontact.com/ws/customers/[user_name]/contacts');//unsubscribe
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
		// membership types
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
				$this->setting['code'] 		    = $_POST['setting']['code'];
				$this->setting['user_name']     = $_POST['setting']['user_name'];
				$this->setting['password'] 	    = $_POST['setting']['password'];
				$this->setting['doubleopt']     = (isset($_POST['setting']['doubleopt'])) ? $_POST['setting']['doubleopt'] : '';
				$this->setting['list_id'] 	    = (isset($_POST['setting']['list_id'])) ? $_POST['setting']['list_id'] : '';
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
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
			case 'box':
			default:
			// from box
				// set fields
				$this->setting['code'] 		= $_POST['setting']['constantcontact']['code'];
				$this->setting['user_name'] = $_POST['setting']['constantcontact']['user_name'];
				$this->setting['password'] 	= $_POST['setting']['constantcontact']['password'];
				$this->setting['list_id'] 	= $_POST['setting']['constantcontact']['list_id'];
				$this->setting['doubleopt'] = $_POST['setting']['constantcontact']['doubleopt'];
				// update object options
				$this->save();
				// message	
				return json_encode(array('status'=>'success','message'=>sprintf(__('%s settings updated','mgm'), $this->name)));
			break;			
		}		
	}
	
	// set postfields
	public function set_postfields($user_id){
		// validate
		if(!isset($this->setting['code']) && !isset($this->setting['user_name']) && !isset($this->setting['password']) && !isset($this->setting['list_id'])){
			return false;
		}
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	
		
		// setting
		$list_id   = $this->setting['list_id'];
		$user_name = $this->setting['user_name'];		
		$doubleopt = $this->setting['doubleopt'];		
		// updated
		$updated = 	date('c');
		//opt
		$opt_in = ($doubleopt) ? 'ACTION_BY_CONTACT':'ACTION_BY_CUSTOMER';
		// xml
		$xml = '<?xml version="1.0"?>
				<entry xmlns="http://www.w3.org/2005/Atom">
					<title type="text"></title>
					<updated>[updated]</updated>
					<author></author>
					<id>data:,none</id>
					<summary type="text">Contact</summary>
					<content type="application/vnd.ctct+xml">
						<Contact xmlns="http://ws.constantcontact.com/ns/1.0/">
							[contact_fields]
							<OptInSource>[opt_in]</OptInSource>
							<ContactLists>
								<ContactList id="http://api.constantcontact.com/ws/customers/[user_name]/lists/[list_id]"/>
							</ContactLists>
						</Contact>
					</content>
				</entry>';
	
		// contact fields
		$contact_fields_default = array('EmailAddress', 'FirstName', 'LastName', 'PostalCode');		
				
		// set postfields		
		$this->postfields = array(
			'ListId'       => $this->setting['list_id'],
			'UserName'     => $this->setting['user_name'],								
			'EmailAddress' => $userdata['email']
		);	
		
		// set extra postfields, not for unsubscribe
		// if($this->method != 'unsubscribe') $this->_set_extra_postfields($userdata, 'ListId');

		// set extra postfields, not for unsubscribe
		if( 'unsubscribe' != $this->method ) {
			$this->_set_extra_postfields($userdata, 'ListId');
		// set list id change only on unsubscribe		
		}else if( 'unsubscribe' == $this->method ){
			$this->_set_provider_listids($userdata, 'ListId');
		}
		
		// contact_fields_string
		$contact_fields = '';
		
		// update contact fields with default fields
		foreach($contact_fields_default as $contact_field){
			// value
			$value = isset($this->postfields[$contact_field]) ? $this->postfields[$contact_field] : '';
			// append
			if($value){
				$contact_fields .=  sprintf('<%s>%s</%s>', $contact_field, $value, $contact_field);
			}
		}	
		
		// loop rest
		foreach($this->postfields as $field=>$value){
			// skip settings
			if(!in_array($field, array_merge($contact_fields_default,array('ListId','UserName')))){
				// value
				$value = isset($this->postfields[$field]) ? $this->postfields[$field] : '';
				// append
				if($value){
					$contact_fields .=  sprintf('<%s>%s</%s>',$field,$value,$field);
				}
			}
		}
			
		// update xml		
		$xml = str_replace(array('[updated]','[contact_fields]','[user_name]','[list_id]','[opt_in]'), 
						   array($updated,$contact_fields,$this->setting['user_name'],$this->postfields['ListId'],$opt_in), 
						   $xml);// list id taken form postfields to capture custom group/list setting
		
		// reset postfields
		unset($this->postfields);
		$this->postfields = $xml;
		
		// return 
		return true;							
	}
	
	// validate
	public function validate(){
		// errors
		$errors = array();
		// check
		if(empty($_POST['setting']['constantcontact']['code'])){
			$errors[] = __('API Key is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['constantcontact']['user_name'])){
			$errors[] = __('User Name is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['constantcontact']['password'])){
			$errors[] = __('Password is required','mgm'); 
		}
		// check		
		if(empty($_POST['setting']['constantcontact']['list_id'])){
			$errors[] = __('Contact List Id is required','mgm'); 
		}
		
		// return
		return count($errors) == 0 ? false : $errors;
	}
	
	// get endpoint
	public function get_endpoint($method='subscribe'){
		// user_name
		$user_name = $this->setting['user_name'];		
		// return		
		return parent::get_endpoint($method, array('user_name'=>$user_name));
	}
	
	// get_transport headers
	public function get_transport_headers($fields){		
		// return 
		return $headers = array("Content-Type:application/atom+xml");
	}
	
	// get all lists
	public function _get_all_lists(){
		// check
		if( ! $this->is_enabled() )
			return ;
		// check
		if( ! isset($this->setting['user_name']) )
			return;
		// user_name
		$user_name = $this->setting['user_name'];
		//end url
		$call ="https://api.constantcontact.com/ws/customers/".$user_name."/lists";		
		//server call
		$xml = $this->doServerCall($call);
		//convertion		
		$_lists = simplexml_load_string($xml);		
		//init
		$lists = array();
		//check
		if(isset($_lists->entry) && !empty($_lists->entry)){
			//loop
			foreach ($_lists->entry as $_list) {
				$link = $_list->id;
				$link_bits = explode('/', $link);
				$list_id = $link_bits[(count($link_bits)-1)];
				$list_name = (string)$_list->title;
				//$lists [] = array('id'=> $list_id, 'name'=> $list_name);		
				$lists [$list_id] = $list_name;		
			}
		}
		//return
		return $lists;
	}
		
	// override get transport curl options to set custom curl options
	public function get_transport_curl_options($fields,$headers){
		// code
		$code      = $this->setting['code'];
		$user_name = $this->setting['user_name'];
		$password  = $this->setting['password'];
		
		// create auth string:
		$user_pwd = $code . '%' . $user_name . ':' . $password;
		
		// set options
		$curl_options = array(
			CURLOPT_HTTPAUTH       => CURLAUTH_BASIC, 
			CURLOPT_USERPWD        => $user_pwd, 
			CURLOPT_TIMEOUT        => 30, 
			CURLOPT_POST           => true, 
			CURLOPT_POSTFIELDS     => trim($fields), 
			CURLOPT_HEADER         => false, // do not return headers in response	
			CURLOPT_HTTPHEADER     => $headers,	// set request headers
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_SSL_VERIFYPEER => false,	
			CURLOPT_SSL_VERIFYHOST => false, // new						
			CURLOPT_REFERER        => home_url() // home url	
		);
		
		// return
		return $curl_options;
	}	
	
	// user unsubscribe from the AR list
	public function unsubscribe($user_id){

		$contacts = array();

		$contacts['items'] = array();
		
		// userdata	
		$userdata = $this->_get_userdata($user_id);	

		$user_name = $this->setting['user_name'];
		
		
		$call ='https://api.constantcontact.com/ws/customers/'.$user_name.'/contacts?email='.$userdata['email'];

		//server call to get contact id using  email
		$return = $this->doServerCall($call);
		
		$parsedReturn = simplexml_load_string($return);        			
		
		foreach ($parsedReturn->entry as $item) {
			$tmp = array();
			$tmp['id'] = (string) $item->id;
			$tmp['EmailAddress'] = (string) $item->content->Contact->EmailAddress;
			$tmp['EmailType'] = (string) $item->content->Contact->EmailType;
			$contacts['items'][] = $tmp;
		}		

		$updated = 	date('c');

		// updated
		$xml ='<?xml version="1.0" encoding="UTF-8"?> 
				<entry xmlns="http://www.w3.org/2005/Atom">
					<title>TitleNode</title>
					<updated>2012-05-30T06:45:20+01:00</updated>
					<author><name>CTCT Samples</name></author>
					<id>'.$contacts['items'][0]['id'].'</id>
					<summary type="text">Customer document</summary>
					<content type="application/vnd.ctct+xml">
						<Contact xmlns="http://ws.constantcontact.com/ns/1.0/">Customer document
							<EmailAddress>'.$contacts['items'][0]['EmailAddress'].'</EmailAddress>
							<OptInSource>ACTION_BY_CUSTOMER</OptInSource>
							<EmailType>'.$contacts['items'][0]['EmailType'].'</EmailType>
							<ContactLists/>
						</Contact>
					</content>
				</entry> ';

		//example url
		//$call ="http://api.constantcontact.com/ws/customers/narendra@ceruleaninfotech.com/contacts/3";

		$call = str_replace('%40', '@', $contacts['items'][0]['id']);
		
		//server call to remove email
		$return = $this->doServerCall($call,$xml,'PUT');
		
	}
	
	//server call
	public function doServerCall($request, $parameter = '', $type = "GET") {
		// only call when enabled
		if( ! $this->is_enabled() )
			return ;

		// check
		if( ! isset($this->setting['user_name']) )
			return;
		
		$code      = $this->setting['code'];
		$user_name = $this->setting['user_name'];
		$password  = $this->setting['password'];

		$ch = curl_init();
        $request = str_replace('http://', 'https://', $request);

        // create auth string:
		$user_pwd = $code . '%' . $user_name . ':' . $password;
		
		curl_setopt($ch, CURLOPT_URL, $request);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)");
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $user_pwd);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/atom+xml", 'Content-Length: ' . strlen($parameter)));
        curl_setopt($ch, CURLOPT_FAILONERROR, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        switch ($type) {
            case 'PUT':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $parameter);
                break;
            default:
                curl_setopt($ch, CURLOPT_HTTPGET, 1);
                break;
    	}                
        
    	$emessage = curl_exec($ch);          

    	if ($this->curl_debug) {   
        	echo $error = curl_error($ch);   
        }      
        
        curl_close($ch);

        return $emessage;
	}		
}
// end of file core/modules/autoresponder/mgm_constantcontact.php