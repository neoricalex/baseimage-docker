<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
error_reporting(0);
// -----------------------------------------------------------------------
/**
 * segpay payment module, integrates AIM and ARB
 * ps: ARB = 1 AIM + ARB for remaining cycles
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 */
class mgm_segpay extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_segpay();
	}
	
	// construct
	function mgm_segpay(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__;
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Segpay';		
		// logo
		$this->logo = $this->module_url( 'assets/segpay.png' );
		// description
		$this->description = __('segpay. ARB and AIM integration for Recurring payments and Single Purchase.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';	
		// cancellation support available ?
		$this->supports_cancellation= 'Y';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'N'; 
		// type of integration
		$this->hosted_payment = 'N';// credit card process onsite
		// if supports rebill status check	
		$this->supports_rebill_status_check = 'Y';	

		
		$this->title           	= $this->settings['title'];
		$this->description     	= $this->settings['description'];
		$this->eticketid        	= $this->settings['eticketid'];
		$this->rticketid        	= $this->settings['rticketid'];
		$this->merchantid       = $this->settings['merchantid'];
		$this->username        	= $this->settings['username'];
		$this->password        	= $this->settings['password'];
		$this->authText        	= $this->settings['authText'];
		$this->decText        	= $this->settings['decText'];
		$this->liveurl         	= $this->settings['livePaymentUrl']; //'https://secure4.segpay.com/billing/poset.cgi';
		$this->priceHashUrl   	= $this->setting['priceHashUrl'];
		$this->priceHashUrlDirect   	= $this->setting['priceHashUrlDirect'];
		$this->refundUrl       	= $this->settings['refundUrl']; //'http://Srs.segpay.com/ADM.asmx/RefundTransaction';
		$this->button_message = 'Make payments with Segpay - it\'s fast, free and secure!';
		// endpoints
			
		// default settings
		$this->_default_setting();
		// set path
		parent::set_tmpl_path();
		// read settings
		$this->read();	
	}		
	
	// default setting
	function _default_setting(){
		$this->setting['type']  				= 'dynamic';	
		$this->setting['title']  				= 'Segpay';	
		$this->setting['description']         	= 'This controls the description which the user sees during checkout.';
		$this->setting['eticketid']        		= '191922:19526';
		$this->setting['rticketid']        		= '999999:9999';
		$this->setting['merchantid']        	= '21617';
		$this->setting['initialamount']        	= '2.95';
		$this->setting['intiallength']        	= '3';
		$this->setting['recurringamount']       = '40.00';
		$this->setting['recurringlength']       = '30';
		$this->setting['return_method']   = 1;// return method 
		$this->setting['username'] 		  		= '';
		$this->setting['password']  	  		= '';
		$this->setting['authText'] 	  			= 'Payment processed, click to return.';	
		$this->setting['decText'] 	  			= 'Payment declined, click to return.';	
		$this->setting['livePaymentUrl']   		= 'https://secure4.segpay.com/billing/poset.cgi';// return method 
		$this->setting['priceHashUrlDirect'] 			= 'http://srs.segpay.com/PricingHash/PricingHash.svc/GetDynamicTrans';// instant/delayed
		$this->setting['priceHashUrl'] 			= 'https://srs.segpay.com/MerchantServices/DynamicRecurring';// instant/delayed
		
				
		$this->setting['refundUrl'] 			= 'http://Srs.segpay.com/ADM.asmx/RefundTransaction';// paypal/ipnpb
		if(in_array('buypost', $this->supported_buttons)){
			$this->setting['purchase_price']  = 4.00;		
		}
		$this->_setup_endpoints();		
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();		
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

	
	// settings box api hook
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
							// set logo
							$this->logo = $_POST['logo_new_'.$this->code];
							// save object options
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
				// paypal specific
			    $this->setting['type'] 				= $_POST['setting']['type'];
				$this->setting['title'] 			= $_POST['setting']['title'];
				$this->setting['description']       = $_POST['setting']['description'];							
				$this->setting['eticketid']         = $_POST['setting']['eticketid'];
				$this->setting['rticketid']         = $_POST['setting']['rticketid'];
				$this->setting['merchantid']        = $_POST['setting']['merchantid'];
				$this->setting['initialamount']     = $_POST['setting']['initialamount'];
				$this->setting['intiallength']      = $_POST['setting']['intiallength'];
				$this->setting['recurringamount']   = $_POST['setting']['recurringamount'];
				$this->setting['recurringlength']   = $_POST['setting']['recurringlength'];
				$this->setting['return_method'] 	 = $_POST['setting']['return_method'];
				$this->setting['username'] 		 	= $_POST['setting']['username'];
				$this->setting['password']  	 	= $_POST['setting']['password'];
				$this->setting['authText'] 	 		= $_POST['setting']['authText'];	
				$this->setting['decText']  			= $_POST['setting']['decText'];
				$this->setting['livePaymentUrl'] 	= $_POST['setting']['livePaymentUrl'];
				$this->setting['priceHashUrl'] 		= $_POST['setting']['priceHashUrl'];	
				$this->setting['refundUrl'] 		= $_POST['setting']['refundUrl'];	


				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}

				$this->status      = $_POST['status'];
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}		
				// fix old data
				$this->hosted_payment = 'Y';	
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$this->_setup_endpoints();						
				// save object options
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}//end: settings_update

	function _setup_endpoints($end_points = array()){
		// define defaults
		$defaults = array('test'     => 'https://secure4.segpay.com/billing/poset.cgi',
						  'live'     => 'https://secure4.segpay.com/billing/poset.cgi',
						   'test_nvp' => 'https://secure2.segpay.com/billing/poset.cgi',
						  'cancel'     => 'https://secure4.segpay.com/billing/poset.cgi',
						  'return'     => 'https://secure4.segpay.com/billing/poset.cgi'
						  );	
		// merge
		$end_points = $defaults;		
		// set
		$this->_set_endpoints($end_points);
	}


	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'first_name','last_name'=>'last_name','address'=>'address1',
		                 'city'=>'city','state'=>'state','zip'=>'zip','country'=>'country');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter address fields
	function _address_fields_filter($name, $value){
		// reuse parent filter unless needed
		switch($name){
			default:
				 $value = parent::_address_field_filter($name, $value);		
			break;
		}	
		// return 
		return $value;
	}
	// return process api hook

	function process_return() {
		// only save once success, there may be multiple try
		$alt_tran_id = $this->_get_alternate_transaction_id();
	
		// check and show message
		if( isset($alt_tran_id) && !empty($alt_tran_id) ){
			// query arg
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($alt_tran_id));
			// is a post redirect?			
			$post_redirect = $this->_get_post_redirect($alt_tran_id);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}				
			// is a register redirect?
			$register_redirect = $this->_auto_login($alt_tran_id);		
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
			}	
			// redirect
			
			
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));
		}else{		

			
			mgm_redirect(add_query_arg(array('status'=>'error'), $this->_get_thankyou_url()));
		}		
		
	
	}

	// notify process api hook, background IPN

	function process_notify() {			
		
		
		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );
		//this is to confirm module for IPN POST
		//fix for issue#: 528
		if(!$this->_confirm_notify()) {
			return;
		}		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();		
		
		// verify 		
		if($this->_verify_callback()){ // verify paypal payment data				
			// log data before validate
			$tran_id = $this->_log_transaction();	
		
			// for test mode which automatically marks all ipns as paid
			if (isset($_REQUEST['approved']) && isset($_REQUEST['action']) ) {
				$_POST['payment_status'] = 'Processed';
			}						
			// exit
			$exit_statuses = array('In-Progress', 'Partially-Refunded','PartiallyRefunded');
			// handle cases that the system must ignore
			if (isset($_POST['payment_status']) && in_array($_POST['payment_status'], $exit_statuses)) {
				exit;
			}	
			// payment type
			$payment_type = $this->_get_payment_type($alt_tran_id);		
			// custom
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
			// hook for pre process
			do_action('mgm_notify_pre_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));

			mgm_add_transaction_option(array('transaction_id'=>$alt_tran_id,'option_name'=>"purchase_id",'option_value'=>$_REQUEST['purchaseid']));
			// check
			switch($payment_type){
				// buypost 
				case 'post_purchase':
				case 'buypost':
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// txn type
					$txn_type = isset($_POST['txn_type']) ? $_POST['txn_type'] : '';					
					$this->_buy_membership(); 		
					//mail("sherman@123789.org","subscription","pay");
					// after capturing txn type, if need to handle other cases		
					do_action('mgm_notify_pre_subscription_process_'.$this->module, array('tran_id'=>$tran_id, 'custom'=>$custom, 'txn_type'=>$txn_type));
					// switch
											
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));				
		}else {
			//Note: Keep the below log: This is to log posts from IPN as there are issues related to recurring IPN POST
			mgm_log('FROM PAYPAL process_notify: VERIFY Failed', $this->module);	
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);		

		// 200 OK to gateway, only external		
		if( ! headers_sent() ){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}	
	}	
	
	
	function _verify_callback(){
		
		if (isset($_REQUEST['action']) && strtolower($_REQUEST['approved'])=="yes" ) {	// reversed for #298 logic gate failure	
			return true;
		}
		else{
			return false;
		}	
		
	}
	
	function process_cancel(){
		// not used for this module
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));	
	}	

	// unsubscribe process, post process for unsubscribe 
	function process_unsubscribe() {		
		
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
		// multiple membership level update:
		$member = mgm_get_member($user_id);
		$trans_id = $member->transaction_id;
	
		$purchase_id = mgm_get_transaction_option($member->transaction_id, 'purchase_id');
		$expire_date = $member->expire_date;


		// multiple membership level update:
		if( ! empty($membership_type) && $member->membership_type != $membership_type){
			$member = mgm_get_member_another_purchase($user_id, $membership_type);				
		}
		
		// init	
		
		$cancel = $this->_cancel_redirect($purchase_id);
		//$cancel = "Successful";
	
		if(strtolower($cancel) == "successful"){			
			$cancel_account = true;
			$mgm_canceld = $this->_cancel_membership($user_id, true);
			
		}

		// message
		$message = __('Error while cancelling subscription', 'mgm') ;				
		// issue #1521
			
		if($cancel_account === true) {		
			$lformat = mgm_get_date_format('date_format_long');
			$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"),($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));	

			if( $is_admin ){					
				mgm_redirect( add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php')) );
			}
			else{					
				wp_redirect(site_url('/membership-details?unsubscribed=true&unsubscribe_errors='.$message));
			}	
		
		}

		wp_redirect(site_url('/membership-details'));	
						
	}
	
	function _cancel_redirect($purchase_id){
		
		
		$username= $this->setting['username'];
		$password= $this->setting['password'];
		$refundUrl = $this->setting['refundUrl'];

		
		//$url = "http://srs.segpay.com/ADM.asmx/CancelMembership";

		$cancelUrl = $refundUrl."?Userid=".$username."&UserAccessKey=".$password."&PurchaseID=".$purchase_id."&CancelReason=cancel";

		

		$getCancel	= file_get_contents($cancelUrl);		
		$getXml			= simplexml_load_string($getCancel);
		return $getXml;		
	}
	
	function get_button_unsubscribe($options=array(), $show_heading=true, $add_form=true){
		
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_unsubscribe'), mgm_home_url('payments'));

		/*// message
		$message = sprintf(__('You have subscribed to <span>%s</span> via <span>%s</span>, to unsubscribe, please click the following link. <br>','mgm'), get_option('blogname'), $this->name);*/

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

		$html='
			<div class="mgm_unsubscribe_btn_wrap">
				' . $heading . '
				<div class="mgm_unsubscribe_btn_desc">' . $message . '</div>
		    </div>';

		if( $add_form ){
		   	$html .='<form name="mgm_unsubscribe_form" id="mgm_unsubscribe_form" method="get" action="' . $action . '">';
		}
				
		$html .='
			<input type="hidden" name="user_id" value="' . $options['user_id'] . '"/>
			<input type="hidden" name="membership_type" value="' . $options['membership_type'] . '"/>
			<!-- this should be in GET ALREADY
			<input type="hidden" name="module" value="' . $this->code . '"/>
			<input type="hidden" name="method" value="payment_unsubscribe"/>
			-->
			<input type="button" name="btn_unsubscribe" value="' . __('Unsubscribe','mgm') . '" 
			onclick="mgm_confirm_unsubscribe(this, \''.$action.'\')" class="button" />';

		if( $add_form ){			
		   $html .='</form>';
		}   
			
		// return
		return $html;		
	}

	function get_transaction_info($member, $date_format){				
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;	

		$purchase_id = mgm_get_transaction_option($member->transaction_id, 'purchase_id');

		// info
		$info = sprintf('<b>%s:</b><br>%s: %s', __('SEGPAY INFO','mgm'), __('PURCHASE ID','mgm'), $purchase_id);		
		// set
		$transaction_info = sprintf('<div class="overline">%s</div>', $info);
		
		// return 
		return $transaction_info;
	}
	
	function _cancel_membership($user_id = NULL, $redirect = false){
			
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// custom var
		$member = mgm_get_member($user_id);
		$alt_tran_id = $member->transaction_id;	
			
		// get form custom
		if( ! $user_id ) {
			// get passthrough, stop further process if fails to parse
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
			// local var
			extract($custom);
		}elseif( isset($this->cancel_data) && is_array($this->cancel_data) ){
			extract( $this->cancel_data );

			//mgm_log('cancel_data:'. mgm_pr($this->cancel_data, true), $this->module. '_'.__FUNCTION__);
		}

		// cancel_membership_type
		if( isset($_GET['membership_type']) ){
			$cancel_membership_type = $_GET['membership_type'];
		}elseif( isset($this->cancel_data['membership_type']) ){
			$cancel_membership_type = $this->cancel_data['membership_type'];
		}else{
			if( isset($membership_type) ){
				$cancel_membership_type = $membership_type;
			}
		}
				
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		// multiple membership level update:	
		$multiple_update = false;		
		// check
		if( ( isset($cancel_membership_type) && $member->membership_type != $cancel_membership_type ) 
			|| ( isset($is_another_membership_purchase) && bool_from_yn($is_another_membership_purchase) ) ) 
		{
			// update
			$multiple_update = true;
			$multi_memtype = isset($cancel_membership_type) ? $cancel_membership_type : $membership_type;

			// member
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);

			//mgm_log( 'multiple_update member:'. mgm_pr($member, true), $this->get_context( __FUNCTION__ ) );
		}
				
		// Don't save if it is cancel request with an upgrade:
		if(isset($_POST['subscr_id']) && isset($member->payment_info->subscr_id) && $_POST['subscr_id'] != $member->payment_info->subscr_id) {			
			return;
		}
			
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
		
		// tracking fields module_field => post_field
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'subscr_id', 'txn_id'=>'txn_id');
		// save tracking fields
		$this->_save_tracking_fields($tracking_fields, $member);	
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;	
		// life time
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');				
		// if trial on 
		if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			
			// if cancel data is before trial end, set cancel on trial expire_date
			$trial_expire_date = strtotime("+{$subs_pack['trial_duration']} {$duration_exprs[$subs_pack['trial_duration_type']]}", $member->join_date);
			
			// if lower
			if(time() < $trial_expire_date){
				$expire_date = date('Y-m-d',$trial_expire_date);
			}
		}	
		// transaction_id		
		$trans_id = $member->transaction_id;
		// old status
		$old_status = $member->status;		
		// if today or set as instant cancel 
		if($expire_date == date('Y-m-d') || $this->setting['subs_cancel']=='instant'){
			// status
			$new_status          = MGM_STATUS_CANCELLED;
			$new_status_str      = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;		
			// expire			
			$member->expire_date = date('Y-m-d');																																				
			// reassign expiry membership pack if exists: issue#: 535			
			$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);			
		}else{		
			// format
			$date_format = mgm_get_date_format('date_format');
			// status
			$new_status     = MGM_STATUS_AWAITING_CANCEL;	
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));	
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;				
			// set reset info
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}
				
		// multiple membership level update:	
		if($multiple_update) {
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
		return "mgm_canceled";
	}	
	
	

	function process_html_redirect(){
				
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return __('Transaction Id invalid','mgm');
		}			
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}		
		
		$payment_type = $tran['payment_type'];

		// update pack/transaction: this is to confirm the module code if it is different
		mgm_update_transaction(array('module'=>$this->module), $tran_id);
		
		// Check user id is set if subscription_purchase. issue #1049
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id'] < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
				
		
		

		if($this->setting['type']=='dynamic')
		{
			if($payment_type == 'subscription_purchase'){
			$priceHash= $this->get_subscription_pricehash($tran['data']);
			$priceInput = "DynamicPricingID";
			$xeticketid = $this->setting['rticketid'];
		}
		else{			
			$priceHash= urldecode($this->get_buypost_pricehash($tran['data']));
			$priceInput = "dynamictrans";
			$xeticketid = $this->setting['eticketid'];
		}

			// generate
			$button_code = $this->_get_button_code($tran['data'], $tran_id);		
			//echo "<pre>"; print_r($tran);echo "</pre>";
			// extra code
			$additional_code = do_action('mgm_additional_code');
		}
		else
		{
			// generate
			$button_code = $this->_get_button_code($tran['data'], $tran_id);

			// echo "<pre>"; print_r($tran);echo "</pre>";
			// extra code

			$additional_code = do_action('mgm_additional_code');
			if($payment_type == 'subscription_purchase')
			{
				if(isset($tran['data']['pack_id']))
				{
					$option_value= 'subscription_id_'.$tran['data']['pack_id'];
				}
				else
				{
					$option_value= 'subscription_id_'.$tran['data']['id'];
					
				}
				$xeticketid= get_option($option_value);
			}
			else
			{
				// mgm static eticket id	
				$xeticketid= get_post_meta($tran['data']['post_id'], 'eticket', true );
			
			}
			
		}
		
		
		// the html
		$html='<form action="'. $this->_get_endpoint() .'" method="get" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'';
				if($this->setting['type']=='dynamic')
				{	
					$html.='<input type="hidden" name="'.$priceInput.'" value="'.$priceHash.'">';
				}
				$html.='
					<input type="hidden" name="x-eticketid" value="'.$xeticketid.'">				
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->label).'</b>												
			  </form>				
			  ';
			
			$html .='   <script type="text/javascript">document.' . $this->code . '_redirect_form.submit();</script>';
			  
		
		
		// return 	  
		return $html;					
	}
	
	
	function get_subscription_pricehash($data){	
		
		$subs_pack = $data;
		$trial_period_type = $pack['trial_duration_type'];
		$recur_period_type = $pack['duration_type'];
		
		
		if (isset($subs_pack['trial_on']) && $subs_pack['trial_on'] > 0 ) {							
				$intial_amount= $subs_pack['trial_cost'];				
				$intial_days = $this->get_number_of_days($subs_pack, "trial");			
		}
		else{
			$intial_amount= $subs_pack['cost'];			
			$intial_days = $this->get_number_of_days($subs_pack, "live");	
		}
				
		$rec_amount = $subs_pack['cost'];
		$rec_days = $this->get_number_of_days($subs_pack, "live");		
	
		
		$url = str_replace('https://', 'https://'.$this->setting['username'].':'.$this->setting['password']."@", $this->setting['priceHashUrl']);
		
		
		$priceHashUrl = $url."?MerchantID=".$this->setting['merchantid']."&InitialAmount=".$intial_amount."&InitialLength=".$intial_days."&RecurringAmount=".$rec_amount."&RecurringLength=".$rec_days;
		$getPriceHash	= file_get_contents($priceHashUrl);		
		$hash = trim($getPriceHash,'"');
		
		return $hash;
		
	}
	
	function get_buypost_pricehash($data){		
		$subs_pack = $data;
		$post_amount= $subs_pack['cost'];		
		$url = $this->setting['priceHashUrlDirect'];		
		$priceHashUrl = $url."?value=".$post_amount;
		$getPriceHash	= file_get_contents($priceHashUrl);	
		$getXml			= simplexml_load_string($getPriceHash);		
		return $getXml;
	}
	
	
	function get_number_of_days($data, $mode){
	
		
		if($mode == "trial"){
			$duration_type = $data['trial_duration_type'];
			$duration =  $data['trial_duration'];
			
		}else{
			$duration_type = $data['duration_type'];
			$duration = $data['duration'];
		}		
		
		
		$unit_types = array('d'=>'days', 'w'=>'days', 'm'=>'months', 'y'=>'months','dr'=>'custom');	// treat year a 12 x months	also  treat 7 x weeks
			
		$x_interval_unit   = $unit_types[strtolower($duration_type)]; // days|months		
				
			if(strtolower($duration_type)=='y'){
			
				$datetime1 = new DateTime($data['duration_range_start_dt']);
				$datetime2 = new DateTime($data['duration_range_end_dt']);
				$interval = $datetime1->diff($datetime2);
				$intial_days = $interval->format('%a');
			}
			else{					
				if(strtolower($duration_type)=='y'){
					$x_interval_length = ((int)$duration * 12);
				}elseif (strtolower($duration_type)=='w'){
					$x_interval_length = ((int)$duration * 7);
				}elseif (strtolower($duration_type)=='dr'){
					$x_interval_length = ((int)$duration * 7);
				}else {
					$x_interval_length = $duration;
				}
				
				$add_by = str_replace('s', '',$x_interval_unit);// DAY|MONTH
				$final_date = date( 'Y-m-d', strtotime("+{$x_interval_length} {$add_by}", strtotime(date('Y-m-d')))) ;	
					$datetime1 = new DateTime(date( 'Y-m-d'));
					$datetime2 = new DateTime($final_date);
					$interval = $datetime1->diff($datetime2);
					$intial_days = $interval->format('%a');
			}
		
		
		return $intial_days;
	}

	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		// if payment initiaed from sidebar widget, do not use permalink : the current url		
		$include_permalink = (isset($options['widget'])) ? false : true;	
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				 
				   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->button_message . '">
				   <div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';
		// return	   
		return $html;
	}
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// get html
		
		
		$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
					<input type="hidden" name="gateway_type" value="buypost">
					<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
					<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->button_message . '">
					<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
			   </form>';				
		// return or print
		if ($return) return $html;
		
		// print
		echo $html;	
		
	}

	function _get_button_code($pack, $tran_id=NULL) {
		// get data
		$data = $this->_get_button_data($pack, $tran_id);
		// strip 
		$data = mgm_stripslashes_deep($data);		
		// init
		$html = '';	

		// create return
		foreach ($data as $key => $value) {
			$html .= '<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />';
		}	
		// return
		return $html;
	}//end:

	function _get_button_data($pack, $tran_id=NULL) {
		// system setting
		$system_obj = mgm_get_class('system');	
		// user data
		if( isset($pack['user_id']) && (int)$pack['user_id'] > 0 ){			
			$user_id = $pack['user_id'];

			$user = get_userdata($user_id); 
			$user_email = $user->user_email;
			$user_name  = $user->user_login;
			$user_password= get_user_meta( $user_id, '_mgm_cf_password' , true );
			$user_password= mgm_decrypt_password($user_password, $user_id);
			//$user_password= $user->user_pass;
		}
		
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}
			
		// item 		
		$item = $this->get_pack_item($pack);		
		// setup data array	
		$user_data = get_user_by( 'ID',$user_id);	
		$mgm_segpay_options = maybe_unserialize(get_option('mgm_system_options'));
		if ($mgm_segpay_options['setting']['enable_email_as_username'] == "Y") {
				$username 	=  $user_data->user_email;
		}
		else
		{
				$username 	=  $user_data->user_login;
		}	
		$data = array(			
			'userId'      		=> $user_id,		
			'invoiceId'       	=> $tran_id,
			'dynamicdesc'     	=> $item['name'],			
			'currency_code' 	=> $pack['currency'],			
			'notify_url'    	=> $this->setting['notify_url'],			
			'rm'           	 	=> (int)$this->setting['return_method'], // 0: GET, 1: GET, 2: POST
			'cbt'           	=> sprintf('Return to %s', get_option('blogname')),
			'username'			=> $username,
			'password'			=> $user_password
		);
				
		// additional fields,see parent for all fields, only different given here	
		if( isset($user) ){
			// email
			if( isset($user_email) && ! empty($user_email) ){
				$data['x-billemail'] = $user_email;
			}
			// set other address
			$this->_set_address_fields($user, $data);	
		}			
		
		// subscription purchase with ongoing/limited
		if( !isset($pack['buypost']) && isset($pack['duration_type']) && $pack['num_cycles'] != 1){ // does not support one-time recurring
		// if ($pack['num_cycles'] != 1 && $pack['duration_type']) { // old style
			// command
			$data['cmd'] = '_xclick-subscriptions';
			// subs
			$data['amount'] = $pack['cost'];
			
			
			// trial
			if (isset($pack['trial_on']) && $pack['trial_on'] > 0 ) {
				$data['amount'] = $pack['trial_cost'];				
			}			
		} else {
		// post purchase or one time billiing
			$data['cmd']    = '_xclick';
			$data['bn']     = 'PP-BuyNowBF';
			$data['amount'] = $pack['cost'];
			
			// Purchase Post Specifix
			if (isset($pack['buypost']) && $pack['buypost'] == 1 ) {				
				$data['src'] = $data['sra'] = 0;														
			} else { // One time payment				
				$data['src'] = $data['sra'] = 1;				
			}
			
			// apply addons
			$this->_apply_addons($pack, $data, array('amount'=>'amount','description'=>'item_name'));// known field => module $data field
		}
		
		
		
		// set custom on request so that it can be tracked for post purchase
		//========== here strt for set custom data ==============
		$payment_custom_data ="";
		if(isset($data['custom'])&& strlen($data['custom'])>0){
			$payment_custom_data = $data['custom'];
		} 	
		 $data['x-decl-link']= add_query_arg(array('custom'=>$payment_custom_data), $this->setting['return_url']);
		

		$data['x-auth-text'] = $this->setting['authText'];
		$data['x-decl-text'] = $this->setting['decText'];		
		
		$data['x-auth-link'] = $this->_get_thankyou_url();
		
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
		// update pack/transaction
		mgm_update_transaction(array('data'=>json_encode($pack),'module'=>$this->module), $tran_id);		
		
		// return data
		return $data;
	}
	
	function _buy_post() {
		global $wpdb;
		
		// system
		$system_obj = mgm_get_class('system');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// custom var
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
		// local var
		extract($custom);

		// find user
		$user = null;
		// check
		if(isset($user_id) && (int)$user_id > 0) $user = get_userdata($user_id);	

		// errors
		$errors = array();
		// purchase status
		$purchase_status = 'Error';

		// status
		switch ($_POST['payment_status']) {
			case 'Completed':
			case 'Processed':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';											  
				
				// transaction id
				$transaction_id = $this->_get_transaction_id();
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

			case 'Reversed':
			case 'Refunded':
			case 'Denied':
				// status
				$status_str = __('Last payment was refunded or denied','mgm');
				// purchase status
				$purchase_status = 'Failure';

				// error
				$errors[] = $status_str;															  
			break;

			case 'Pending':
				// status
				$status_str = __('Last payment is pending. Reason: Unknown','mgm');
				// purchase status
				$purchase_status = 'Pending';

				// error
				$errors[] = $status_str;															  
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['payment_status']);
				// purchase status
				$purchase_status = 'Unknown';	

				// error
				$errors[] = $status_str;																						  
			break;
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 		
		
		// status
		$status = __('Failed join', 'mgm'); //overridden on a successful payment
		// check status
		if ( $purchase_status == 'Success' ) {
			// mark as purchased
			if( isset($user->ID) ){	// purchased by user	
				// call coupon action
				do_action('mgm_update_coupon_usage', array('user_id' => $user_id));		
				// set as purchased	
				$this->_set_purchased($user_id, $post_id, NULL, $alt_tran_id);
			}else{
				// purchased by guest
				if( isset($guest_token) ){
					// issue #1421, used coupon
					if(isset($coupon_id) && isset($coupon_code)) {
						// call coupon action
						do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
						// set as purchased
						$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id, $coupon_code);
					}else {
						$this->_set_purchased(NULL, $post_id, $guest_token, $alt_tran_id);				
					}
				}
			}	

			// status
			$status = __('The post was purchased successfully', 'mgm');
		}
		

		// transaction status
		mgm_update_transaction_status($alt_tran_id, $status, $status_str);
		
		// blog
		$blogname = get_option('blogname');			
		// post being purchased			
		$post = get_post($post_id);

		// notify user and admin, only if gateway emails on	
		if ( ! $dpne ) {			
			// notify user
			if( isset($user->ID) ){
				// mgm post setup object
				$post_obj = mgm_get_post($post_id);
				// check
				if( $this->is_payment_email_sent($alt_tran_id) ) {	
				// check
					if( mgm_notify_user_post_purchase($blogname, $user, $post, $purchase_status, $system_obj, $post_obj, $status_str) ){
					// update as email sent 
						$this->record_payment_email_sent($alt_tran_id);
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
		
		//$alt_tran_id=  $this->_read_transaction_id();

		$subscr_id = $_POST['subscr_id']; 
		
		
		if(strtolower($_REQEST['approved']) == "yes"){
			$_POST['payment_status'] = 'Processed';
		}
		$alt_tran_id = $this->_get_alternate_transaction_id();
		
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// custom var
			
		
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);
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
		if (!$join_date = $member->join_date) $member->join_date = time(); // Set current AC join date\
		
		// if there is no duration set in the user object then run the following code
		if (empty($duration_type)) {
			// if there is no duration type then use Months
			$duration_type = 'm';
		}
	
		// membership type default
		if (empty($membership_type)) {
			// if there is no account type in the custom string then use the existing type
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
		// exit flag
		$exitif_subscr_signup = true;
		
		// if trial on	
		if (isset($custom['trial_on']) && $custom['trial_on'] == 1) {
			$member->trial_on            = $custom['trial_on'];
			$member->trial_cost          = $custom['trial_cost'];
			$member->trial_duration      = $custom['trial_duration'];
			$member->trial_duration_type = $custom['trial_duration_type'];
			$member->trial_num_cycles    = $custom['trial_num_cycles'];
			
			// 0 cost trial does not send payment_status, make check here
			// this should be causing trouble for MGA #969, mis firing payment success for failed payment, add support
			if($member->trial_cost == 0 && !isset($_REQEST['approved'])){				
				// when subscr_id present, treat it as Processed , #287 issue, with trial cost is 0, only subscr_signup is sent without payment_status
				// with trial cost > 0, subscr_signup and subscr_payment sent with payment_status
				if(isset($subscr_id)){
					$_POST['payment_status']  = 'Processed';
					$exitif_subscr_signup = false;
				}		
			}
			
		}
		// check this later:(will need to be commented if it is being saved in transactions data)	
		elseif ($subs_pack['trial_on']) {
			
			$member->trial_on            = $subs_pack['trial_on'];
			$member->trial_cost          = $subs_pack['trial_cost'];
			$member->trial_duration      = $subs_pack['trial_duration'];
			$member->trial_duration_type = $subs_pack['trial_duration_type'];
			$member->trial_num_cycles    = $subs_pack['trial_num_cycles'];
			
			// 0 cost trial does not send payment_status, make check here
			// this should be causing trouble for MGA #969, mis firing payment success for failed payment, add support
			if($member->trial_cost == 0 && !isset($_REQEST['approved'])){				
				// when subscr_id present, treat it as Processed , #287 issue, with trial cost is 0, only subscr_signup is sent without payment_status
				// with trial cost > 0, subscr_signup and subscr_payment sent with payment_status
				if(isset($subscr_id)){
					$_POST['payment_status'] = 'Processed';
					$exitif_subscr_signup = false;
				}		
			}
		}			
		
		// exit scenarios
		if(!isset($_POST['payment_status'])) {			
			exit;
		}
	
		
		//pack currency over rides genral setting currency - issue #1602
		if(isset($subs_pack['currency']) && $subs_pack['currency'] != $currency){
			$currency =$subs_pack['currency'];
		}
		// member fields
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;		
		$member->pack_id         = $pack_id;
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		
			
		//one time pack subscription id option become an issue #1507
		if(isset($subs_pack['num_cycles']) == 1 && !isset($_POST['subscr_id'])){
			$_POST['subscr_id'] = 'ONE-TIME SUBSCRIPTION';
		}		
		
		// tracking fields module_field => post_field, will be used to unsubscribe
		$tracking_fields = array('txn_type'=>'txn_type', 'subscr_id'=>'subscr_id', 'txn_id'=>'txn_id');
		// save tracking fields 
		$this->_save_tracking_fields($tracking_fields, $member);
		// check here: ->module is absent in payment_info, its is _save_tracking_fields
		// if (!isset($member->payment_info->module)) $member->payment_info->module = $this->code;		
		// set parent transaction id
		$member->transaction_id = $alt_tran_id;
		
		// process PayPal response
		$new_status = $update_role = false;
		// status
		
		switch ($_POST['payment_status']) {
			case 'Completed':
			case 'Processed':
				// status
				
				$new_status = MGM_STATUS_ACTIVE;
				$member->status_str = __('Last payment was successful','mgm');					
				
				// old type match
				$old_membership_type = mgm_get_user_membership_type($user_id, 'code');
				// set
				if ($old_membership_type != $membership_type) {
					$member->join_date = time(); // type join date as different var
				}
				// old content hide
				$member->hide_old_content = (isset($hide_old_content)) ? $hide_old_content : 0;
				
				$time = time();
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				// last pay
				$member->last_pay_date = date('Y-m-d', $time);				
				
				
				
				// as per version 1.0, there was chance of double process, with new separation logic for rebill, this is safe
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
					// consider trial duration if trial period is applicable
					if( isset($trial_on) && $trial_on == 1 && (!isset($member->trial_used) || (int)$member->trial_used < (int)$member->trial_num_cycles) ) {// is it the root of #1150 issue
						// Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							// set
							$time = strtotime("+{$trial_duration} {$duration_exprs[$trial_duration_type]}", $time);		
							// increment trial used, each IPN should increement this and extend
							$member->trial_used = ( !isset($member->trial_used) || empty($member->trial_used)) ? 1 : ((int)$member->trial_used+1);												
						}					
					} else {
						// time - issue #1068
						$time = strtotime("+{$member->duration} {$duration_exprs[$member->duration_type]}", $time);							
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
							
				// update rebill: issue #: 489				
				if(isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 && ((int)$member->rebilled < (int)$member->active_num_cycles)) {
					// rebill
					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);	
				}	
				
				// cancel previous subscription:
				// issue#: 565				
				//$this->cancel_recurring_subscription($alt_tran_id, null, null, $pack_id);
				
				// role update
				if ($role) $update_role = true;
							
				// transaction_id
				$transaction_id = $this->_get_transaction_id();
				
				// hook args
				$args = array('user_id' => $user_id, 'transaction_id'=>$transaction_id);
				// another membership
				if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
					$args['another_membership'] = $custom['membership_type'];
				}
				// after succesful payment hook
				do_action('mgm_membership_transaction_success', $args);// backward compatibility				
				do_action('mgm_subscription_purchase_payment_success', $args);// new organized name	

			break;
			case 'Reversed':
			case 'Refunded':
			case 'Denied':
				// status
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				break;

			case 'Pending':
				// status
				$new_status = MGM_STATUS_PENDING;
				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				break;

			default:
				// status
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $_POST['payment_status']);
				break;
		}

		// handle exceptions from the subscription specific fields
		if ($new_status == MGM_STATUS_ACTIVE && in_array($_POST['txn_type'], array('subscr_failed', 'subscr_eot'))) {
			$new_status = MGM_STATUS_NULL;
			$member->status_str = __('The subscription is not active','mgm');
		}
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;		
		
		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->is_payment_email_sent($alt_tran_id);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $_POST['custom']);
		
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {
			//issue #1227
			if($subs_pack['hide_old_content'])
				$member->hide_old_content = $subs_pack['hide_old_content']; 

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
			
		// role update
		if ($update_role) {						
			$obj_role = new mgm_roles();				
			$obj_role->add_user_role($user_id, $role);
		}		
		
		// return action
		do_action('mgm_return_'.$this->module, array('user_id' => $user_id, 'acknowledge_user' => $acknowledge_user));// backward compatibility
		do_action('mgm_return_subscription_payment_'.$this->module, array('user_id' => $user_id));// new , individual	
		do_action('mgm_return_subscription_payment', array('user_id' => $user_id, 'acknowledge_ar' => $acknowledge_ar, 'mgm_member' => $member));// new, global: pass mgm_member object to consider multiple level purchases as well. 	

		// read member again for internal updates if any
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
		
		// for paypal only
		if( in_array($_POST['txn_type'], array( 'subscr_payment', 'subscr_signup','web_accept')) && 
			in_array($_POST['payment_status'], array( 'Processed', 'Completed')) ){ 
			$acknowledge_user = true;
		}else{
			$acknowledge_user = false;
		}

		// notify
		if( $acknowledge_user ) {
			// notify user, only if gateway emails on 
			if ( ! $dpne ) {			
				// notify
				if( mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj) ){						
					// update as email sent 
					$this->record_payment_email_sent($alt_tran_id);	
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
	}
	

	
		// get custom var from mulple sources
	function _get_alternate_transaction_id(){
		// custom
		$alt_tran_id = '';
		
		// check alternate
		if(isset($_REQUEST['invoiceId']) && !empty($_REQUEST['invoiceId'])){
			$alt_tran_id = $_REQUEST['invoiceId'];
		}else{
		// default custom	
			$alt_tran_id = $this->_get_alternate_transaction_id();
		}  
	
		// return 		
		return $alt_tran_id;
	}
	
	//transaction status check - issue #1963
	/*
	function _get_transaction_status_api($tran = NULL) {
		// post data
		$post_data = array();			
		// add internal vars
		$secure =array(
			'USER'         => $this->setting['username'],	
			'PWD'          => $this->setting['password'],		
			'SIGNATURE'    => $this->setting['signature'],
			'VERSION'      => '64.0');
		// merge
		$post_data = array_merge($post_data, $secure); // overwrite post data array with secure params		
		// method
		$post_data['METHOD']     	= 'GetTransactionDetails';
		//trans
		$post_data['TRANSACTIONID'] = $tran;
		// endpoint	
		$endpoint = $this->_get_endpoint($this->status . '_nvp');		
		//issue #1508
		$url_parsed = parse_url($endpoint);  			
		// domain/host
		$domain = $url_parsed['host'];
		// version
		$product_version = mgm_get_class('auth')->get_product_info('product_version');
		// headers
		$http_headers = array (
			'POST /cgi-bin/webscr HTTP/1.1\r\n',
			'Content-Type: application/x-www-form-urlencoded\r\n',
			'Host: '.$domain.'\r\n',
			'User-Agent : MagicMembers V.'.$product_version.'; '.home_url().'\r\n',
			'Connection: close\r\n\r\n');		
		
		//force to use http 1.1 header
		// add_filter( 'http_request_version', 'mgm_use_http_header');		
		$http_args = array(
			'headers'=>$http_headers,'timeout'=>30,'sslverify'=>false,'httpversion'=>'1.1',
			'user-agent'=>'MagicMembers V.'.$product_version.'; '.home_url());
		// post
		$http_response = mgm_remote_post($endpoint, $post_data, $http_args);
		//init
		$response = array();
		// parse
		parse_str($http_response, $response);
		//log
		mgm_log($response, $this->get_context( __FUNCTION__ ));
		mail('sherman@123789.org','res-log',print_r($response,true))
		//return
		return $response;	
	}	
	}
*/



	
}



// end file