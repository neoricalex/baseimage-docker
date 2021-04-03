<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------

/**
 * Epoch Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_epoch extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_epoch();
	}
	
	// construct
	function mgm_epoch(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Epoch';
		// logo
		$this->logo = $this->module_url( 'assets/epoch.jpg' );
		// desc
		$this->description = __('Epoch is a secure online retail outlet for more than 10,000 digital product '. 
								'vendors and 100,000 active affiliate marketers.','mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'N';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y';
		// type of integration
		$this->hosted_payment = 'Y';// html redirect
		// supports rebill status check, Epoch has search api, we are using DataPost cancenllation		
		$this->supports_rebill_status_check = 'Y';
		// supports rebill status check with member status awaiting cancel
	 	$this->supports_rebill_status_check_awaiting_cancel = 'Y';
		// rebill status check delay
		$this->rebill_status_check_delay = true;
		// endpoints
		$this->_setup_endpoints();		
		// default settings
		$this->_default_setting();
		// set path
		parent::set_tmpl_path();
		// read settings
		$this->read();	
	}
	
	// MODULE API COMMON HOOKABLE CALLBACKS  //////////////////////////////////////////////////////////////////
	
	// settings
	function settings(){
		global $wpdb;
		// data
		$data = array();		
		// set 
		$data['module'] = $this;	
		// load template view
		$this->loader->template('settings', array('data'=>$data));
	}	
	
	// settings box
	function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;	
		// load template view
		return $this->loader->template('settings_box', array('data'=>$data), true);
	}
	
	// settings update
	function settings_update(){
		// form type 
		switch($_POST['setting_form']){
			case 'box':
			// from box	
				switch($_POST['act']){
					case 'logo_update':
						// logo if uploaded
						if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
							$this->logo = $_POST['logo_new_'.$this->code];
							// save
							$this->save();
						}
						// message
						$message = sprintf(__('%s logo updated', 'mgm'), $this->name);			
						$extra   = array();
					break;
					case 'status_update':
					default:
						// enable
						$enable_state = (isset($_POST['payment']) && $_POST['payment']['enable'] == 'Y') ? 'Y' : 'N';
						// enable
						if( bool_from_yn($enable_state) ){
							$this->install();
							$stat = ' enabled.';
						}else{
						// disable
							$this->uninstall();	
							$stat = ' disabled.';
						}	
						
						// message
						$message = sprintf(__('%s module has been %s', 'mgm'), $this->name, $stat);							
						$extra   = array('enable' => $enable_state);		
					break;
				}							
				// print message
				echo json_encode(array_merge(array('status'=>'success','message'=>$message,'module'=>array('name'=>$this->name,'code'=>$this->code,'tab'=>$this->settings_tab)), $extra));
			break;
			case 'main':
			default:
			// from main						
				// epoch specific				
				$this->setting['reseller']                 = $_POST['setting']['reseller'];
				$this->setting['co_code']                  = $_POST['setting']['co_code'];
				$this->setting['send_userpass']            = $_POST['setting']['send_userpass'];// yes/no	
				// @todo deprecate this since dataplus is required when rebill_status_query=enabled 
				// and rebill_check_method=dataplus which is default and required setup
				$this->setting['dataplus_enable']          = $_POST['setting']['dataplus_enable'];// yes/no

				// data transfer mode
				$this->setting['dataplus_data_transfer']   = $_POST['setting']['dataplus_data_transfer'];// database, http_post
				$this->setting['dataplus_database_server'] = $_POST['setting']['dataplus_database_server'];// local/remote
				// remote database 
				$this->setting['dataplus_database_user']     = $_POST['setting']['dataplus_database_user'];
				$this->setting['dataplus_database_password'] = $_POST['setting']['dataplus_database_password'];
				$this->setting['dataplus_database_name']     = $_POST['setting']['dataplus_database_name'];
				$this->setting['dataplus_database_host']     = $_POST['setting']['dataplus_database_host'];
				// http post url
				$this->setting['dataplus_http_post_url']     = $_POST['setting']['dataplus_http_post_url'];		
				
				// searchapi_auth_user for rebill status check
				if( isset($_POST['setting']['searchapi_auth_user']) ){
					$this->setting['searchapi_auth_user'] = $_POST['setting']['searchapi_auth_user'];
				}

				// searchapi_auth_pass for rebill status check
				if( isset($_POST['setting']['searchapi_auth_pass']) ){
					$this->setting['searchapi_auth_pass'] = $_POST['setting']['searchapi_auth_pass'];	
				}
				
				// rebill check
				$this->setting['rebill_status_query'] = $_POST['setting']['rebill_status_query'];
				$this->setting['rebill_check_method'] = $_POST['setting']['rebill_check_method'];
				// rebill_check_delay
				if($this->rebill_status_check_delay){
					$this->setting['rebill_check_delay'] = $_POST['rebill_check_delay'];	
				}

				// private key
				$this->setting['digest_private_key'] = $_POST['setting']['digest_private_key'];

				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];

				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}	
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$this->_setup_endpoints();				
				// setup dataplus				
				$this->_setup_dataplus((($this->setting['dataplus_enable'] == 'yes') ? true :  false));			
				// fix old data
				$this->supported_buttons = array('subscription');		
				// save
				$this->save();							
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// settings post purchase
	function settings_post_purchase($data=NULL){
		// not supported
		// if(!$this->is_button_supported('buypost')) return '';
		
		// product_code
		$product_code = isset($data->product['epoch_product_code']) ? $data->product['epoch_product_code'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="postpurhase-heading">'.__('Epoch Settings','mgm') .'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Product Code','mgm') . '</p>
								<input type="text" name="mgm_post[product][epoch_product_code]" classs="mgm_text_width_payment" value="'.esc_html($product_code).'" />								
							</div>
						</div>
					 </div>
				 </div>';
		// html
		/*$html=' <li>
					<label>'.__('Epoch Product Code','mgm').' <input type="text" classs="mgm_text_width_payment" name="mgm_post[product][epoch_product_code]" value="'. esc_html($product_code) .'" /></label>
				</li>';*/

		// return
		return $html;		
	}	
	
	// settings postpack purchase
	function settings_postpack_purchase($data=NULL){
		// not supported
		//if(!$this->is_button_supported('buypost')) return '';
		
		// product_code
		$product_code = isset($data->product['epoch_product_code']) ? $data->product['epoch_product_code'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Epoch Settings','mgm').'</div></div>
				     </div>
				     <div class="row">
						<div class="cell width125px mgm-padding-tb"><b>'. __('Product ID','mgm') . ':</b></div>
					 </div>	
					 <div class="row">	
						<div class="cell textalignleft">
							<input type="text" name="product[epoch_product_code]" value="'.esc_html($product_code).'" />
						</div>
				     </div>
			     </div>';
		// return
		return $html;
	}
	
	// settings subscription package
	function settings_subscription_package($data=NULL){
		// product_code
		$product_code = isset($data['pack']['product']['epoch_product_code']) ? $data['pack']['product']['epoch_product_code'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Epoch Settings','mgm') .'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Product Code','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][epoch_product_code]" value="'.esc_html($product_code).'" />								
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}	
	
	// settings coupon
	function settings_coupon($data=NULL){
		// product_code
		$product_code = isset($data['epoch_product_code']) ? $data['epoch_product_code'] : '';
		// overwrite this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('Epoch Settings','mgm') . '</div></div>
				 </div>
				 <div class="row">
					<div class="cell width125px"><b>' . __('Product Code','mgm') . ':</b></div>
				 </div>	
			     <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[epoch_product_code]" value="' . esc_html($product_code) . '" />
					</div>
				 </div>';
		// return
		return $html;
	}
	
	// return process api hook, link back to site after payment is made
	function process_return(){		
		// log
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		

		// parse
		// check and show message
		if( $success = $this->_parse_response('boolean') ){
			// deprecated #2749 calling process_notify via process_return since Epoch does have a POST Back which requires 
			// to setup, URL printed

			// caller
			// $this->set_webhook_called_by( 'self' );
			// process notify, internally called
			// $this->process_notify();

			// redirect as success if not already redirected
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_REQUEST['x_custom']));
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_REQUEST['x_custom']);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}
			// is a register redirect?
			$register_redirect = $this->_auto_login($_REQUEST['x_custom']);
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
			}	
			// redirect			
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
		}else{
			// error			
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()));
		}	
	}
	
	// notify process api hook, background IPN url 
	function process_notify() {
		// record POST/GET data if called externally		
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	
		
		// verify
		if($this->_verify_callback()){
			// log data before validate
			$tran_id = $this->_log_transaction();
			// payment type
			$payment_type = $this->_get_payment_type($_REQUEST['x_custom']);
			// custom
			$custom = $this->_get_transaction_passthrough($_REQUEST['x_custom']);
			// hook for pre process
			do_action('mgm_notify_pre_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
			// check
			switch($payment_type){
				// buypost				
				case 'post_purchase':
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// update payment check
					if( isset($custom['user_id']) ): mgm_update_payment_check_state($custom['user_id'], 'notify'); endif;	
					// buy
					$this->_buy_membership(); //run the code to process a new/extended membership
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));	
		}	
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	

		// 200 OK to gateway, only external		
		if( $this->is_webhook_called_by('merchant') ){
			if( ! headers_sent() ){
				@header('HTTP/1.1 200 OK');
				exit('OK');
			}	
		}
	}
	
	// rebill status check
	function process_rebill_status($user_id, $member=NULL){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		
		// member 
		if(!$member) $member = mgm_get_member($user_id);

		// set
		if(isset($_REQUEST['ans']) && $_REQUEST['ans'] == 'Y'){
			// old status
			$old_status = $member->status;	
			// set new status
			$member->status = $new_status = MGM_STATUS_ACTIVE;
			// status string
			$member->status_str = __('Last payment cycle processed successfully','mgm');	
			// last pay date
			$member->last_pay_date = (isset($_REQUEST['trans_date']) && !empty($_REQUEST['trans_date'])) ? date('Y-m-d', strtotime($_REQUEST['trans_date'])) : date('Y-m-d');	
			// 
			// expire date
			if(isset($member->last_pay_date) && !empty($member->expire_date)){							
				// date to add
				$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);		
				// check 
				if($date_add !== false){
					// new expire date should be later than current expire date, #1223
					$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));
					// apply on last pay date so the calc always treat last pay date form gateway	
					if(strtotime($new_expire_date) > strtotime($member->expire_date)){							
						$member->expire_date = $new_expire_date;
					}
				}else{
				// set last pay date if greater than expire date
					if(strtotime($member->last_pay_date) > strtotime($member->expire_date)){
						$member->expire_date = $member->last_pay_date;
					}
				}				
			} 
			// save
			$member->save();

			// state
			mgm_update_payment_check_state($user_id, 'epochtransstats');
			
			// action
			if( isset($new_status) && $new_status != $old_status){
				// user status change
				do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
				// rebill status change
				do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'notify');// query or notify	
			}
		}
		// log
		mgm_log($member, $this->get_context( __FUNCTION__ ));	
		
		// return
		// return $this->api_status_check($member);
		return false;
	}
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page		
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}
	
	// unsubscribe process, IPN for unsubscribe 
	function process_unsubscribe(){
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;	

		// get user id
		if( $is_admin ){
			$user_id = (int)$_GET['user_id'];	
			$membership_type = isset($_GET['membership_type']) ? $_GET['membership_type'] : '';	
		}else{				
		// get user id
			$user_id = (int)$_POST['user_id'];	
			$membership_type = isset($_POST['membership_type']) ? $_POST['membership_type'] : '';	
		}	
		// get user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		// multiple membership level update:
		if( ! empty($membership_type) && $member->membership_type != $membership_type){
			$member = mgm_get_member_another_purchase($user_id, $membership_type);				
		}			
		
		// init
		$cancel_account = true;	
		// check		
		if(isset($member->payment_info->module) && $member->payment_info->module == $this->code) {// self check
			$subscr_id = null;				
			if(!empty($member->payment_info->subscr_id))
				$subscr_id = $member->payment_info->subscr_id;
			elseif (!empty($member->pack_id)) {	
				//check the pack is recurring
				$s_packs = mgm_get_class('subscription_packs');				
				$sel_pack = $s_packs->get_pack($member->pack_id);										
				if($sel_pack['num_cycles'] != 1) 
					$subscr_id = 0;// 0 stands for a lost subscription id
			}

			// cancel at epoch
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id);						
		}	
			
		// cancel in MGM
		if( $cancel_account === true ){
			$this->_cancel_membership($user_id, true);// redirected
		}
		
		// error
		$message = isset($error_message) ? $error_message : __('Error while cancelling subscription', 'mgm') ;
		// issue #1521
		if( $is_admin ){			
			mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
		}
		// force full url, bypass custom rewrite bug
		mgm_redirect(mgm_get_custom_url('membership_details', false, array('unsubscribe_errors'=>urlencode($message))));	
	}
	
	// process html_redirect, proxy for form submit
	function process_html_redirect(){
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return __('Transaction Id invalid','mgm');
		}
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}	
		
		// update pack/transaction: this is to confirm the module code if it is different
		mgm_update_transaction(array('module'=>$this->module), $tran_id);
		// Check user id is set if subscription_purchase. issue #1049
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// generate
		$button_code = $this->_get_button_code($tran['data'],$tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');

		// log
		// mgm_log('button_code: '. $button_code, $this->get_context(__FUNCTION__));

		// the html
		$html='<form action="'. $this->_get_endpoint() .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>														
			  </form>				
			  <script type="text/javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		
		// log
		// mgm_log('html: '. $html, $this->get_context(__FUNCTION__));
			  
		// return 	  
		return $html;					
	}	
	
	// status notify, used by dataplus
	function process_status_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );			
		// verify
		if($this->_verify_postback_ip()){
						
			// check EpochTransStats post
			if(isset($_POST['ets_transaction_id'])){
				// get db ref
				if($epdb = $this->_get_epdb_ref()){	
					// columns
					$columns = array(
						'ets_transaction_id', 'ets_member_idx', 'ets_transaction_date','ets_transaction_type',
						'ets_co_code','ets_pi_code','ets_reseller_code','ets_transaction_amount','ets_payment_type',
						'ets_username','ets_ref_trans_ids','ets_password_expire','ets_email');	
					// data
					$data = array();
					// loop
					foreach($columns as $column){
						$data[$column] = isset($_POST[$column]) ? $_POST[$column] : '';
					}
					// insert, is there a duplicate issue
					$row = $epdb->get_row($epdb->prepare("SELECT `ets_transaction_id` FROM `". TBL_MGM_EPOCH_TRANS_STATUS ."` WHERE `ets_transaction_id` = '%d'", $data['ets_transaction_id']), ARRAY_A);
					// check
					if(isset($row['ets_transaction_id'])){
						$success = $epdb->update(TBL_MGM_EPOCH_TRANS_STATUS, $data, array('ets_transaction_id'=>$row['ets_transaction_id']));
					}else{
						$success = $epdb->insert(TBL_MGM_EPOCH_TRANS_STATUS, $data);
					}	
					// log
					mgm_log('DataPlus EpochTransStats Insert SQL: ' . $epdb->last_query, $this->get_context( __FUNCTION__ ) );
				}
			}else{
				// check MemberCancelStats post
				if(isset($_POST['mcs_or_idx'])){
					// get db ref
					if($epdb = $this->_get_epdb_ref()){	
						// columns
						$columns = array(
							'mcs_or_idx','mcs_canceldate','mcs_signupdate','mcs_cocode','mcs_picode','mcs_reseller',
							'mcs_reason','mcs_memberstype','mcs_username','mcs_email','mcs_passwordremovaldate'
						);	
						// data
						$data = array();
						// loop
						foreach($columns as $column){
							$data[$column] = isset($_POST[$column]) ? $_POST[$column] : '';
						}
						// insert, is there a duplicate issue
						$row = $epdb->get_row($epdb->prepare("SELECT `mcs_or_idx` FROM `". TBL_MGM_EPOCH_CANCEL_STATUS ."` WHERE `mcs_or_idx` = '%d'", $data['mcs_or_idx']), ARRAY_A);
						// check
						if(isset($row['mcs_or_idx'])){
							$success = $epdb->update(TBL_MGM_EPOCH_CANCEL_STATUS, $data, array('mcs_or_idx'=>$row['mcs_or_idx']));
						}else{
							$success = $epdb->insert(TBL_MGM_EPOCH_CANCEL_STATUS, $data);
						}	
						// log
						mgm_log('DataPlus MemberCancelStats Insert SQL: ' . $epdb->last_query, $this->get_context( __FUNCTION__ ) );
					}
				}
			}
		}else{
			// log
			mgm_log('DataPlus Verify Failed', $this->get_context( __FUNCTION__ ) );
		}
		
		// headers
		if(!headers_sent()){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}	
	}
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// permlink
		$include_permalink = (isset($options['widget'])) ? false : true;
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect', $include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
				   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';
		// return	   
		return $html;
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// cb depends on product id, check for it before generating button
		if(!isset($options['pack']['product']['epoch_product_code']) || empty($options['pack']['product']['epoch_product_code'])){
			return '<div class="mgm_button_subscribe_payment">
						<b>'.__('Error in Epoch settings : No Product ID set.','mgm').'</b>
					</div>';
			exit;
		}	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}
		
	// unsubscribe button api hook
	function get_button_unsubscribe($options=array(), $show_heading=true, $add_form=true){	
		// action
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments'));	
		
		/*// message
		$message = sprintf(__('You have subscribed to <span>%s</span> via <span>%s</span>, to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);	*/	

		// heading
		$heading = '';
		if( $show_heading ){
			$heading = '<h4>'.__('Unsubscribe','mgm').'</h4><br>';
			//$heading = '<span class="mgm_unsubscribe_btn_head">'.__('Unsubscribe','mgm').'</span>';
		}

		if( is_super_admin() ){
		// message
			$message = sprintf(__('Member subscribed to <span>%s</span> via <span>%s</span>, to unsubscribe, please click the button below. <br>','mgm'), get_option('blogname'), $this->name);		

			// action
			$action = add_query_arg(array('user_id'=>$options['user_id'],'membership_type'=>$options['membership_type']), $action);	
		}else{
			$message = sprintf(__('You have subscribed to <span>%s</span> via <span>%s</span>, to unsubscribe, please click the button below. <br>','mgm'), get_option('blogname'), $this->name);	
		}

		// html
		$html = '
			<div class="mgm_unsubscribe_btn_wrap">
				' . $heading . '
				<div class="mgm_unsubscribe_btn_desc">' . $message . '</div>
		   </div>';

		if( $add_form ){
		   	$html .='<form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="post" action="' . $action . '">';
		}
			
		$html .='
			<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
			<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
			<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" 
			onclick="mgm_confirm_unsubscribe(this, \''.$action.'\')" class="button" />';

		if( $add_form ){			
		   $html .='</form>';
		}   	

		// return
		return $html;		
	}	

	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;	

		// last one, keep first intact
		if( isset($member->payment_info->epoch_txn_id) && !empty($member->payment_info->epoch_txn_id)){
			$transaction_id = $member->payment_info->epoch_txn_id;	
		}	

		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('EPOCH INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
						__('TRANSACTION ID','mgm'), $transaction_id);					
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		
		// return 
		return $transaction_info;
	}
	
	/**
	 * get gateway tracking fields for sync
	 *
	 * @todo process another subscription
	 */
	function get_tracking_fields_html(){
		// html
		$html = sprintf('<p>%s: <input type="text" size="20" name="epoch[subscriber_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="epoch[transaction_id]"/></p>', 
						 __('Subscription ID','mgm'), __('Transaction ID','mgm'));
		
		// return			
		return $html;				
	}
	
	/**
      * update and sync gateway tracking fields
	  *
	  * @param array $data
	  * @param object $member	  
	  * @return boolean 
	  * @uses _save_tracking_fields()
	  */
	 function update_tracking_fields($post_data, &$member){
	 	// validate
		if(isset($member->payment_info->module) && $member->payment_info->module != $this->code) return false;
		
	 	// fields, module_field => post_field
		$fields = array('subscr_id'=>'subscriber_id','txn_id'=>'transaction_id');
		// data
		$data = $post_data['epoch'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
	 }	
	 
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// get button code	
	function _get_button_code($pack, $tran_id=NULL) {
		// get data
		$data = $this->_get_button_data($pack, $tran_id);
		// strip 
		$data = mgm_stripslashes_deep($data);
		// init
		$return = '';	
		// create return
		foreach ($data as $key => $value) {
			$return .= '<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
		}	
		// return
		return $return;
	}

	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system
		$system_obj = mgm_get_class('system');		
		// user data
		if( isset($pack['user_id']) && (int)$pack['user_id'] > 0 ){			
			$user_id = $pack['user_id'];
			$user = get_userdata($user_id); 
			$user_email = $user->user_email;
		}
		// item 		
		$item = $this->get_pack_item($pack);		
		// set data
		$data = array(			
			'co_code' => $this->setting['co_code'],
			'pi_code' => (isset($pack['product']['epoch_product_code']) ? $pack['product']['epoch_product_code'] : ''),
			'reseller' => $this->setting['reseller'], 
			'product_description' => $item['name'],	
			'response_post' => 'Y',
			'pi_returnurl'  => $this->setting['return_url']
		);

		// additional fields,see parent for all fields, only different given here	
		if( isset($user) ){
			// email
			if( isset($user_email) && ! empty($user_email) ){
				$data['email'] = $user_email;
			}
			// set other address
			$this->_set_address_fields($user, $data);	
		}	
													   
		// username/password	
		if($this->setting['send_userpass']=='yes'){
			// check
			if( isset($user) ){
				$data['username'] = $user->user_login;			
				$data['password'] = mgm_decrypt_password(mgm_get_member($user_id)->user_password, $user_id); 
			}		
		}else{
			$data['no_userpass']       = 1;
			$data['no_userpassverify'] = 1;
		}
		
		// custom passthrough
		$data['x_custom'] = $tran_id; // passthrough field prefixed with x_
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);

		// add verification
		if( $epoch_digest = $this->_create_epoch_digest($data) ){
			$data['epoch_digest'] = $epoch_digest;
		}

		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);
		
		// return data
		return $data;
	}	
		
	// buy post
	function _buy_post() {
		global $wpdb;
		// system
		$system_obj = mgm_get_class('system');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));		

		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_REQUEST['x_custom']);
		// local var
		extract($custom);
		
		// set user
		$user = null;
		// check
		if(isset($user_id) && (int)$user_id > 0) $user = get_userdata($user_id);

		// errors
		$errors = array();
		// purchase status
		$purchase_status = 'Error';

		// response code
		$response = $this->_parse_response();
		// process on response code	
		switch ($response['code']) {
			case 'Approved':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';
				
				// transation id
				$transaction_id = $this->_get_transaction_id('x_custom',$_REQUEST);
				// hook args
				$args = array('post_id'=>$post_id, 'transaction_id'=>$transaction_id);
				// user purchase
				if(isset($user_id) && (int)$user_id > 0){
					$args['user_id'] = $user_id;
				}else{
				// guest purchase	
					$args['guest_token'] = $guest_token;
				}											  
				// after succesful payment hook
				do_action('mgm_buy_post_transaction_success', $args);// backward compatibility
				do_action('mgm_post_purchase_payment_success', $args);// new organized name
			break;

			case 'DECLINED':
			case 'MYVSNOTACCEPTED':
			case 'DECLINEDCVV2':
			case 'CALLCENTER':
			case 'EXPIRED':
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';
															  
				// error
				$errors[] = $status_str;
			break;

			case 'Pending':
				// status
				$reason = 'Unknown';
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// purchase status
				$purchase_status = 'Pending';	
																  
				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$response['code']);
				// purchase status
				$purchase_status = 'Unknown';	
				
				// error
				$errors[] = $status_str;
																							  
		}

		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
		
		// join 
		$status = __('Failed join', 'mgm'); //overridden on a successful payment
		// check status
		if ( $purchase_status == 'Success' ) {
			// mark as purchased
			if( isset($user->ID) ){	// purchased by user	
				// call coupon action
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		
				// set as purchased	
				$this->_set_purchased($user_id, $post_id, NULL, $_REQUEST['x_custom']);
			}else{
				// purchased by guest
				if( isset($guest_token) ){
					// issue #1421, used coupon
					if(isset($coupon_id) && isset($coupon_code)) {
						// call coupon action
						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
						// set as purchased
						$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['x_custom'], $coupon_code);
					}else {
						$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['x_custom']);				
					}
				}
			}	
			
			// status
			$status = __('The post was purchased successfully', 'mgm');
		}

		// transaction status
		mgm_update_transaction_status($_REQUEST['x_custom'], $status, $status_str);
		
		// blog
		$blogname = get_option('blogname');			
		// post being purchased			
		$post = get_post($post_id);
			
		// notify user, only if gateway emails on	
		if( ! $dpne ) {			
			// notify user
			if( isset($user->ID) ){
				// mgm post setup object
				$post_obj = mgm_get_post($post_id);
				// check
				if( $this->is_payment_email_sent($_REQUEST['x_custom']) ) {	
				// check
					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){
					// update as email sent 
						$this->record_payment_email_sent($_REQUEST['x_custom']);
					}	
				}					
			}			
		}
		
		// notify admin, only if gateway emails on
		if ( ! $dge ) {
			// notify admin, 
			mgm_notify_admin_post_purchase($blogname, $user, $post, $status);
		}

		// error condition redirect
		if(count($errors)>0){
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// buy membership
	function _buy_membership() {	
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));

		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($_REQUEST['x_custom']);
		// local var
		extract($custom);	
		
		// currency
		if (!$currency) $currency = $system_obj->get_setting('currency');
		
		// find user
		$user = get_userdata($user_id);
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);			
		}else {
			$member = mgm_get_member($user_id);			
		}		
		//init - issue#2384
		$extend_pack_id = $member->pack_id;
		//check 
		if(isset($custom['subscription_option']) && $custom['subscription_option'] == 'extend' ){
			//check
			if(isset($custom['pack_id']) && $custom['pack_id'] != $extend_pack_id)	{
				$member = mgm_get_member_another_purchase($user_id, $custom['membership_type'],$custom['pack_id']);
			}
		}		
		// Get the current AC join date		
		if (!$join_date = $member->join_date) $member->join_date = time(); // Set current AC join date		

		//if there is no duration set in the user object then run the following code
		if (empty($duration_type)) {
			//if there is no duration type then use Months
			$duration_type = 'm';
		}
		// membership type default
		if (empty($membership_type)) {
			//if there is no account type in the custom string then use the existing type
			$membership_type = md5($member->membership_type);
		}
		// validate parent method
		$membership_type_verified = $this->_validate_membership_type($membership_type, 'md5|plain');
		// verified
		if (!$membership_type_verified) {
			if (strtolower($member->membership_type) != 'free') {
				// notify admin, only if gateway emails on
				if( ! $dge ) mgm_notify_admin_membership_verification_failed( $this->name );				
				// abort
				return;
			} else {
				$membership_type_verified = $member->membership_type;
			}
		}
		// set
		$membership_type = $membership_type_verified;
		// sub pack
		$subs_pack = $s_packs->get_pack($pack_id);
		// if trial on		
		if ($subs_pack['trial_on']) {
			$member->trial_on            = $subs_pack['trial_on'];
			$member->trial_cost          = $subs_pack['trial_cost'];
			$member->trial_duration      = $subs_pack['trial_duration'];
			$member->trial_duration_type = $subs_pack['trial_duration_type'];
			$member->trial_num_cycles    = $subs_pack['trial_num_cycles'];
		}
		//pack currency over rides genral setting currency - issue #1602
		if(isset($subs_pack['currency']) && $subs_pack['currency'] != $currency){
			$currency = $subs_pack['currency'];
		}		
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;	
		$member->pack_id         = $pack_id;			
		// $member->payment_type = 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		// payment info for unsubscribe		
		if(!isset($member->payment_info))
			$member->payment_info    = new stdClass;
		$member->payment_info->module = $this->code;		
		$member->payment_info->txn_type = 'subscription';
		if(isset($_REQUEST['order_id'])){
			$member->payment_info->subscr_id = $_REQUEST['order_id'];		
		}	
		if(isset($_REQUEST['transaction_id'])){	
			$member->payment_info->txn_id = $_REQUEST['transaction_id'];	
		}	
		if(isset($_REQUEST['member_id'])){
			$member->payment_info->member_id = $_REQUEST['member_id'];		
		}	
		// mgm transaction id
		$member->transaction_id = $_REQUEST['x_custom'];
		
		// process response
		$new_status = $update_role = false;		
		// errors
		$errors = array();			
		// response code
		$response = $this->_parse_response();
		// process on response code		
		switch ($response['code']) {
			case 'Approved':
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');				
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				$member->last_pay_date = date('Y-m-d', $time);
				
				// check subscription_option
				if(isset($subscription_option)){
					// on option
					switch($subscription_option){
						// @ToDo, apply expire date login
						case 'create':
						// expire date will be based on current time					
						case 'upgrade':
						// expire date will be based on current time
							// already on top
						break;
						case 'downgrade':
						// expire date will be based on expire_date if exists, current time other wise					
						case 'extend':
							// expire date will be based on expire_date if exists, current time other wise
							// extend/expire date
							// update expire date				
							// calc expiry	- issue #1226
							// membership extend functionality broken if we try to extend the same day so removed && $last_pay_date != date('Y-m-d', $time) check	
							if (!empty($member->expire_date) ) {
								$expiry = strtotime($member->expire_date);
								if ($expiry > 0 && $expiry > $time) {
									$time = $expiry;
								}
							}
						break;
					}
				}
				// type expanded
				$duration_exprs = $s_packs->get_duration_exprs();
				// if not lifetime/date range
				if(in_array($member->duration_type, array_keys($duration_exprs))) {// take only date exprs
					//consider trial duration if trial period is applicable
					if(isset($trial_on) && $trial_on == 1 ) {
						//Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							$time = strtotime('+' . $trial_duration . ' ' . $duration_exprs[$trial_duration_type], $time);								
						}					
					}else {
						// time - issue #1068
						$time = strtotime('+' . $member->duration . ' ' . $duration_exprs[$member->duration_type], $time);
					}
					// formatted
					$time_str = date('Y-m-d', $time);				
					// date extended				
					if (!$member->expire_date || strtotime($time_str) > strtotime($member->expire_date)) {
						$member->expire_date = $time_str;										
					}	
				}else{
					//if lifetime:
					if($member->duration_type == 'l'){// el = lifetime
						$member->expire_date = '';
					}
					//issue #1096
					if($member->duration_type == 'dr'){// el = /date range
						$member->expire_date = $duration_range_end_dt;
					}	
				}
								
				//update rebill: issue #: 489				
				if($member->active_num_cycles != 1 && (int)$member->rebilled < (int)$member->active_num_cycles) {
					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);	
				}
				
				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($_REQUEST['x_custom'], null, null, $pack_id);
				
				// role update
				if ($role) $update_role = true;			
				
				// transaction_id
				$transaction_id = $this->_get_transaction_id('x_custom',$_REQUEST);
				// hook args
				$args = array('user_id'=>$user_id, 'transaction_id'=>$transaction_id);
				// another membership
				if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
					$args['another_membership'] = $custom['membership_type'];
				}
				// after succesful payment hook
				do_action('mgm_membership_transaction_success', $args);// backward compatibility				
				do_action('mgm_subscription_purchase_payment_success', $args);// new organized name		
			break;

			case 'DECLINED':
			case 'MYVSNOTACCEPTED':
			case 'DECLINEDCVV2':
			case 'CALLCENTER':
			case 'EXPIRED':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;

			case 'Pending':// not avaiilable
				$new_status = MGM_STATUS_PENDING;

				$reason = 'Unnown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// error
				$errors[] = $member->status_str;
			break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $response['code']);
				// error
				$errors[] = $member->status_str;
			break;
		}	
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;
						
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->is_payment_email_sent($_REQUEST['x_custom']);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_REQUEST['x_custom']);
		
		//another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {//issue #1227
			// hide
			if($subs_pack['hide_old_content']) $member->hide_old_content = $subs_pack['hide_old_content']; 
			
			// save
			mgm_save_another_membership_fields($member, $user_id);

			// Multiple membership upgrade: first time
			if (isset($custom['multiple_upgrade_prev_packid']) && is_numeric($custom['multiple_upgrade_prev_packid'])) {
				mgm_multiple_upgrade_save_memberobject($custom, $member->transaction_id);	
			}							
		}else {
			//check - issue#2384
			if(isset($custom['subscription_option']) && $custom['subscription_option'] == 'extend' ){
				//check
				if(isset($custom['pack_id']) && $custom['pack_id'] != $extend_pack_id)	{			
					mgm_save_another_membership_fields($member, $user_id);
				}else {
					$member->save();
				}
			}else {
				$member->save();
			}			
		}		
		
		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
		
		//update coupon usage
		do_action('mgm_update_coupon_usage', array('user_id' => $user_id));
		
		// update role
		if ($update_role) {						
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $role);
		}	

		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id));// backward compatibility
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
		do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global: pass mgm_member object to consider multiple level purchases as well. 	
		
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			$member = mgm_get_member_another_purchase($user_id, $custom['membership_type']);				
		}else {
			$member = mgm_get_member($user_id);
		}
		//check - issue #2384
		if(isset($custom['subscription_option']) && $custom['subscription_option'] == 'extend' ){
			//check
			if(isset($custom['pack_id']) && $custom['pack_id'] != $extend_pack_id)	{
				$member = mgm_get_member_another_purchase($user_id, $custom['membership_type'],$custom['pack_id']);
			}
		}		
		// transaction status
		mgm_update_transaction_status($member->transaction_id, $member->status, $member->status_str);
		
		// send email notification to client
		$blogname = get_option('blogname');	
		
		// notify
		if( $acknowledge_user ) {
			// notify user, only if gateway emails on 
			if ( ! $dpne ) {			
				// notify
				if( mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj) ){						
					// update as email sent 
					$this->record_payment_email_sent($_REQUEST['x_custom']);	
				}				
			}
			// notify admin, only if gateway emails on 
			if ( ! $dge ) {
				// pack duration
				$pack_duration = $s_packs->get_pack_duration($subs_pack);
				// notify admin,
				mgm_notify_admin_membership_purchase($blogname, $user, $member, $pack_duration);
			}
		}	

		// error condition redirect
		// if(count($errors) > 0){			
		//   mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		//	 exit;
		// }
	}
	
	// cancel membership
	function _cancel_membership($user_id=null, $redirect=false) {
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;
		// get passthrough, stop further process if fails to parse
		if( isset($_REQUEST['x_custom']) && !empty($_REQUEST['x_custom'])){
		// set	
			$custom = $this->_get_transaction_passthrough($_REQUEST['x_custom']);
			// local var
			extract($custom);
		}
		
		// currency
		if (!$currency) $currency = $system_obj->setting['currency'];
		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		$multiple_update = false;
		// multiple membership level update:		
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y') ){
			$multiple_update = true;
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);	
		}	
		
		// txn type
		$_REQUEST['txn_type'] = 'subscription_cancel';		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'order_id', 'txn_id'=>'transaction_id');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member, $_REQUEST);
		
		// expire_date	
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');
		
		// transaction_id	
		$trans_id = $member->transaction_id;	
		// old status
		$old_status = $member->status;	
		// if today 
		if($expire_date == date('Y-m-d')){
			// status
			$new_status          = MGM_STATUS_CANCELLED;
			$new_status_str      = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;					
			$member->expire_date = date('Y-m-d');
			
			//reassign expiry membership pack if exists: issue#: 535			
			$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);			
		}else{
			// date
			$date_format = mgm_get_date_format('date_format');
			// status
			$new_status     = MGM_STATUS_AWAITING_CANCEL;	
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));	
			// set		
			$member->status     = $new_status;
			$member->status_str = $new_status_str;			
			// set reset date
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}

		// multiple membership level update:	
		if($multiple_update){
			mgm_save_another_membership_fields($member, $user_id);	
		}else{				
			$member->save();
		}
		
		// transaction status
		mgm_update_transaction_status($trans_id, $new_status, $new_status_str);

		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'member_unsubscribe', $member->pack_id);
			
		// send email notification to client
		$blogname = get_option('blogname');
		
		// notify user
		if( ! $dpne ) {
			// notify user
			mgm_notify_user_membership_cancellation($blogname, $user, $member, $new_status, $system_obj);			
		}
		// notify admin
		if ( ! $dge ) {
			// notify admin	
			mgm_notify_admin_membership_cancellation($blogname, $user, $member, $new_status);
		}
		
		// after cancellation hook
		do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	

		// redirect
		if( $redirect ){
			// format
			$lformat = mgm_get_date_format('date_format_long');
			$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
			                  ($expire_date == date('Y-m-d') ? __('Today','mgm') : date($lformat, strtotime($expire_date))));			
			//issue #1521
			if( $is_admin ){
				mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
			}			
			// redirect 		
			mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message))));
		}	
	}
	
	/**
	 * Cancel Recurring Subscription
	 * This is not a private function
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $subscr_id	
	 * @param int $pack_id	
	 * @return boolean
	 */	
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $subscr_id = null, $pack_id = null) {
		//if coming form process return after a subscription payment
		if(!empty($trans_ref)) {
			$transdata = $this->_get_transaction_passthrough($trans_ref);
			if($transdata['payment_type'] != 'subscription_purchase')
				return false;				
					
			$user_id = $transdata['user_id'];
							
			if(isset($transdata['is_another_membership_purchase']) && $transdata['is_another_membership_purchase'] == 'Y') {
				$member = mgm_get_member_another_purchase($user_id, $transdata['membership_type']);			
			}else {
				$member = mgm_get_member($user_id);			
			}
			
			if(isset($member->payment_info->module)) {				
				if(isset($member->payment_info->subscr_id)) {
					$subscr_id = $member->payment_info->subscr_id; 
				}else {
					//check pack is recurring:
					$pid = $pack_id ? $pack_id : $member->pack_id;					
					if($pid) {
						$s_packs = mgm_get_class('subscription_packs');
						$sel_pack = $s_packs->get_pack($pid);												
						if($sel_pack['num_cycles'] != 1)
							$subscr_id = 0;
					}										
				}
												
				//check for same module: if not call the same function of the applicale module.
				if(str_replace('mgm_','' , $member->payment_info->module) != str_replace( 'mgm_','' , $this->code ) ) {
					// log
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code, $this->get_context(__FUNCTION__));
					// return
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}	
		
		// when a subscription found
		if($subscr_id) {
			// check cancel via dataplus
			if($this->setting['rebill_check_method'] == 'dataplus') {
				// searchapi is given preference as per issue #2670
				if($this->setting['dataplus_enable'] == 'yes' ){
					return $this->_dataplus_transaction_status($user_id, $member, 'cancel');
				}
			}		

			// call api
			// check cancel via searchapi, @deprecated and discouraged
			if($this->setting['rebill_check_method'] == 'searchapi') {	
				// empty check
				if( !empty($this->setting['searchapi_auth_user']) && !empty($this->setting['searchapi_auth_pass']) ){
					// end  point
					$endpoint = $this->_get_endpoint('search') ; // search
					// headers
					$http_headers = array();//array('Content-Type' => 'text/xml');	
					// data	
					$query = array(
						'auth_user'=>$this->setting['searchapi_auth_user'],'auth_pass'=>$this->setting['searchapi_auth_pass'],
						'member_id'=>$subscr_id,'trans_id'=>$member->payment_info->txn_id,'api_action'=>'search'
					);//prevcanc	//

					// url
					$post_url = add_query_arg($query, $endpoint);

					// log
					mgm_log($post_url, $this->get_context( __FUNCTION__ ) );

					// create curl post				
					$http_response = mgm_remote_get($post_url, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));

					// log
					mgm_log($http_response, $this->get_context( __FUNCTION__ ) );

					// parse
					if($xml = @simplexml_load_string($http_response)){
						// log
						mgm_log($xml, $this->get_context( __FUNCTION__ ) );
						// has status
						if( isset($xml->Record->Customer->MemberId) ){
							// get actions
							$actions = (array)$xml->Record->AvailableActions;
							// check				
							if(isset($actions['Action']) && in_array('cancel', $actions['Action'])){
								// 2nd call
								$query = array_merge($query, array('api_action'=>'cancel','cancel_reason'=>'wmapprvd'));
								// post url
								$post_url = add_query_arg($query, $endpoint);
								// log
								mgm_log($post_url, $this->get_context( __FUNCTION__ ) );
								// cancel
								$http_response = mgm_remote_get($post_url, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));
								// log
								mgm_log($http_response, $this->get_context( __FUNCTION__ ) );
								
								// parse
								if($xml = @simplexml_load_string($http_response)){	
									// flag. @todo fetch exact response status
									// log
									mgm_log($xml, $this->get_context( __FUNCTION__ ) );
									// flag. @todo fetch response status
									if( isset($xml->Cancel->Response) && (string)$xml->Cancel->Response == 'Success'){
										return true;			
									}	
								}			
							}else{
								return __('Member already cancelled','mgm');
							}
						}else{
							// error
							if( isset( $xml->Error->Response ) ){
								return (string)$xml->Error->Response;				
							}else{
								//mgm_pr((array)$xml);
								$xml_a = (array)$xml;
								return isset( $xml_a[0]) ? $xml_a[0] : __('Unknown','mgm');
							}	
						}			
					}	
				} // end searchapi_auth_user empty check		
			}// end rebill_check_method	
		// end	
		}elseif ( ( is_null($subscr_id) || $subscr_id === 0)  ) {
			$system_obj = mgm_get_class('system');		
			$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			// check
			if( ! $dge ) {
				// blog
				$blogname = get_option('blogname');
				// user
				$user = get_userdata($user_id);
				// notify admin
				mgm_notify_admin_membership_cancellation_manual_removal_required($blogname, $user, $member);				
			}
			// return
			return true;
		}	
		// return
		return false;
	}

	/**
	 * Specifically check recurring status of each rebill for an expiry date
	 * ALong with IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 */
	function query_rebill_status($user_id, $member=NULL) {	
		// check
		if($this->setting['rebill_status_query'] == 'disabled') return false;	

		// log context 
		$log_context = $this->get_context( 'debug', __FUNCTION__ );

		// check rebill_status_query via dataplus
		if($this->setting['rebill_check_method'] == 'dataplus') {
			// searchapi is given preference as per issue #2670
			if( $this->setting['dataplus_enable'] == 'yes' ){	
				$subscr_id = null;
				if ( isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ) {
					$subscr_id = $member->payment_info->subscr_id;
				}	
				
				return $this->_dataplus_transaction_status($user_id, $member, 'rebill');
			}	

			// false
			return false;
		}	
		
		// check rebill_status_query via searchapi, @deprecated and discouraged
		if($this->setting['rebill_check_method'] == 'searchapi') {	

			// when searchapi_auth_user or searchapi_auth_pass present
			if( !empty($this->setting['searchapi_auth_user']) && !empty($this->setting['searchapi_auth_pass']) ){
				// check	
				if ( isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ) {	
					// end  point
					$endpoint = $this->_get_endpoint('search') ; // search
					// headers
					$http_headers = array();//array('Content-Type' => 'text/xml');	
					// data	
					$query = array(
						'auth_user'=>$this->setting['searchapi_auth_user'],'auth_pass'=>$this->setting['searchapi_auth_pass'],
						'member_id'=>$member->payment_info->subscr_id,'trans_id'=>$member->payment_info->txn_id,
						'api_action'=>'search'
					);//prevcanc	//
					// url
					$post_url = add_query_arg($query, $endpoint);

					// log
					mgm_log('request_url:'. $post_url, $log_context);

					// create curl post				
					$http_response = mgm_remote_get($post_url, array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false));

					// init
					$response = array();
					// parse
					if($xml = @simplexml_load_string($http_response)){
						// dump
						mgm_log($xml, $log_context);

						$response = array();

						// status
						if(isset($xml->Record->Customer->Status)){
							$response['status'] = (string)$xml->Record->Customer->Status;
						}

						// tarsn date
						if(isset($xml->Record->Customer->LastTransDate)){
							$response['last_trans_date'] = date('Y-m-d', strtotime((string)$xml->Record->Customer->LastTransDate));
						}

						// trans id
						if(isset($xml->Record->Transactions->Transaction->TransID)){
							$response['trans_id'] = (string)$xml->Record->Transactions->Transaction->TransID;
						}

						// event type
						if(isset($xml->Record->Events->Event->EventType)){
							$response['event_type'] = (string)$xml->Record->Events->Event->EventType;
						}

						// event date
						if(isset($xml->Record->Events->Event->EventInitdate)){
							$response['event_date'] = (string)$xml->Record->Events->Event->EventInitdate;
						}				
						
					}	

					// dump
					mgm_log($response, $log_context);
					//return false;						
					
					// check		
					if (isset($response['status'])) {
						// old status
						$old_status = $member->status;	
						// set status
						switch( $response['status'] ){
							case 'Active Trial':
							case 'Active Recurring':
							case 'Active Preview':
							case 'Active':
								// set new status
								$member->status = $new_status = MGM_STATUS_ACTIVE;
								// status string
								$member->status_str = __('Last payment cycle processed successfully','mgm');
								
								// last pay date
								$member->last_pay_date = (isset($response['last_trans_date']) && !empty($response['last_trans_date'])) ? $response['last_trans_date'] : date('Y-m-d');	
								// expire date
								if(isset($response['last_trans_date']) && !empty($response['last_trans_date']) && !empty($member->expire_date)){													
									// date to add
								 	$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);		
									// check 
									if($date_add !== false){
										// new expire date should be later than current expire date, #1223
										$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));
										// apply on last pay date so the calc always treat last pay date form gateway, 
										if(strtotime($new_expire_date) > strtotime($member->expire_date)){
											$member->expire_date = $new_expire_date;
										}
									}else{
									// set last pay date if greater than expire date
										if(strtotime($member->last_pay_date) > strtotime($member->expire_date)){
											$member->expire_date = $member->last_pay_date;
										}
									}				
								} 						
								// set eway txn no
								if(isset($response['trans_id'])){
									$member->payment_info->epoch_txn_id = $response['trans_id'];
								}
								
								// save
								$member->save();

								// only run in cron, other wise too many tracking will be added
								// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
								// transaction_id
								$transaction_id = $member->transaction_id;
								// hook args
								$args = array('user_id' => $user_id, 'transaction_id' => $transaction_id);
								// after succesful payment hook
								do_action('mgm_membership_transaction_success', $args);// backward compatibility				
								do_action('mgm_subscription_purchase_payment_success', $args);// new organized name	
								// }										
							break;
							case 'Canceled':
							case 'Cancelled Recurring':
							case 'Cancelled Trial':
							case 'Cancelled Preview':				
								// if expire date in future, let as awaiting
								if(!empty($member->expire_date) && strtotime($member->expire_date) > time()){
									// date format
									$date_format = mgm_get_date_format('date_format');				
									// status				
									$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;	
									// status string	
									$member->status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($member->expire_date)));							
									// set reset date				
									$member->status_reset_on = $member->expire_date;
									// reset as
									$member->status_reset_as = MGM_STATUS_CANCELLED;
								}else{
								// set cancelled
									// status			
									$member->status = $new_status = MGM_STATUS_CANCELLED;
									// status string
									$member->status_str = __('Last payment cycle cancelled','mgm');	
								}
								// save
								$member->save();

								// only run in cron, other wise too many tracking will be added
								// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
								// after cancellation hook
								do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
								// }
							break;					
							case 'Suspended':
							case 'Terminated':
							case 'Expired':						
								// set new statis
								$member->status = $new_status = MGM_STATUS_EXPIRED;
								// status string
								$member->status_str = __('Last payment cycle expired','mgm');	
								// save
								$member->save();						
							break;
						}					
						// action
						if( isset($new_status) && $new_status != $old_status){
							// user status change
							do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
							// rebill status change
							do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notify
						}		
						// return as a successful rebill
						return true;
					}			
				}
			}	
		}
			
		// return
		return false;//default to false to skip normal modules
	}

	// default setting
	function _default_setting(){
		// epoch specific	
		$this->setting['reseller']      = 'a';
		$this->setting['co_code']       = '';
		$this->setting['send_userpass'] = 'no';
		
		// search api
		$this->setting['rebill_status_query'] = 'enabled';
		$this->setting['rebill_check_method'] = 'dataplus';// dataplus|searchapi
		$this->setting['searchapi_auth_user'] = '';
		$this->setting['searchapi_auth_pass'] = '';

		// dataplus
		$this->setting['dataplus_enable']          = 'yes';
		$this->setting['dataplus_data_transfer']   = 'database';// database|http_post
		$this->setting['dataplus_database_server'] = 'local';
		$this->setting['dataplus_http_post_url']   = add_query_arg(array('module'=>$this->code,'method'=>'payment_status_notify'), mgm_home_url('payments'));	

		// digest
		$this->setting['digest_private_key'] = '';
		
		// purchase price
		if(in_array('buypost', $this->supported_buttons)){
			$this->setting['purchase_price']  = 4.00;		
		}		
		// rebill check delay
		$this->setting['rebill_check_delay'] = 0;	
		// callback messages				
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();	
	}
	
	// log transaction
	function _log_transaction(){
		// check
		if($this->_is_transaction($_REQUEST['x_custom'])){	
			// tran id
			$tran_id = (int)$_REQUEST['x_custom'];			
			// return data				
			if(isset($_REQUEST['txn_type'])){
				$option_name = $this->module.'_'.strtolower($_REQUEST['txn_type']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_REQUEST)));
			
			// options 
			$options = array('order_id','transaction_id');
			// loop
			foreach($options as $option){
				if(isset($_REQUEST[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_REQUEST[$option]));
				}
			}
			// return transaction id
			return $tran_id;
		}
		// error
		return false;	
	}
	
	// setup
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array(
			'test' => 'https://test.wnu.com/secure/fpost.cgi',
			'live' => 'https://wnu.com/secure/fpost.cgi',
			'search' => 'https://epoch.com/services/customer_search/'
		);	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings = array(
			'full_name'=>'name','phone'=>'phone','address'=>'street',						
		    'city'=>'city','state'=>'state','zip'=>'zip','country'=>'country'
		);
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
	function _address_fields_filter($name, $value){
		// trim space
		$value = trim($value);
		// reuse parent filter unless needed
		switch($name){		
			case 'address':				
				// newlines
				$value = str_replace("\n","", $value);				
				// trim chars
				$value = substr($value, 0, 80);
			break;
			case 'full_name':
			case 'city':
			case 'state':
			case 'phone':
				// trim chars
				$value = substr($value, 0, 32);
			break;			
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// parse response
	function _parse_response($return='array'){
		// init
		$response = array('status'=>'N','code'=>'Unknown');	
		// check
		if( isset($_REQUEST['ans']) && ! empty($_REQUEST['ans']) ){	
			// bank respone
			$ans_codes = explode('|', $_REQUEST['ans']);// ans=YGOODTEST|1418464163		
			$response['status'] = substr($ans_codes[0], 0, 1); 
			// check
			if($response['status'] == 'Y'){ // success
				$response['code'] = 'Approved';
			}else{// N failure			
				$response['code'] = substr($ans_codes[0], 1); // after first char(Y/N), decline code form epoch
			}	
		}					
		// set test status
		if($this->status == 'test'){
			$response['status'] = 'Y';
			$response['code'] = 'Approved';
		}

		// array
		if( 'array' == $return ){
		// return 
			return $response;
		}

		// boolean
		return ('Approved' == $response['code']);// true false
	}
	
	// setup dataplus tables
	function _setup_dataplus($enable=true) {		
		// get db ref
		if(!$epdb = $this->_get_epdb_ref()){			
			return;
		}
		// charset_collate
		$charset_collate = mgm_get_charset_collate();
		// create tables
		if($enable){
			// transaction status		
			$sql = "
				CREATE TABLE IF NOT EXISTS `" . TBL_MGM_EPOCH_TRANS_STATUS . "` ( 
					ets_transaction_id bigint(20) UNSIGNED NOT NULL default '0', 
					ets_member_idx bigint(20) UNSIGNED NOT NULL default '0', 
					ets_transaction_date datetime default NULL, 
					ets_transaction_type char(1) NOT NULL default '', 
					ets_co_code varchar(6) NOT NULL default '', 
					ets_pi_code varchar(32) NOT NULL default '', 
					ets_reseller_code varchar(64) default 'a', 
					ets_transaction_amount decimal(10,2) UNSIGNED NOT NULL default '0.00', 
					ets_payment_type char(1) default 'A', 
					ets_username varchar(32) default NULL, 
					ets_ref_trans_ids int(11) UNSIGNED default NULL, 
					ets_password_expire varchar(20) default NULL, 
					ets_email varchar(64) default NULL,
					process_status char(1) default 'N',
					PRIMARY KEY (ets_transaction_id), 
					KEY idx_reseller (ets_reseller_code), 
					KEY idx_product (ets_pi_code), 
					KEY idx_transdate (ets_transaction_date), 
					KEY idx_type (ets_transaction_type), 
					KEY idx_proctstatus (process_status) 
				) {$charset_collate} COMMENT = 'epoch dataplus transaction status'";
			$epdb->query($sql);
			//cancel status
			$sql = "
				CREATE TABLE IF NOT EXISTS `" . TBL_MGM_EPOCH_CANCEL_STATUS . "` ( 
					mcs_or_idx bigint(20) UNSIGNED NOT NULL default '0', 
					mcs_canceldate datetime default NULL, 
					mcs_signupdate datetime default NULL, 
					mcs_cocode varchar(16) NOT NULL default '', 
					mcs_picode varchar(32) NOT NULL default '', 
					mcs_reseller varchar(32) default NULL, 
					mcs_reason varchar(64) default NULL, 
					mcs_memberstype char(1) default NULL, 
					mcs_username varchar(32) default NULL, 
					mcs_email varchar(64) default NULL, 
					mcs_passwordremovaldate datetime default NULL, 
					process_status char(1) default 'N',
					PRIMARY KEY (mcs_or_idx),
					KEY idx_canceldate (mcs_canceldate),					
					KEY idx_useremail (mcs_email),
					KEY idx_procstatus (process_status)
				) {$charset_collate} COMMENT = 'epoch dataplus cancel status'";		
			$epdb->query($sql);	
			
			//set cron to check transactions			
			/*if(!wp_next_scheduled('mgm_quarterhourly_schedule')) {						
				wp_schedule_event( time(), 'quarterhourly', 'mgm_quarterhourly_schedule' );		
			}*/
		}/*else{
			// remove scheduled task for checking transactions
			wp_clear_scheduled_hook('mgm_quarterhourly_schedule');	
		}*/	
	}
	
	/**
	 * Get DB ref, this allows to keep epoch tables in a different database
	 */	 
	 function _get_epdb_ref(){
	 	// check
		if(!is_null($this->epdb)) 
			return $this->epdb;
		
	 	// set
		$this->epdb = null;		

		// local when http_post
		if( $this->setting['dataplus_data_transfer'] == 'http_post' ){
			$this->setting['dataplus_database_server'] = 'local';
		}

	 	// check
	 	if($this->setting['dataplus_enable'] == 'yes'){
			// local
			if($this->setting['dataplus_database_server'] == 'local' || $this->setting['dataplus_data_transfer'] == 'http_post'){
				global $wpdb;
				$this->epdb = $wpdb;
			}else{
				// get
				foreach(array('dataplus_database_user','dataplus_database_password','dataplus_database_name','dataplus_database_host') as $dp_dbkey){
					${$dp_dbkey} = $this->setting[$dp_dbkey];
				}
				// connect
				$this->epdb = new wpdb($dataplus_database_user, $dataplus_database_password, $dataplus_database_name, $dataplus_database_host);
				// error
				if(!$this->epdb->ready){
					// error
					if($error = $this->epdb->error){
					// message
						if(is_wp_error($error)) 
							$error_message = $error->get_error_message();
						else 
							$error_message = $error;
					}		
					// error
					$this->dataplus_error = sprintf(__('Dataplus setup error!, %s', 'mgm'), $error_message);
					// return
					return false;
				}
			}
		}
		
		// return
		return $this->epdb;
	 }
	
	/**
	 * get epoch dataplus transaction types to deal with, recurring mainly
	 */ 
	function get_dataplus_transaction_types(){
		/**
		 * Transaction Type Codes
		 * --------------------------
		 * C = Credit to Customers Account
		 * D = Chargeback Transaction
		 * F = Initial Free Trial Transaction
		 * I = Standard Initial Recurring Transaction
		 * N = Non-Initial Recurring Transaction
		 * O = Non-Recurring Transaction
		 * T = Initial Paid Trial Order Transaction
		 * U = Initial Trial Conversion
		 * X = Returned Check Transaction
		 * A = Adjustments Initiated Internally / Rejected Check Transaction
		 */
		
		/**
		 * consider only 
		 * 
		 * 'F' : Initial Free Trial Transaction
		 * 'I' : Standard Initial Recurring Transaction
		 * 'N' : Non­-Initial Recurring Transaction	
		 */
		// init  
		return $transaction_types = array(
			'F' => 'free_trial_transaction', 
			'I' => 'initial_recurring_transaction', 
			'N' => 'recurring_transaction',
			'R' => 'recurring_transaction'
		);	

		/*// transaction_types
		$transaction_types = array(
			'F' => 'free_trial_transaction', 
			'I' => 'initial_recurring_transaction', 
			'N' => 'recurring_transaction', 
			'R' => 'recurring_transaction'
		);*/
	} 

	/**
	 * Sync Membership data and DataPlus Recurring transactions
	 *
	 */
	function update_dataplus_transactions() {
		// log context 
		$log_context = $this->get_context( 'debug', __FUNCTION__ );

		// check rebill_check_method is not dataplus
		if($this->setting['rebill_check_method'] != 'dataplus') {
			// log
			mgm_log('Exit update_dataplus_transactions since rebill_check_method using searchapi.', $log_context );
			// do not process database
			return false;
		}

		// comment the below return once finished:
		// return;
		if(!$epdb = $this->_get_epdb_ref()){
			return;
		}
		
		//global $wpdb; 
		//skip if table doesn't exist
		if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_TRANS_STATUS ."'" ))) {
			return;
		}
		
		// transaction types
		$transaction_types = $this->get_dataplus_transaction_types();
		
		// create sql in 
		$transaction_types_in =  mgm_map_for_in(array_keys($transaction_types));
		
		// sql;		
		$sql = sprintf(	'SELECT ets_transaction_id, ets_transaction_date, ets_transaction_type, ets_ref_trans_ids,
		 				 ets_member_idx FROM `'.TBL_MGM_EPOCH_TRANS_STATUS.'` WHERE ets_transaction_type IN(%s) 
						 AND (process_status="N" OR process_status IS NULL)
						 ORDER BY ets_transaction_date ASC LIMIT 0,50', $transaction_types_in);
		// tran stats
		$trans_stats = $epdb->get_results($sql);	
		// log
		mgm_log('fetch sql: '. $epdb->last_query, $log_context);
		// log
		// mgm_log($trans_stats, $log_context);	
		// count
		if(count($trans_stats) > 0) {	
			// set	
			foreach( $trans_stats as $trans_stat ) {		
				// init		
				$data = array();		
				$data['trans_date'] 	= $trans_stat->ets_transaction_date;
				$data['txn_type'] 		= $transaction_types[$trans_stat->ets_transaction_type];
				$data['transaction_id'] = $trans_stat->ets_transaction_id;
				// as per Epoch Tech Support, Only Sucsessful Transactions will be inserted to EpochTransStats
				$data['ans'] 			= 'Y';//rewrite if status is dynamic
				$data['order_id']       = $trans_stat->ets_member_idx;
				// mgm tran
				$tran = mgm_get_transaction_by_option('epoch_order_id', $data['order_id']);				
				// verify
				if(isset($tran['id'])){
					// set
					$data['x_custom'] = $tran['id'];
					// set user
					if(isset($tran['user_id'])){
						$data['user_id'] = $tran['user_id'];
					}
					// rewrite global $_REQUEST for parameter compatibility
					$_REQUEST = $data;			
					// log
					// mgm_log($_REQUEST, $log_context);	
					// caller
					$this->set_webhook_called_by( 'self' );
					//Update membership status
					// $this->process_notify();
					$this->process_rebill_status( $data['user_id'] );// @todo use this 
					// update code					
					$update_data = array('process_status' => 'Y');
					$update_where = array('ets_transaction_id' => $trans_stat->ets_transaction_id);
					// change record status to 'Y' once updated: so next time when the cron runs, current record will be skipped
					$epdb->update(TBL_MGM_EPOCH_TRANS_STATUS, $update_data, $update_where);	
					// log
					mgm_log('Trans Stat Update SQL: '. $epdb->last_query, $log_context);
				}else{
					mgm_log('Trans Stat - No tran found by subscr id: '. $trans_stat->ets_member_idx, $log_context);	
				}						
			}		
		}
	}
	
	/**
	 * Sync Membership data and DataPlus Cancellations
	 *
	 */
	function update_dataplus_cancellations() {
		// log context 
		$log_context = $this->get_context( 'debug', __FUNCTION__ );

		// check rebill_check_method is not dataplus
		if($this->setting['rebill_check_method'] != 'dataplus') {
			// log
			mgm_log('Exit update_dataplus_cancellations since rebill_check_method is searchapi.', $log_context );
			// do not process database
			return false;
		}

		// comment the below return once finished:		
		// incomplete
		// return;	
		
		// return;
		if(!$epdb = $this->_get_epdb_ref()){
			return;
		}
				
		//skip if table doesn't exist
		if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_CANCEL_STATUS ."'" ))) {
			return;
		}
		
		// transaction types
		$transaction_types = $this->get_dataplus_transaction_types();		

		// sql
		$sql = 'SELECT mcs_or_idx, mcs_canceldate, mcs_reason, mcs_email FROM '.TBL_MGM_EPOCH_CANCEL_STATUS.' 
				WHERE 1 AND (process_status="N" OR process_status IS NULL) ORDER BY mcs_canceldate ASC';
		// getch
		$cancel_stats = $epdb->get_results($sql);
		// log
		mgm_log('fetch sql: '. $epdb->last_query, $log_context);
		// log
		mgm_log($cancel_stats, $log_context);
		// check
		if(count($cancel_stats) > 0) {	
			// loop
			foreach( $cancel_stats as $cancel_stat ) {
				// rewrite global $_REQUEST for parameter compatibility
				$user = get_user_by('email', $cancel_stat->mcs_email);

				// log
				mgm_log($cancel_stat, $log_context);
				mgm_log($user, $log_context);

				// cancel
				if( isset($user->ID) ){
					// cancel
					$this->_cancel_membership( $user->ID );
					// update code					
					$update_data = array('process_status' => 'Y');
					$update_where = array('mcs_or_idx' => $cancel_stat->mcs_or_idx);
					// change record status once updated:
					$epdb->update(TBL_MGM_EPOCH_CANCEL_STATUS, $update_data, $update_where);	
					// log
					mgm_log('Cancel Stat Update SQL: '. $epdb->last_query, $log_context);		
				}else{
					mgm_log('Cancel Stat - No user found by email: '. $cancel_stat->mcs_email, $log_context);	
				}			
			}	
		}	
	}
	
	/**
	 * verify callback 
	 * 
	 */ 
	function _verify_callback(){	
		// params	
		$params_verified = (isset($_REQUEST['x_custom']) && isset($_REQUEST['ans'])) ? true : false;

		// postback ip
		$postback_ip_verified = $this->_verify_postback_ip();

		// digest
		$digest_verified = $this->_verify_digest();// @todo

		// verify
		if( $params_verified && $postback_ip_verified /*&& $digest_verified*/ ){
			return true;
		}

		return false;
	}

	/**
	 * create digest
	 */ 
	function _create_epoch_digest( $data ){
		// enabled
		if( isset($this->setting['digest_private_key']) && !empty($this->setting['digest_private_key']) ){
			// sort
			asort($data);

			// join
			$str = '';
			foreach ($data as $key => $value) {
				$str .= $key . $value;
			}

			// hash
			return $epoch_digest = hash_hmac('md5', $str, $this->setting['digest_private_key']);
		}	

		return '';
	}

	// verify digest 
	function _verify_digest(){
		// enabled
		if( isset($this->setting['digest_private_key']) && !empty($this->setting['digest_private_key']) ){
			// check
			if( isset($_POST['epoch_digest']) && !empty($_POST['epoch_digest'])){
				// copy
				$epoch_digest = $_POST['epoch_digest'];
				// unset
				unset($_POST['epoch_digest']);

				// take all post valuse
				$data = $_POST;

				// sort
				asort($data);

				// join
				$str = '';
				foreach ($data as $key => $value) {
					$str .= $key.$value;
				}

				// hash
				$calc_hash = hash_hmac('md5', $str, $this->setting['digest_private_key']);

				// msg
				$msg = 'Epoch Digest Verify: epoch digest(' . $epoch_digest .') === calculated hash ('.$calc_hash.')';
				
				// error
				mgm_log($msg, $this->get_context(__FUNCTION__)); 

				// return 
				return ($epoch_digest == $epoch_digest_hash);
			}
		}	
		
		// default pass	
		return true;
	}

	/**
	 * verify dataplus postback ip
	 * 
	 */
	function _verify_postback_ip(){		
		// ips
		$epoch_ips = $this->_get_epoch_postback_ips();
		// test ip
		$request_ip = mgm_get_client_ip_address();	//'65.17.248.99';//	
		// match
		$match = false;
		// matach
		foreach($epoch_ips as $epoch_ip){
			// check
			if(preg_match('#^'.preg_quote($epoch_ip).'#', $request_ip)){
				$match = true; break;
			}
		}
		// log
		mgm_log('request_ip: ' . $request_ip . ' ip_match: ' . (int)$match, $this->get_context( __FUNCTION__ ) );
		// return
		return $match;		
	}
	
	/**
	 * fetch of site
	 */ 
	function _get_epoch_postback_ip_list(){
		// url
		if( 'test' == $this->status ){
			$url = 'https://test.epoch.com/ip_list';// .php redirects
		}else{
			$url = 'https://epoch.com/ip_list.php';
		} 

		// check
		if($ip_list = mgm_remote_get($url, null, null, 'CONNECT_ERROR')){
			// check
			if($ip_list != 'CONNECT_ERROR' ){
				$epoch_postback_ips = explode('|', $ip_list);
			}	
		}

		// known
		if(empty($epoch_postback_ips)){
			// known
			if( 'test' == $this->status ){
				$epoch_postback_ips = array('50.18.250.40','184.169.131.85');
			}else{	
				$epoch_postback_ips = array(
					'174.129.249.162','65.17.248.','68.71.103.','184.73.155.222','184.72.56.152',
					'184.72.56.199','184.73.192.230','184.169.131.85','52.0.132.63','52.71.25.2',
					'52.71.19.16','52.71.25.60','52.71.25.97','52.11.235.107','52.35.106.209',
					'52.32.146.111','52.25.210.125','52.33.176.145'
				);
			}	
		}	

		return $epoch_postback_ips;
	}

	/**
	 * get epoch ips
	 * 
	 */ 
	function _get_epoch_postback_ips(){
		// test or live
		$transient_key = 'mgm_epoch_postback_ips_'.$this->status;

		// ips
		if( ! $epoch_postback_ips = get_transient($transient_key) ){	
			// test
			$epoch_postback_ips = $this->_get_epoch_postback_ip_list();

			// set cache		
			set_transient($transient_key, $epoch_postback_ips, mgm_time2second('30 DAY'));

			// log
			mgm_log($epoch_postback_ips, $this->get_context( __FUNCTION__ ) );
		}
		
		// return
		return $epoch_postback_ips;
	}

	/**

	 * Specifically check recurring status of each rebill for an expiry date using dataplus

	 * 

	 * @param int $user_id

	 * @param object $member

	 * @param string $action (rebill|cancel)

	 * @return boolean

	 */	

	function _dataplus_transaction_status($user_id, $member=null, $action='rebill') {

		// log context 

		$log_context = $this->get_context( 'debug', __FUNCTION__ );



		// get db ref

		if( ! $epdb = $this->_get_epdb_ref()){

			return false;

		}	

			

		//skip if table doesn't exist		

		if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_TRANS_STATUS ."'" ))) {

			return false;

		}	

		

		//skip if table doesn't exist

		if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_CANCEL_STATUS ."'" ))) {

			return false;

		}							

			

		// skip if no subscr_id

		if (!isset($member->payment_info->subscr_id) || 

			( isset($member->payment_info->subscr_id) && empty($member->payment_info->subscr_id)) ) {

			return false;

		}



		// set

		$subscr_id = $member->payment_info->subscr_id;


		// log

		mgm_log('user_id: '.$user_id.' - subscr_id: '.$subscr_id.' - action: ' .$action, $log_context);

		// fetch last cancel data

		// sql

		$sql = "SELECT * FROM `".TBL_MGM_EPOCH_CANCEL_STATUS."`

			    WHERE `mcs_or_idx` = '%s' 

			    ORDER BY `mcs_canceldate` DESC LIMIT 0,1";



		$sql = $epdb->prepare($sql, $subscr_id);        

		

		// log

		mgm_log('cancel stat sql: ' .$sql, $log_context);



		$ep_cancel_stat = $epdb->get_row($sql);



		// log

		mgm_log('cancel stat row: ' .mgm_pr($ep_cancel_stat, true), $log_context);


		// fetch last transaction data

		if( 'rebill' == $action ){

			// sql

			$sql = "SELECT * FROM `". TBL_MGM_EPOCH_TRANS_STATUS ."` 

			        WHERE `ets_member_idx` = '%s' 

			        ORDER BY `ets_transaction_date` DESC LIMIT 0,1";



			$sql = $epdb->prepare($sql, $subscr_id);        

			

			// log

			mgm_log('trans stat sql: ' .$sql, $log_context);



			$ep_trans_stat = $epdb->get_row($sql);



			// log

			mgm_log('trans stat row: ' .mgm_pr($ep_trans_stat, true), $log_context);


			// if cancelled but rebill called, switch to cancel
			if( isset($ep_cancel_stat->mcs_or_idx) && isset($ep_trans_stat->ets_transaction_id) ){
				// last transaction is cancel
				// last trabsaction date is less or equal to canceldate
				//if( 'C' == $ep_trans_stat->ets_transaction_type ){
				if( strtotime($ep_trans_stat->ets_transaction_date) > strtotime($ep_cancel_stat->mcs_canceldate) ){
					$action = 'rebill';// same

					// log

					mgm_log('ets_transaction_date is later than mcs_canceldate: ' .$ep_trans_stat->ets_transaction_date.' - '.$ep_cancel_stat->mcs_canceldate.' = '.$action, $log_context);
				}else{
					$action = 'cancel';// cancel

					// log

					mgm_log('ets_transaction_date is earlier than mcs_canceldate: ' .$ep_trans_stat->ets_transaction_date.' - '.$ep_cancel_stat->mcs_canceldate.' = '. $action, $log_context);
				}
			}	
		}	

		// log

		mgm_log('finally action: ' .$action, $log_context);

		// rebill check

		switch( $action ){

			case 'rebill':
				
				// found last transaction

				if( isset($ep_trans_stat->ets_transaction_id) ){

					// check in valid ets_transaction_type

					// transaction types

					$transaction_types = $this->get_dataplus_transaction_types();	

					// take keys

					$transaction_types_keys = array_keys($transaction_types);



					// check ets_transaction_type

					if( in_array($ep_trans_stat->ets_transaction_type, $transaction_types_keys) ){

						// log

						mgm_log('check 1 pass - ets_transaction_type: ' .$ep_trans_stat->ets_transaction_type, $log_context);



						// check if already canceled

						//if( ! isset($ep_cancel_stat->mcs_or_idx) ){

							// check already updated, ets_password_expire is empty some time

							// $ets_password_expire = strtotime($ep_trans_stat->ets_password_expire);

							// log

							//mgm_log('check 2 pass - not canceled: ', $log_context);



							// old status

							$old_status = $member->status;

							// set new status

							$member->status = $new_status = MGM_STATUS_ACTIVE;

							// status string

							$member->status_str = __('Last payment cycle processed successfully','mgm');

							// last pay date

							if( isset($ep_trans_stat->ets_transaction_date) && 

								!empty($ep_trans_stat->ets_transaction_date) )

							{

								$last_pay_date = $ets_transaction_date = $ep_trans_stat->ets_transaction_date;

							}else{

								$last_pay_date = date('Y-m-d H:i:s');

							}

							// set

							$member->last_pay_date = $last_pay_date;



							// log

							mgm_log('set last_pay_date: '.$member->last_pay_date, $log_context);



							// expire date

							if( isset($ets_transaction_date) && !empty($member->expire_date) ){

								// date to add

							 	$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);

								// check 

								if($date_add !== false){

									// new expire date should be later than current expire date, #1223

									$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));

									// apply on last pay date so the calc always treat last pay date form gateway, 

									if(strtotime($new_expire_date) > strtotime($member->expire_date)){

										$member->expire_date = $new_expire_date;

									}

								}else{

									// set last pay date if greater than expire date

									if(strtotime($member->last_pay_date) > strtotime($member->expire_date)){

										$member->expire_date = $member->last_pay_date;

									}

								}

							}



							// log

							mgm_log('set expire_date: '.$member->expire_date, $log_context);



							// set epoch txn no

							if(isset($ep_trans_stat->ets_transaction_id)){

								// set

								$member->payment_info->epoch_txn_id = $ep_trans_stat->ets_transaction_id;



								// log

								mgm_log('set epoch_txn_id: '.$member->payment_info->epoch_txn_id, $log_context);

							}



							// save

							$member->save();

							// transaction_id

							$transaction_id = $member->transaction_id;

							// hook args

							$args = array('user_id' => $user_id, 'transaction_id' => $transaction_id);

							// after succesful payment hook

							do_action('mgm_membership_transaction_success', $args);// backward compatibility

							do_action('mgm_subscription_purchase_payment_success', $args);// new organized name

													

							// action

							if( isset($new_status) && $new_status != $old_status){

								// user status change

								do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	

								// rebill status change

								do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif

							}



							// mark success

							return true;

							

						/*}else{

						// canceled



							$str = sprintf('user(%s) has already canceled on %s, cancel awaiting on %s', 

							$ep_cancel_stat->mcs_email, $ep_cancel_stat->mcs_canceldate, 

							$ep_cancel_stat->mcs_passwordremovaldate );

						

							mgm_log($str, $log_context);



							// mark success

							return true;

						}*/

					}else{

						//xxx case when canceled

						$str = sprintf('ets_transaction_type(%s) not in process range(%s)', 

							$ep_trans_stat->ets_transaction_type, implode(',', $transaction_types_keys) );

						

						mgm_log($str, $log_context);

					}

				}	  

			break;  

			case 'cancel':
				// not cancelled already
				if( in_array($member->status, array(MGM_STATUS_AWAITING_CANCEL, MGM_STATUS_CANCELLED)) ){
					return false;
				}

				// old status

				$old_status = $member->status;				

				if( !empty($ep_cancel_stat->mcs_passwordremovaldate) ){
					$cancel_date = $ep_cancel_stat->mcs_passwordremovaldate;
				}else{
					$cancel_date = $ep_cancel_stat->mcs_canceldate;
				}			

				mgm_log('cancel_date: '.date('Y-m-d H:i:s', strtotime($cancel_date)), $log_context);

				// if expire date in future, let as awaiting

				if( strtotime($cancel_date) > time()){

					

					// date format

					$date_format = mgm_get_date_format('date_format');

					// status

					$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;

					// status string

					$member->status_str = sprintf(__('Subscription awaiting cancellation on %s, member requested cancelation on %s','mgm'), date($date_format, strtotime($cancel_date)),date($date_format, strtotime($ep_cancel_stat->mcs_canceldate)));

					// set reset date

					$member->status_reset_on = $cancel_date;

					// reset as

					$member->status_reset_as = MGM_STATUS_CANCELLED;								

					// reset expire

					$member->expire_date = $cancel_date;

					// reset last pay

					$member->last_pay_date = isset($ep_trans_stat->ets_transaction_date) ? $ep_trans_stat->ets_transaction_date : $ep_cancel_stat->mcs_canceldate;								

				}else{

					// date format

					$date_format = mgm_get_date_format('date_format');

					// status

					$member->status = $new_status = MGM_STATUS_CANCELLED;

					// reset expire

					$member->expire_date = $cancel_date;

					// reset last pay

					$member->last_pay_date = isset($ep_trans_stat->ets_transaction_date) ? $ep_trans_stat->ets_transaction_date : $ep_cancel_stat->mcs_canceldate;							

					// status string

					$member->status_str = sprintf(__('Last payment cycle cancelled on %s, member requested cancelation on %s','mgm'), date($date_format, strtotime($cancel_date)), date($date_format, strtotime($cancel_date)));

				}

				// save

				$member->save();

				

				// after cancellation hook

				do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	

				// action

				if( isset($new_status) && $new_status != $old_status){

					// user status change

					do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	

					// rebill status change

					do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif

				}



				// mark success

				return true;

			break;

		}	

		

		// mark failure

		return false;	

	}
		
	/**
	 * Specifically check recurring status of each rebill for an expiry date using dataplus
	 * 
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 * 
	 * @deprecated
	 */	
	function _dataplus_query_rebill_status($user_id, $member=null, $subscr_id=null) {
		// log context 
		$log_context = $this->get_context( 'debug', __FUNCTION__ );
		// get db ref
		if($epdb = $this->_get_epdb_ref()){
			//init
			$ep_response = array();
			//skip if table doesn't exist		
			if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_TRANS_STATUS ."'" ))) {
				return false;
			}	
			//skip if table doesn't exist
			if (!count($epdb->get_results("SHOW TABLES LIKE '" . TBL_MGM_EPOCH_CANCEL_STATUS ."'" ))) {
				return false;
			}							
			
			//skip if subscription id doesn't exist
			/*if (!isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {
				return false;
			}*/

			if (!isset($member->payment_info->subscr_id) || 
				( isset($member->payment_info->subscr_id) && empty($member->payment_info->subscr_id)) ) {
				return false;
			}
						
			/*$sql = "SELECT cs.mcs_canceldate, cs.mcs_signupdate, cs.mcs_passwordremovaldate, cs.mcs_email, cs.process_status".
			$sql = ",ts.ets_member_idx, ts.ets_transaction_date, ts.ets_password_expire, ts.ets_transaction_id, ts.process_status, ts.ets_password_expire".
			$sql .= " FROM " . TBL_MGM_EPOCH_TRANS_STATUS ." as ts JOIN " . TBL_MGM_EPOCH_CANCEL_STATUS ." as cs ON ts.ets_member_idx=cs.mcs_or_idx";
			$sql .= " WHERE ts.ets_member_idx = '".$member->payment_info->subscr_id."'";*/

			$sql = "SELECT cs.mcs_canceldate, cs.mcs_signupdate, cs.mcs_passwordremovaldate, cs.mcs_email, 
				   cs.process_status AS mcs_process_status,ts.process_status AS ets_process_status,ts.ets_member_idx, 
				   ts.ets_transaction_date, ts.ets_password_expire, ts.ets_transaction_id, ts.process_status, 
				   ts.ets_password_expire FROM " . TBL_MGM_EPOCH_TRANS_STATUS ." AS ts 
				   JOIN " . TBL_MGM_EPOCH_CANCEL_STATUS ." AS cs ON ts.ets_member_idx=cs.mcs_or_idx
				   WHERE ts.ets_member_idx = '".$member->payment_info->subscr_id."' 
				   AND (cs.process_status='N' OR cs.process_status IS NULL)
				   ORDER BY ets_transaction_date DESC";
			
			// log
			mgm_log('first sql: ' . $sql, $log_context);
			
			$ep_response = $epdb->get_results($sql);
			
			$ep_response = array_filter($ep_response);
		
			mgm_log('first ep_response: '. mgm_pr($ep_response, true), $log_context);

			//check
			if(!empty($ep_response)) {
				//loop
				foreach ($ep_response as $eprs)	{						
					//check if cancel
					if(isset($eprs->mcs_canceldate) && !empty($eprs->mcs_canceldate)) {
						// old status
						$old_status = $member->status;							
						// if expire date in future, let as awaiting
						if(!empty($eprs->mcs_passwordremovaldate) && strtotime($eprs->mcs_passwordremovaldate) > time()){
							// date format
							$date_format = mgm_get_date_format('date_format');
							// status
							$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;
							// status string
							$member->status_str = sprintf(__('Subscription awaiting cancellation on %s, member requested cancelation on %s','mgm'), date($date_format, strtotime($eprs->mcs_passwordremovaldate)),date($date_format, strtotime($eprs->mcs_canceldate)));
							// set reset date
							$member->status_reset_on = $eprs->mcs_passwordremovaldate;
							// reset as
							$member->status_reset_as = MGM_STATUS_CANCELLED;								
							// reset expire
							$member->expire_date = $eprs->mcs_passwordremovaldate;
							// reset last pay
							$member->last_pay_date = $eprs->ets_transaction_date;								
						}else{
							// date format
							$date_format = mgm_get_date_format('date_format');
							// status
							$member->status = $new_status = MGM_STATUS_CANCELLED;
							// reset expire
							$member->expire_date = $eprs->mcs_passwordremovaldate;
							// reset last pay
							$member->last_pay_date = $eprs->ets_transaction_date;							
							// status string
							$member->status_str = sprintf(__('Last payment cycle cancelled on %s, member requested cancelation on %s','mgm'), date($date_format, strtotime($eprs->mcs_passwordremovaldate)), date($date_format, strtotime($eprs->mcs_canceldate)));
						}
						// save
						$member->save();
						
						// after cancellation hook
						do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
						// action
						if( isset($new_status) && $new_status != $old_status){
							// user status change
							do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
							// rebill status change
							do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif
						}

						// keep awaiting not processed
						if( $member->status == MGM_STATUS_CANCELLED ){
							// update process status
							$epdb->update(TBL_MGM_EPOCH_CANCEL_STATUS, array('process_status' => 'Y'), 
								array('mcs_or_idx' => $member->payment_info->subscr_id));	

							// log
							mgm_log('Trans Cancel SQL: '. $epdb->last_query, $log_context);	
						}
							
						//return
						return true;							
					}
				}					
			}else {
				// apply date
				// $apply_date = date('2016-09-14');
				// AND DATE_FORMAT(`ets_transaction_date`, '%Y-%m-%d') > '{$apply_date}' 

				// date
				$sql = "SELECT * FROM `". TBL_MGM_EPOCH_TRANS_STATUS ."` 
				        WHERE `ets_member_idx` = '%s' 
				        AND (`process_status` = 'N' OR `process_status` IS NULL)
				        ORDER BY `ets_transaction_date` DESC LIMIT 0,1";

				$sql = $epdb->prepare($sql, $member->payment_info->subscr_id);        
				
				mgm_log('second sql: ' .$sql, $log_context);

				$ep_response = $epdb->get_row($sql, ARRAY_A);
				
				$ep_response = array_filter($ep_response);
			
				mgm_log('second ep_response: '. mgm_pr($ep_response, true), $log_context);

				//check
				if(!empty($ep_response)) {						
					// processed alredy?
					if( $ep_response['process_status'] == 'Y' ){
						
						// mgm_log('process_status is Y, '.$ep_response['ets_transaction_id'], $log_context);

						return true;				
					}

					// log
					// mgm_log($member, $log_context);
					
					if( $member->status == MGM_STATUS_CANCELLED ){

						// mgm_log('member_status is '.$member->status . ' '.$ep_response['ets_transaction_id'], $log_context);

						return true;
					}

					// old status
					$old_status = $member->status;
					// set new status
					$member->status = $new_status = MGM_STATUS_ACTIVE;
					// status string
					$member->status_str = __('Last payment cycle processed successfully','mgm');
					// last pay date
					$member->last_pay_date = (isset($ep_response['ets_transaction_date']) && !empty($ep_response['ets_transaction_date'])) ? $ep_response['ets_transaction_date'] : date('Y-m-d');
					// expire date
					if(isset($ep_response['ets_transaction_date']) && !empty($ep_response['ets_transaction_date']) && !empty($member->expire_date)){
						// date to add
					 	$date_add = mgm_get_pack_cycle_date((int)$member->pack_id, $member);
						// check 
						if($date_add !== false){
							// new expire date should be later than current expire date, #1223
							$new_expire_date = date('Y-m-d', strtotime($date_add, strtotime($member->last_pay_date)));
							// apply on last pay date so the calc always treat last pay date form gateway, 
							if(strtotime($new_expire_date) > strtotime($member->expire_date)){
								$member->expire_date = $new_expire_date;
							}
						}else{
							// set last pay date if greater than expire date
							if(strtotime($member->last_pay_date) > strtotime($member->expire_date)){
								$member->expire_date = $member->last_pay_date;
							}
						}
					}
					// set epoch txn no
					if(isset($ep_response['ets_transaction_id'])){
						$member->payment_info->epoch_txn_id = $ep_response['ets_transaction_id'];
					}
					// save
					$member->save();
					// transaction_id
					$transaction_id = $member->transaction_id;
					// hook args
					$args = array('user_id' => $user_id, 'transaction_id' => $transaction_id);
					// after succesful payment hook
					do_action('mgm_membership_transaction_success', $args);// backward compatibility
					do_action('mgm_subscription_purchase_payment_success', $args);// new organized name
											
					// action
					if( isset($new_status) && $new_status != $old_status){
						// user status change
						do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
						// rebill status change
						do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif
					}		

					// all processed
					$epdb->update(TBL_MGM_EPOCH_TRANS_STATUS, array('process_status' => 'Y'), 
							array('ets_member_idx' => $member->payment_info->subscr_id));	

					// log
					mgm_log('Trans Update SQL: '. $epdb->last_query, $log_context);	
										
					//return
					return true;	
				
				} elseif(empty($ep_response)) {	
					// current date
					$current_date = mgm_get_current_datetime('Y-m-d H:i:s', false, 'date');	

					// check expire date
					if( strtotime($current_date) <= strtotime($member->expire_date) ){
						// do not process expired
						// log
						// mgm_log('Expire check in, current_date: '. $current_date.' expire_date: '.$member->expire_date, $log_context);	

						// reset expired
						if( $member->status == MGM_STATUS_EXPIRED ){
							// mgm_log('Expire check in, user_id: '. $user_id, $log_context);					
							// status
							$old_status = $member->status;
							// set new status
							$member->status = $new_status = MGM_STATUS_ACTIVE;
							// status string
							$member->status_str = sprintf(__('Last payment cycle processed successfully.','mgm'));				
							//check
							if( isset($new_status) && $new_status != $old_status){
								// user status change
								do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
								// rebill status change
								do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif
							}	
					
							// save
							$member->save();				
						}
					}else{

						// active only
						if( $member->status == MGM_STATUS_ACTIVE ){
							// status
							$old_status = $member->status;
							// set new status
							$member->status = $new_status = MGM_STATUS_EXPIRED;
							// status string
							$member->status_str = sprintf(__('Subscription not found at epoch data plus records - epoch trans stats table.','mgm'));				
							//check
							if( isset($new_status) && $new_status != $old_status){
								// user status change
								do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
								// rebill status change
								do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notif
							}					
							// save
							$member->save();
						}
					}			

					//return
					return true;					
				}				
			}
			//return
			return false;
		}
		//return
		return false;		
	}	
	
	/**
     * get rebill status check delay
	 *
	 * @param string $time
	 * @return string 
	 */
	function get_rebill_status_check_delay( $time=null ){
	 	// delayed
	 	if( $this->is_rebill_status_check_delayed() ){
	 		// time
	 		if( is_null($time) ) $time = time();
	 		// delay
	 		$time = strtotime($this->setting['rebill_check_delay'], $time);// '-24 HOUR'
	 	}

	 	// return
	    return $time; 
	}	
}

// end file