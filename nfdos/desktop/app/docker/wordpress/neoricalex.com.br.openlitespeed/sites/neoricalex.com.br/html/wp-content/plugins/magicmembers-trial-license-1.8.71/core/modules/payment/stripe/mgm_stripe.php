<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Stripe payment module, integrates Subscription and Charge API
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 * @uses       Stripe-Version 2017-08-15
 */
class mgm_stripe extends mgm_payment{
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_stripe();
	}
	
	// construct
	function mgm_stripe(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__;
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'Stripe';		
		// logo
		$this->logo = $this->module_url( 'assets/stripe.png' );
		// description
		$this->description = __('Stripe. Recurring payments and One Off Purchase.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription', 'buypost');
		// trial support available ?
		$this->supports_trial= 'Y';	
		// cancellation support available ?
		$this->supports_cancellation= 'Y';	
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y'; 
		// type of integration
		$this->hosted_payment = 'N';// credit card process onsite
		// supports rebill status check	
		$this->supports_rebill_status_check = 'Y';
		// supports rebill status check with member status awaiting cancel
	 	$this->supports_rebill_status_check_awaiting_cancel = 'Y';
		// rebill status check delay
		$this->rebill_status_check_delay = true;				
		// if supports card token save
		$this->supports_card_token_save = 'Y';
		// if supports card update
		$this->supports_card_update = 'Y';
		// reactivate subscription
		$this->supports_reactivate_subscription = 'Y';
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
	
	// settings_box
	function settings_box(){
		global $wpdb;
		// data
		$data = array();	
		// set 
		$data['module'] = $this;		
		// load template view
		return $this->loader->template('settings_box', array('data'=>$data), true);
	}
	
	// update
	function settings_update(){
		// form type 
		switch($_POST['setting_form']){
			case 'box':
			// from box	
				switch($_POST['act']){
					case 'logo_update':
						// logo if uploaded
						if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
							// set
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
				// stripe specific				
				$this->setting['secretkey']  = $_POST['setting']['secretkey'];
				$this->setting['publishable_key'] = $_POST['setting']['publishable_key'];	
				$this->setting['currency']  = $_POST['setting']['currency'];	
				// csutom end points flag
				if( isset($_POST['setting']['end_points']) ){
					$this->setting['end_points'] = $_POST['setting']['end_points'];		
				}
				// update supported card types
				if( isset($_POST['card_types']) && !empty($_POST['card_types']) ){
					$this->setting['supported_card_types'] = $_POST['card_types'];
				}else{
					$this->setting['supported_card_types'] = array();	
				}
				// cancel
				$this->setting['subs_cancel'] = $_POST['setting']['subs_cancel'];
				// webhook_secret
				$this->setting['webhook_secret'] = $_POST['setting']['webhook_secret'];
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				//$this->rebill_status_check_delay      = $_POST['rebill_status_check_delay'];
				//check
				if($this->rebill_status_check_delay){
					$this->setting['rebill_check_delay'] = $_POST['rebill_check_delay'];	
				}				
				// save
				$this->setting['stripe_version'] = '2017-08-15';

				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
					$this->logo = $_POST['logo_new_'.$this->code];
				}				
				// rebill query
				$this->setting['rebill_status_query'] = $_POST['setting']['rebill_status_query'];	
				// fix old data
				$this->hosted_payment = 'N';	
				$this->requires_product_mapping = 'Y'; 
				$this->supports_card_token_save = 'Y';
				$this->supports_card_update = 'Y';
				// setup callback messages				
				$this->_setup_callback_messages($_POST['setting']);
				// re setup callback urls
				$this->_setup_callback_urls($_POST['setting']);
				// re setup endpoints
				$end_points = (isset($_POST['end_points'])) ? $_POST['end_points'] : array(); 
				// update
				$this->_setup_endpoints($end_points);												
				// save
				$this->save();
				// message
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated','mgm'), $this->name)));
			break;
		}		
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// plan_id
		$plan_id = isset($data['pack']['product']['stripe_plan_id']) ? $data['pack']['product']['stripe_plan_id'] : ''; 
		// display
		$display = 'class="displaynone"';
		// check
		if(isset($data['pack']['modules']) && in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displayblock"';
		}
		// html
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('Stripe Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Plan ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][stripe_plan_id]" value="'.esc_html($plan_id).'" />
								<div class="tips width95">' . __('Plan ID from Stripe.','mgm') . '</div>
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;
	}
	
	// hook for coupon setting
	function settings_coupon($data=NULL){
		// stripe coupon
		$stripe_coupon = isset($data['stripe_coupon']) ? $data['stripe_coupon'] : ''; 
		// html
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('Stripe Settings','mgm') . '</div></div>
			     </div>
			     <div class="row">
					<div class="cell width125px"><b>' . __('Coupon Code','mgm') . ':</b></div>
			     </div>	
			     <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[stripe_coupon]" value="' . esc_html($stripe_coupon) . '" size="50" />
					</div>
			     </div>';
		// return
		return $html;
	}

	// return process api hook, link back to site after payment is made
	function process_return(){
		// check and show message		
		if( isset($this->response->id) ){// id		
			// caller
			$this->webhook_called_by = 'self';				
			// process notify, internally called
			$this->process_notify();
			// redirect as success if not already redirected
			$query_arg = array('status'=>'success', 'trans_ref' => mgm_encode_id($_POST['custom']));
			// is a post redirect?
			$post_redirect = $this->_get_post_redirect($_POST['custom']);
			// set post redirect
			if($post_redirect !== false){
				$query_arg['post_redirect'] = $post_redirect;
			}			
			// is a register redirect?			
			$register_redirect = $this->_auto_login($_POST['custom']);
			// set register redirect
			if($register_redirect !== false){
				$query_arg['register_redirect'] = $register_redirect;
			}
			// redirect			
			mgm_redirect(add_query_arg($query_arg, $this->_get_thankyou_url()));			
		}else{			
			// error
			$error = isset($this->response->error->message) ? $this->response->error->message : 'Unknown error';
			// error
			mgm_redirect(add_query_arg(array('status'=>'error','errors'=>urlencode($error)), $this->_get_thankyou_url()));
		}		
	}
	
	// notify process api hook, background IPN url 
	// used as proxy IPN for this module
	function process_notify(){		
		// notify to post, should be moved to status_notify, just for backward compatibility
		$silent = $this->is_webhook_called_by('merchant') ? true : false;
		if ( $silent ){
			$this->_notify2post();	
		}

		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	

		// verify			
		if ( $this->_verify_callback( true ) ){	
			// log data before validate
			$tran_id = $this->_log_transaction();
			// payment type
			$payment_type = $this->_get_payment_type($_POST['custom']);
			// custom
			$custom = $this->_get_transaction_passthrough($_POST['custom']);
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
					// notify
					if( isset($_POST['notify_type']) ){
						if( isset($custom['user_id']) ){
							$this->_process_webhook_notifications($custom['user_id'], $silent);
						}
					}else{
						// new subscription 						
						$this->_buy_membership(); //run the code to process a new/extended membership								
					}					
				break;							
			}
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
		}		
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
		
		// 200 OK to gateway, only when called externally by Merchant i.e. Push Notify	
		if( $silent ){	
			if( ! headers_sent() ){
			  	@header('HTTP/1.1 200 OK');
			 	exit('OK');
			} 
		}	
	}

	/**
	 * status notify process api hook, background webhook url
	 * 
	 * @uses Stripe Webhook
	 * customer.subscription.deleted
	 * customer.deleted
	 * customer.subscription.updated
	 */
	function process_status_notify(){
		// post
		$this->_notify2post();	

		//record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );

		// proces
		if($this->_verify_callback(true)){
			// custom
			$custom = $this->_get_transaction_passthrough($_POST['custom'], true);
			// process 
			if( isset($custom['user_id']) ){
				$this->_process_webhook_notifications($custom['user_id'], true);
			}
		}
		
		// 200 OK to gateway, only external		
		if(!headers_sent()){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}
	}

	/**
	 * process
	 */ 
	function _process_webhook_notifications($user_id, $silent=true){
		// txn type
		$notify_type = $_POST['notify_type'];
		// switch
		switch($notify_type){
			case 'customer.subscription.deleted':	
				// update payment check
				mgm_update_payment_check_state($user_id, 'notify');		
				// cancellation
				$this->_cancel_membership($user_id, $silent); //run the code to process a membership cancellation
			break;
			case 'customer.deleted':
				// update payment check
				mgm_update_payment_check_state($user_id, 'notify');
				// update status
				$this->_expire_membership($user_id);
			break;
			case 'customer.subscription.updated':	
				// update payment check
				mgm_update_payment_check_state($user_id, 'notify');
			break;	
		}
	}
	
	// process cancel api hook 
	function process_cancel(){
		// redirect to cancel page
		mgm_redirect(add_query_arg(array('status'=>'cancel'), $this->_get_thankyou_url()));
	}
	
	/**
	 * unsubscribe process, proxy for unsubscribe
	 */ 
	public function process_unsubscribe() {	
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
		$cancel_account = true;	
		
		// multiple membership level update:
		if( ! empty($membership_type) && $member->membership_type != $membership_type){
			$member = mgm_get_member_another_purchase($user_id, $membership_type);				
		}			

		// fix
		$member = $this->_fix_stripe_customer_data($member);
		// check
		$cust_id = $member->payment_info->cust_id; 
		// check
		if(isset($member->payment_info->module)) {
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
			// cancel 
			$cancel_account = $this->cancel_recurring_subscription(null, $user_id, $subscr_id, null, $cust_id);						
		}	
			
		// verify
		if($cancel_account === true){
			$this->_cancel_membership($user_id);
		}else{
			// message
			$message = isset($this->response->error->message) ? $this->response->error->message : __('Error while cancelling subscription', 'mgm') ;
			//issue #1521
			if( $is_admin ){
				// url
				$url = add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php'));
				// redirect
				mgm_redirect($url);
			}			
			// force full url, bypass custom rewrite bug
			mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribe_errors'=>urlencode($message))));
		}
	}

	/**
	 * reactivate subscription process
	 */ 
	public function process_reactivate_subscription() {				
		// current user
		$user = wp_get_current_user();

		// get user id
		$user_id = (int)$_POST['user_id'];	

		if( $user->ID != $user_id ){
			return __('User invalid!', 'mgm');
		}	

		//die;
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// get user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);
		$reactivate_account = true;	
		
		/*// multiple membership level update:
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);				
		}*/			

		// fix
		$member = $this->_fix_stripe_customer_data($member);
		// check
		$cust_id = $member->payment_info->cust_id; 
		// check
		if(isset($member->payment_info->module)) {
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

			// check
			if( $subscr_id ){
				// api_data
				$api_data = array('customer_id'=>$cust_id, 'subscription_id'=>$subscr_id);
				// call			
				if ( $this->response = $this->_get_api_notify( 'reactivate_subscription', null, $api_data ) ){
					// log
					mgm_log('reactivate_subscription response: '. mgm_pr($this->response, true), $this->get_context( __FUNCTION__ ));
					// check		
					if( (isset($this->response->cancel_at_period_end) && (bool)$this->response->cancel_at_period_end == false ) ){	
						$reactivate_account = true;
					}
				}
			}						
		}	
			
		// verify
		if($reactivate_account === true){
			$this->_reactivate_membership($user_id, $subscr_id);
		}else{
			// message
			$message = isset($this->response->error->message) ? $this->response->error->message : __('Error while reactivating subscription', 'mgm') ;
			//issue #1521
			if($is_admin){
				// url
				$url = add_query_arg(array('user_id'=>$user_id,'reactivate_errors'=>urlencode($message)), admin_url('user-edit.php'));
				// redirect
				mgm_redirect($url);
			}			
			// force full url, bypass custom rewrite bug
			mgm_redirect(mgm_get_custom_url('membership_details', false,array('reactivate_errors'=>urlencode($message))));
		}
	}
	
	// process credit_card, proxy for credit_card processing
	function process_credit_card(){			
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return $this->throw_cc_error(__('Transaction Id invalid','mgm'));
		}	
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return $this->throw_cc_error(__('Transaction invalid','mgm'));
		}		

		// Check user id is set if subscription_purchase. issue #1049
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return $this->throw_cc_error(__('Transaction invalid . User id field is empty','mgm'));		
		}
		// default
		$error_string = 'Unknown Error occurred';
		// system
		$system_obj = mgm_get_class('system');
		// tran data
		$tran_data = $tran['data'];
		// get data		
		$data = $this->_get_button_data($tran['data'], $tran_id);	
		// merge
		$post_data = array_merge($_POST, $data); 		
		// set email
		$this->_set_default_email($post_data, 'email');	
		// action
		if( isset($data['plan']) ){
			// has a recurring plan, subscription payment
			if( ! empty($data['plan']) ){
				$action = 'create_customer';// ongoing subsctiption
			}else{
			// one time 	
				$action = 'create_customer_and_charge';// onetime subscription
			}
		}else{
			$action = 'create_charge';// one off/ purchase content
		}
		// log
		mgm_log('main action: '.$action, $this->get_context( __FUNCTION__ ) );
		// check if upgrade, we can check $tran['data']['subscription_option'] == 'upgrade'
		$cust_id = $subscr_id = $card_id = $input_data = null;
		// check -issue #2317 skip for buypost
		if( isset($tran['data']['user_id']) && $tran['payment_type'] == 'subscription_purchase' ){
			// check
			if( $user_id = $tran['data']['user_id']){
				// get member
				if( $member = mgm_get_member($user_id) ){
					// fix
					$member = $this->_fix_stripe_customer_data($member);

					// has previous payment
					if( isset($member->payment_info->module) && $member->payment_info->module == $this->code ){
						// cust_id 
						if( isset($member->payment_info->cust_id) && !empty($member->payment_info->cust_id) ){
							$cust_id = $member->payment_info->cust_id;
						}
						// subscr_id
						if( isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ){
							$subscr_id = $member->payment_info->subscr_id;
						}
						// card id
						if( isset($member->payment_info->card_id) && !empty($member->payment_info->card_id) ){
							$card_id = $member->payment_info->card_id;
						}
						
						// has subscription
						if( $cust_id ){
							
							/*  //old check for one time billing and ongoing billing
							if( in_array($action, array('create_customer','create_customer_and_charge')) ){
								$action = 'use_customer_and_change_subscription';
							}*/
							
							// one time billing and ongoing billing
							if( preg_match('/^create_customer/', $action)){
								// ontime
								if( isset($tran['data']['num_cycles']) && (int)$tran['data']['num_cycles'] == 1 ){
									$action = 'use_customer_and_charge';
								}elseif(isset($tran['data']['is_another_membership_purchase']) && bool_from_yn($tran['data']['is_another_membership_purchase'])) {
									// purchase another
									$action = 'use_customer_and_create_subscription';// @todo
								}else{
									// upgrade/change	
									$action = 'use_customer_and_change_subscription';
								}
							}else{
								$action = 'use_customer_and_charge';
							}								
						}
					}
				}
				// input_data
				if( $cust_id || $subscr_id || $card_id ){
					// init
					$input_data = array();
					
					// customer
					if( ! is_null($cust_id) ){
						$input_data = $input_data + array('customer_id'=>$cust_id);
					} 

					// subscription			
					if( ! is_null($subscr_id) ){
						$input_data = $input_data + array('subscription_id'=>$subscr_id);
					}

					// card
					if( ! is_null($card_id) ){
						$input_data = $input_data + array('card_id'=>$card_id);
					}

					// charge_type
					if( preg_match('/charge/', $action) ){
						//issue #2568
						if( ! isset($tran_data['buypost']) && isset($tran_data['duration_type'])/* && $tran_data['num_cycles'] != 1 */){
							$charge_type = 'purchase_subscription';
						}else{
							$charge_type = 'purchase_content';
						}

						$input_data = $input_data + array('charge_type'=>$charge_type);
					}
				}				
			}
		}
		// check -issue #2317 
		if( isset($tran_data['buypost']) ){
			$input_data = array('charge_type'=>'purchase_content');
		}			
		// merge
		$post_data = $this->_filter_postdata($action, $post_data); // overwrite post data array with secure params	

		// log
		mgm_log( 'action: ' . $action.' post_data: ' . mgm_pr(mgm_filter_cc_details($post_data), true).' input_data: ' . mgm_pr($input_data, true), $this->get_context( __FUNCTION__ ));
		
		// to object
		if( $this->response = $this->_get_api_notify($action, $post_data, $input_data) ){
			// dumo
			mgm_log( 'response: '. mgm_pr($this->response, true), $this->get_context( __FUNCTION__ ) );
			// ok
			if( isset($this->response->id) ){// check id				
				// store custom
				$_POST['custom'] = !empty($post_data['custom']) ? $post_data['custom'] : $tran_id;
				// treat as return
				$this->process_return(); exit;		
			}else{
				// wp error 
				if ( is_wp_error( $this->response ) ){
					$error_string = $this->response->get_error_message();  
				}  
			}			
		}		
		
		// stripe error
		if ( isset($this->response->error) ) {
			// return to credit card form
			$error_string = $this->response->error->message;	
		}

		// return error
		return $this->throw_cc_error($error_string);			
	}		

	// process html_redirect, proxy for form submit
	//The credit card form will get submitted to the same function, then validate the card and if everything is clear
	//() will be called internally
	function process_html_redirect(){	
		// read tran id
		if(!$tran_id = $this->_read_transaction_id()){		
			return __('Transaction Id invalid','mgm');
		}
		
		// get trans
		if(!$tran = mgm_get_transaction($tran_id)){
			return __('Transaction invalid','mgm');
		}
		// Check user id is set if subscription_purchase. issue #1049	
		if ($tran['payment_type'] == 'subscription_purchase' && 
			(!isset($tran['data']['user_id']) || (isset($tran['data']['user_id']) && (int) $tran['data']['user_id']  < 1))) {
			return __('Transaction invalid . User id field is empty','mgm');		
		}
		// get user
		$user_id = $tran['data']['user_id'];
		$user    = get_userdata($user_id);		
		
		// update pack/transaction: this is to confirm the module code if it is different
		mgm_update_transaction(array('module'=>$this->module), $tran_id);
				
		// cc field
		$cc_fields = $this->_get_ccfields($user, $tran);
		
		// validate card: This will validate card and reload the form with errors
		// if validated process_credit_card() method will be called internally			
		$html = $this->validate_cc_fields_process(__FUNCTION__);		
		// the html
		$html .='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="tran_id" value="'.$tran_id.'">
					<input type="hidden" name="submit_from" value="'.__FUNCTION__.'">
					'. $cc_fields .'
			   </form>';
		// return 	  
		return $html;					
	}	
	
	// subscribe button api hook
	function get_button_subscribe($options=array()){	
		$include_permalink = (isset($options['widget'])) ? false : true;
		// get html
		$html='<form action="'. $this->_get_endpoint('html_redirect',$include_permalink) .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
				   <input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
				   <input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
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
			onclick="mgm_confirm_unsubscribe(this, \''.$action.'\')" class="button button-primary" />';

		if( $add_form ){			
		   $html .='</form>';
		}   

		// return
		return $html;		
	}	
	
	// dependency_check
	function dependency_check(){
	// return		
		return false;			
	}
	
	/**
	 * get module transaction info
	 */ 
	function get_transaction_info($member, $date_format){		
		// fix
		$member = $this->_fix_stripe_customer_data($member);

		// customer_id
		$customer_id = '';
		if( isset($member->payment_info->cust_id) ){
			$customer_id = $member->payment_info->cust_id;
		}

		// subscription_id
		$subscription_id = '';
		if( isset($member->payment_info->subscr_id) ){
			$subscription_id = $member->payment_info->subscr_id;
		}

		// card_id
		$card_id = '';
		if( isset($member->payment_info->card_id) ){
			$card_id = $member->payment_info->card_id;
		}
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s<br>%s: %s', 
			    __('STRIPE INFO','mgm'), 
			    __('CUSTOMER ID','mgm'), $customer_id, 
			    __('SUBSCRIPTION ID','mgm'), $subscription_id,
			    __('CARD ID','mgm'), $card_id);

		// transaction_id
		$transaction_id = '';
		if( isset($member->payment_info->txn_id) ){
			$transaction_id = $member->payment_info->txn_id;
		}
		// check
		if( !empty($transaction_id) ){
			$info .= sprintf('<br>%s: %s', __('TRANSACTION ID','mgm'), $transaction_id);
		}
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
		$html = sprintf('<p>%s: <input type="text" size="20" name="stripe[customer_id]"/></p>
			             <p>%s: <input type="text" size="20" name="stripe[subscriber_id]"/></p>
			             <p>%s: <input type="text" size="20" name="stripe[card_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="stripe[transaction_id]"/></p>', 
						 __('Customer ID','mgm'),
						 __('Subscription ID','mgm'), 
						 __('Card ID','mgm'),
						 __('Transaction ID','mgm'));
		
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
		$fields = array('cust_id'=>'customer_id','subscr_id'=>'subscriber_id','card_id'=>'card_id','txn_id'=>'transaction_id');
		// data
		$data = $post_data['stripe'];
	 	// return
	 	return $this->_save_tracking_fields($fields, $member, $data); 			
	 }

	/**
	 * fix cust id in member node
	 */
	 function _fix_stripe_customer_data($member){
	 	// cust id
		if( ! isset($member->payment_info->cust_id) && isset($member->payment_info->subscr_id) ){
			// copy
			$customer_id = $member->payment_info->subscr_id;
			// get from charge
			if( preg_match('/^ch_/', $customer_id) ){
				// check
				if( $charge = $this->_get_api_notify('get_charge', null, array('charge_id'=>$customer_id) ) ){
					$customer_id = $charge->customer;
				}
			}
			// skip sub
			if( ! preg_match('/^sub_/', $customer_id) ){
				// check
				if( $customer = $this->_get_api_notify('get_customer', null, array('customer_id'=>$customer_id) ) ){
					// set
					$member->payment_info->cust_id = $customer->id;
					$member->payment_info->subscr_id = $customer->subscription->id;
					// save
					$member->save();
				}
			}
		}

		// fetch default card
		if( ! isset($member->payment_info->card_id) ){
			if( isset($customer->default_card) ){
				$member->payment_info->card_id = $customer->default_card;
			}elseif( isset($customer->default_source) ){
				$member->payment_info->card_id = $customer->default_source;
			}else {
				if( isset($member->payment_info->cust_id) ){
					// customer
					$customer_id = $member->payment_info->cust_id;
					// check
					if( ! preg_match('/^sub_/', $customer_id) ){
						// fetch again
						if( $customer = $this->_get_api_notify('get_customer', null, array('customer_id'=>$customer_id) ) ){
							if( isset($customer->default_card) ){
								$member->payment_info->card_id = $customer->default_card;
							}elseif( isset($customer->default_source) ){
								$member->payment_info->card_id = $customer->default_source;
							}	
						}
					}
				}	
			}	

			// check
			if( isset($member->payment_info->card_id) && !empty($member->payment_info->card_id) ){
			// save
				$member->save();
			}
		}	

		// return
		return $member;
	} 
					
	// MODULE API COMMON PRIVATE HELPERS /////////////////////////////////////////////////////////////////	
	
	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system
		$system_obj = mgm_get_class('system');	
		// user data
		if( isset($pack['user_id']) && (int)$pack['user_id'] > 0 ){			
			$user_id = $pack['user_id'];
			$user = get_userdata($user_id); 
			$user_email = $user->user_email;
			$member = mgm_get_member($user_id);
		}

		// item 		
		$item = $this->get_pack_item($pack);
		// set data
		$data = array(			
			'invoice_num' => $tran_id, 					
			'description' => $item['name'],			
			'currency'    => strtolower($this->setting['currency'])	
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

		// product based	
		if(isset($pack['product']['stripe_plan_id'])){
			$data['plan']     = trim($pack['product']['stripe_plan_id']);	
			$data['quantity'] = 1;
			// coupon
			if( isset($pack['coupon_id']) ) {
				// coupon
				$coupon = mgm_get_coupon($pack['coupon_id']);
				// set
				if( isset( $coupon->product->stripe_coupon) && ! empty($coupon->product->stripe_coupon) ){
					$data['coupon'] = $coupon->product->stripe_coupon;
				}else{
					$data['coupon'] = $coupon->name;
				}				
			}	
		}else{
		// use total
			$data['amount'] = mgm_convert_to_cents($pack['cost']);
		}	

		// one time billing, plan will be empty
		if( isset($data['plan']) && empty($data['plan']) ){
			$data['amount'] = mgm_convert_to_cents($pack['cost']);
		}
		
		// custom passthrough
		$data['custom'] = $tran_id;

		// update currency
		if($pack['currency'] != $this->setting['currency']){
			$pack['currency'] = $this->setting['currency'];
		}
		
		// strip
		$data = mgm_stripslashes_deep($data);
		
		// add filter @todo test
		$data = apply_filters('mgm_payment_button_data', $data, $tran_id, $this->module, $pack);
		
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
		
		// passthrough
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

		// response code
		$response_code = ($this->response->paid == true) ? 'Approved' : 'Declined';
		// process on response code
		switch ($response_code) {
			case 'Approved':
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';				
										  
				// after succesful payment hook
				do_action('mgm_buy_post_transaction_success', array('post_id' => $post_id));// backward compatibility
				do_action('mgm_post_purchase_payment_success', array('post_id' => $post_id));// new organized name
			break;

			case 'Declined':
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
			case 'Held for Review':
				// reason
				// $reason = $this->response['message_text'];
				// status
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $response_code);				
				// purchase status
				$purchase_status = 'Pending';	
										  
				// error
				$errors[] = $status_str;
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$response_code);
				// purchase status
				$purchase_status = 'Unknown';					
																							  
				// error
				$errors[] = $status_str;
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 	
		
		// STATUS
		$status = __('Failed join', 'mgm'); // overridden on a successful payment
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
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		
		// passthrough
		$alt_tran_id = $this->_get_alternate_transaction_id();
				
		// get passthrough, stop further process if fails to parse
		$custom = $this->_get_transaction_passthrough($alt_tran_id);		
		// local var
		extract($custom);
		
		// currency
		if (!$currency) $currency = $system_obj->get_setting('currency');		
		// find user
		$user = get_userdata($user_id);
		//another_subscription modification
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
		if ( ! $membership_type_verified ) {
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
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;		
		$member->pack_id         = $pack_id;
		// $member->payment_type = 'subscription';		
		//save num_cycles in mgm_member object:(issue#: 478)
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type    = ((int)$member->active_num_cycles == 1) ? 'one-time' : 'subscription';
		
		// skip for update
		if( ! isset($_POST['notify_type']) || 
			( isset($_POST['notify_type']) && ! in_array($_POST['notify_type'], array('customer.subscription.updated')) ) ){

			// payment info for unsubscribe		
			if(!isset($member->payment_info)) $member->payment_info = new stdClass;
			// module
			$member->payment_info->module = $this->code;		
			// transaction type
			$member->payment_info->txn_type = 'subscription';

			// response type
			if( isset($this->response->object) ){
				// object returned			
				switch( $this->response->object ){
					case 'customer':
					// customer created, first subscription
						// cust_id
						$member->payment_info->cust_id = $this->response->id;

						// subscr_id
						if( isset($this->response->subscription->id) ){
							$member->payment_info->subscr_id = $this->response->subscription->id;
						}

						// card_id
						if( isset($this->response->default_card) ){
							$member->payment_info->card_id = $this->response->default_card;
						}elseif( isset($this->response->default_source) ){
							$member->payment_info->card_id = $this->response->default_source;
						}
					break;
					case 'subscription':
					// subscription created, second subscription on existing customer	
						// subscr_id
						$member->payment_info->subscr_id = $this->response->id;

						// cust_id
						if( isset($this->response->customer) ){
							// cust
							$member->payment_info->cust_id = $this->response->customer;
						}
					break;
				}
				
				// reset rebilled count
				if(isset($member->rebilled)) unset($member->rebilled);		
			}

			// transaction id	
			if(isset($this->response->invoice)){	
				$member->payment_info->txn_id = $this->response->invoice;	
			}
		}
		
		// mgm transaction id
		$member->transaction_id = $alt_tran_id;
		// process response
		$new_status = $update_role = false;
		// errors
		$errors = array();	
		// check
		$subscription_status = 'unknown';
		if( isset($this->response->subscription->status) && ! empty($this->response->subscription->status) ){// create
			$subscription_status = strtolower($this->response->subscription->status);
		}elseif( isset($this->response->status) && ! empty($this->response->status) ){// upgrade
			$subscription_status = strtolower($this->response->status);
		}
		// log
		mgm_log('subscription_status: '.$subscription_status, $this->get_context( __FUNCTION__ ));
		//issue #2581
		$subscription_status = ($subscription_status == 'paid') ? 'approved' : $subscription_status;
		// log
		mgm_log('subscription_status: '.$subscription_status, $this->get_context( __FUNCTION__ ));			
		// status
		switch ($subscription_status) {
			case 'approved':
			case 'active':
			case 'trialing':
				// status
				$new_status = MGM_STATUS_ACTIVE;
				// $member->status_str = __('Last payment was successful','mgm');	
				$member->status_str = sprintf(__('Last %s was successful','mgm'), ($subscription_status == 'trialing' ? 'trial cycle' : 'payment') );
										
				// current time
				if( isset($this->response->current_period_start) && !is_null($this->response->current_period_start) ){
					$time = $this->response->current_period_start;
				}else{
					$time = time();
				}
				// get last pay
				$last_pay_date = isset($member->last_pay_date) ? $member->last_pay_date : null;			
				// new last pay date			
				$member->last_pay_date = date('Y-m-d', $time);				
				// default expire_date_ts to calculate next cycle expire date
				$expire_date_ts = $time;
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
							$expire_date_ts = $time;
						break;
						case 'downgrade':
						// expire date will be based on expire_date if exists, current time other wise					
						case 'extend':
						// expire date will be based on expire_date if exists, current time other wise
							$expire_date_ts = $time;
							// extend/expire date
							//if (!empty($member->expire_date) && $member->last_pay_date != date('Y-m-d', $expire_date_ts)) {
							// calc expiry	- issue #1226
							// membership extend functionality broken if we try to extend the same day so removed && $last_pay_date != date('Y-m-d', $time) check	
							if ( ! empty($member->expire_date) ) {
								// expiry
								$expire_date_ts2 = strtotime($member->expire_date);
								// valid
								// valid && expiry date is greater than today
								if ($expire_date_ts2 > 0 && $expire_date_ts2 > $expire_date_ts) {
									// set it for next calc
									$expire_date_ts = $expire_date_ts2;
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
					if(isset($trial_on) && $trial_on == 1 ) {
						// Do it only once
						if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
							$expire_date_ts = strtotime('+' . $trial_duration . ' ' . $duration_exprs[$trial_duration_type], $expire_date_ts);								
						}					
					}else {
						// recalc - issue #1068
						$expire_date_ts = strtotime('+' . $member->duration . ' ' . $duration_exprs[$member->duration_type], $expire_date_ts);										
					}
					// formatted
					$expire_date = date('Y-m-d', $expire_date_ts);		
					// date extended				
					if (!$member->expire_date || $expire_date_ts > strtotime($member->expire_date)) {
						$member->expire_date = $expire_date;			
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
				if($member->active_num_cycles != 1){
					// check			
					if(!isset($member->rebilled)){
						$member->rebilled = 1;
					}else if((int)$member->rebilled < (int)$member->active_num_cycles) { // 100 
						// rebill
						$member->rebilled = ((int)$member->rebilled + 1);	
					}	
				}
				
				//clear cancellation status if already cancelled:
				if(isset($member->status_reset_on)) unset($member->status_reset_on);
				if(isset($member->status_reset_as)) unset($member->status_reset_as);				
				
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

			case 'declined':
			case 'refunded':
			case 'denied':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
				// error
				$errors[] = $member->status_str;
			break;
			
			case 'pending':
			case 'held for review':
				$new_status = MGM_STATUS_PENDING;
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $subscription_status);				
				// error
				$errors[] = $member->status_str;
			break;

			default:
				$new_status = MGM_STATUS_ERROR;
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $subscription_status);
				// error
				$errors[] = $member->status_str;
			break;
		}
		
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;			
				
		// whether to acknowledge the user by email - This should happen only once
		$acknowledge_user = $this->is_payment_email_sent($alt_tran_id);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $alt_tran_id);
		
		// update member
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {// issue #1227
			// hide old content
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
		// error condition redirect
		if(count($errors)>0){			
			mgm_redirect(add_query_arg(array('status'=>'error', 'errors'=>implode('|', $errors)), $this->_get_thankyou_url()));
		}
	}
	
	// cancel membership
	function _cancel_membership($user_id, $no_redirect=false){
		// system	
		$system_obj  = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge     = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne    = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		// multiple membership level update:					
		$multiple_update = false;	
		// check
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$multiple_update = true;
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	
		}
			
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
				
		// reset payment info
		$member->payment_info->txn_type = 'subscription_cancel';
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');	
							
		// if trial on 
		if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			
			// if cancel data is before trial end, set cancel on trial expire_date
			$trial_expire_date = strtotime('+' . $subs_pack['trial_duration'] . ' ' . $duration_exprs[$subs_pack['trial_duration_type']], $member->join_date);
			
			// if lower
			if(time() < $trial_expire_date){
				$expire_date = date('Y-m-d',$trial_expire_date);
			}
		}
		// old status
		$old_status = $member->status;			
		// transaction_id
		$trans_id = $member->transaction_id;	
		// if today 
		if( $expire_date == date('Y-m-d') || 'instant' == $this->setting['subs_cancel'] ){
			// status
			$new_status          = MGM_STATUS_CANCELLED;
			$new_status_str      = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;					
			$member->expire_date = date('Y-m-d');

			// unset canceled subscr_id
			if( isset($member->payment_info->subscr_id) ){
				unset($member->payment_info->subscr_id);
			} 
				
			// reassign expiry membership pack if exists: issue#: 535			
			$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);					
		}else{
			// date
			$date_format = mgm_get_date_format('date_format');
			// status
			$new_status     = MGM_STATUS_AWAITING_CANCEL;	
			$new_status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, strtotime($expire_date)));
			// set		
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;		
			// set reset date
			$member->status_reset_on = $expire_date;
			$member->status_reset_as = MGM_STATUS_CANCELLED;
		}
						
		// multiple memberhip level update:	
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
			//check
			if($old_status != $new_status) {
				// notify user
				mgm_notify_user_membership_cancellation($blogname, $user, $member, $new_status, $system_obj);	
			}
		}
		// notify admin
		if ( ! $dge ) {
			// notify admin	
			mgm_notify_admin_membership_cancellation($blogname, $user, $member, $new_status);
		}
		
		// key
		$canceled_subs_key = $this->get_subscription_cancel_key( $member );
		// log
		//mgm_log( 'canceled_subs_key: ' . $canceled_subs_key, $this->get_context(__FUNCTION__));

		// only run in cron, other wise too many tracking will be added
		// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
		if( ! mgm_get_user_option( $canceled_subs_key, $user_id ) ){
			
			// after cancellation hook
			do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));

			// update
			update_user_option($user_id, $canceled_subs_key, time(), true);		
		}	
		
		/*// message
		$lformat = mgm_get_date_format('date_format_long');
		$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
		                  ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));		
		//issue #1521
		if($is_admin){
			$url = add_query_arg(array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message)), admin_url('user-edit.php'));
			mgm_redirect($url);
		}		
		// redirect 		
		mgm_redirect(mgm_get_custom_url('membership_details', false,array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message))));*/

		// redirect
		if( ! $no_redirect ){
			// message
			$lformat = mgm_get_date_format('date_format_long');
			$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
			                  ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));		
			//issue #1521
			if($is_admin){
				$params = array('user_id'=>$user_id,'unsubscribe_errors'=>urlencode($message));
				$url = add_query_arg($url, admin_url('user-edit.php'));
				
			}else{
				$params = array('unsubscribed'=>'true','unsubscribe_errors'=>urlencode($message));
				$url = mgm_get_custom_url('membership_details', false, $params);
			}		
			
			// redirect
			mgm_redirect($url); exit;
		}	
	}
	
	// reactivate membership
	function _reactivate_membership($user_id, $subscr_id){
		// system	
		$system_obj  = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge     = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne    = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));	
		//issue #1521
		$is_admin = (is_super_admin()) ? true : false;		
		// find user
		$user = get_userdata($user_id);
		$member = mgm_get_member($user_id);
		
		// multiple membership level update:					
		$multiple_update = false;	
		// check
		if(isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']){
			$multiple_update = true;
			$member = mgm_get_member_another_purchase($user_id, $_POST['membership_type']);	
		}
			
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}
				
		// reset payment info
		$member->payment_info->txn_type = 'subscription_reactivate';
		
		// types
		$duration_exprs = $s_packs->get_duration_exprs();
						
		// default expire date				
		$expire_date = $member->expire_date;
		// if lifetime:
		if($member->duration_type == 'l') $expire_date = date('Y-m-d');	
							
		// if trial on 
		if ($subs_pack['trial_on'] && isset($duration_exprs[$subs_pack['trial_duration_type']])) {			
			// if cancel data is before trial end, set cancel on trial expire_date
			$trial_expire_date = strtotime('+' . $subs_pack['trial_duration'] . ' ' . $duration_exprs[$subs_pack['trial_duration_type']], $member->join_date);
			
			// if lower
			if(time() < $trial_expire_date){
				$expire_date = date('Y-m-d',$trial_expire_date);
			}
		}
		// old status
		$old_status = $member->status;			
		// transaction_id
		$trans_id = $member->transaction_id;	
		
		// status
		$new_status          = MGM_STATUS_ACTIVE;
		$new_status_str      = __('Subscription reactivated','mgm');
		// set
		$member->status      = $new_status;
		$member->status_str  = $new_status_str;					
		$member->expire_date = date('Y-m-d');

		// set subscr_id
		//if( ! isset($member->payment_info->subscr_id) ){
		if( ! isset($member->payment_info->subscr_id) || 
			( isset($member->payment_info->subscr_id) && $member->payment_info->subscr_id != $subscr_id ) ){	
			$member->payment_info->subscr_id = $subscr_id;
		} 

		unset($member->status_reset_on, $member->status_reset_as);
			
		// reassign expiry membership pack if exists: issue#: 535			
		$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);					
						
		// multiple memberhip level update:	
		if($multiple_update) {			
			mgm_save_another_membership_fields($member, $user_id);
		}else{ 			
			$member->save();	 					
		}	
		
		// transaction status
		mgm_update_transaction_status($trans_id, $new_status, $new_status_str);
		
		// status change event
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'member_reactivated', $member->pack_id);

		// send email notification to client
		$blogname = get_option('blogname');		
									  
		// notify user
		if( ! $dpne ) {
			//check
			if($old_status != $new_status) {
				// notify user
				//mgm_notify_user_membership_cancellation($blogname, $user, $member, $new_status, $system_obj);	
			}
		}
		// notify admin
		if ( ! $dge ) {
			// notify admin	
			//mgm_notify_admin_membership_cancellation($blogname, $user, $member, $new_status);
		}
		
		/*// key
		$canceled_subs_key = $this->get_subscription_cancel_key( $member );
		// only run in cron, other wise too many tracking will be added
		// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
		if( mgm_get_user_option( $canceled_subs_key, $user_id ) === False ){
			
			// after cancellation hook
			do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));

			// update
			update_user_option($user_id, $canceled_subs_key, time(), true);		
		}*/	
		
		// message
		$lformat = mgm_get_date_format('date_format_long');
		$message = __("You have successfully reactivated your subscription.", "mgm");		
		//issue #1521
		if($is_admin){
			$url = add_query_arg(array('user_id'=>$user_id,'reactivate_errors'=>urlencode($message)), admin_url('user-edit.php'));
			mgm_redirect($url);
		}		
		// redirect 		
		mgm_redirect(mgm_get_custom_url('membership_details', false, array('reactivated'=>'true','reactivate_errors'=>urlencode($message))));
	}

	/**
	 * Cancel Recurring Subscription
	 * This is not a private function
	 * @param int/string $trans_ref	
	 * @param int $user_id	
	 * @param int/string $subscr_id	
	 * @return boolean
	 */	
	function cancel_recurring_subscription($trans_ref = null, $user_id = null, $subscr_id = null, $pack_id = null, $cust_id=null) {
		//if coming form process return after a subscription payment
		if(!empty($trans_ref)) {
			// tran
			$transdata = $this->_get_transaction_passthrough($trans_ref);
			// type
			if($transdata['payment_type'] != 'subscription_purchase'){				
				return false;				
			}
			
			// user			
			$user_id = $transdata['user_id'];
			
			// another				
			if(isset($transdata['is_another_membership_purchase']) && $transdata['is_another_membership_purchase'] == 'Y') {
				$member = mgm_get_member_another_purchase($user_id, $transdata['membership_type']);			
			}else {
				$member = mgm_get_member($user_id);			
			}
			
			// fix
			$member = $this->_fix_stripe_customer_data($member);
			// check
			$cust_id = $member->payment_info->cust_id; 

			// update
			if(isset($member->payment_info->module) && !empty($member->payment_info->module)) {
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
					// mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code);
					// return
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}				
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}
		
		//only for subscription_purchase		
		if($subscr_id) {				
			// call			
			if ( $this->response = $this->_get_api_notify('cancel_subscription', null, array('customer_id'=>$cust_id, 'subscription_id'=>$subscr_id) ) ){
				// log
				mgm_log('cancel_subscription response: '. mgm_pr($this->response, true), $this->get_context( __FUNCTION__ ));
				// check		
				if( (isset($this->response->status) && $this->response->status == 'canceled') 
					|| (isset($this->response->cancel_at_period_end) && (int)$this->response->cancel_at_period_end == 1) ){	
					return true;
				}
			}
		}elseif($subscr_id === 0) {			
			//send email to admin if subscription Id is absent		
			$system_obj = mgm_get_class('system');			
			$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
			//send email only if setting enabled
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

		// log_context
		$log_context = $this->get_context( __FUNCTION__ );
		// fix
		$member = $this->_fix_stripe_customer_data($member);
		// check
		// mgm_log($member, $log_context);
		// check	
		if ( isset($member->payment_info->cust_id) && !empty($member->payment_info->cust_id) && 
			 isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ) {					
			// id
			$cust_id = $member->payment_info->cust_id;	
			// id
			$subscr_id = $member->payment_info->subscr_id;	
			// check
			if( ! preg_match('/^sub_/', $subscr_id) ){
				// check
				mgm_log($subscr_id.' is not a subscription', $log_context);
				// return
				return false;
			}		
			// check		
			if ( $this->response = $this->_get_api_notify('get_subscription', null, array('customer_id'=>$cust_id,'subscription_id'=>$subscr_id)) ) {
				// log
				mgm_log($this->response, $log_context);
				// old status
				$old_status = $member->status;	
				// check
				$subscription_status = 'not found';
				if( isset($this->response->status) && ! empty($this->response->status) ){
					$subscription_status = strtolower($this->response->status);
				}
				
				// log
				mgm_log('subscription_status: '.$subscription_status, $log_context);

				// period_end
				if( 'period_end' == $this->setting['subs_cancel'] ){
					if( isset($this->response->cancel_at_period_end) && (int)$this->response->cancel_at_period_end == 1 ){
						$subscription_status = 'awaiting_cancel';
					}
				}

				// log
				mgm_log('subscription_status2: '.$subscription_status, $log_context);

				// set status
				switch($subscription_status){
					case 'active':
					case 'trialing':
						// set new status
						$member->status = $new_status = MGM_STATUS_ACTIVE;
						// status string
						$member->status_str = __('Last payment cycle processed successfully','mgm');
						// start date
						$current_period_start = $this->response->current_period_start;
						// trial fix
						if('trialing' == $subscription_status){
							// expire
							if(empty($member->expire_date)){
								$member->expire_date = date('Y-m-d', $this->response->trial_end);
							}
							// start date
							$current_period_start = $this->response->trial_start;
							// status string
							$member->status_str = __('Last trial cycle processed successfully','mgm');
						}
						// last pay date
						$member->last_pay_date = (isset($current_period_start) && !empty($current_period_start)) ? date('Y-m-d', $current_period_start) : date('Y-m-d');	
						// expire date
						if(isset($current_period_start) && !empty($current_period_start) && !empty($member->expire_date)){													
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
					case 'canceled':
					case 'awaiting_cancel':
						// now
						$timestamp = time();

						// awaiting
						if( 'awaiting_cancel' == $subscription_status ){
							$expire_at = $this->response->current_period_end;
							$canceled_at = $this->response->canceled_at;
						}else{
							if( isset($this->response->canceled_at) ){
								$expire_at = $canceled_at = $this->response->canceled_at;
							}else{
								$expire_at = $canceled_at = ! empty($member->expire_date) ? strtotime($member->expire_date) : $timestamp;
							}
						}

						// if expire date in future, let as awaiting
						if( $expire_at > $timestamp )	{	
							// date format
							$date_format = mgm_get_date_format('date_format');				
							// status				
							$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;	
							// status string	
							$member->status_str = sprintf(__('Subscription awaiting cancellation on %s','mgm'), date($date_format, $expire_at) );							
							// set reset date				
							$member->status_reset_on = date('Y-m-d', $expire_at);
							// reset as
							$member->status_reset_as = MGM_STATUS_CANCELLED;
						}else{
						// set cancelled
							// status			
							$member->status = $new_status = MGM_STATUS_CANCELLED;
							// set reset date				
							$member->cancel_date = $member->expire_date = date('Y-m-d', $canceled_at);
							// status string
							$member->status_str = __('Last payment cycle cancelled','mgm');	

							// unset canceled subscr_id
							if( isset($member->payment_info->subscr_id) ){
								unset($member->payment_info->subscr_id);
							} 
						}
						// save
						$member->save();

						// key
						$canceled_subs_key = $this->get_subscription_cancel_key( $member );
						// log
						//mgm_log( 'canceled_subs_key: '.$canceled_subs_key, $this->get_context(__FUNCTION__));
						// only run in cron, other wise too many tracking will be added
						if( ! mgm_get_user_option( $canceled_subs_key, $user_id ) ){
							
							// after cancellation hook
							do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));

							// update
							update_user_option($user_id, $canceled_subs_key, time(), true);		
						}
					break;					
					case 'suspended':
					case 'terminated':
					case 'expired':		
					case 'error':
					case 'not found':		
					case 'past_due':
					default:						
						// set new statis
						$member->status = $new_status = MGM_STATUS_EXPIRED;
						// status string
						$member->status_str = sprintf(__('Last payment cycle expired, subscription: %s.','mgm'), $subscription_status );
						/*// unset canceled subscr_id
						if( isset($member->payment_info->subscr_id) ){
							unset($member->payment_info->subscr_id);
						} */
						// save
						$member->save();						
					break;
				}					
				// action
				if( isset($new_status) ){
					// user status change
					do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);	
					// rebill status change
					do_action('mgm_rebill_status_change', $user_id, $new_status, $old_status, 'query');// query or notify
				}		
				// return as a successful rebill
				return true;
			}			
		}
		// return
		return false;//default to false to skip normal modules
	}
				
	// default setting
	function _default_setting(){
		// authorize.net specific
		$this->setting['secretkey']  = '';
		$this->setting['publishable_key'] = '';	
		$this->setting['currency']  = mgm_get_setting('currency');
		$this->setting['subs_cancel'] = 'instant';// instant/period_end  
		$this->setting['webhook_secret'] = '';
		$this->setting['stripe_version'] = '2017-08-15';
		$this->setting['rebill_status_query'] = 'enabled';
		// purchase price
		if(in_array('buypost', $this->supported_buttons)){
			$this->setting['purchase_price']  = 4.00;		
		}
		$this->setting['rebill_check_delay'] = 0;						
		// callback messages				
		$this->_setup_callback_messages();
		// callback urls
		$this->_setup_callback_urls();	
	}
	
	// log transaction
	function _log_transaction(){
		// check
		if($this->_is_transaction($_POST['custom'])){	
			// tran id
			$tran_id = (int)$_POST['custom'];			
			// return data				
			if( isset($this->response->object) ){
				$option_name = $this->module.'_'.strtolower($this->response->object).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($this->response)));
			
			// options 
			$options = array('object','id','created','invoice');
			// loop
			foreach($options as $option){
				if( isset($this->response->{$option}) ){
					// value
					$option_value = $this->response->{$option};
					// id
					if( $option == 'id' ){ 
						$option = $this->response->object.'_'.$option;// customer_id, charge_id ??
						// $option = $this->response->object_id;
					}	
					
					// add
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$option_value));
				}
			}
			
			// return transaction id
			return $tran_id;	
		}	
		// error
		return false;	
	}
	
	/**
	 * setup endpoints
	 */ 
	function _setup_endpoints($end_points = array()){
		// define defaults
		$end_points_default = array('api' => 'https://api.stripe.com/v1');	
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		// set
		$this->_set_endpoints($end_points);
	}
	
	/**
	 * set 
	 */ 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'first_name','last_name'=>'last_name','address'=>array('address_line1','address_line2'),
		                 'city'=>'city','state'=>'address_state','zip'=>'address_zip','country'=>'address_country',
						 'phone'=>'phone');
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	/**
	 * filter
	 */ 
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
	
	/**
	 * verify callback 
	 */ 
	function _verify_callback($signature_verify=false){	
		// no check when internally called
		if( $this->is_webhook_called_by('self') ){
			return true;
		}

		// log
		$log_context = $this->get_context( __FUNCTION__ );

		// only when verify needed
		if( $signature_verify ){
			// webhook_secret
			if( $endpoint_secret = $this->setting['webhook_secret'] ){

				if( isset($_POST['notify_type']) ){
					mgm_log( 'notify_type: '.$_POST['notify_type'], $log_context);
				}

				mgm_log( 'endpoint_secret: '.$endpoint_secret, $log_context);
				
				if( isset($_SERVER["HTTP_STRIPE_SIGNATURE"]) && ! empty($_SERVER["HTTP_STRIPE_SIGNATURE"]) ){
					$sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
					$payload = @file_get_contents("php://input");

					$sig_headers = explode(',', $sig_header);
					
					mgm_log( 'sig_header: '.$sig_header, $log_context);

					if( !empty($sig_headers) ){
						$sig_values = array();
						foreach( $sig_headers as $sig ){
							list($key, $value) = explode('=', $sig);
							$sig_values[$key] = $value;
						}

						mgm_log( 'sig_values: '.mgm_pr($sig_values, true), $log_context);

						$timestamp = time();

						$signed_payload = $timestamp .'.'. $payload;

						$digest = hash_hmac('sha256', $signed_payload, $endpoint_secret);

						mgm_log( 'digest: '. $digest, $log_context);

						$match = false;
						foreach( $sig_values as $key => $value ){
							if( 'v1' == $key ){
								if( $value == $digest ){
									$match = true; break;

									mgm_log( 'sig value: '. $value, $log_context);
								}
							}
						}

						mgm_log( 'match: '. $match, $log_context);

						if( $match == true ){
							if( isset($sig_values['t']) ){
								$time = new DateTime($sig_values['t']);
								$timenow = new DateTime($timestamp);

								$interval = $timenow->diff($time);

								mgm_log( 'time diff: '. mgm_pr($interval, true), $log_context);
							}else{
								mgm_log( 'time diff: no timestamp');
							}
						}	
					}	
				}
			}
		}	

		// keep it simple		
		return (isset($_POST['custom']) && !empty($_POST['custom'])) ? true : false;
	}
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////

	/**
	 * filter postdata
	 */ 
	function _filter_postdata($action, $post_data, $join=false){
		// init
		$filtered = array();		
		// action
		switch( $action ){
			case 'create_customer':
				// desc
				$filtered['description'] = $post_data['description'];
				$filtered['plan'] = $post_data['plan'];
				// email
				if( isset($post_data['email']) && ! empty($post_data['email']) ){
					$filtered['email'] = $post_data['email']; 
				}	
				// coupon
				if( isset($post_data['coupon']) && ! empty($post_data['coupon']) ){
					$filtered['coupon'] = $post_data['coupon']; 
				}
			break;
			case 'create_charge':
				// desc
				$filtered['description'] = $post_data['description'];
				$filtered['amount'] = $post_data['amount'];
				$filtered['currency'] = $post_data['currency'];
				// receipt_email
				if( isset($post_data['email']) && ! empty($post_data['email']) ){
					$filtered['receipt_email'] = $post_data['email'];
				}				
			break;
			case 'create_customer_and_charge':
				// desc
				$filtered['description'] = $post_data['description'];				
				$filtered['amount'] = $post_data['amount'];
				$filtered['currency'] = $post_data['currency'];
				// email
				if( isset($post_data['email']) && ! empty($post_data['email']) ){
					$filtered['email'] = $post_data['email'];
				}	
			break;
			case 'use_customer_and_charge':
				// desc
				$filtered['description'] = $post_data['description'];
				$filtered['amount'] = $post_data['amount'];
				$filtered['currency'] = $post_data['currency'];
			break;
			case 'use_customer_and_create_subscription':// multiple subscription purchase
				// desc
				/*$filtered['description'] = $post_data['description'];
				$filtered['amount'] = $post_data['amount'];
				$filtered['currency'] = $post_data['currency'];*/
				
				// plan
				$filtered['plan'] = $post_data['plan'];
				// coupon
				if( isset($post_data['coupon']) && ! empty($post_data['coupon']) ){
					$filtered['coupon'] = $post_data['coupon'];
				}				
			break;
			case 'create_subscription':
			break;
			case 'use_customer_and_change_subscription':
				// plan
				$filtered['plan'] = $post_data['plan'];
				// coupon
				if( isset($post_data['coupon']) && ! empty($post_data['coupon']) ){
					$filtered['coupon'] = $post_data['coupon']; 
				}
			break;
		}
		
		if( ! preg_match('/charge/', $action) ){		
			// quantity
			if( isset( $post_data['quantity']) ){		
				$filtered['quantity'] = $post_data['quantity'];
			}	
	
			// trial end
			if( isset( $post_data['trial_end']) ){		
				$filtered['trial_end'] = $post_data['trial_end'];
			}
		}
		// card
		if( isset($post_data['mgm_card_number']) && !empty($post_data['mgm_card_number']) ){
			// set
			$filtered['card']['number']    = $post_data['mgm_card_number'];
			$filtered['card']['exp_month'] = $post_data['mgm_card_expiry_month'];
			$filtered['card']['exp_year']  = $post_data['mgm_card_expiry_year'];
			$filtered['card']['cvc']       = $post_data['mgm_card_code'];		
			$filtered['card']['name']      = $post_data['mgm_card_holder_name'];	

			// street
			if(isset($post_data['address_line1'])){
				$filtered['card']['address_line1'] = $post_data['address_line1'];
			}
			if(isset($post_data['address_line2'])){
				$filtered['card']['address_line2'] = $post_data['address_line2'];
			}
			// zip
			if(isset($post_data['address_zip'])){
				$filtered['card']['address_zip'] = $post_data['address_zip'];
			}
			// state
			if(isset($post_data['address_state'])){
				$filtered['card']['address_state'] = $post_data['address_state'];
			}
			// country
			if(isset($post_data['address_country'])){
				$filtered['card']['address_country'] = $post_data['address_country'];
			}
		}

		// send filtered
		return ($join) ? mgm_http_build_query($filtered) : $filtered;
	}
	
	/**
	 * fetch remote data via http POST
	 *
	 * @param string $url
	 * @param array $data to post
	 * @param array $options
	 * @param bool $on_error_message (CONNECT_ERROR|WP_ERROR)
	 * @return string $response
	 */
	/*function _remote_post($request_url, $data=array(), $options=array(), $on_error_message='CONNECT_ERROR'){
		// args
		$args = array('body' => $data);
		
		// merge		
		if(is_array($options)) $args = array_merge($args, $options);	
		
		// request
		$request = wp_remote_post($request_url, $args);

		// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often
		if ( is_wp_error( $request ) )  
			return $request->get_error_message();  
			
		// return
		return wp_remote_retrieve_body( $request ); 
	}*/

	/**
	 * remore delete
	 */ 
	/*function _remote_delete($request_url, $data=null, $options = array()) {
		// fetch
		$objFetchSite = _wp_http_get_object();
		
		$defaults = array('method' => 'DELETE');
		$r = wp_parse_args( $options, $defaults );

		// request
		$request = $objFetchSite->request($request_url, $r);
		
		// validate, 200 and 302, WP permalink cacuses 302 Found/Temp Redirect often
		if ( is_wp_error( $request ) )  
			return $request->get_error_message();  
		
		// return
		return wp_remote_retrieve_body( $request ); 
	}*/

	/**
	 * get notify
	 */ 
	function _get_notify(){
		// get
		if( $notify_json = @file_get_contents("php://input") ){
			return $notify = json_decode($notify_json);	
		}
		return false;		
	}

	/**
	 * create post value from notify object
	 */ 
	function _notify2post(){
		// parse
		if( $notify_response = $this->_get_notify() ){
			// type
			$type = 'general';
			if( isset($notify_response->type) ){
				$type = str_replace('.','_',$notify_response->type);
			}
			// log
			mgm_log($notify_response, $type . '_' . $this->get_context( __FUNCTION__ ));
			// set notify type
			$_POST['notify_type'] = $notify_response->type;
			// canceled event
			if( $notify_response->type == 'customer.subscription.deleted'){
				// get customer
				$customer_id = $notify_response->data->object->customer;
				// get transaction by customer
				if( $tran = mgm_get_transaction_by_option('stripe_customer_id', $customer_id) ){
					// check
					$process_cancellation = true;
					// check
					if( $member = mgm_get_member($tran['data']['user_id']) ){
						// match customer id and subscription
						if ( isset($member->payment_info->cust_id) && !empty($member->payment_info->cust_id) && 
			 				 isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id) ) {	
			 				
			 				// active
			 				$active_cust_id = $member->payment_info->cust_id;
			 				$active_subscr_id = $member->payment_info->subscr_id;

			 				// check
			 				if( isset($notify_response->data->object->id) && isset($notify_response->data->object->customer) ){
			 					// cancelling
			 					$cancel_cust_id = $notify_response->data->object->customer;
			 					$cancel_subscr_id = $notify_response->data->object->id;

			 					// exact
			 					if( $cancel_cust_id == $active_cust_id && $cancel_subscr_id == $active_subscr_id ){
			 						$process_cancellation = true;
			 					}else{
			 						$process_cancellation = false;
			 					}
			 				}			 				
			 			}
			 				
			 			// proces only when not canceled
						if( $process_cancellation && ! in_array($member->status, array( MGM_STATUS_AWAITING_CANCEL, MGM_STATUS_CANCELLED)) ){
							// set post fields
							$_POST['custom'] = $tran['id']; 
							// set notify
							$this->response = $notify_response;
							// log
							mgm_log('Cancelling from Notify Post' . mgm_pr($tran, true), $this->get_context( __FUNCTION__ ));
						}						
					}					
				}
			}

			// updated event
			if( $notify_response->type == 'customer.subscription.updated'){
				// get customer
				$customer_id = $notify_response->data->object->customer;
				// get transaction by customer
				if( $tran = mgm_get_transaction_by_option('stripe_customer_id', $customer_id) ){
					// set post fields
					$_POST['custom'] = $tran['id']; 
					// set notify
					$this->response = $notify_response->data->object;
					// log
					mgm_log('Updating from Notify Post' . mgm_pr($tran, true), $this->get_context( __FUNCTION__ ));
				}
			}

			// updated event
			if( $notify_response->type == 'customer.deleted'){
				// get customer
				$customer_id = $notify_response->data->object->customer;
				// get transaction by customer
				if( $tran = mgm_get_transaction_by_option('stripe_customer_id', $customer_id) ){
					// set post fields
					$_POST['custom'] = $tran['id']; 
					// set notify
					$this->response = $notify_response->data->object;
					// log
					mgm_log('Updating from Notify Post' . mgm_pr($tran, true), $this->get_context( __FUNCTION__ ));
				}
			}
		}
	}

	/**
	 * api notify
	 */  
	function _get_api_notify($action, $post_data=null, $input_data=null){
		// log context
		$log_context = $this->get_context( __FUNCTION__ );

		/*define('DEBUG_REMOTE_GET', true);
		define('DEBUG_REMOTE_POST', true);
		define('DEBUG_REMOTE_DELETE', true);*/	

		// input data
		if( is_array($input_data) ) extract($input_data);
		// headers	
		$http_headers = array(
			'Authorization' => 'Basic ' . base64_encode( $this->setting['secretkey'] . ':' ),
			// 'Stripe-Version' => $this->setting['stripe_version']
		);
		// just in case	
		// base 
		$api_url = trailingslashit( $this->_get_endpoint('api') );
		// log
		mgm_log( 'action: ' . $action . ' post_data: ' . mgm_pr( mgm_filter_cc_details($post_data), true), $log_context);
		// action
		switch(  $action ) {
			case 'create_customer':
			case 'create_charge':
				// endpoint
				$api_url .= ($action == 'create_customer') ? 'customers' : 'charges';
				// options
				$options = array('headers'=>$http_headers,'timeout'=>30,'sslverify'=>false);
				// post
				$http_response = mgm_remote_post($api_url, $post_data, $options, false, array());	
				// log
				mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
				// return
				if( $notify = json_decode($http_response) ){
					// check object
					if( 'create_customer' == $action && ! isset($notify->subscription) ){
						// new after https://stripe.com/docs/upgrades#2014-01-31
						if( isset($notify->subscriptions->data) ){
							// loop
							foreach( $notify->subscriptions->data as $subscription ){
								$notify->subscription = $subscription; break;
							}
						}	
					}
					
					// return
					return $notify;
				}
			break;				
			case 'create_customer_and_charge':
				// needed
				$post_data_customer = array();
				foreach(array('description','email','card') as $f){
					$post_data_customer[$f] = $post_data[$f];
				}
				
				// create customer
				if( $customer = $this->_get_api_notify('create_customer', $post_data_customer) ){
					// check
					if( isset($customer->id) ){
						// needed
						$post_data_charge = array('customer'=>$customer->id);
						foreach(array('description','amount','currency') as $f){
							$post_data_charge[$f] = $post_data[$f];
						}
						
						// charge
						if( $charge = $this->_get_api_notify('create_charge', $post_data_charge) ){
							// content purchase
							if( isset($charge_type) && 'purchase_content' == $charge_type ){
								return $charge;
							}

							// check
							if( $charge->paid == true ){
								// create a dummy node
								$customer->subscription = new stdClass;
								// mock
								$customer->subscription->id = $charge->id;
								$customer->subscription->object = 'subscription';								 
					            $customer->subscription->start = $charge->created;
					            $customer->subscription->status = 'active';
					            $customer->subscription->customer = $customer->id;
					            $customer->subscription->cancel_at_period_end = '';
					            $customer->subscription->current_period_start = $charge->created;
					            $customer->subscription->current_period_end = '';
					            $customer->subscription->ended_at = '';
					            $customer->subscription->trial_start = '';
					            $customer->subscription->trial_end = '';
					            $customer->subscription->canceled_at = '';
					            $customer->subscription->quantity = 1;
					            $customer->subscription->application_fee_percent = '';
					            $customer->subscription->discount = '';					            
								// log
								mgm_log( 'action: ' . $action  .' customer: ' . mgm_pr($customer, true), $log_context);
								// return
								return $customer;							
							}
						}
					}
				}				
			break;	
			case 'use_customer_and_charge':
				// check
				if( ! isset($customer_id) ){
					if( isset($post_data['stripe_customer_id']) && !empty($post_data['stripe_customer_id']) ){
						$customer_id = $post_data['stripe_customer_id'];
					}
				}	
				// check
				if( $customer_id ){
					// set customer
					$post_data['customer'] = $customer_id;	
					//check
					if( isset($post_data['card']) ){
						unset($post_data['card']);
					}
					// charge
					if( $charge = $this->_get_api_notify('create_charge', $post_data, $input_data) ){
						// content purchase
						if( isset($charge_type) && 'purchase_content' == $charge_type ){
							return $charge;
						}

						// subscription
						if( $charge->paid == true ){
							// check
							if( $customer = $this->_get_api_notify('get_customer', null, array('customer_id'=>$customer_id)) ){
								// create a dummy node
								$customer->subscription = new stdClass;
								// mock
								$customer->subscription->id = $charge->id;
								$customer->subscription->object = 'subscription';								 
					            $customer->subscription->start = $charge->created;
					            $customer->subscription->status = 'active';
					            $customer->subscription->customer = $customer->id;
					            $customer->subscription->cancel_at_period_end = '';
					            $customer->subscription->current_period_start = $charge->created;
					            $customer->subscription->current_period_end = '';
					            $customer->subscription->ended_at = '';
					            $customer->subscription->trial_start = '';
					            $customer->subscription->trial_end = '';
					            $customer->subscription->canceled_at = '';
					            $customer->subscription->quantity = 1;
					            $customer->subscription->application_fee_percent = '';
					            $customer->subscription->discount = '';					            
								
								// return
								return $customer;							
							}
						}
					}	
				}	
			break;
			case 'use_customer_and_create_subscription':
				// check
				if( isset($post_data['stripe_customer_id']) && !empty($post_data['stripe_customer_id']) ){
					// verify customer
					if( $customer = $this->_get_api_notify('get_customer', null,  array('customer_id'=>$post_data['stripe_customer_id'])) ){
						// charge
						if( $subscription = $this->_get_api_notify('create_subscription', $post_data, array('customer_id'=>$post_data['stripe_customer_id']) ) ){
							return $subscription;
						}
					}
				}	
			break;
			case 'create_subscription':
				// @todo for purchase another level
				// @see https://stripe.com/docs/api#create_subscription
				// check	
				if( ! empty($customer_id)  ){
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions', $customer_id) ;
					// options
					$options = array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false);
					// return
					$http_response = mgm_remote_post($api_url, $post_data, $options, false, array());
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// log
						// mgm_log($notify, $this->module . '_' . $action . __FUNCTION__);
						// return
						return $notify;
					}
				}	
			break;
			case 'get_subscription':
				// check	
				if( ! empty($customer_id) && ! empty($subscription_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions/%s', $customer_id, $subscription_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false), false, array(200, 302, 404));
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;	
			case 'get_subscriptions':
				// check	
				if( ! empty($customer_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions', $customer_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false),false);
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;
			case 'get_charge':
				// check	
				if( ! empty($charge_id) ){
					// endpoint
					$api_url .= sprintf('charges/%s', $charge_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false),false);
					
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;
			case 'use_customer_and_change_subscription':
				// @see https://stripe.com/docs/api/curl#update_subscription
				// new after https://stripe.com/docs/upgrades#2014-01-31
				// check	
				if( ! empty($customer_id) && ! empty($subscription_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions/%s', $customer_id, $subscription_id) ;
					// options
					$options = array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false);
					// return
					$http_response = mgm_remote_post($api_url, $post_data, $options, false, array());
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' post_date: '. mgm_pr(mgm_filter_cc_details($post_data),true) . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				//issue #2309
				}elseif ( ! empty($customer_id) && (empty($subscription_id) || !isset($subscription_id))){				
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions', $customer_id) ;
					// options
					$options = array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false);
					// return
					$http_response = mgm_remote_post($api_url, $post_data, $options, false, array());
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// log
						//mgm_log($notify, $this->module . '_' . $action . __FUNCTION__);
						// return
						return $notify;
					}
				}
			break;				
			case 'cancel_subscription':
				// check	
				if( ! empty($customer_id) && ! empty($subscription_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions/%s', $customer_id, $subscription_id) ;
					// new after https://stripe.com/docs/upgrades#2014-01-31

					$data = null;

					if( strtotime($this->setting['stripe_version']) >= strtotime('2017-06-05') ){
						if( 'period_end' == $this->setting['subs_cancel'] ){
							$data = array('at_period_end'=>'true');
						}	
					}

					// return
					$http_response = mgm_remote_delete($api_url, $data, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false));
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;	
			case 'reactivate_subscription':
				// check	
				if( ! empty($customer_id) && ! empty($subscription_id) ){
					// get subscription
					$subscription = $this->_get_api_notify('get_subscription', null, array('customer_id'=>$customer_id,'subscription_id'=>$subscription_id));

					mgm_log( 'subscription: '. mgm_pr($subscription, true), $log_context );

					// endpoint
					$api_url .= sprintf('customers/%s/subscriptions/%s', $customer_id, $subscription_id) ;
					// new after https://stripe.com/docs/upgrades#2014-01-31

					$data = null;

					if( strtotime($this->setting['stripe_version']) >= strtotime('2017-06-05') ){
						if( 'period_end' == $this->setting['subs_cancel'] ){
							$data = array(
								'items[0][id]'=>$subscription->items->data[0]->id,
								'items[0][plan]'=>$subscription->items->data[0]->plan->id
							);
						}	
					}

					// log
					mgm_log('action: ' . $action . ' data: ' . mgm_pr($data, true), $log_context);

					// options
					$options = array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false);

					// return
					$http_response = mgm_remote_post($api_url, $data, $options, false, array());
					
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;	//		
			case 'get_customer':
			// check
				if( ! empty($customer_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s', $customer_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false),false);
					// log
					mgm_log('action: ' . $action . ' api_url: ' . $api_url . ' http_response: ' . $http_response, $log_context);
					// return
					if( $notify = json_decode($http_response) ){
						// check object
						if( ! isset($notify->subscription) ){
							// new after https://stripe.com/docs/upgrades#2014-01-31
							if( isset($notify->subscriptions->data) ){
								// loop
								foreach( $notify->subscriptions->data as $subscription ){
									$notify->subscription = $subscription; break;
								}
							}	
						}
						// return
						return $notify;
					}
				}	
			break;
			case 'get_invoice':
				// check
				if( ! empty($invoice_id) ){
					// endpoint
					$api_url .= sprintf('invoices/%s', $invoice_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false),false);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;
			case 'get_customer_card':
				// check
				if( ! empty($customer_id) && ! empty($card_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/cards/%s', $customer_id, $card_id) ;
					// return
					$http_response = mgm_remote_get($api_url, null, array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false),false);
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;					
					}
				}	
			break;
			case 'update_customer_card':
				// check
				if( ! empty($customer_id) && ! empty($card_id) ){
					// endpoint
					$api_url .= sprintf('customers/%s/cards/%s', $customer_id, $card_id) ;
					// options
					$options = array('headers'=>$http_headers, 'timeout'=>30, 'sslverify'=>false);
					// return
					$http_response = mgm_remote_post($api_url, $post_data, $options, false, array());
					// return
					if( $notify = json_decode($http_response) ){
						// return
						return $notify;
					}
				}	
			break;
		}

		// return
		return false;		
	}

	/**
	 * expire
	 */ 
	function _expire_membership($user_id){
		// member 
		$member = mgm_get_member($user_id);
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status = MGM_STATUS_EXPIRED;
		// status string
		$member->status_str = __('Last payment cycle expired','mgm');	
		// save
		$member->save();
		// action
		do_action('mgm_user_status_change', $user_id, $new_status, $old_status, 'module_' . $this->module, $member->pack_id);
	}

	//update credit card details
	function process_update_card_details () {		
		//check
		if(!$user_id = mgm_post_var('user_id', '', true) ) {
			return __('User Id invalid','mgm');
		}
		//check
		if(!$pack_id = mgm_post_var('pack', '', true) ) {
			return __('Pack Id invalid','mgm');
		}				
		//get member		
		$member= mgm_get_pack_member_obj($user_id,mgm_decode_id($pack_id));	
		//check
		if (isset($member->payment_info->cust_id) && !empty($member->payment_info->cust_id) && is_object($member)) {
			//post data
			$post_data = array(	'exp_month'=> mgm_post_var('mgm_card_expiry_month', '', true),
								'exp_year'=>  mgm_post_var('mgm_card_expiry_year', '', true),
								'name'=> mgm_post_var('mgm_card_holder_name', '', true));			
			//customer id
			$customer_id = $member->payment_info->cust_id;
			//customer 
			$customer = $this->_get_api_notify('get_customer', null, array('customer_id'=>$customer_id) );
			//inti card
			$card_id = '';
			//check
			if( isset($customer->default_card) ){
				$card_id = $customer->default_card;
			}elseif( isset($customer->default_source) ){
				$card_id = $customer->default_source;
			}			
			//post data
			$card_response = $this->_get_api_notify('update_customer_card', $post_data, array('customer_id'=>$customer_id,'card_id'=>$card_id) );
			//check
			if(isset($card_response->error)) {
				// return to credit card form
				$error_string = sprintf('Stripe error type %s: %s ',$card_response->error->type,$res->error->message);
				// return
				return $this->throw_cc_error($error_string);			
			}else {
				// redirect 		
				mgm_redirect(mgm_get_custom_url('membership_details', false,array('update_card_details'=>true)));exit;
				
			}
		}	
		//return
		return false;		
	}
		
	//payment_update_credit_card_html
	function process_update_credit_card_html(){	
		/*//check
		if(!$user_id = mgm_get_var('user_id', '', true) ) {
			return __('User Id invalid','mgm');
		}*/
		//check
		if( ! $user_id = mgm_get_var('user_id', '', true) ) {
			$user = mgm_get_user_by_querystring();

			if( (int)$user->ID == 0 ){
				return __('User Id invalid','mgm');
			}
		}

		//check
		if(!$pack_id = mgm_get_var('pack', '', true) ) {
			return __('Pack Id invalid','mgm');
		}

		// user
		$user = get_userdata($user_id);
		// cc field
		$cc_fields = $this->_update_card_ccfields($user,'div',mgm_decode_id($pack_id));
		//html
		$html = $this->validate_update_cc_fields_process(__FUNCTION__);
		//skip card nummber, cvv and card type stripe not support for update
		$html .= "<style>#mgm_pid_card_number,#mgm_pid_card_code,#mgm_pid_card_type  { display:none ; }</style>";
		// the html
		$html .='<form action="'. $this->_get_endpoint('update_credit_card_html') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
					<input type="hidden" name="user_id" value="'.$user_id.'">
					<input type="hidden" name="pack" value="'.$pack_id.'">
					<input type="hidden" name="submit_from" value="'.__FUNCTION__.'">
					'. $cc_fields .'
			    </form>	';
		// return 	  
		return $html;
	}	

	/**
	 * Override card token save
	 */ 
	function is_card_token_save_supported(){
		// use 
		if( isset($this->supports_card_token_save) ){
		// return
			return bool_from_yn( $this->supports_card_token_save );
		}

		// return
		return true;
	}

	/**
	 * Override card update
	 */ 
	function is_card_update_supported(){
		// use 
		if( isset($this->supports_card_update) ){
		// return
			return bool_from_yn( $this->supports_card_update );
		}

		// return
		return true;
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
	
	
	/**
	 * unsubscribe button api hook
	 * 
	 */ 
	public function get_button_reactivate($member){	

		//mgm_pr($member);

		// canceled
		//$canceled_at = $member->cancel_date;
		$awaiting_cancel_at = $member->status_reset_on;

		// action
		$action = add_query_arg(array('module'=>$this->code,'method'=>'payment_reactivate_subscription'), mgm_home_url('payments'));	
		// message
		$message = sprintf(__('You have cancelled from <span>%s</span> subscription and your cancellation is pending on <span>%s</span>, if you wish to reactivate your subscription now, please click the Reactivate button.','mgm'), get_option('blogname'), $awaiting_cancel_at);	

		// html
		$html='<div class="mgm_reactivate_btn_wrap">
					<h3 class="mgm_reactivate_btn_head">'.__('Reactivate Subscription','mgm').'</h3>
					<div class="mgm_reactivate_btn_desc">' . $message . '</div>
			   </div>
			   <br>
			   <form name="mgm_reactivate_form" id="mgm_reactivate_form" method="post" action="' . $action . '">
					<input type="hidden" name="user_id" value="' . $member->id . '"/>
					<input type="submit" name="btn_reactivate" value="' . __('Reactivate','mgm') . '" class="button" />	
			   </form>';	
		// return
		return $html;		
	}	
}

// end file