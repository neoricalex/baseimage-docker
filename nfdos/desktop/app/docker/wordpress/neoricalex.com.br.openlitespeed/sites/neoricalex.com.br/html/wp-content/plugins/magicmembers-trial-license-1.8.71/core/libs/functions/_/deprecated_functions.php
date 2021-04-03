<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Deprecated Functions
 *
 */

/*
function mgm_get_coupon_pack2($member, &$pack){
	// set coupon
	$member->coupon = (array) $member->coupon;
	// check
	if(isset($member->coupon['id'])){				
		// main 		
		if($pack && $member->coupon['cost']){
			// original
			$pack['original_cost'] = $pack['cost'];
			// payable
			$pack['cost'] = $member->coupon['cost'];
		}	
		
		if($pack && $member->coupon['duration'])
			$pack['duration'] = $member->coupon['duration'];
		if($pack && $member->coupon['duration_type'])
			$pack['duration_type'] = $member->coupon['duration_type'];
		if($pack && $member->coupon['membership_type'])
			$pack['membership_type'] = $member->coupon['membership_type'];
		//issue#: 478/ add billing cycles.	
		if($pack && isset($member->coupon['num_cycles']))
			$pack['num_cycles'] = $member->coupon['num_cycles'];	
		
		// trial	
		if($pack && $member->coupon['trial_on'])
			$pack['trial_on'] = $member->coupon['trial_on'];
		if($pack && $member->coupon['trial_cost'])
			$pack['trial_cost'] = $member->coupon['trial_cost'];
		if($pack && $member->coupon['trial_duration_type'])
			$pack['trial_duration_type'] = $member->coupon['trial_duration_type'];
		if($pack && $member->coupon['trial_duration'])
			$pack['trial_duration'] = $member->coupon['trial_duration'];	
		if($pack && $member->coupon['trial_num_cycles'])
			$pack['trial_num_cycles'] = $member->coupon['trial_num_cycles'];	
			
		// mark pack as coupon applied
		$pack['coupon_id'] = $member->coupon['id'];				
	}
}*/

// get pp packs
/*
function mgm_get_ppp_pack_posts($pack_id = false) {
    global $wpdb;
    
    $return = new stdClass();
    $return->id = $return->pack_id = $return->post_id = $return->unixtime = false;
    
    if ($pack_id) {
        $sql = 'SELECT id, pack_id, post_id, unixtime FROM `' . TBL_MGM_POST_PACK_POST_ASSOC . '` WHERE pack_id =  ' . $pack_id;
        $return = $wpdb->get_results($sql);
    }
    
    return $return;
}
*/

/*// check post hide?
function mgm_content_protection() {
	return (mgm_get_setting('hide_posts') == 'Y') ? true : false;
}*/

// deep array merge recursive
/*function mgm_array_merge_deep($value1,$value2) {
	// return merged, wrapper for attending any bug later on
	return  array_merge_recursive($value1,$value2);	
}*/


// deprecated : not used
/*function mgm_get_userdatabylogin($user_login='') {
	// login	
	if (isset($_GET[$user_login])) {
		$return = get_user_by('login', $_GET[$user_login]);
	} else if($login){
		$return = get_user_by('login', $user_login);
	} else {
		// current
		$current_user = wp_get_current_user();
		$return = get_userdata($current_user->ID);
	}
	// return
	return $return;
}*/

/**
 * get url
 *
 * @deprecated
 */
/*function mgm_get_url() {
	$url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	$http = ($_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	return $http . $url ;
}*/

/**
 * remote request
 * @deprecated
 */
/*function mgm_remote_request($url, $error_string=true, $method='curl') {	
	// init
    $string = '';
    // check curl   
	if (extension_loaded('curl') && $method == 'curl') {		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$string = curl_exec($ch);
		// check error
		if(($errno = curl_errno($ch)) != CURLE_OK ){
			// on
		    if ($error_string) {
				$error  = curl_error($ch);
				$string = ($errno == 7) ? sprintf('%s "%s"',$error,parse_url($url,PHP_URL_HOST)) : $error ;				
			}
		}
		curl_close($ch);
	// check url fopen	
	}else if (ini_get('allow_url_fopen') && $method == 'fopen') {
		if (!$string = @file_get_contents($url)) {
            if ($error_string) {
				$string = sprintf(__('Could not connect to "%s", request failed.','mgm'), parse_url($url,PHP_URL_HOST));
            }
		}	 	
	} else if ($error_string) {
	    $string = __('This feature will not function until either CURL or fopen to urls is turned on.','mgm');
	}
	
	// return
	return $string;
}*/

/**
 * remote post
 * @deprecated
 */

//function mgm_remote_post_x($url, $post_fields=NULL, $auth='', $http_header=array()) {			
	// set headers	
	//$headers   = array();	
	//$headers[] = "User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.11) Gecko/20071127 Firefox/2.0.0.11";
	//$headers[] = "Accept: text/xml,application/xml,application/xhtml+xml,text/html,application/json;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
	//$headers[] = "Accept-Language: en-us,en;q=0.5";
	//$headers[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
	/*$headers[] = "Keep-Alive: 300";
	$headers[] = "Connection: keep-alive";
	$headers[] = "Content-Type: application/x-www-form-urlencoded";
	$headers[] = "Content-Length: " . strlen($fields);
	
	// init
    $ch = curl_init();	
	// set other params
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_USERAGENT,'Magic Members Membership Software');//$_SERVER['HTTP_USER_AGENT']
	curl_setopt($ch, CURLOPT_TIMEOUT, 5);		
	// post
	if($post_fields){
		curl_setopt($ch, CURLOPT_POST, true);			
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
	}
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);		
	curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl'));	
	// auth
	if($auth){
		curl_setopt($ch, CURLOPT_USERPWD, $auth);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	}
	// get result
	$response = curl_exec($ch);					
	curl_close($ch);
	// return
	return $response;
}*/

// log
	// mgm_log($jqueryui_version, __FUNCTION__);

	/*// compare version if greater than 2.9
	if (version_compare(get_bloginfo('version'), '2.9', '>=') && version_compare(get_bloginfo('version'), '3.0', '<')){
		// ui 1.7.3 for jQuery 1.4+ options : 1.7.3 , 1.8.2
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.7.3';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}
	// compare version if greater than 3.5 issue #1182
	}else if (version_compare(get_bloginfo('version'), '3.5', '>=')){
		// 1.9.2
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.9.2';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}	
	// compare version if greater than 3.6 issue #1182
	}else if (version_compare(get_bloginfo('version'), '3.6', '>=')){
		// 1.10.3
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.10.3';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}	
	// compare version if greater than 3.0 issue #1010
	}else if (version_compare(get_bloginfo('version'), '3.0', '>=')){
		// 1.8.16
		if( ! $jqueryui_version = get_option('mgm_jqueryui_version') ){// not defined, use as coded
			$jqueryui_version = '1.8.16';		
			update_option('mgm_jqueryui_version', $jqueryui_version); // and update		 
		}
	}else {
		// ui 1.7.2 for jQuery 1.3.2+
		$jqueryui_version = '1.7.2';			 
	}
	*/

/*
function mgm_notify_user_membership_cancellation2(){
	
	// email	
	$subject = sprintf(__('[%s] Subscription Cancelled','mgm'),$blogname);				
	$message = __('This is an automatic notification from %1$s to %2$s (%3$s). This is a notification to inform you that your subscription has been cancelled. For more information please contact %4$s','mgm');
	$message = sprintf($message, $blogname, $user->display_name, $user->user_email, $system_obj->setting['admin_email']);
	
	// send email notification to user
	mgm_mail($user->user_email, $subject, $message);	
}*/	

/*function mgm_notify_admin_membership_cancellation2(){
	// notify admin, only if gateway emails on
	if (!$dge) {
		$subject = "[$blogname] {$user->user_email} - {$new_status}";
		$message = "	User display name: {$user->display_name}\n\n<br />
				User email: {$user->user_email}\n\n<br />
				User ID: {$user->ID}\n\n<br />
				Membership Type: {$membership_type}\n\n<br />
				New status: {$new_status}\n\n<br />
				Status message: {$member->status_str}\n\n<br />					
				Payment Mode: Cancelled\n\n<br />
				POST Data was: \n\n<br /><br /><pre>" . print_r($_POST, true) . '</pre>';
		mgm_mail($system_obj->setting['admin_email'], $subject, $message);
	}
}*/

/*function mgm_notify_admin_membership_cancellation_manual_removal_required2($user_id){
	$user = get_userdata($user_id);
	//send notification email to admin:
	$message = (__('The User: ', 'mgm')). $user->user_email.' ('. $user_id .') '.(__('has upgraded/cancelled subscription.', 'mgm'));
	$message .= "<br/>" .__('Please unsubscribe the user from Gateway Merchant panel.', 'mgm');
	if(!empty($rebill['rebill_customer_id']))
		$message .= "<br/><br/>" .__('Customer Rebill Id: ','mgm' ) . $rebill['rebill_customer_id'];
	if(!empty($rebill['rebill_id']))
		$message .= "<br/><br/>" .__('Rebill Id: ','mgm' ) . $rebill['rebill_id'];	
	if(isset($member->transaction_id))				
		$message .= "<br/>" .__('MGM Transaction Id:' ,'mgm' ) . $member->transaction_id;		
	//admin email:
	if(!empty($system_obj->setting['admin_email']))
		@mgm_mail($system_obj->setting['admin_email'], sprintf(__('[%s] User Subscription Cancellation', 'mgm'), get_option('blogname')), $message);
}*/

/*// blog
		$blogname = get_option('blogname');

		//getting purchase post title and & price - issue #981				
		$post_obj = mgm_get_post($post_id);
		$purchase_cost = mgm_convert_to_currency($post_obj->purchase_cost);
		$post = get_post($post_id);
		$post_title = $post->post_title;	*/	
		/*// emails not for guest
		if( $user_id ){			
			//update coupon usage - issue #1421
			do_action('mgm_update_coupon_usage', array('user_id' => $user_id));										  
		}		

					

		// mark as purchased
		if(isset($guest_token)){
			// issue #1421
			if(isset($coupon_id) && isset($coupon_code)) {
				do_action('mgm_update_coupon_usage', array('guest_token' => $guest_token,'coupon_id' => $coupon_id));
				$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['custom'],$coupon_code);
			}else {
				$this->_set_purchased(NULL, $post_id, $guest_token, $_REQUEST['custom']);				
			}
		}else{
			$this->_set_purchased($user_id, $post_id, NULL, $_REQUEST['custom']);
		}*/
		
		/*

		// notify user		
		if( ! $dpne ) {
			// mail
			if( isset($user_id) && $this->is_payment_email_sent($_REQUEST['custom'])) {	
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// body
				$message = $system_obj->get_template('payment_success_email_template_body', 
										array('blogname'=>$blogname, 'name'=>$user->display_name,
											  'post_title'=>$post_title,'purchase_cost'=>$purchase_cost,  
											  'email'=>$user->user_email, 
											  'admin_email'=>$system_obj->setting['admin_email']), true);
				//issue #862
				$subject = mgm_replace_email_tags($subject,$user_id);
				$message = mgm_replace_email_tags($message,$user_id);
				
				// mail			
				mgm_mail($user->user_email, $subject, $message); //send an email to the buyer
				//update as email sent 
				$this->record_payment_email_sent($_REQUEST['custom']);	
			}
		}		
				
		// notify admin, only if gateway emails on 
		if (!$dge) {
			// not for guest
			if($user_id){
				// subject
				$subject = "[" . $blogname . "] Admin Notification: " . $user->user_email . " purchased post " . $post_id;
				// message
				$message = "User display name: {$user->display_name}<br />
							User email: {$user->user_email}<br />
							User ID: {$user->ID}<br />Status: " . $status . "<br />
							Action: Purchase post:" . $subject . "<br /><br />" . 
							$message;
			}else{
				$subject = "[" . $blogname . "] Admin Notification: Guest[IP: ".mgm_get_client_ip_address()."] purchased post " . $post_id;
				$message = "Guest Purchase";
			}			
			// mail
			mgm_mail($system_obj->setting['admin_email'], $subject, $message);
		}*/

///////////
/**
 * show buttons of modules available for upgrade/downgrade
 * moved complete payment to own method for simplicity
 *
 * @param array args
 * @return string html
 * 
 */
function mgm_get_upgrade_buttons($args=array(), $user=null) { 
	global $wpdb;	

	// current user
	if( ! $user ){
		$user = wp_get_current_user();
	}
	
	// validate
	if( ! $user->ID ) {
		return __('User must be logged in!', 'mgm');
	}
	
	// userdata
	$username = $user->user_login;
	$mgm_home = get_option('siteurl');
	// upgrdae multiple
	$multiple_upgrade = false;
	//issue #1511
	$prev_pack_id = mgm_get_var('prev_pack_id', '', true);
	$prev_membership_type = mgm_get_var('membership_type', '', true);
	$upgrade_prev_pack = mgm_get_var('upgrade_prev_pack', '', true);

	// get member
	// issue#: 843 (3)
	if(isset($prev_pack_id) && (int)$prev_pack_id > 0 && isset($prev_membership_type) && !empty($prev_membership_type)) {
		// only for multiple membership upgrade
		$multiple_upgrade = true;
		// get member
		$member = mgm_get_member_another_purchase($user->ID, $prev_membership_type, $prev_pack_id);
		// mark status as inactive
		$member->status = MGM_STATUS_NULL;		
	}else {
		$member = mgm_get_member($user->ID);
		
		//this is a fix for issue#: 589, see the notes for details:
		//This is to read saved coupons as array in order to fix the fatal error on some servers.	
		//This will change the object on each users profile view.
		//Also this will avoid using patch for batch update,	
		$old_coupons_found = 0;
		// loop		
		foreach (array('upgrade', 'extend') as $coupon_type) {
			// check
			if(isset($member->{$coupon_type}['coupon']) && is_object($member->{$coupon_type}['coupon'])) {
				// convert
				$member->{$coupon_type}['coupon'] = (array) $member->{$coupon_type}['coupon'];
				// mark
				$old_coupons_found++ ;
			}
		}
		// save if old coupons found
		if($old_coupons_found) $member->save();		
	}
	
	// other objects
	$system_obj = mgm_get_class('system');	
	$packs_obj  = mgm_get_class('subscription_packs');	
	// membership_type
	$membership_type = (isset($prev_membership_type) && !empty($prev_membership_type)) ? $prev_membership_type : mgm_get_user_membership_type($user->ID, 'code');// captured above	
	
	// duration	
	$duration_str = $packs_obj->duration_str;
	$trial_taken  = $member->trial_taken;	
	// pack_id if main mgm_member / multiple membership	
	$pack_id = (isset($prev_pack_id) && (int)$prev_pack_id > 0) ? $prev_pack_id : (int)$member->pack_id;
	// got pack
	if($pack_id) {
		$pack_details = $packs_obj->get_pack($pack_id);
		$pack_membership_type = $pack_details['membership_type'];
		$preference = $pack_details['preference'];
	}else {
		$preference = 0;
	}
	
	// action - issue #1275	
	$action = mgm_get_var('action', '', true);
	
	// complete payment		
	/*if( in_array($action, array('complete_payment','resubscribe')) ) {
		// get active packs on complete payment page	
		$active_packs = $packs_obj->get_packs('register', true, null, $pack_id);	
	}else {*/
		// get active packs on upgrade page	
		$active_packs = $packs_obj->get_packs('upgrade');		
		//issue #1368
		// loop and preference		
		foreach ($active_packs as $_pack) {						
			// set preference order for later sort
			$pack_preferences[] = $_pack['preference'];
		}
		
		// preference sort packs
		if(count($pack_preferences)>0){
			// preference sort
			sort($pack_preferences);			
			//preference sorted
			$preferences_sorted = array();
			// loop by preference
			foreach($pack_preferences as $pack_preference){
				//issue #1710 & 2591
				if($pack_preference >= $preference){
					// loop packs
					foreach ($active_packs as $_pack) {
						// preference order match
						if($_pack['preference'] == $pack_preference){
							// duplicate check
							if(!in_array($_pack['id'], $preferences_sorted)){
								// set pack
								$preference_packs[] = mgm_stripslashes_deep($_pack);							
								// mark as preference sorted
								$preferences_sorted[] = $_pack['id'];
							}
						}
					}
				}
			}
		}			
		
		$active_packs = $preference_packs;			
	//}
	
	// issue#: 664
	// action : upgrade/complete_payment. Allow complete payment only if there is an associated $pack_id and the current subscription is not free/trial
	$action = (!empty($action) && (int)$pack_id > 0) ? $action : 'upgrade'; // upgrade or complete_payment	
	// show current
	//echo $action;
	$show_current_pack = false;	

	// switch
	/*if($action == 'complete_payment' && isset($pack_membership_type) && in_array($pack_membership_type, array('free', 'trial'))) {
		// upgrade 
		$action = 'upgrade';
		// show current
		$show_current_pack = true;		
	}*/

	// issue#: 2709
	//if($action == 'upgrade') {
		$show_current_pack = true;
	//}
	// form action
	// carry forward get params	
	$url_parms  = array('action' => $action, '__utoken__' => mgm_get_auth_token($user->ID));// 'username'=> $username,
	
	// prev_membership_type
	if (isset($prev_membership_type) && !empty($prev_membership_type)) {
		$url_parms['membership_type'] = $prev_membership_type;
	}
		
	// prev_pack_id
	if (isset($prev_pack_id) && !empty($prev_pack_id)) {
		$url_parms['prev_pack_id'] = $prev_pack_id;	
	}
		
	// upgrade previous pack id
	if (isset($upgrade_prev_pack) && !empty($upgrade_prev_pack)) {
		$url_parms['upgrade_prev_pack'] = $upgrade_prev_pack;	
	}
		
	// form action
	$form_action = mgm_get_custom_url('transactions', false, $url_parms);

	// issue 1009
	if( ! $membership_details_url = $system_obj->get_setting('membership_details_url') ){		
		$membership_details_url = get_admin_url() . 'profile.php?page=mgm/profile';
	}
	
	// cancel 
	$cancel_url = ($action == 'upgrade' && $user->ID > 0) ? $membership_details_url : mgm_get_custom_url('login');
	
	// active modules
	$a_payment_modules = $system_obj->get_active_modules('payment');	
		
	// bug from liquid-dynamiks.com theme #779
	if( isset($_POST['wpsb_email']) ) unset($_POST['wpsb_email']);
	
	// posted form-----------------------------------------------------------------------	
	//if( ! empty($_POST) || (isset($_GET['edit_userinfo']) && (int)$_GET['edit_userinfo'] == 1) ){			
		// update user data
		/*if(isset($_POST['method']) && $_POST['method'] == 'update_user'){
			// user lib
			if ( mgm_compare_wp_version('3.1', '<') ){// only before 3.1
				require_once( ABSPATH . WPINC . '/registration.php');
			}
			// callback
			// do_action('personal_options_update', $user->ID);	
			// not multisite, duplicate email allowed ?	
			if ( ! is_multisite() ) {
				// save
				$errors = mgm_user_profile_update($user->ID);
			}else {
			// multi site
				// get user
				$user = get_userdata( $user->ID );
				// update here:
				// Update the email address, if present. duplicate check
				if ( $user->user_login && isset( $_POST[ 'user_email' ] ) && is_email( $_POST[ 'user_email' ] ) && $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM {$wpdb->signups} WHERE user_login = '%s'", $user->user_login ) ) )
					$wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->signups} SET user_email = '%s' WHERE user_login = '%s'", $_POST[ 'user_email' ], $user->user_login ) );
				
				// edit 
				if ( !isset( $errors ) || ( isset( $errors ) && is_object( $errors ) && false == $errors->get_error_codes() ) )
					$errors = mgm_user_profile_update($user->ID);
			}
			
			// errors
			if(isset($errors) && !is_numeric($errors)) {				
				// get error
				$error_html = mgm_set_errors($errors, true);
				// edit flag
				$_GET['edit_userinfo'] = 1;
			}	
		}*/	

		//echo $form_action; die;
		
		// second step for complete payment, userdata edit
		/*if(isset($_GET['edit_userinfo']) && (int)$_GET['edit_userinfo'] == 1){	
			// error
			if(isset($error_html)){
				$html .= $error_html;
			}		
			// form
			$html .= sprintf('<form action="%s" method="post" class="mgm_form">', $form_action);
			$html .= sprintf('<p>%s</p>', __('Edit Your Personal Information', 'mgm'));
			// get custom fields
			$html .= mgm_user_profile_form($user->ID, true);

			$pack_ref = mgm_get_pack_ref($member);

			// html
			$html .= '<input type="hidden" name="ref" value="'. $pack_ref .'" />';					
			$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
			$html .= '<input type="hidden" name="subs_opt" value="'. $_POST['subs_opt'] .'" rel="mgm_subscription_options"/>';	
			
			//issue #2226
			if(isset($_POST['mgm_upgrade_field']['autoresponder']) && !empty($_POST['mgm_upgrade_field']['autoresponder'])) {
				$html .= '<input type="hidden" name="mgm_upgrade_field[autoresponder]" value="'. $_POST['mgm_upgrade_field']['autoresponder'] .'" class="mgm_upgrade_field">';
			}			
			// carry forward mgm_payment_gateways field value: issue#: 919
			if(isset($_POST['mgm_payment_gateways']))
				$html .= '<input type="hidden" name="mgm_payment_gateways" value="'. $_POST['mgm_payment_gateways'] .'" />';
			//issue #1236
			if(isset($_POST['mgm_upgrade_field']['coupon']) && !empty($_POST['mgm_upgrade_field']['coupon'])) {
				//issue #1250 - Coupon validation 
				if(!empty($_POST['form_action'])) {				
					//issue #1591
					$coupon_err_redirect_url= $_POST['form_action'];
					if(preg_match('/complete_payment/', $coupon_err_redirect_url)){						
						$coupon_err_redirect_url =	str_replace('&edit_userinfo=1','',$coupon_err_redirect_url);
					}					
					// check if its a valid coupon
					if(!$coupon = mgm_get_coupon_data($_POST['mgm_upgrade_field']['coupon'])){				
						//redirect back to the form							
						$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
						$redirect = add_query_arg($q_arg, $coupon_err_redirect_url);														
						mgm_redirect($redirect);
						exit;
					}else{
						// get subs 			
						if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){	
							// values
							$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
							// check
							if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
								$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
								$q_arg = array(	'error_field' => 'Coupon', 
											   	'error_type' => 'invalid',
											   	'membership_type' => $coupon_values['new_membership_type'],
											   	'error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
								$redirect = add_query_arg($q_arg, $coupon_err_redirect_url);														
								mgm_redirect($redirect);
								exit;							
							}
						}	
					}
				}			
				$html .= '<input type="hidden" name="mgm_upgrade_field[coupon]" value="'. $_POST['mgm_upgrade_field']['coupon'] .'" class="mgm_upgrade_field">';
			}
			// set
			$html .= sprintf('<p>
								 <input class="button button-primary" type="button" name="back" onclick="window.location=\'%s\'" value="%s" />						
							 	 <input class="button button-primary" type="submit" name="submit" value="%s" />&nbsp;&nbsp;
						      	 <input class="button button-primary" type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" />&nbsp;					
					          </p>', $form_action, __('Back','mgm'), __('Save & Next','mgm'), $cancel_url, __('Cancel','mgm'));
			// html
			$html .= '</form>';
		
		}else*/

		// final step, show payment buttons
		if( isset($_POST['submit']) ) {		
			//mgm_pr($_POST);
			// verify selected
			if( ! isset($_POST['subs_opt']) || (isset($_POST['subs_opt']) && empty($_POST['subs_opt'])) ){
				// die
				return sprintf(__('Package not selected, <a href="%s">go back</a>.','mgm'), $_POST['form_action']); exit;
			}	

			$pack_ref = mgm_get_pack_ref($member);
			
			// check and validate passed data		
			if ($_POST['ref'] != $pack_ref) {
				// die
				return __('Package data tampered. Cheatin!','mgm'); exit;				
			}
			
			// get selected pack 			
			$selected_pack = mgm_decode_package($_POST['subs_opt']);

	        //mgm_pr($_POST);
			//mgm_pr($selected_pack);
			
			// check selected pack is a valid pack		     
			$valid = false;
			// loop packs
			foreach($active_packs as $pack) {
				// check
				if ($pack['cost'] == $selected_pack['cost'] 
					&& $pack['duration'] == $selected_pack['duration'] 
					&& $pack['duration_type'] == $selected_pack['duration_type'] 
					&& $pack['membership_type'] == $selected_pack['membership_type']
					&& $pack['id'] == $selected_pack['pack_id'] 
					) 
				{
					// valid
					$valid = true; break;
				}
			}
			// error
			if ( ! $valid) {  
				return __('Invalid package data. Cheatin!','mgm'); exit;	
			}
			
			//update description if not set
			if(!isset($selected_pack['description'])) {
				$selected_pack['description'] = $pack['description'];
			}
			
			//update pack currency - issue #1602
			if(isset($pack['currency']) && !empty($pack['currency'])) {
				$selected_pack['currency'] = $pack['currency'];
			}			
			// num cycle
			if(!isset($selected_pack['num_cycles'])) {
				//Note the above break in for loop:
				$selected_pack['num_cycles'] = $pack['num_cycles'];
			}		
			//issue#: 658
			if(isset($pack['role'])) {
				$selected_pack['role'] = $pack['role'];
			}		
			//applicable modules:
			$selected_pack['modules'] = $pack['modules']; 
			$selected_pack['product'] = $pack['product']; 
			// trial
			if($pack['trial_on']) {
				$selected_pack['trial_on'] 			  = $pack['trial_on']; 
				$selected_pack['trial_duration'] 	  = $pack['trial_duration']; 
				$selected_pack['trial_duration_type'] = $pack['trial_duration_type']; 
				$selected_pack['trial_cost'] 		  = $pack['trial_cost']; 
				$selected_pack['trial_num_cycles'] 	  = $pack['trial_num_cycles']; 
			}
			// save member data including coupon etc, MUST save after all validation passed, we dont want any 
			// unwanted value in member object unless its a valid upgrade			
			// save
			if ($multiple_upgrade) {
				$member = mgm_save_partial_fields(array('on_upgrade'=>true),'mgm_upgrade_field', $selected_pack['cost'], true, strip_tags($_GET['action']), $member);
			}else {
				$member = mgm_save_partial_fields(array('on_upgrade'=>true),'mgm_upgrade_field', $selected_pack['cost'], true, strip_tags($_GET['action']));
			}
			//save custom fields issue #1285
			if(isset($_POST['mgm_upgrade_field']) && !empty($_POST['mgm_upgrade_field'])) {					
				//upgrade custom fields
				$cfu_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_upgrade'=>true)));			
				//loop fields
				foreach($cfu_fields as $cf_field){
					//skip coupon and autoresponder
					if(in_array($cf_field['name'], array('coupon','autoresponder')) || $cf_field['type'] =='html') { continue; }
					// check upgrae and required		
					if((bool)$cf_field['attributes']['required'] === true){								
						//check
						if(isset($_POST['mgm_upgrade_field'][$cf_field['name']]) && empty($_POST['mgm_upgrade_field'][$cf_field['name']])){
							//redirect back to the form							
							$q_arg = array('error_field' => $cf_field['label'], 'error_type' => 'empty','error_field_value'=>$_POST['mgm_upgrade_field'][$cf_field['name']]);
							$redirect = add_query_arg($q_arg, $_POST['form_action']);														
							mgm_redirect($redirect);
							exit;									
						}else if($cf_field['name'] !='autoresponder' && $cf_field['type'] =='checkbox' && !isset($_POST['mgm_upgrade_field'][$cf_field['name']])) {
							//redirect back to the form							
							$q_arg = array('error_field' => $cf_field['label'], 'error_type' => 'empty','error_field_value'=>$_POST['mgm_upgrade_field'][$cf_field['name']]);
							$redirect = add_query_arg($q_arg, $_POST['form_action']);														
							mgm_redirect($redirect);
							exit;							
						}											
					}					
					//check	- issue #2042
					if(isset($_POST['mgm_upgrade_field'][$cf_field['name']])){					
						//appending custom fields
						if(isset($member->custom_fields->$cf_field['name'])){
							$member->custom_fields->$cf_field['name'] = $_POST['mgm_upgrade_field'][$cf_field['name']];
						}else {
							$member->custom_fields->$cf_field['name'] = $_POST['mgm_upgrade_field'][$cf_field['name']];
						}											
 					}
				}
				$member->save();					
			}
			
			//issue #860
			if (isset($_POST['mgm_upgrade_field']['autoresponder']) && bool_from_yn($_POST['mgm_upgrade_field']['autoresponder']) ) {
				$member->subscribed    = 'Y';
				$member->autoresponder = $system_obj->active_modules['autoresponder'];
				//issue #1511
				if ($multiple_upgrade){
					mgm_save_another_membership_fields($member, $user->ID);
				}else {
					$member->save();
				}			
			//issue #1276
			}else {
				$member->subscribed    = '';
				$member->autoresponder = '';
				//issue #1511
				if ($multiple_upgrade){
					mgm_save_another_membership_fields($member, $user->ID);
				}else {
					$member->save();
				}			
			}
			//issue #1236
			if(isset($_POST['mgm_upgrade_field']['coupon']) && !empty($_POST['mgm_upgrade_field']['coupon'])) {
				//issue #1250 - Coupon validation 
				if(!empty($_POST['form_action'])) {				
					// check if its a valid coupon
					if(!$coupon = mgm_get_coupon_data($_POST['mgm_upgrade_field']['coupon'])){				
						//redirect back to the form							
						$q_arg = array('error_field' => 'Coupon', 'error_type' => 'invalid','error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
						$redirect = add_query_arg($q_arg, $_POST['form_action']);														
						mgm_redirect($redirect);
						exit;
					}else{
						// get subs 			
						if( $subs_pack = mgm_decode_package(mgm_post_var('subs_opt')) ){	
							// values
							$coupon_values = mgm_get_coupon_values(NULL, $coupon['value'], true);
							// check
							if(isset($coupon_values['new_membership_type']) && $coupon_values['new_membership_type'] != $subs_pack['membership_type']){
								$new_membership_type = mgm_get_membership_type_name($coupon_values['new_membership_type']);							
								$q_arg = array(	'error_field' => 'Coupon', 
											   	'error_type' => 'invalid',
											   	'membership_type' => $coupon_values['new_membership_type'],
											   	'error_field_value'=>$_POST['mgm_upgrade_field']['coupon']);
								$redirect = add_query_arg($q_arg, $_POST['form_action']);														
								mgm_redirect($redirect);
								exit;							
							}
						}	
					}
				}			
			}
			// payment_gateways if set: Eg: $_POST['mgm_payment_gateways'] = mgm_paypal
			$cf_payment_gateways = (isset($_POST['mgm_payment_gateways']) && !empty($_POST['mgm_payment_gateways'])) ? $_POST['mgm_payment_gateways'] : null;				
			// bypass step2 if payment gateway is submitted: issue #: 469
			// removed complete_payment checking here in order to enable coupon for complete_payment. issue#: 802			
			if(!is_null($cf_payment_gateways)) {				
				// get pack				
				mgm_get_upgrade_coupon_pack($member, $selected_pack, strip_tags($_GET['action']));				
				// cost
				if ((float)$selected_pack['cost'] > 0) {
					//get an object of the payment gateway:
					$mod_obj = mgm_get_module($cf_payment_gateways,'payment');
					// tran options
					$tran_options = array('user_id' => $user->ID);
					// is register & purchase
					if(isset($_POST['post_id'])){
						$tran_options['post_id'] = (int)$_POST['post_id'];
					}
					// if multiple membership
					if ($multiple_upgrade) {
						$tran_options['is_another_membership_purchase'] = true; 
						// This is to replace current mgm_member object with new mgm_member object of the upgrade pack
						$tran_options['multiple_upgrade_prev_packid'] = mgm_get_var('prev_pack_id', '', true); 
					}
					// upgrade flag
					if($action == 'upgrade'){
						$tran_options['subscription_option'] = 'upgrade';
						$tran_options['upgrade_prev_pack'] = mgm_get_var('upgrade_prev_pack', '', true);
					}
					// create transaction				
					// $tran_id = $mod_obj->_create_transaction($selected_pack, $tran_options);
					$tran_id = mgm_add_transaction($selected_pack, $tran_options);
					
					//bypass directly to process return if manual payment:				
					if($cf_payment_gateways == 'mgm_manualpay') {
						// set 
						$_POST['custom'] = $tran_id;
						// direct call to module return function:
						$mod_obj->process_return();				
						// exit	
						exit;
					}
					// set redirect
					$redirect = add_query_arg(array( 'tran_id' => mgm_encode_id($tran_id) ), $mod_obj->_get_endpoint('html_redirect', true)); 	
					// redirect	
					mgm_redirect($redirect);// this goes to subscribe, mgm_functions.php/mgm_get_subscription_buttons
					// exit						
					exit;						
				}
			}// end gateway
			// get coupon pack
			mgm_get_upgrade_coupon_pack($member, $selected_pack, strip_tags($_GET['action']));			
			// start html
			$html = '<div>';
			// free package
			if (($selected_pack['cost'] == 0 || $selected_pack['membership_type'] == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->enabled=='Y') {	
				// html		
				$html .= sprintf('<div>%s - %s</div>', __('Create a free account ','mgm'), ucwords($selected_pack['membership_type']));			
				// module
				$module = 'mgm_free';
				// payments url
				$payments_url = mgm_get_custom_url('transactions');			
				// if tril module selected and cost is 0 and free moduleis not active
				if($selected_pack['membership_type'] == 'trial'){
					// check
					if(in_array('mgm_trial', $a_payment_modules)){
						// module
						$module = 'mgm_trial';
					}
				}
				// query_args -issue #1005
				$query_args = array(
					'method' => 'payment_return', 'module'=>$module, 
					'custom' => implode('_', array($user->ID, $selected_pack['duration'], $selected_pack['duration_type'], $selected_pack['pack_id'],'N',$selected_pack['membership_type']))
				);
				// redirector
				if(isset($_REQUEST['redirector'])){
					// set
					$query_args['redirector'] = $_REQUEST['redirector'];
				}
				// redirect to module to mark the payment as complete
				$redirect = add_query_arg($query_args, $payments_url);			
				// redirect
				if (!headers_sent()) {							
					@header('location: ' . $redirect);
				}else{
				// js redirect
					$html .= sprintf('<script type="text/javascript">window.location = "%s";</script><div>%s</div>', $redirect, $packs_obj->get_pack_desc($pack));
				}			
			} else {		
			// paid package, generate buy buttons
				// set html	
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', $packs_obj->get_pack_desc($selected_pack));
				// coupon			
				if(isset($member->upgrade) && is_array($member->upgrade) && isset($member->upgrade['coupon']['id'])){	
					// set html 
					$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', sprintf(__('Using Coupon "%s" - %s','mgm'), $member->upgrade['coupon']['name'], $member->upgrade['coupon']['description']));
				}
				// set html
				$html .= sprintf('<div class="mgm_get_subs_btn">%s</div>', __('Please Select from Available Payment Gateways','mgm'));
			}			
			// init 
			$payment_modules = array();			
			// active
			if(count($a_payment_modules)>0){
				// loop
				foreach($a_payment_modules as $payment_module){
					// not trial
					if(in_array($payment_module, array('mgm_free','mgm_trial'))) continue;	
					// consider only the modules assigned to pack
					if(isset($selected_pack['modules']) && !in_array($payment_module, (array)$selected_pack['modules'])) continue;			
					// store
					$payment_modules[] = $payment_module;					
				}
			}
			
			// loop payment module if not free		
			if (count($payment_modules) && $selected_pack['cost']) {
				// transaction
				$tran_id = false;
				$tran_options = array('user_id' => $user->ID);	
				// if multiple membership					
				if ($multiple_upgrade) {
					// another
					$tran_options['is_another_membership_purchase'] = true; 
					// This is to replace current mgm_member object with new mgm_member object of the upgrade pack
					$tran_options['multiple_upgrade_prev_packid'] = mgm_get_var('prev_pack_id', '', true); 
				}	
				// upgrade
				if($action == 'upgrade'){
					$tran_options['subscription_option'] = 'upgrade';
					$tran_options['upgrade_prev_pack'] = mgm_get_var('upgrade_prev_pack', '', true);
				}
				// loop
				foreach($payment_modules as $module) {
					// module
					$mod_obj = mgm_get_module($module,'payment');	
					// create transaction
					// if(!$tran_id) $tran_id = $mod_obj->_create_transaction($selected_pack, $extra_options);
					if(!$tran_id) $tran_id = mgm_add_transaction($selected_pack, $tran_options);
					// set html				
					$html .= sprintf('<div>%s</div>', $mod_obj->get_button_subscribe(array('pack'=>$selected_pack,'tran_id'=>$tran_id)));
				}
				// mgm_pr($_REQUEST);
				// profile edit #698
				/*if($_GET['action'] == 'complete_payment'){
					// update $form_action for user data edit
					if(isset($_COOKIE['wp_tempuser_login']) && $_COOKIE['wp_tempuser_login'] == $user->ID && !isset($_GET['edit_userinfo'])){
						// form action
						$form_action = add_query_arg(array('edit_userinfo'=>1), $form_action);
						// action
						$html .= sprintf('<form action="%s" method="post" class="mgm_form">', $form_action);	
						$pack_ref = mgm_get_pack_ref($member);					
						$html .= '<input type="hidden" name="ref" value="'. $pack_ref .'" />';					
						$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
						$html .= '<input type="hidden" name="subs_opt" value="'. $_POST['subs_opt'] .'" rel="mgm_subscription_options"/>';	
						// set
						$html .= sprintf('<p><input type="button" name="back" onclick="window.location=\'%s\'" value="%s" class="button-primary" />	
											 <input type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" class="button-primary" />&nbsp;					
										  </p>', $form_action, __('Edit Personal Information','mgm'), $cancel_url, __('Cancel','mgm'));
						// html
						$html .= '</form>';
					}					
				}*/
			} else {
			// no module error
				if($selected_pack['cost']){		
					// set html	
					$html .= sprintf('<div>%s</div>', __('Error, no payment gateways active on upgrade page, notify administrator.','mgm'));
				}
			}
			// html
			$html .= '</div>';
		}// end final step post 
	//}else{

		// generate upgrade/complete payment form
		// selected subscription, from args (shortcode) or get url	
		$selected_pack = mgm_get_selected_subscription($args);		
		$css_group = mgm_get_css_group();				
		// upgrade_packages
		$upgrade_packages = '';		
		// pack count
		$pack_count = 0;
		// pack to modules
		$pack_modules = array();	
		//mgm_pr($active_packs);
		//issue #1553		
		if(!empty($active_packs)) {
			// loop	packs	
			foreach($active_packs as $pack) {	
				// mgm_pr($pack);			
				// default			
				$checked = '';
				// for complete payment only show purchased pack
				/*if($action == 'complete_payment'){
					// pack selected
					if(isset($pack_id)){
						// leave other pack, if not show other packs
						if($pack['id'] != $pack_id && !isset($_GET['show_other_packs'])) continue;									
	
						// select 
						if($pack['id'] == $pack_id) $checked='checked="checked"';
					}
				}else{*/
				//  'upgrade':
				// upgrade
					// echo '<br>pack#' . $pack['id'] . ' step1';
					// leave current pack, it will goto extend
					if(isset($pack_id)){						
						if(!$show_current_pack && $pack['id'] == $pack_id) continue;	
					}
					
					// echo '<br>pack#' . $pack['id'] . ' step2';
					// skip trial or free packs
					if(in_array($pack['membership_type'], array('trial','free'))) continue;
					
					// echo '<br>pack#' . $pack['id'] . ' step3';
					// skip if not allowed
					if(!mgm_pack_upgrade_allowed($pack)) continue;		
					
					// echo '<br>pack#' . $pack['id'] . ' step4';
					
					// selected pack
					if($selected_pack !== false){
						// checked
						$checked = mgm_select_subscription($pack, $selected_pack);																				
						// skip other when a package sent as selected
						if( empty($checked) ) {
							continue; 					
						}	
					}
					
					// echo '<br>pack#' . $pack['id'] . ' step5';				
				//}				
				
				// checked
				if(!$checked) $checked = ((int)$pack['default'] == 1 ? ' checked="checked"': ''); 
				
				// duration                      
				if ($pack['duration'] == 1) {
					$dur_str = rtrim($duration_str[$pack['duration_type']], 's');
				} else {
					$dur_str = $duration_str[$pack['duration_type']];
				}
				
				// encode pack
				$subs_opt_enc = mgm_encode_package($pack);			
				
				// set 
				$pack_modules[$subs_opt_enc] = $pack['modules'];

				// free
				if (($pack['cost'] == 0 || strtolower($pack['membership_type']) == 'free') && in_array('mgm_free', $a_payment_modules) && mgm_get_module('mgm_free')->is_enabled()) {
					// input
					$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
					// html				
					$upgrade_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">							
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';
				} else {
					// input
					$input = sprintf('<input type="radio" %s class="checkbox" name="subs_opt" value="%s" rel="mgm_subscription_options"/>', $checked, $subs_opt_enc);
					// html
					$upgrade_packages .= '  
						<div class="mgm_subs_wrapper '.$pack['membership_type'].'">
							<div class="mgm_subs_option '.$pack['membership_type'].'">
								' . $input . '
							</div>
							<div class="mgm_subs_pack_desc '.$pack['membership_type'].'">
								' . $packs_obj->get_pack_desc($pack) . '
							</div>
							<div class="clearfix"></div>
							<div class="mgm_subs_desc '.$pack['membership_type'].'">
								' . mgm_stripslashes_deep($pack['description']) . '
							</div>
						</div>';				
				}	
				// count
				$pack_count++;		
			}
		}
		// start
		$html = '';
		
		// html
		if($pack_count > 1){
			$html .= sprintf('<p class="message register">%s</p>', __('Please Select from Available Membership Packages','mgm'));	
		}	

		// add pack_modules as json data, may consider jquery data later
		if( ! empty( $pack_modules ) ){
			$html .= sprintf('<script type="text/javascript">var mgm_pack_modules = %s</script>', json_encode($pack_modules));
		}

		//issue #867
		if($css_group != 'none') {		
			// set css
			$html .= sprintf('<link rel="stylesheet" href="%s/css/%s/mgm.form.fields.css" type="text/css" media="all" />', untrailingslashit(MGM_ASSETS_URL), $css_group);
		}
		
		// show error when no upgrde
		if( ! $upgrade_packages ){
			// html
			$html .= '<div class="mgm_subs_wrapper">
						<div  class="mgm_subs_pack_desc">
							' . __('Sorry, no upgrades available.','mgm') . '
						</div>
					  </div>
					  <p>						
					  	  <input type="button" name="cancel" onclick="window.location=\''.$cancel_url.'\'" value="'.__('Cancel','mgm').'" class="button-primary" />&nbsp;					
					  </p>';
		}else{									
			// edit/other pack link
			$edit_button  = $other_packs_button = '';
			// issue #: 675, issue #1279
			/*if($action == 'complete_payment' || (isset($_REQUEST['action']) && $_REQUEST['action'] =='complete_payment')){
				// issue#: 416
				// mgm_pr($_GET);
				if(isset($_GET['show_other_packs'])){
					// other packs url - issue #1279, #1215 update, other packs url missed username
					$other_packs_url = add_query_arg(array('action' => 'complete_payment','usertoken' => mgm_get_auth_token($user->ID)), mgm_get_custom_url('transactions'));	// mgm_get_current_url()
					//$other_packs_url   = str_replace('&show_other_packs=1', '', $other_packs_url);
					$other_packs_label = __('Show subscribed package','mgm') . '';
				}else{
					// other packs url - issue #1279, #1215 update, other packs url missed username
					$other_packs_url = add_query_arg(array('action' => 'complete_payment','show_other_packs'=>1,
					'usertoken' => mgm_get_auth_token($user->ID)),  mgm_get_custom_url('transactions'));// mgm_get_current_url()	
					$other_packs_label = __('Show other packages','mgm');					
				}		
				// issue#: 710
				if(count($active_packs) > 1){
					// button			
					$other_packs_button = sprintf('<input type="button" value="%s" class="button-primary" onclick="window.location=\'%s\'">', $other_packs_label, $other_packs_url);
				}
				
				// update $form_action for user data edit
				if(isset($_COOKIE['wp_tempuser_login']) && $_COOKIE['wp_tempuser_login'] == $user->ID && !isset($_GET['edit_userinfo'])){
					$form_action = add_query_arg(array('edit_userinfo'=>1), $form_action);
				}else {
					//issue #1279
					$form_action = add_query_arg(array('action' => 'complete_payment',
						'usertoken' => mgm_get_auth_token($user->ID),'edit_userinfo'=>1), mgm_get_current_url());			
				}
			}*/			
			
			// echo $form_action;
			
			// check errors if any:
			$html .= mgm_subscription_purchase_errors();	


			$form_action = mgm_secure_query_string($form_action, $user->ID);		
			
			// form
			$html .= sprintf('<form action="%s" method="post" class="mgm_form" name="mgm-form-user-upgrade" id="mgm-form-user-upgrade">', $form_action);
			$html .= sprintf('<div class="mgm_get_pack_form_container">%s', $upgrade_packages);
			//issue #1285
			$html .= mgm_get_custom_fields($user->ID, array('on_upgrade'=>true), 'upgrade', 'mgm-form-user-upgrade');

			$pack_ref = mgm_get_pack_ref($member);

			$html .= '<input type="hidden" name="ref" value="'. $pack_ref .'" />';					
			$html .= '<input type="hidden" name="form_action" value="'. $form_action .'" />';	
			// set
			$html .= sprintf('<p>%s						
							 	 <input class="button button-primary" type="submit" name="submit" value="%s" />&nbsp;&nbsp;
						      	 <input class="button button-primary" type="button" name="cancel" onclick="window.location=\'%s\'" value="%s" />&nbsp;					
					          </p>', $other_packs_button, __('Next','mgm'), $cancel_url, __('Cancel','mgm'));
			// html
			$html .= '</div></form>';
		}
		// end generate form 		
	//}// end	
    
	// return    	
	return $html;	
}		


/**
 * copy function
 *
 * @deprecated
 */
/*
function mgm_get_unsubscribe_status_button_copy(){
	// cancelled
	if($member->status == MGM_STATUS_CANCELLED) {
		$html .= '<div class="mgm_margin_bottom_10px ">'.
					'<h4>'. __('Unsubscribed','mgm').'</h4>'.
					'<div class="mgm_margin_bottom_10px mgm_color_red">'.
						 __('You have unsubscribed.','mgm'). 
					'</div>'.
					'</div>';
	}elseif((isset($member->status_reset_on) && isset($member->status_reset_as)) && $member->status == MGM_STATUS_AWAITING_CANCEL) {
		$lformat = mgm_get_date_format('date_format_long');
		$html .= '<div class="mgm_margin_bottom_10px">'.
				'<h4>'. __('Unsubscribed','mgm').'</h4>'.
				'<div class="mgm_margin_bottom_10px mgm_color_red">'.
					 sprintf(__('You have unsubscribed. Your account has been marked for cancellation on <b>%s</b>.','mgm'), date($lformat, strtotime($member->status_reset_on))). 
				'</div>'.
				'</div>';
	}else {		
		// show unsucscribe button			
		if(!is_super_admin()) {
			if(!empty($member->payment_info->module)) {
				$module = $member->payment_info->module;			
				$obj_module = mgm_get_module($module,'payment');
				if($module && is_object($obj_module) && method_exists($obj_module, 'get_button_unsubscribe')) {
					// output button
					$html .= mgm_get_module($module,'payment')->get_button_unsubscribe(array('user_id'=>$user->ID, 'membership_type' => $member->membership_type));
					$html .= '<script type="text/javascript">'.
							'confirm_unsubscribe=function(element){'.
								'if(confirm("' .__('You are about to unsubscribe. Do you want to proceed?','mgm') . '")){'.																
									'jQuery(element).closest("form").submit();'.
								'}'.								
							'}'.
						'</script>';
				}
			}
		}	
	}
}
*/