<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// ---------------------------------------------------------------------
/**
 * CCBill Payment Module
 *
 * @author     MagicMembers
 * @copyright  Copyright (c) 2011, MagicMembers 
 * @package    MagicMembers plugin
 * @subpackage Payment Module
 * @category   Module 
 * @version    3.0
 * @updated    2013-03-14
 */
class mgm_ccbill extends mgm_payment{	
	// construct
	function __construct(){
		// php4 construct
		$this->mgm_ccbill();
	}
	
	// php4 construct
	function mgm_ccbill(){
		// parent
		parent::__construct();
		// set code
		$this->code = __CLASS__; 
		// set module
		$this->module = str_replace('mgm_', '', $this->code);
		// set name
		$this->name = 'CCBill';	
		// logo
		$this->logo = $this->module_url( 'assets/ccbill.jpg' );
		// description
		$this->description = __('CCBill. Recurring payments and Single Purchase.', 'mgm');
		// supported buttons types
	 	$this->supported_buttons = array('subscription','buypost');// buypost is possible if dynamic pricing is allowed
		// trial support available ?
		$this->supports_trial= 'Y';		
		// do we depend on product mapping	
		$this->requires_product_mapping = 'Y';
		// type of integration
		$this->hosted_payment = 'Y';// html redirect	
		// supports rebill status check	
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
				// ccbill specific
				$this->setting['client_acccnum']      = $_POST['setting']['client_acccnum'];	
				$this->setting['client_subacc']       = $_POST['setting']['client_subacc'];	
				$this->setting['formname']            = $_POST['setting']['formname'];	
				$this->setting['upgrade_api']	      = $_POST['setting']['upgrade_api'];	
				$this->setting['upgrade_enc_key']     = $_POST['setting']['upgrade_enc_key'];					
				$this->setting['dynamic_pricing']     = $_POST['setting']['dynamic_pricing'];	
				$this->setting['md5_hashsalt']        = $_POST['setting']['md5_hashsalt'];	
				$this->setting['datalink_username']   = $_POST['setting']['datalink_username'];	
				$this->setting['datalink_password']   = $_POST['setting']['datalink_password'];	
				$this->setting['currency']            = $_POST['setting']['currency'];	
				$this->setting['send_userpass']       = $_POST['setting']['send_userpass'];	
				$this->setting['rebill_status_query'] = $_POST['setting']['rebill_status_query'];	
				$this->setting['debug_log'] 		  = $_POST['setting']['debug_log'];	
				// csutom end points flag
				if( isset($_POST['setting']['end_points']) ){
					$this->setting['end_points'] = $_POST['setting']['end_points'];		
				}
				// purchase price
				if(isset($_POST['setting']['purchase_price'])){
					$this->setting['purchase_price'] = $_POST['setting']['purchase_price'];
				}
				// if from cache:
				if(!in_array('buypost', $this->supported_buttons)) {
					$this->supported_buttons[] = 'buypost';
				}
				// common
				$this->description = $_POST['description'];
				$this->status      = $_POST['status'];
				// rebill_check_delay
				if($this->rebill_status_check_delay){
					$this->setting['rebill_check_delay'] = $_POST['rebill_check_delay'];	
				}				
				// logo if uploaded
				if(isset($_POST['logo_new_'.$this->code]) && !empty($_POST['logo_new_'.$this->code])){
				// set
					$this->logo = $_POST['logo_new_'.$this->code];
				}
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
				echo json_encode(array('status'=>'success','message'=> sprintf(__('%s settings updated', 'mgm'), $this->name)));
			break;
		}		
	}

	// settings post purchase
	function settings_post_purchase($data=NULL){
		// pricing form
		$formname = isset($data->product['ccbill_formname']) ? $data->product['ccbill_formname'] : $this->setting['formname'];
		
		// display
		$display = 'class="displaynone"';

		// check
		if(isset($data->allowed_modules) && in_array($this->code,(array)$data->allowed_modules)){
			$display = 'class="displayblock"';
		}

		// dynamic_pricing
		$tip = 'You need to enable and setup Dynamic Pricing to use this feature.';
		// check
		if( isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled' ){
			$tip = __('Please use a Pricing Form with Dynamic Pricing enabled.','mgm');
		}

		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					<div class="row">
						<div class="cell"><div class="postpurhase-heading">'.__('CCBill Settings','mgm') .'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Pricing Form Name','mgm') . '</p>
								<input type="text" name="mgm_post[product][ccbill_formname]" classs="mgm_text_width_payment" value="'.esc_html($formname).'" />								
								<div class="tips width95">' . $tip . '</div>	
							</div>
						</div>
					 </div>
				 </div>';
		// return
		return $html;		
	}	
	
	// settings postpack purchase
	function settings_postpack_purchase($data=NULL){
		// pricing form
		$formname = isset($data->product['ccbill_formname']) ? $data->product['ccbill_formname'] : $this->setting['formname'];
		
		// display
		$display = 'class="displaynone"';
		
		// check
		if(isset($data->modules) && in_array($this->code,(array)$data->modules)){
			$display = 'class="displayblock"';
		}
		
		// overwrite this
		$html = '<div id="settings_postpurchase_package_' . $this->module. '" ' . $display . '>
					 <div class="row">
						<div class="cell"><div class="subscription-heading">'.__('CCBill Settings','mgm').'</div></div>
				     </div>
				     <div class="row">
						<div class="cell width125px mgm-padding-tb"><b>'. __('Pricing Form Name','mgm') . ':</b></div>
					 </div>	
					 <div class="row">	
						<div class="cell textalignleft">
							<input type="text" name="product[ccbill_formname]" value="'.esc_html($formname).'" />
						</div>
				     </div>
			     </div>';
		// return
		return $html;
	}
	
	// hook for subscription package setting
	function settings_subscription_package($data=NULL){
		// substype_id
		$substype_id = isset($data['pack']['product']['ccbill_substype_id']) ? $data['pack']['product']['ccbill_substype_id'] : '';
		// pricing form
		$formname = isset($data['pack']['product']['ccbill_formname']) ? $data['pack']['product']['ccbill_formname'] : $this->setting['formname'];
		
		// dynamic_pricing
		$tip = __('SubscriptionType / Price ID from CCBill. ','mgm');
		// check
		if( isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled' ){
			$tip .= __('To use Dynamic Pricing, leave it empty. Please setup and use a Pricing Form with Dynamic 
				Pricing enabled, otherwise you will receive Invalid request (6).','mgm');
		}

		// display
		$display = '';
		// check
		if(isset($data['pack']['modules']) && !in_array($this->code,(array)$data['pack']['modules'])){
			$display = 'class="displaynone"';
		}

		// override this
		$html = '<div id="settings_subscription_package_' . $this->module. '" ' . $display . '>
				 	<div class="row">
						<div class="cell"><div class="subscription-heading">'.__('CCBill Settings','mgm').'</div></div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Subscription Type / Price ID','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][ccbill_substype_id]" value="'.esc_html($substype_id).'" /> 
								<div class="tips width95">' . $tip . '</div>																
							</div>
						</div>
					</div>
					<div class="row">
						<div class="cell">
							<div class="marginleft10px">	
								<p class="fontweightbold">' . __('Pricing Form Name','mgm') . '</p>
								<input type="text" name="packs['.($data['pack_ctr']-1).'][product][ccbill_formname]" value="'.esc_html($formname).'" size="20"/>
								<div class="tips width95">' . __('CCBill Formname to use in Payments.','mgm') . '</div>
							</div>
						</div>
					 </div>	
				 </div>';
		// return
		return $html;
	}
	
	// hook for coupon setting
	function settings_coupon($data=NULL){
		// substype_id
		$substype_id = isset($data['ccbill_substype_id']) ? $data['ccbill_substype_id'] : ''; 
		$formname = isset($data['ccbill_formname']) ? $data['ccbill_formname'] : $this->setting['formname']; 
		// dynamic_pricing
		$dynamic_pricing_tip = ( isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled' ) ? sprintf('<div class="tips width95">%s</div>',__('If left empty, Dynamic Pricing will be used.','mgm')) : '';
		// override this
		$html = '<div class="row">
					<div class="cell"><div class="subscription-heading">' . __('CCBill Settings','mgm') . '</div></div>
			     </div>
			     <div class="row">
					<div class="cell width125px"><b>' . __('Subscription Type / Price ID','mgm') . ':</b></div>
				 </div>	
			     <div class="row">						
					<div class="cell textalignleft">
						<input type="text" name="product[ccbill_substype_id]" value="' . esc_html($substype_id) . '" />
						' . $dynamic_pricing_tip . '
					</div>
				</div>
				<div class="row">
					<div class="cell width125px"><b>' . __('Form Name','mgm') . ':</b></div>
			    </div>	
			    <div class="row">	
					<div class="cell textalignleft">
						<input type="text" name="product[ccbill_formname]" value="' . esc_html($formname) . '" size="20" />
					</div>
			    </div>';
		// return
		return $html;
	}
	
	/**
	 * return process api hook, link back to site after payment is made
	 * 
	 * used for approval and denial url
	 */ 
	function process_return() {
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	
		
		// (isset($_POST['subscription_id']) && !empty($_POST['subscription_id'])) 
		// this works ok on paypal sandbox but not on live, treat success always for now
		$alt_tran_id = $this->_get_alternate_transaction_id();	
		// check and show message
		if(isset($alt_tran_id) && !empty($alt_tran_id) && isset($_GET['status']) && $_GET['status'] == 'success')
		{
			// notify when post not empty
			if( ! empty($_POST) ){
				// caller
				$this->set_webhook_called_by( 'self' );
				// process notify, internally called
				$this->process_notify();
			}

			// query arg
			$query_arg = array('status'=>'success', 'trans_ref'=>mgm_encode_id($alt_tran_id));
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
	
	/**
	 * notify process api hook, background IPN url
	 * 
	 * @see https://kb.ccbill.com/Background+Post
	 */ 
	function process_notify() {
		// record POST/GET data		
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );	

		// verify 
		if ($this->_verify_callback()) {
			// upgrade api
			if( ! isset($_POST['custom']) && isset($_POST['originalSubscriptionId']) 
				&& !empty($_POST['originalSubscriptionId']))
			{								
				$this->_convert_upgrade_data();
			}			
			
			// custom var
			$alt_tran_id = $this->_get_alternate_transaction_id();	

			// log data before validate
			$tran_id = $this->_log_transaction();			
			// payment type
			$payment_type = $this->_get_payment_type($alt_tran_id);
			// custom
			$custom = $this->_get_transaction_passthrough($alt_tran_id);
				
			// hook for pre process
			do_action('mgm_notify_pre_process_' . $this->module, array('tran_id'=>$tran_id,'custom'=>$custom));
			// check
			switch($payment_type){
				// buypost 				
				case 'post_purchase':
				case 'buypost':	
					$this->_buy_post(); //run the code to process a purchased post/page
				break;
				// subscription	
				case 'subscription':
					// update tpayment chckype
					if( isset($custom['user_id']) ): mgm_update_payment_check_state($custom['user_id'], 'notify'); endif;
					// cancellation
					if(isset($_GET['eventType']) && $_GET['eventType'] == 'Cancellation') {
					// @todo test real						
						$this->_cancel_membership(); //run the code to process a membership cancellation					
					}else{						
						$this->_buy_membership(); //run the code to process a new/extended membership
					}	
				break;						
			}		
			// after process		
			do_action('mgm_notify_post_process_'.$this->module, array('tran_id'=>$tran_id,'custom'=>$custom));					
		}
		// after process unverified		
		do_action('mgm_notify_post_process_unverified_'.$this->module);	
		
		// issue #: 366 (send header only if called directly from ccbill)		
		// 200 OK to ccbill, this is IMPORTANT, otherwise CB will keep on sending IPN .........
		if( $this->is_webhook_called_by('merchant') ){
			if( ! headers_sent() ){
				@header('HTTP/1.1 200 OK');
				exit('OK');
			}	
		}	
	}	
	
	/**
	 * status notify process api hook, background webhook url, 
	 * 
	 * 
	 * @see https://kb.ccbill.com/Webhooks+User+Guide
	 */ 
	function process_status_notify(){
		// record POST/GET data
		do_action('mgm_print_module_data', $this->module, __FUNCTION__ );		
		
		// eventType
		$eventType = isset($_GET['eventType']) ? $_GET['eventType'] : '';

		// on event type
		switch( $eventType ){
			// rebill
			case 'UserReactivation':
			case 'RenewalSuccess':
				if( isset($_POST['subscriptionId']) ){
					// tran
					$tran = mgm_get_transaction_by_option('ccbill_subscription_id', $_POST['subscriptionId']);

					// try alternate
					if( ! $tran ){
						$tran = mgm_get_transaction_by_option('ccbill_subscriptionid', $_POST['subscriptionId']);
					}

					// get tran
					if( $tran ){
						// get member
						if( $member = mgm_get_member($tran['data']['user_id']) ){
							// set new status
							$member->status = $new_status = MGM_STATUS_ACTIVE;
							// status string
							/*$member->status_str = __('Last payment cycle processed successfully, member reactivated','mgm');*/
							
							// status string
							if( 'RenewalSuccess' == $eventType ){
								$status_str = __('Last payment cycle processed successfully, member renewed','mgm');
							}elseif( 'UserReactivation' == $eventType ){
								$status_str = __('Last payment cycle processed successfully, member reactivated','mgm');
							}else{
							// 	NewSaleSuccess
								$status_str = __('Last payment was successful','mgm');
							}

							$member->status_str = $status_str;
							
							//Get pack cycle and less form cc-bill nexbilling date to find last pay date.
							$pack_cycle_format = mgm_get_pack_cycle_date((int)$member->pack_id, $member);
	                        // last pay date
							if($pack_cycle_format !== false){
								$pack_cycle_less = str_replace('+','-',$pack_cycle_format);							
								$member->last_pay_date = (isset($_POST['nextRenewalDate'])) ? date('Y-m-d', strtotime($pack_cycle_less, strtotime($_POST['nextRenewalDate']))) : date('Y-m-d');	
							}else {							
								$member->last_pay_date = (isset($_POST['nextRenewalDate'])) ? date('Y-m-d', strtotime($_POST['nextRenewalDate'])) : date('Y-m-d');	
							}	
							// expire date
							if(isset($_POST['nextRenewalDate']) && !empty($member->expire_date)){
								// consider next billing date as expire date from ccbill.
								$member->expire_date = date('Y-m-d', strtotime($_POST['nextRenewalDate']));
							} 
							// save
							$member->save();

							// hook args
							$args = array('user_id' => $user_id, 'transaction_id' => $member->transaction_id);
							// after succesful payment hook
							do_action('mgm_membership_transaction_success', $args);// backward compatibility				
							do_action('mgm_subscription_purchase_payment_success', $args);// new organized name								
						}	
					}
				}
						
			break;
			case 'NewSaleSuccess':
				// process notify, internally called
				$this->process_notify();
			break;

			case 'UpgradeSuccess':
				$this->process_notify();
			break;
			case 'Cancellation':
				// process notify, internally called
				// $this->process_notify();

				if( isset($_POST['subscriptionId']) ){
					// get tran
					$tran = mgm_get_transaction_by_option('ccbill_subscription_id', $_POST['subscriptionId']);

					// try alternate
					if( ! $tran ){
						$tran = mgm_get_transaction_by_option('ccbill_subscriptionid', $_POST['subscriptionId']);
					}

					// check
					if( $tran ){
						// cancel
						$this->_cancel_membership($tran['data']['user_id'], false);
					}
				}			
				
			break;
		}
		
		// 200 OK to gateway, only external		
		if(!headers_sent()){
			@header('HTTP/1.1 200 OK');
			exit('OK');
		}
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

			// cancel at ccbill
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
		
		// purchase_another
		if(isset($tran['data']['is_another_membership_purchase']) && bool_from_yn($tran['data']['is_another_membership_purchase'])) {	
			$tran['data']['subscription_option'] = 'purchase_another';			
		}
		
		// generate
		$button_code = $this->_get_button_code($tran['data'], $tran_id);
		// extra code
		$additional_code = do_action('mgm_additional_code');	
		
		// enc error
		if(isset($this->enc_error) && !empty($this->enc_error)){
			return $this->enc_error;
		}
		
		// log
		// mgm_log('button_code: '. $button_code, $this->get_context(__FUNCTION__));
		// endpoint
		$post_url = $this->_get_endpoint();
		// upgrade api
		if($this->setting['upgrade_api'] == 'upgrade' && ($tran['data']['subscription_option'] == 'upgrade' || $tran['data']['subscription_option'] == 'purchase_another')){
			$post_url = $this->_get_endpoint('upgrade');
		}	
		
		// log
		// mgm_log('post_url: '. $post_url, $this->get_context(__FUNCTION__));
		
		// the html
		$html='<form action="'. $post_url .'" method="post" class="mgm_form" name="' . $this->code . '_redirect_form" id="' . $this->code . '_redirect_form">
					'. $button_code .'					
					'. $additional_code .'						
					<img src="'.MGM_ASSETS_URL.'images/ajax/ajax-loader.gif"/><br>
					<b>'.sprintf(__('Please wait, you are being redirected to %s...','mgm'), $this->name).'</b>																
			   </form>				
			   <script type="text/javascript">document.' . $this->code . '_redirect_form.submit();</script>';
		// return 	  
		return $html;					
	}	
		
	// subscribe button api hook
	function get_button_subscribe($options=array()){		
		// ccbill depends on ccbill_substype_id/dynamic_pricing
		// check first ccbill_substype_id
		if(!isset($options['pack']['product']['ccbill_substype_id']) || (isset($options['pack']['product']['ccbill_substype_id']) && empty($options['pack']['product']['ccbill_substype_id']))){
		// check dynamic pricing		
			if( !isset($this->setting['dynamic_pricing']) || (isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] != 'enabled') ) {
			// return error
				return '<div class="mgm_button_subscribe_payment">
							<b>'.__('Error in CCBill settings : No Subscription Type ID set.','mgm').'</b>
						</div>';
				exit;
			}
		}
		// permalink
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
	
	// buypost button api hook
	function get_button_buypost($options=array(), $return = false) {
		// html
		$html = '';		
		// if dynamic_pricing is not enable do not allow PPP
		if( isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled' ) {
			// get html
			$html='<form action="'. $this->_get_endpoint('html_redirect') .'" method="post" class="mgm_form" name="' . $this->code . '_form" id="' . $this->code . '_form">
						<input type="hidden" name="tran_id" value="'.$options['tran_id'].'">
						<input class="mgm_paymod_logo" type="image" src="' . mgm_site_url($this->logo) . '" border="0" name="submit" alt="' . $this->name . '">
						<div class="mgm_paymod_description">'. mgm_stripslashes_deep($this->description) .'</div>
				   </form>';	
		}			
		// return or print
		if ($return) return $html;
		
		// print 
		echo $html;		
	}
	
	// dependency_check
	function dependency_check(){
		// default
		$this->dependency = array();
		// simple xml
		if(!extension_loaded('SimpleXML')){
			$this->dependency[] = '<b class="mgm_module_dependency_high">'.__('SimpleXML PHP extension must be loaded for CCBill Data Link SMS ','mgm').'.</b>';
		}
		// transaction details api
		$this->dependency[] = '<b class="mgm_module_dependency_medium">'.__('DataLink SMS API in CCBill must be enabled for Rebill Status Query','mgm').'.</b>';
		// error		
		return (count($this->dependency)>0) ? true : false ;		
	}
	
	// get module transaction info
	function get_transaction_info($member, $date_format){		
		// data
		$subscription_id = $member->payment_info->subscr_id;
		$transaction_id  = $member->payment_info->txn_id;	
		
		// info
		$info = sprintf('<b>%s:</b><br>%s: %s<br>%s: %s', __('CCBILL INFO','mgm'), __('SUBSCRIPTION ID','mgm'), $subscription_id, 
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
		$html = sprintf('<p>%s: <input type="text" size="20" name="ccbill[subscriber_id]"/></p>
				 		 <p>%s: <input type="text" size="20" name="ccbill[transaction_id]"/></p>', 
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
		$data = $post_data['ccbill'];
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
			$return .= '	<input type="hidden" name="'. $key .'" value="'. esc_html($value) .'" />' . "\n";
		}	
		// return
		return $return;
	}

	// get button data
	function _get_button_data($pack, $tran_id=NULL) {
		// system setting
		$system_obj  = mgm_get_class('system');			
		// user data
		if( isset($pack['user_id']) && (int)$pack['user_id'] > 0 ){			
			$user_id = $pack['user_id'];
			$user = get_userdata($user_id); 
			$user_email = $user->user_email;			
		}	
		
		//pack currency over rides genral setting currency - issue #1602
		if(!isset($pack['currency']) || empty($pack['currency'])){
			$pack['currency']=$this->setting['currency'];
		}		
		// setup data array		
		$data = array(			
			'clientAccnum' => $this->setting['client_acccnum'],
			'clientSubacc' => $this->setting['client_subacc'], // when Datalink user assigned to sub account
			//'usingSubacc'  => $this->setting['client_subacc'],// when Datalink user not assigned to sub account, act as ALL
		);
		
		// upgrade
		if($this->setting['upgrade_api'] == 'upgrade' && ($pack['subscription_option'] == 'upgrade' || $pack['subscription_option'] == 'purchase_another')){
			// encode
			$data['enc'] = $this->_create_upgrade_enc($pack, $data, $tran_id);
		}else{
		// merge	
			// formname 
			$formname = (isset($pack['product']['ccbill_formname']) && !empty($pack['product']['ccbill_formname']))
						? $pack['product']['ccbill_formname'] : $this->setting['formname'];
			// data
			$data = array_merge($data, array('formName' => $formname, 'language' => 'English'));
					
			// address fields
			if( isset($user) ){
				// email
				if( isset($user_email) && ! empty($user_email) ){
					$data['email'] = $user_email;
				}
				// set other address
				$this->_set_address_fields($user, $data);	
			}		
			
			// subscription purchase with ongoing/limited
			if( !isset($pack['buypost']) && isset($pack['duration_type']) /*&& $pack['num_cycles'] != 1*/ ){// supports one-time recurring
			// if ($pack['num_cycles'] != 1 && isset($pack['duration_type']) ) { // old style	
				// old functionality: using subscription id
				if(isset($pack['product']['ccbill_substype_id']) && !empty($pack['product']['ccbill_substype_id'])){
					// format type id
					$typeid_fmt = str_pad(trim($pack['product']['ccbill_substype_id']), 10, '0', STR_PAD_LEFT);
					// iso currency
					$currency_iso = mgm_get_currency_iso4217($pack['currency']);
					// set
					$data['subscriptionTypeId'] = implode(':', array($typeid_fmt, $currency_iso));	
					$data['allowedTypes'] = $data['subscriptionTypeId'];				
				}elseif(isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled'){
				// use dynamic pricing
					// vaidate
					$this->_valididate_dynamic_pricing(false);		
					// types
					$intv_types = array('d'=> 1,'w' => 7, 'm'=> 30, 'y'=> 365 );	
					// currency
					$data['currencyCode'] = mgm_get_currency_iso4217($pack['currency']);// #1086 iso integer code code for currency		
					// price
					$data['formPrice'] = number_format($pack['cost'], 2, '.', '');
					
					//issue #1666	
					if(isset($pack['num_cycles']) && (int)$pack['num_cycles'] > 0) {
						$data['formPeriod'] = $intv_types[strtolower($pack['duration_type'])] * $pack['duration'] * $pack['num_cycles'];// 2;// in days	
					}else {
						$data['formPeriod'] = $intv_types[strtolower($pack['duration_type'])] * $pack['duration'];
					}
					
					// trial
					if (isset($pack['trial_on']) && $pack['trial_on']) {					
						$data['formPrice']  = number_format($pack['trial_cost'], 2, '.', '');	
						$data['formPeriod'] = $intv_types[$pack['trial_duration_type']] * $pack['trial_duration'] * $pack['trial_num_cycles'];//in days														
					}	
					//issue #1739
					if(isset($pack['num_cycles']) && $pack['num_cycles'] != 1) {
						// cost	- issue #1902					
						if(!$pack['trial_on']){
							$data['formRecurringPrice'] = $data['formPrice'];						
						}else{
							$data['formRecurringPrice'] = number_format($pack['cost'], 2, '.', '');
						}						
						
						$data['formRecurringPeriod'] = $intv_types[$pack['duration_type']] * $pack['duration']; //number of days
						$data['formRebills'] = (isset($pack['num_cycles']) && (int)$pack['num_cycles'] > 0) ?  (int)$pack['num_cycles'] : 99; // iteration(99=infinite)
					}
					//check
					if(isset($pack['num_cycles']) && $pack['num_cycles'] != 1) {
						$data['formDigest'] = md5( $data['formPrice'] . $data['formPeriod'] . $data['formRecurringPrice'] . $data['formRecurringPeriod'] . $data['formRebills'] . $data['currencyCode'] . $this->setting['md5_hashsalt'] );	
					}else {
						$data['formDigest'] = md5( $data['formPrice'] . $data['formPeriod'] . $data['currencyCode'] . $this->setting['md5_hashsalt'] );	
					}
				}			
				
				// send mgm created username/password
				if($this->setting['send_userpass'] == 'yes'){		
					// username			
					if( isset($user) ){
						$data['username'] = $user->user_login;			
						$data['password'] = mgm_decrypt_password(mgm_get_member($user_id)->user_password, $user_id); 
					}						
				}
			}else{
			// post purchase	
				if(isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled') {
					// vaidate
					$this->_valididate_dynamic_pricing(false);	
					// set price for addons
					$data['formPrice'] = $pack['cost'];				
					// apply addons
					$this->_apply_addons($pack, $data, array('amount'=>'formPrice'));
					// price
					$data['formPrice'] 	  = number_format($data['formPrice'], 2, '.', '');
					$data['formPeriod']   = 2;// in days @todo manage via settings
					$data['currencyCode'] = mgm_get_currency_iso4217($pack['currency']);// #1086 iso integer code code for currency					
					$data['formDigest']   = md5( $data['formPrice'] . $data['formPeriod'] . $data['currencyCode'] . $this->setting['md5_hashsalt'] );	
				}
			}				
			
			// custom passthrough
			$data['custom'] = $tran_id;		
		}
		
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
		
		// alt
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
		if(isset($_POST['subscription_id']) && !empty($_POST['subscription_id']) 
			&& isset($_POST['reasonForDeclineCode']) && (int)$_POST['reasonForDeclineCode'] == 0)
		{	
		// processed	
			$payment_status = 'Processed';
		}elseif( isset($_POST['subscriptionId']) && !empty($_POST['subscriptionId']) ){
		// web hook	post
			$payment_status = 'Processed';	
		}else{
		// denied	
			$payment_status = 'Denied';
		}
		
		// verify
		if( ! $this->_verify_digest($payment_status) ){
			$payment_status = 'Error';
		}	

		// process on response code
		switch ($payment_status) {
			case 'Processed':	
				// status
				$status_str = __('Last payment was successful','mgm');
				// purchase status
				$purchase_status = 'Success';	
				
				// transation id
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
				$reason = 'Unknown';
				$status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
				// purchase status
				$purchase_status = 'Pending';	
													  
				// error
				$errors[] = $status_str;					
			break;

			default:
				// status
				$status_str = sprintf(__('Last payment status: %s','mgm'),$_POST['reasonForDecline']);
				// purchase status
				$purchase_status = 'Unknown';	
				
				// error
				$errors[] = $status_str;	
																										  
		}
		
		// do action
		do_action('mgm_return_post_purchase_payment_'.$this->module, array('post_id' => $post_id));// new, individual
		do_action('mgm_return_post_purchase_payment', array('post_id' => $post_id));// new, global 
		
		// set as purchase
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
		if( ! $dpne ) {
			
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

		// alt
		$alt_tran_id = $this->_get_alternate_transaction_id();

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

		// run double process issue#678
		if((isset($_POST['reasonForDeclineCode']) && (int)$_POST['reasonForDeclineCode'] == 12) 
			|| (isset($_POST['failureCode']) && (int)$_POST['failureCode'] == 12)){
			// check if active
			if($member->status == MGM_STATUS_ACTIVE){
			// exit
				mgm_log('Double process:' . print_r($_POST,1), $this->get_context('transaction_error', __FUNCTION__) ); exit();
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
			$currency =$subs_pack['currency'];
		}		
		// duration
		$member->duration        = $duration;
		$member->duration_type   = strtolower($duration_type);
		$member->amount          = $amount;
		$member->currency        = $currency;
		$member->membership_type = $membership_type;	
		$member->pack_id         = $pack_id;	
		// $member->payment_type    = ($_POST['recurringPrice']==0 && $_POST['recurringPeriod'] == 0) ? 'one-time' : 'subscription';
		$member->active_num_cycles = (isset($num_cycles) && !empty($num_cycles)) ? $num_cycles : $subs_pack['num_cycles']; 
		$member->payment_type      = ((int)$member->active_num_cycles == 1 && $subscription_option != 'upgrade') ? 'one-time' : 'subscription';
		// payment info for unsubscribe	
		if(!isset($member->payment_info)) $member->payment_info = new stdClass;
		// module
		$member->payment_info->module = $this->code;	
		
		// subscr_id	
		if(isset($_POST['originalSubscriptionId']) && !empty($_POST['originalSubscriptionId'])){
			// id
			$member->payment_info->prev_subscr_id = $_POST['originalSubscriptionId'];
			// type
			if($subscription_option == 'upgrade'){	
				$member->payment_type = 'subscription_upgrade';	
			}else{
				$member->payment_type = 'subscription';	
			}
		}	
		
		// type
		$member->payment_info->txn_type = $member->payment_type;	
		
		// subscr_id	
		if( isset($_POST['subscription_id']) && !empty($_POST['subscription_id']) ){
		// approval post	
			$member->payment_info->subscr_id = $_POST['subscription_id'];		
		}elseif( isset($_POST['subscriptionId']) && !empty($_POST['subscriptionId']) ){
		// web hook	post
			$member->payment_info->subscr_id = $_POST['subscriptionId'];	
		}	

		// txn_id
		if( isset($_POST['typeId']) && !empty($_POST['typeId']) ){	
			$member->payment_info->txn_id = $_POST['typeId'];	
		}elseif( isset($_POST['transactionId']) && !empty($_POST['transactionId']) ){
		// transactionId	
			$member->payment_info->txn_id = $_POST['transactionId'];	
		}	

		// mgm transaction id
		$member->transaction_id = $alt_tran_id;
	
		// process response
		$new_status = $update_role = false;

		// status
		if(isset($_POST['subscription_id']) && !empty($_POST['subscription_id']) 
			&& isset($_POST['reasonForDeclineCode']) && (int)$_POST['reasonForDeclineCode'] == 0)
		{
		// processed	
			$payment_status = 'Processed';
		}elseif( isset($_POST['subscriptionId']) && !empty($_POST['subscriptionId']) ){
		// processed	
			$payment_status = 'Processed';
		}else{
		// error	
			$payment_status = 'Denied';
		}
		
		// verify
		if( ! $this->_verify_digest($payment_status) ){
			$payment_status = 'Error';
		}
		
		// status
		switch ($payment_status) {
			case 'Processed':
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
						case 'purchase_another':
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
							// calc expiry				
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
						// old status
						$old_status = $member->status;	# iss 2168
						//Do it only once
						// if(!isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) {							
						if( ( ! isset($member->rebilled) && isset($member->active_num_cycles) && $member->active_num_cycles != 1 ) 
							|| $old_status == MGM_STATUS_EXPIRED || (int)$member->rebilled == 0 ) {		
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
					// rebill
					$member->rebilled = (!$member->rebilled) ? 1 : ((int)$member->rebilled+1);			
				}

				//cancel previous subscription:
				//issue#: 565				
				$this->cancel_recurring_subscription($alt_tran_id, null, null, $pack_id);
							
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
			case 'Denied':
				$new_status = MGM_STATUS_NULL;
				$member->status_str = __('Last payment was refunded or denied','mgm');
			break;

			case 'Pending':
				$new_status = MGM_STATUS_PENDING;
				$reason = 'Unknown';
				$member->status_str = sprintf(__('Last payment is pending. Reason: %s','mgm'), $reason);
			break;
			case 'Error':
				$new_status = MGM_STATUS_ERROR;
				$reason = 'Response digest verification error!';
				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $reason);
			break;
			default:
				$new_status = MGM_STATUS_ERROR;

				if(isset($_POST['reasonForDecline']) && !empty($_POST['reasonForDecline'])){
					$reason = $_POST['reasonForDecline'];	
				}elseif(isset($_POST['failureReason']) && !empty($_POST['failureReason'])){
					$reason = $_POST['failureReason'];	
				}

				$member->status_str = sprintf(__('Last payment status: %s','mgm'), $reason);
			break;
		}
				
		// old status
		$old_status = $member->status;	
		// set new status
		$member->status = $new_status;

		// whether to acknowledge the user - This should happen only once
		$acknowledge_user = $this->is_payment_email_sent($alt_tran_id);
		// whether to subscriber the user to Autoresponder - This should happen only once
		$acknowledge_ar = mgm_subscribe_to_autoresponder($member, $alt_tran_id);
			
		// another_subscription modification
		if(isset($custom['is_another_membership_purchase']) && bool_from_yn($custom['is_another_membership_purchase'])) {			//issue #1227
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
		//debug log
		if(bool_from_yn($this->setting['debug_log'])) mgm_log("Updated member :".mgm_pr($member,true),$this->get_context( 'debug', __FUNCTION__ ) );		
		
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
	
	// cancel membership
	function _cancel_membership($user_id, $redirect = false){
		// system	
		$system_obj = mgm_get_class('system');		
		$s_packs = mgm_get_class('subscription_packs');
		$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
		$dpne = bool_from_yn($system_obj->get_setting('disable_payment_notify_emails'));
		// currency
		if (!$currency) $currency = $system_obj->setting['currency'];
		//admin user check
		$is_admin = (is_super_admin()) ? true : false;	
		// find user
		$user = get_userdata($user_id);	
		$member = mgm_get_member($user_id);		
		// multiple membership level update:	
		$multiple_update = false;	
		// check
		if((isset($_POST['membership_type']) && $member->membership_type != $_POST['membership_type']) || (isset($is_another_membership_purchase) && $is_another_membership_purchase == 'Y' )) {
			$multiple_update = true;
			$multi_memtype = (isset($_POST['membership_type'])) ? $_POST['membership_type'] : $membership_type;	
			$member = mgm_get_member_another_purchase($user_id, $multi_memtype);
		}			
		// get pack
		if($member->pack_id){
			$subs_pack = $s_packs->get_pack($member->pack_id);
		}else{
			$subs_pack = $s_packs->validate_pack($member->amount, $member->duration, $member->duration_type, $member->membership_type);
		}	
		
		/*//debug log
		if(bool_from_yn($this->setting['debug_log'])) {
			mgm_log("subs_pack :".mgm_pr($subs_pack,true),$this->get_context( 'debug', __FUNCTION__ ) );
			mgm_log("member :".mgm_pr($member,true),$this->get_context( 'debug', __FUNCTION__ ) );
		}	*/

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
		// if today 
		if($expire_date == date('Y-m-d')){
			// status
			$new_status          = MGM_STATUS_CANCELLED;
			$new_status_str      = __('Subscription cancelled','mgm');
			// set
			$member->status      = $new_status;
			$member->status_str  = $new_status_str;					
			$member->expire_date = date('Y-m-d');
									
			// reassign expiry membership pack if exists: issue#: 535			
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
		if($multiple_update) {
			mgm_save_another_membership_fields($member, $user_id);	
		}else {			
			$member->save();
		}	
		
		//debug log
		/*if(bool_from_yn($this->setting['debug_log'])){ 
			mgm_log("Updated member :".mgm_pr($member,true),$this->get_context( 'debug', __FUNCTION__ ) );
		}*/			
		
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

		// redirect only internal
		if( $redirect ) {
			// message
			$lformat = mgm_get_date_format('date_format_long');
			$message = sprintf(__("You have successfully unsubscribed. Your account has been marked for cancellation on %s", "mgm"), 
			                  ($expire_date == date('Y-m-d') ? 'Today' : date($lformat, strtotime($expire_date))));		
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
	 * 
	 * https://www.ccbill.com/cs/manuals/CCBill_SMS_Users_Guide.pdf
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
					
					mgm_log('RECALLing '. $member->payment_info->module .': cancel_recurring_subscription FROM: ' . $this->code, $this->get_context( __FUNCTION__ ));
					return mgm_get_module($member->payment_info->module, 'payment')->cancel_recurring_subscription($trans_ref, null, null, $pack_id);				
				}
				//skip if same pack is updated
				if(empty($member->pack_id) || (is_numeric($pack_id) && $pack_id == $member->pack_id) )
					return false;
				
			}else 
				return false;
		}
		
		
		// when a subscription found
		if( $subscr_id ) {
		// @todo add api call
			// query data
			$query_data = array();			
			// add internal vars
			$secure = array(				
				'clientAccnum' => $this->setting['client_acccnum'],
				'clientSubacc' => $this->setting['client_subacc'],	
				//'usingSubacc'  => $this->setting['client_subacc'],
				'username'     => $this->setting['datalink_username'],
				'password'     => $this->setting['datalink_password']
			);
			// merge
			$query_data = array_merge($query_data, $secure); // overwrite post data array with secure params		
			// method
			$query_data['action'] = 'cancelSubscription';			
			//check
			if(isset($member)) {
				$query_data['subscriptionId'] = $member->payment_info->subscr_id;
			}else {
				$query_data['subscriptionId'] = $subscr_id;
			}						
			// xml response
			$query_data['returnXML'] = 1;
			
			// post string
			$query_string = _http_build_query($query_data, null, '&');
			
			// endpoint	
			$endpoint = $this->_get_endpoint('datalink_sms');		
			
			// url
			$url = $endpoint . '?' . $query_string;			
			
			//issue #2518
			$args = array(
			    'timeout'     => 60,
			    'sslverify'   => false,
			);
						
			// log
			mgm_log( $url, $this->get_context( 'debug', __FUNCTION__ ));
			
			// remote get			
			$http_response = mgm_remote_get($url, null, $args, false);

			// log
			mgm_log( $http_response, $this->get_context( 'debug', __FUNCTION__ ));

			// xml		
			if($xml = @simplexml_load_string($http_response)){	
				// log
				mgm_log( $xml, $this->get_context( 'debug', __FUNCTION__ ));
				// return
				return (int)$xml == 1;	// success
			}	
		// end api call	
		}elseif( ( is_null($subscr_id) || $subscr_id === 0) ) {
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
	 * Along with IPN post mechanism for rebills, the module will need to specifically request for the rebill status
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 * @deprecated as not supported by standard	 
	 * 
	 * @see https://www.ccbill.com/cs/manuals/CCBill_SMS_Users_Guide.pdf
	 */
	function query_rebill_status($user_id, $member=NULL) {	
		// check
		if($this->setting['rebill_status_query'] == 'disabled') return false;	

		// check	
		if (isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {					
			// query data
			$query_data = array();			
			// add internal vars
			$secure = array(				
				'clientAccnum' => $this->setting['client_acccnum'],
				'clientSubacc' => $this->setting['client_subacc'],	// when Datalink user assigned to sub account
				//'usingSubacc'  => $this->setting['client_subacc'],// when Datalink user not assigned to sub account, act as ALL	
				'username'     => $this->setting['datalink_username'],
				'password'     => $this->setting['datalink_password']
			);
			// merge
			$query_data = array_merge($query_data, $secure); // overwrite post data array with secure params		
			// method
			$query_data['action'] = 'viewSubscriptionStatus';
			$query_data['subscriptionId'] = $member->payment_info->subscr_id;			
			// xml response
			$query_data['returnXML'] = 1;
			// post string
			$query_string = _http_build_query($query_data, null, '&');
			// endpoint	
			$endpoint = $this->_get_endpoint('datalink_sms');
			// url
			$url = $endpoint . '?' . $query_string;			

			// debug log
			$this->log_debug("url :".$url, __FUNCTION__);		
			
			//issue #2518
			$args = array(
			    'timeout'     => 60,
			    'sslverify'   => false,
			);
									
			// remote get			
			$http_response = mgm_remote_get($url, null, $args, false);
			
			// debug log
			$this->log_debug("http_response:".mgm_pr($http_response,true), __FUNCTION__ );
				
			// subs data
			$subsdata = array('subscriptionStatus' => 'Error');
			
			// xml		
			if($xml = @simplexml_load_string($http_response)){				
				// debug log
				$this->log_debug("xml:".mgm_pr($xml,true), __FUNCTION__);				
				// check
				if(isset($xml->subscriptionStatus)){
					// get status
					switch((int)$xml->subscriptionStatus){
						case 0: // inactive
							//$subsdata['subscriptionStatus'] = 'Pending'; 
							//consider expired - got confirmation from ccbill team
							$subsdata['subscriptionStatus'] = 'Expired';
							if($expirationDate = (string)$xml->expirationDate){
								$subsdata['expirationDate'] = (strlen($expirationDate) > 8) ? substr($expirationDate, 0, 8) : $expirationDate;
							}							
						break;
						case 1:// cancelled
							$subsdata['subscriptionStatus'] = 'Cancelled';
							if($cancelDate = (string)$xml->cancelDate){
								$subsdata['cancelDate'] = (strlen($cancelDate) > 8) ? substr($cancelDate, 0, 8) : $cancelDate;
							}
							if($expirationDate = (string)$xml->expirationDate){
								$subsdata['expirationDate'] = (strlen($expirationDate) > 8) ? substr($expirationDate, 0, 8) : $expirationDate;
							}							
						break;
						case 2:// active
							$subsdata['subscriptionStatus'] = 'Active';
						break;
						default: // error
						break;
					}
				}		
				// next pay
				if(isset($xml->nextBillingDate)){
					if($nextBillingDate = (string)$xml->nextBillingDate){
						$subsdata['nextBillingDate'] = (strlen($nextBillingDate) > 8) ? substr($nextBillingDate, 0, 8) : $nextBillingDate;
					}
				}						
			}											
			
			// check		
			if (isset($subsdata['subscriptionStatus'])) {
				// old status
				$old_status = $member->status;
				// date format
				$date_format = mgm_get_date_format('date_format');					
				// set status
				switch($subsdata['subscriptionStatus']){
					case 'Active':
						// set new status
						$member->status = $new_status = MGM_STATUS_ACTIVE;
						// status string
						$member->status_str = __('Last payment cycle processed successfully','mgm');
						
						//Get pack cycle and less form cc-bill nexbilling date to find last pay date.
						$pack_cycle_format = mgm_get_pack_cycle_date((int)$member->pack_id, $member);
                        // last pay date
						if($pack_cycle_format !== false){
							$pack_cycle_less   = str_replace('+','-',$pack_cycle_format);							
							$member->last_pay_date = (isset($subsdata['nextBillingDate'])) ? date('Y-m-d', strtotime($pack_cycle_less, strtotime($subsdata['nextBillingDate']))) : date('Y-m-d');	
						}else {							
							$member->last_pay_date = (isset($subsdata['nextBillingDate'])) ? date('Y-m-d', strtotime($subsdata['nextBillingDate'])) : date('Y-m-d');	
						}	
						// expire date
						if(isset($subsdata['nextBillingDate']) && !empty($member->expire_date)){							
							
							// consider next billing date as expire date from cc bill.
							$member->expire_date = date('Y-m-d', strtotime($subsdata['nextBillingDate']));
							
							/*
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
							*/				
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
					case 'Cancelled':					
						// if expire date in future, let as awaiting
						if(isset($subsdata['expirationDate']) && !empty($subsdata['expirationDate']) && strtotime($subsdata['expirationDate']) > time()){
							// status				
							$member->status = $new_status = MGM_STATUS_AWAITING_CANCEL;
							//taking expire date from ccbill							
							$member->expire_date = date('Y-m-d', strtotime($subsdata['expirationDate']));
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
							if(isset($subsdata['cancelDate']) && !empty($subsdata['cancelDate'])){
								$member->status_str = sprintf(__('Last payment cycle cancelled on %s','mgm'), date($date_format, strtotime($subsdata['cancelDate'])));
							}else {
								$member->status_str = __('Last payment cycle cancelled','mgm'); 
							}
						}
						//Before this fix users having wrong last pay date so updating here - issue #1520
						if(isset($subsdata['expirationDate']) && !empty($subsdata['expirationDate'])){
							//Get pack cycle and less form cc-bill nexbilling date to find last pay date
							$pack_cycle_format = mgm_get_pack_cycle_date((int)$member->pack_id, $member);
							// last pay date
							if($pack_cycle_format !== false){
								$pack_cycle_less   = str_replace('+','-',$pack_cycle_format);
								$member->last_pay_date = date('Y-m-d', strtotime($pack_cycle_less, strtotime($subsdata['expirationDate'])));
							}
						}						
						// reassign expiry membership pack if exists - issue #2168 & issue#2555
						if($member->status == MGM_STATUS_CANCELLED) { 
							$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'CANCEL', true);
						}					
						// save
						$member->save();

						// only run in cron, other wise too many tracking will be added
						// if( defined('DOING_QUERY_REBILL_STATUS') && DOING_QUERY_REBILL_STATUS != 'manual' ){
						// after cancellation hook
						do_action('mgm_membership_subscription_cancelled', array('user_id' => $user_id));	
						// }
					break;	
					case 'Expired':
						// set new status
						$member->status = $new_status = MGM_STATUS_EXPIRED;
						// status string
						if(isset($subsdata['expirationDate']) && !empty($subsdata['expirationDate'])){
							$member->status_str = sprintf(__('Last payment cycle expired on %s','mgm'), date($date_format, strtotime($subsdata['expirationDate'])));	
						}else {
							$member->status_str = __('Last payment cycle expired','mgm'); 
						}
						//Before this fix users having wrong last pay date so updating here - issue #1520
						if(isset($subsdata['expirationDate']) && !empty($subsdata['expirationDate'])){
							//Get pack cycle and less form cc-bill nexbilling date to find last pay date
							$pack_cycle_format = mgm_get_pack_cycle_date((int)$member->pack_id, $member);
							// last pay date
							if($pack_cycle_format !== false){
								$pack_cycle_less   = str_replace('+','-',$pack_cycle_format);
								$member->last_pay_date = date('Y-m-d', strtotime($pack_cycle_less, strtotime($subsdata['expirationDate'])));
							}
							//taking expire date from ccbill
							$member->expire_date = date('Y-m-d', strtotime($subsdata['expirationDate']));							
						}
						// reassign expiry membership pack if exists - issue #2168		
						$member = apply_filters('mgm_reassign_member_subscription', $user_id, $member, 'EXPIRE', true);						
						// save
						$member->save();
					break;
				}
				//debug log
				if(bool_from_yn($this->setting['debug_log'])) mgm_log("Updated member :".mgm_pr($member,true),$this->get_context( 'debug', __FUNCTION__ ) );			
				// action
				if( isset($new_status)  && $new_status != $old_status){
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
		return false;
	}
		
	// default setting
	function _default_setting(){
		// ccbill specific
		$this->setting['client_acccnum']      = '';	
		$this->setting['client_subacc']       = '';	
		$this->setting['formname']            = '';	
		$this->setting['upgrade_api']	      = 'signup';		
		$this->setting['upgrade_enc_key']	  = '';		
		$this->setting['send_userpass']       = 'no';		
		$this->setting['dynamic_pricing']     = 'disabled';		
		$this->setting['debug_log']     	  = 'N';		
		$this->setting['md5_hashsalt']	      = '';
		$this->setting['datalink_username']	  = '';
		$this->setting['datalink_password']	  = '';				
		$this->setting['rebill_status_query'] = 'enabled';
		$this->setting['currency']	 	      = mgm_get_setting('currency');		
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
		// custom
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// check
		if($this->_is_transaction($alt_tran_id)){	
			// tran id
			$tran_id = (int)$alt_tran_id;			
			// return data				
			if(isset($_GET['eventType'])){
				$option_name = $this->module.'_'.strtolower($_GET['eventType']).'_return_data';
			}else{
				$option_name = $this->module.'_return_data';
			}
			// set
			mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>$option_name,'option_value'=>json_encode($_POST)));
			
			// options 
			$options = array('subscription_id','typeId','reservationId','subscriptionId','transactionId');
			// loop
			foreach($options as $option){
				// check
				if(isset($_POST[$option])){
					mgm_add_transaction_option(array('transaction_id'=>$tran_id,'option_name'=>strtolower($this->module.'_'.$option),'option_value'=>$_POST[$option]));
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
			'test' => false,
			'live' => 'https://bill.ccbill.com/jpost/signup.cgi',
			'datalink_sms' => 'https://datalink.ccbill.com/utils/subscriptionManagement.cgi', // datalink sms 
			'datalink_extract' => 'https://datalink.ccbill.com/data/main.cgi',// datalink csv
			'upgrade' => 'https://bill.ccbill.com/jpost/upgradeSubscription.cgi'
		);	// upgrade
		
		// merge
		$end_points = (is_array($end_points)) ? array_merge($end_points_default, $end_points) : $end_points_default;
		
		// set
		$this->_set_endpoints($end_points);
	}
	
	// set 
	function _set_address_fields($user, &$data){
		// mappings
		$mappings= array('first_name'=>'customer_fname','last_name'=>'customer_lname','address'=>'address1',
		                 'city'=>'city','state'=>'state','zip'=>'zipcode','country'=>'country','phone'=>'phone_number');
						 
		// parent
		parent::_set_address_fields($user, $data, $mappings, array($this,'_address_fields_filter'));				 
	}
	
	// filter
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

	// verify callback 
	function _verify_callback(){	
		/**
		* Post Server Ranges 
		* Ashburn: 64.38.212.(0-255)		
		* Amsterdam: 64.38.215.(0-255)		
		* Phoenix: 64.38.240.(0-255) 64.38.241.(0-255)
		*/
		
		// return true; // disabled for POST/GET issue from CCBill iss#2721
		$alt_tran_id = $this->_get_alternate_transaction_id();

		return ( isset($alt_tran_id) && !empty($alt_tran_id) );

		/*// CCBill can not post to HTTPS 

		// just kept for future protecting
		$post_vars = array('custom','subscription_id','denialId','originalSubscriptionId');
		// return
		$return = false;
		// loop
		foreach($post_vars as $post_var){
			// check
			if(isset($_POST[$post_var]) && !empty($_POST[$post_var])){
				$return = true; break;
			}
		}
		//debug log
		if(bool_from_yn($this->setting['debug_log'])) {
			if(!$return) mgm_log("Verify callback back failed :".mgm_pr($_POST,true),$this->get_context( 'debug', __FUNCTION__ ) );	
		}
		// return
		return $return;	*/
	}	
	
	// MODULE SPECIFIC PRIVATE HELPERS /////////////////////////////////////////////////////////////////
	
	// validate
	function _valididate_dynamic_pricing($return=true){
		// check hash
		if(!isset($this->setting['md5_hashsalt']) || (isset($this->setting['md5_hashsalt']) && empty($this->setting['md5_hashsalt']))){
			// error
			$error = __('CCBill - MD5 Hash Salt is required.','mgm');
			// return 
			if($return) return $error;
			// die
			die($error);			
		}
		// check currency
		if(!isset($this->setting['currency']) || (isset($this->setting['currency']) && empty($this->setting['currency']))){
			// error
			$error = __('CCBill - MD5 Currency Code is required.','mgm');
			// return 
			if($return) return $error;
			// die
			die($error);	
		}	
	}
	
	// _create_upgrade_enc
	function _create_upgrade_enc($pack, $data, $tran_id){
		// get member
		$member = mgm_get_member($pack['user_id']);	
		// copy
		$enc_data = array();		
		// check
		if (isset($member->payment_info->subscr_id) && !empty($member->payment_info->subscr_id)) {
			// set
			$enc_data['subscriptionId']      = $member->payment_info->subscr_id;
			$enc_data['upgradeClientAccnum'] = $data['clientAccnum'];
			$enc_data['upgradeClientSubacc'] = $data['clientSubacc'];
			//'usingSubacc'  => $this->setting['client_subacc'],
			// return base64_encode();
			if(isset($pack['product']['ccbill_substype_id']) && !empty($pack['product']['ccbill_substype_id'])){				
				// set
				$enc_data['upgradeTypeId'] = trim($pack['product']['ccbill_substype_id']);
				// custom passthrough
				$enc_data['custom'] = $tran_id;	
				// str
				$enc_str = _http_build_query($enc_data, null, '&', '', false);							
				// enc
				return $enc = $this->encrypt($enc_str, $this->setting['upgrade_enc_key']);	
			}					
		}else{
			$this->enc_error = __('Member has no active CCBill Subscription, failed to process upgrade', 'mgm');			
		}
		
		// return
		return '';
	}
	
	// encode
	function encrypt($str,$key){
		// hex mod
		$hex_key     = $this->hexmod($key);
		$bin_hex_key = pack('H*', str_pad($hex_key, 16*3, '0'));		
		// Pad string length to exact multiple of 8
		$str         = $str . str_repeat(' ', 8-(strlen($str)%8));   
		// return
		return $out  = base64_encode( mcrypt_ecb(MCRYPT_3DES, $bin_hex_key, $str, MCRYPT_ENCRYPT) );		
	}
	
	// Hex Modulus: Converts G-Z/g-z to 0-f (See @Jinyo's Post)
	// Necessary to match CCBill's Encryption
	function hexmod($str){
		// Convert G-Z & g-z to 0-f
		$ascii_in  = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
		$ascii_out = '0123456789ABCDEF0123456789ABCDEF0123abcdef0123456789abcdef0123';
		// return
		return $hex_out = str_replace(str_split($ascii_in),str_split($ascii_out),$str);				
	}
	
	// upgrade
	function _convert_upgrade_data(){
		global $wpdb;				
		// validate
		if( isset($_POST['clientAccnum']) && $_POST['clientAccnum'] == $this->setting['client_acccnum'] 
			&& ((isset($_POST['clientSubacc']) && $_POST['clientSubacc'] == $this->setting['client_subacc']) 
				|| (isset($_POST['usingSubacc']) && $_POST['usingSubacc'] == $this->setting['client_subacc'])) 
			&& isset($_POST['reasonForDeclineCode']) && (int)$_POST['reasonForDeclineCode'] == 0){		
			// valid
			$user_id = NULL;
			// by email
			if($user = get_user_by('email', $_POST['email'])){		
				// id
				if($this->_verify_member_for_upgrade($user->ID)){
					$user_id = $user->ID;	
				}
			}		
			
			// not found
			if( ! $user_id ){			
				// subs id
				$org_subs_id = $_POST['originalSubscriptionId'];// @todo, secure post data
				// sql
				$sql = "SELECT `user_id` FROM `{$wpdb->usermeta}` WHERE `meta_key` = 'mgm_member_options' 
						AND `meta_value` LIKE '%\"subscr_id\"%\"{$org_subs_id}\"%'";		
				//debug log
				if(bool_from_yn($this->setting['debug_log'])) mgm_log("SQL : ".$sql,$this->get_context( 'debug', __FUNCTION__ ) );				
				// get user id
				if($usermeta = $wpdb->get_row($sql)){
					// check
					if($this->_verify_member_for_upgrade($usermeta->user_id)){
						$user_id = $usermeta->user_id;	
					}
				}
			}		
			
			//debug log
			if(bool_from_yn($this->setting['debug_log'])) mgm_log("user_id : ".$user_id,$this->get_context( 'debug', __FUNCTION__ ) );		
			// check
			if($user_id){										
				// row
				$tran = $wpdb->get_row($wpdb->prepare("SELECT `id`,`data` FROM `".TBL_MGM_TRANSACTION."` WHERE `user_id`='%d' ORDER BY transaction_dt DESC LIMIT 0,1", $user_id), ARRAY_A);		
				// reset data
				if(isset($tran['id'])){
					// set
					$_POST['custom'] = $tran['id'];// set													
				}							
			}
			//debug log
			if(bool_from_yn($this->setting['debug_log'])) mgm_log("Append custom transaction id to post :".mgm_pr($_POST,true),$this->get_context( 'debug', __FUNCTION__ ) );
		}else {
			//debug log
			if(bool_from_yn($this->setting['debug_log'])) mgm_log("convert upgrade data failed :".mgm_pr($_POST,true),$this->get_context( 'debug', __FUNCTION__ ) );	
		}
	}
	
	// verify
	function _verify_member_for_upgrade($user_id){
		// get member
		$member = mgm_get_member((int)$user_id);					
		// validate
		if($member->payment_info->subscr_id == $_POST['originalSubscriptionId']){
			// valid
			return true;
		}
		// return
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

	/**
	 * verify digest for dynamic pricing
	 * 
	 * @see https://www.ccbill.com/cs/manuals/CCBill_Background_Post_Users_Guide.pdf 
	 * Hash digest of a Dynamic Pricing response. If not using Dynamic Pricing,
	 * this value will return as a blank string.
	 */ 
	function _verify_digest($payment_status){
		// verify md5 Hash:
		if( isset($this->setting['dynamic_pricing']) && $this->setting['dynamic_pricing'] == 'enabled' ) {
			// background post
			if( isset($_POST['responseDigest']) && !empty($_POST['responseDigest']) ){
			// background post	
				$resp_digest = $_POST['responseDigest'];
			}elseif( isset($_POST['dynamicPricingValidationDigest']) && !empty($_POST['dynamicPricingValidationDigest']) ){
			// webhook	
				$resp_digest = $_POST['dynamicPricingValidationDigest'];
			}	
			
			// verify
			if( isset($resp_digest) && !empty($resp_digest) ){	
				// check
				if($payment_status == 'Processed'){
					// str
					$str = '';

					if( isset($_POST['subscription_id']) ){
						$str = $_POST['subscription_id'];
					}elseif( isset($_POST['subscriptionId']) ){
						$str = $_POST['subscriptionId'];
					}

					$str .='1';
				}else{
					$str = isset($_POST['denialId']) && !empty($_POST['denialId']) ? $_POST['denialId']: '';

					$str .='0';
				}	
				
				// hash salt
				$str .=	$this->setting['md5_hashsalt'];

				// hash
				$hash = md5( $str );
				
				// match
				if( $resp_digest != $hash ) {
					// str
					$error = 'Response verification failed: resp_digest(' . $resp_digest .') === calc hash ('.$hash.')';
					// error
					mgm_log($error, $this->get_context(__FUNCTION__)); 

					return false;
				}
			}
		}

		return true;
	}

	 // get custom var from mulple sources
	function _get_alternate_transaction_id(){
		// custom
		$alt_tran_id = '';
		
		// check alternate
		if(isset($_POST['X-custom']) && !empty($_POST['X-custom'])){
			$alt_tran_id = $_POST['X-custom'];
		}else{
		// default custom	
			$alt_tran_id = parent::_get_alternate_transaction_id('custom');
		}  

		// return 		
		return $alt_tran_id;
	}

	// get tran id
	function _get_transaction_id(){
		// custom
		$alt_tran_id = $this->_get_alternate_transaction_id();
		// validate
		if($this->_is_transaction($alt_tran_id)){	
			// tran id
			return $tran_id = (int)$alt_tran_id;
		}
		// return 
		return 0;	
	}
}

// end file