<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members email functions
 *
 * @package MagicMembers
 * @since 2.7.2
 */

/**
 * Send Email Notification to Admin
 * Base function for all notifications to admin, control should be applied here
 *
 * @uses mgm_mail()
 * @param string optional $admin_email
 * @param string $subject
 * @param string $message
 * @param string $context
 * @return bool $send
 */
function mgm_notify_admin($admin_email=null, $subject='You have a notification', $message='Notification for Administrator', $context='general'){
	// admin email
	if( ! $admin_email ) $admin_email = mgm_get_setting('admin_email');
	
	// apply callable filter
	$enabled = apply_filters('mgm_notify_admin', true, $context, $admin_email);

	// log
	$msg = sprintf('mgm_notify_admin - context: %s, enabled: %d, admin_email: %s, subject: %s, message: %s', 
		           $context, (int)$enabled, $admin_email, $subject, $message);
	// log
	// mgm_log( $msg, __FUNCTION__ . '_' . $context );

	// enabled
	if( $enabled ){
		// send
		return @mgm_mail( $admin_email, $subject, $message );	
	}

	// pretend as sent
	return true;
}

/**
 * Send Email Notification to User
 * Base function for all notifications to user, control should be applied here
 * 
 * @uses mgm_mail()
 * @param string optional $user_email
 * @param string $subject
 * @param string $message
 * @param string $context
 * @return bool $send
 */
function mgm_notify_user($user_email=null, $subject='You have a notification', $message='Notification for User', $context='general'){
	// current user email
	if( ! $user_email ) $user_email = mgm_get_current_user_email();
	
	// apply callable filter
	$enabled = apply_filters('mgm_notify_user', true, $context, $user_email);

	// log
	$msg = sprintf('mgm_notify_user - context: %s, enabled: %d, user_email: %s, subject: %s, message: %s', 
		           $context, (int)$enabled, $user_email, $subject, $message);
	// log
	// mgm_log( $msg, __FUNCTION__ . '_' . $context );

	// enabled
	if( $enabled ){
		// send
		return @mgm_mail( $user_email, $subject, $message );	
	}

	// pretend as sent
	return true;	
}

// ----- NOTIFY CONTROL START --------------------------------
/**
 * control admin notifications
 * 
 * @param bool
 * @param string
 * @return bool
 * @since 1.8.51
 */
function mgm_notify_admin_control( $enabled, $context, $admin_email ){

	// return
	return $enabled;
}  
// add the callback
add_filter('mgm_notify_admin', 'mgm_notify_admin_control', 10, 3);

/**
 * control user notifications
 * 
 * @param bool
 * @param string
 * @return bool
 * @since 1.8.51
 */
function mgm_notify_user_control( $enabled, $context, $user_email ){

	// base context
	switch( $context ){
		case 'registration_welcome':
			// $enabled = false;
		break;
	}

	// return
	return $enabled;
}  
// add the callback
add_filter('mgm_notify_user', 'mgm_notify_user_control', 10, 3);

// ----- NOTIFY CONTROL END ----------------------------------

// ----- ADMIN NOTIFICATIONS START ---------------------------

/**
 * Send Email Notification to Admin on Post Purchase
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $post
 * @param string $status
 * @return bool $send
 */
function mgm_notify_admin_post_purchase($blogname, $user, $post, $status){
	//post link
	$link =  "<a href=". get_permalink($post->ID).">".$post->post_title."</a>";	
	// not for guest
	if( isset($user->ID) ){
		$subject = sprintf("[%s] Admin Notification - %s purchased post: %s [%d]", 
						   $blogname, $user->user_email, $post->post_title, $post->ID);						
		$message = sprintf("User display name: %s<br />
			                User email: %s<br />
			                User ID: %s<br />
		            		Status: %s<br />
		            		Action: Purchase post <br/>
		            		Post Title: %s 
							Post Link: %s", 
		            		$user->display_name, $user->user_email, $user->ID, $status, $post->post_title,$link);
	}else{
		$subject = sprintf("[%s] Admin Notification - Guest[IP: %s] purchased post: %s [%d]", 
						  $blogname, mgm_get_client_ip_address(), $post->post_title, $post->ID);
						  		
		$message = sprintf("Action: Guest Purchase post <br/>
		            		Post Title: %s 
							Post Link: %s", 
		            		$post->post_title,$link);		
	}

	// return
	return @mgm_notify_admin(null, $subject, $message, 'post_purchase');
}

/**
 * Send Email Notification to Admin on Membership Verification failure
 *
 * @uses mgm_notify_admin()
 * @param string $module_name
 * @return bool $send
 */
function mgm_notify_admin_membership_verification_failed( $module_name ){
	// subject
	$subject = sprintf('Error in membership verification using %s Gateway', $module_name);
	// message
	$message = sprintf('Could not read membership type in the following POST data. <br>				
						Please debug or contact magicmembers to fix the problem making sure to pass on the following data.<br>
						POST DATA: %s<br>', mgm_pr($_POST, true));
	// send
	return @mgm_notify_admin(null, $subject, $message, 'membership_verification_failed');
}

/**
 * Send Email Notification to Admin on Membership Purchase
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param string $pack_duration
 * @return bool $send
 */
function mgm_notify_admin_membership_purchase($blogname, $user, $member, $pack_duration){
	// subject
	$subject = sprintf("[%s] Admin Notification - %s purchased membership: %s [%d] - [%s]", 
						$blogname, $user->user_email, $member->membership_type, $member->pack_id, $member->status);
	//issue #2096
	$member_duration = (strtolower($pack_duration) != 'lifetime') ? $member->duration : '';						
	// message
	$message = sprintf("User display name: %s<br />
						User email: %s<br />
						User ID: %s<br />
						Membership Type: %s<br />
						New status: %s<br />
						Status message: %s<br />
						Subscription period: %s %s<br />
						Subscription amount: %s %s<br />
						Payment Mode: %s", 
						$user->display_name, $user->user_email, $user->ID, $member->membership_type, $member->status, 
						$member->status_str, $member_duration, $pack_duration, $member->amount, $member->currency, 
						$member->payment_type);

	// return
	return @mgm_notify_admin(null, $subject, $message, 'membership_purchase');
}

/**
 * Send Email Notification to Admin on Membership Cancellation
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param string $new_status
 * @param string $membership_type
 * @return bool $send
 */
function mgm_notify_admin_membership_cancellation($blogname, $user, $member, $new_status){
	// subject
	$subject = sprintf("[%s] %s - %s", $blogname, $user->user_email, $new_status);
	// message
	$message = sprintf("User display name: %s<br />
					    User email: %s<br />
						User ID: %s<br />
						Membership Type: %s<br />
						New status: %s<br />
						Status message: %s<br />					
						Payment Mode: Cancelled",
						$user->display_name, 
						$user->user_email, 
						$user->ID, 
						$member->membership_type, 
						$new_status, 
						$member->status_str);
	// return
	return @mgm_notify_admin(null, $subject, $message, 'membership_cancellation');
}

/**
 * Send Email Notification to Admin on Membership Cancellation manual removal required
 *
 * @uses mgm_notify_admin()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @return bool $send
 */
function mgm_notify_admin_membership_cancellation_manual_removal_required($blogname, $user, $member, $additional=null){
	// subject
	$subject = sprintf(__('[%s] User Subscription Cancellation', 'mgm'), $blogname);	
	// message
	$message = sprintf(__('The User: %s (%d) has upgraded/cancelled subscription.<br/>
						  Please unsubscribe the user subscription from Gateway Merchant panel.<br/>
						  MGM Transaction Id: %d<br/>', 'mgm'), $user->user_email, $user->ID, $member->transaction_id);
	// addtional	
	if( ! is_null($additional) ){
		$message .= $additional;
	}
	// send			
	return @mgm_notify_admin(null, $subject, $message, 'membership_cancellation_manual_removal_required');
}

/**
 * Send Email Notification to Admin on passthrough verification failed
 *
 * @uses mgm_notify_admin()
 * @param string $passthrough
 * @param string $module
 * @return bool @send
 */
function mgm_notify_admin_passthrough_verification_failed($passthrough, $module){
	// system
	$system_obj = mgm_get_class('system');
	$dge = bool_from_yn($system_obj->get_setting('disable_gateway_emails'));
	
	// notify admin, only if gateway emails on
	if( ! $dge ){		
		// subject		
		$subject = sprintf('Error in %s custom passthrough verification', ucwords($module));
		// message
		$message = sprintf('Could not read custom passthrough:<br />passthrough: %s;<br>request: %s', $passthrough, mgm_pr($_REQUEST, true) );
		// mail
		return @mgm_notify_admin(null, $subject, $message, 'passthrough_verification_failed');
	}
	// error
	return false;
}

/**
 * Send Email Notification to Admin on ARB Creation failure
 *
 * @uses mgm_notify_admin()
 * @param string optional user_email
 * @param string @subject
 * @param string @message
 * @return bool @send
 */
function mgm_notify_admin_arb_creation_failed( $blogname, $post_data, $arb_data ){
	// subject
	$subject  = sprintf( '[%s] Admin Notification: Authorize.Net ARB Creation Failure(%s)', $blogname, $post_data['x_email'] );
	// message
	$message  = sprintf( 'Authorize.Net ARB Creation failed for the below user:<br/>ID: %s<br/>Email: %s<br/>MGM Transaction Id: %s<br/>
						  ARB Response: %s<br>', 
						 $post_data['x_cust_id'], $post_data['x_email'], $post_data['x_custom'], mgm_pr($arb_data, true) );
	// send
	return @mgm_notify_admin(null, $subject, $message, 'arb_creation_failed');
}

/**
 * Send Email Notification to Admin on IPN verification failed
 *
 * @uses mgm_notify_admin()
 * @param string $module
 * @return bool @send
 */
function mgm_notify_admin_ipn_verification_failed( $module ){		
	// subject		
	$subject = sprintf('Error in %s IPN verification', ucwords($module));
	// message
	$message = sprintf('Could not verify IPN:<br />post data: %s;', mgm_pr($_POST, true) );
	// mail
	return @mgm_notify_admin(null, $subject, $message, 'ipn_verification_failed');	
}

/**
 * Send an email notification to Admin for remote post connection error
 *
 * @uses mgm_notify_admin()
 * @param string $connect_url,$error_response
 * @return bool $send
 */
function mgm_notify_admin_remote_post_connection_error( $connect_url,$error_response ){
	// subject
	$subject = sprintf('Remote post connection error notification');
	// message
	$message = sprintf('Could not be connet following url: %s<br>				
						Please debug or contact magicmembers to fix the problem making sure to pass on the following data.<br>
						RESPONSE DATA: %s<br>', $connect_url, mgm_pr($error_response, true));
	// send
	return @mgm_notify_admin(null, $subject, $message, 'remote_post_connection_error');
}

/**
 * Send an email notification to Admin for user upgrade
 *
 * @uses mgm_notify_admin()
 * @param int $user_id
 * @param bool $notify
 * @return bool $send
 */
function mgm_notify_admin_user_upgraded( $user_id, $notify=true ){
	// admin notification		
	if( $notify ) {
		// mgm_system	
		$system_obj = mgm_get_class('system');	
		
		// code
		$tpl_code = 'user_upgrade_notification_email_template_';
		//getting email template
		$subject = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'subject', array(), true));
		$message = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'body', array(), true));	

		//replacing email tags
		$subject = mgm_replace_email_tags($subject, $user_id);
		$message = mgm_replace_email_tags($message, $user_id);		
		
		// return
		return @mgm_notify_admin(null, $subject, $message, 'user_upgraded');		
	}

	return false;
}	


/**
 * Send an email notification to Admin for user register
 *
 * @uses mgm_notify_admin()
 * @param int $user_id
 * @return bool $send
 */
function mgm_notify_admin_user_registered($user_id){
	// mgm_system	
	$system_obj = mgm_get_class('system');	

	// code
	$tpl_code = 'new_user_notification_email_template_';

	// template -issue #1069
	$subject = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'subject', array(), true));
	$message = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'body', array(), true));	
	
	// replace tags
	$subject = mgm_replace_email_tags($subject, $user_id);
	$message = mgm_replace_email_tags($message, $user_id);

	// set up template for callable filter
	$template = array('subject'=>$subject, 'message'=>$message);
	
	// apply filter
	$template = apply_filters('mgm_new_user_notification_email_template', $template, $user_id);	

	// return
	return @mgm_notify_admin(null, $template['subject'], $template['message'], 'user_registered');	
}

/**
 * Send an email notification to Admin for new users register
 *
 * @uses mgm_notify_admin()
 * @param array $new_users
 * @param array $response
 * @return bool $sent
 */
function mgm_notify_admin_new_users_registered($new_users, $response){
	// system
	$system_obj = mgm_get_class('system');
	// message
	$message  = sprintf('(%d) %s  %s: <br/><br/>', count($new_users), 
		        __( 'New user registration on your blog', 'mgm'), get_option('blogname'));
	// loop
	foreach ($new_users as $user_id => $n_user) {	
		// set
		$message .= sprintf('%s: %s <br/>', __('Username', 'mgm'), $n_user['user_login']);
		$message .= sprintf('%s: %s <br/>', __('E-mail', 'mgm'), $n_user['email']);
		$message .= "-----------------------------------<br/><br/>";
		// unset
		unset($n_user);
		// send email to the user:
		// mgm_new_user_notification($user_id, $new['user_password'],false);
	}
	// Issue #1703
	// Update the option: mgm_userids with newly created user IDS
	mgm_update_userids($new_users);
	// unset
	unset($new_users);
	// check
	if(isset($response['message'])) {
		// set
		$message .= $response['message'];
		$message .= "-----------------------------------<br/><br/>";
	}

	// subject
	$subject = sprintf('[%s] %s', __('New User Registration','mgm'), get_option('blogname'));

	// admin email
	return @mgm_notify_admin(null, $subject, $message, 'new_users_registered');				
}

/**
 * Send an email notification to Admin for getresponse user confirmed
 *
 * @uses mgm_notify_admin()
 * @param array $confirm_users
 * @param array $campaign
 * @return bool $sent
 */
function mgm_notify_admin_getresponse_users_confirmed($confirm_users, $campaign){
	// contacts
	$confirm_users_str = implode(',', $confirm_users);
	// campaign
	$campaign_name = $campaign['name'];
	$campaign_admin_email = $campaign['from_email'];

	// subject
	$subject = "User(s) confirmed in campaign - {$campaign_name} - Automated mail";

	// message
	$message  = "Hi Admin,\n\n<br/><br />";
	$message .= "Campaign Name : {$campaign_name}\n\n<br /><br />";

	// check
	if ( ! empty($confirm_users) ) {
		if ( count($confirm_users) == 1) { 
			$message .= " Below user has been confirmed today. \n\n<br /><br /> ";
			$message .= " User : {$confirm_users_str}\n\n<br /";
		}else {			
			$message .= " Below users has been confirmed today: \n\n<br /><br /> ";
			$message .= " Users : {$confirm_users}\n\n<br /";
		}
	}
	
	// admin email
	return @mgm_notify_admin($campaign_admin_email, $subject, $message, 'getresponse_users_confirmed');		
}

/**
 * Send Email Notification to Admin on Zombaio cancellation failure
 *
 * @uses mgm_notify_admin()
 * @param string optional user_email
 * @param string @subject
 * @param string @message
 * @return bool @send
 */
function mgm_notify_admin_zombaio_membership_cancellation_failed( $blogname ){
	// subject			
	$subject = sprintf(__('[%s] Admin Notification: Error in Zombaio membership cancellation', 'mgm'), $blogname);

	// message
	$message = sprintf(__('Could not read member in the following REQUEST data. Please debug or contact magicmembers to 
		                   fix the problem making sure to pass on the following data. <br /><br /> Request: %s', 'mgm'), 
						   mgm_pr($_REQUEST, true) );
		
	// mail
	return @mgm_notify_admin(null, $subject, $message, 'zombaio_cancellation_failed' );
}

// ----- ADMIN NOTIFICATIONS END---------------------------

// ----- USER NOTIFICATIONS START---------------------------

/**
 * Send Email Notification to User on Post Purchase
 *
 * @uses mgm_notify_user()
 * @param object $blogname
 * @param object $user
 * @param object $post
 * @param string $status
 * @param object $system_obj
 * @param object $post_obj
 * @param string $status_str
 * @return bool $send
 */
function mgm_notify_user_post_purchase($blogname, $user, $post, $status, $system_obj, $post_obj, $status_str){
	// emails not for guest
	if( isset($user->ID) ){					
		// purchase status
		switch($status){
			case 'Success':
				// subject
				$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
				// data
				$data = array('blogname'=>$blogname,'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'admin_email'=>$system_obj->get_setting('admin_email'));
				// message
				$message = $system_obj->get_template('payment_success_email_template_body', $data, true);
				//
			break;
			case 'Failure':
				// subject
				$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);				
				// data
				$data = array('blogname'=>$blogname,'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'payment_type'=>'post purchase payment','reason'=>$status_str,'admin_email'=>$system_obj->get_setting('admin_email')) ;
				// message			
				$message = $system_obj->get_template('payment_failed_email_template_body', $data, true);
			break;
			case '':
				// subject
				$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
				// data
				$data = array('blogname'=>$blogname, 'name'=>$user->display_name,'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'reason'=>$status_str, 'admin_email'=>$system_obj->get_setting('admin_email'));
				// message	
				$message = $system_obj->get_template('payment_pending_email_template_body', $data, true);
			break;
			case 'Unknown':
			default:
				// subject
				$subject = $system_obj->get_template('payment_unknown_email_template_subject', array('blogname'=>$blogname), true);		
				// data
				$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'post_title'=>$post->post_title,
							  'purchase_cost'=>mgm_convert_to_currency($post_obj->purchase_cost),'email'=>$user->user_email, 
							  'reason'=>$status_str,'admin_email'=>$system_obj->get_setting('admin_email'));
				// message	
				$message = $system_obj->get_template('payment_unknown_email_template_body', $data, true);
			break;
		}		

		// replace tags
		$subject = mgm_replace_email_tags($subject, $user->ID);
		$message = mgm_replace_email_tags($message, $user->ID);

		// return
		return @mgm_notify_user($user->user_email, $subject, $message, 'post_purchase'); // send an email to the buyer	
	}

	// return
	return false;
}

/**
 * Send Email Notification to User on Membership Purchase
 *
 * @uses mgm_notify_user()
 * @param string $blogname
 * @param object $user
 * @param object $member
 * @param array $custom
 * @param array $subs_pack
 * @param object $s_packs
 * @param object $system_obj
 * @return bool $send
 */
function mgm_notify_user_membership_purchase($blogname, $user, $member, $custom, $subs_pack, $s_packs, $system_obj){
	// local var
	extract($custom);
	// on status
	switch ($member->status) {
		case MGM_STATUS_ACTIVE:
			//Sending notification email to user - issue #1468
			if( isset($notify_user) && isset($is_registration) && bool_from_yn($is_registration) ){
				// get pass
				$user_pass = mgm_decrypt_password($member->user_password, $user->ID);
				// action				
				// send notification only once - issue #1601
				if($system_obj->setting['enable_new_user_email_notifiction_after_user_active'] == 'Y' && $notify_user) {
					//check - issue #1794
					if(isset($member->transaction_id) && $member->transaction_id > 0) {
						$trans =mgm_get_transaction($member->transaction_id);
						$trans['data']['notify_user'] = false;
						mgm_update_transaction(array('data'=>json_encode($trans['data'])), $member->transaction_id);
					}
					//notify					
					do_action('mgm_register_user_notification', $user->ID, $user_pass);					
				}
			}
			//sending upgrade notifaction email to admin
			if(isset($subscription_option) && $subscription_option =='upgrade'){
				do_action('mgm_user_upgrade_notification', $user_id);
			}			
			// init
			$subscription = '';
			// add trial 
			if ( isset($subs_pack['trial_on']) && (int)$subs_pack['trial_on'] == 1 ) {
				// trial
				$subscription = sprintf('%1$s %2$s for the first %3$s %4$s,<br> then ', $member->trial_cost, $member->currency, 
										($member->trial_duration * $member->trial_num_cycles), $s_packs->get_pack_duration($subs_pack,true));
				//$pack_amount = $member->trial_cost;
			}
			
			// on type
			if ($member->payment_type == 'subscription') {
				$payment_type = 'recurring subscription';
				$subscription .= sprintf('%1$s %2$s for each %3$s %4$s, %5$s',
										$member->amount,$member->currency,$member->duration,$s_packs->get_pack_duration($subs_pack),
										((int)$member->active_num_cycles > 0 ? sprintf('for %d installments',(int)$member->active_num_cycles) : 'until cancelled'));
				$pack_amount = $member->amount;										
			} else {
				$payment_type = 'one-time payment';
				//issue #2096
				$member_duration = ($subs_pack['duration_type'] != 'l') ? $member->duration : '';				
				$subscription .= sprintf('%1$s %2$s for %3$s %4$s',$member->amount, $member->currency, $member_duration, $s_packs->get_pack_duration($subs_pack));
				$pack_amount = $member->amount;
			}
			// subject
			$subject = $system_obj->get_template('payment_success_email_template_subject', array('blogname'=>$blogname), true);
			// data
			$data = array('blogname'=>$blogname,'name'=>$user->display_name, 'email'=>$user->user_email,'payment_type'=>$payment_type,
						  'subscription'=>$subscription,'admin_email'=>$system_obj->get_setting('admin_email'),'amount'=>$pack_amount,'membership_type'=>$member->membership_type);
			// message
			$message = $system_obj->get_template('payment_success_subscription_email_template_body', $data, true);
		break;

		case MGM_STATUS_NULL:
			// subject
			$subject = $system_obj->get_template('payment_failed_email_template_subject', array('blogname'=>$blogname), true);		
			// data
			$data = array('blogname'=>$blogname,'name'=>$user->display_name,'email'=>$user->user_email, 'payment_type'=>'subscription payment',
									  'reason'=>$member->status_str,'admin_email'=>$system_obj->get_setting('admin_email'),'membership_type'=>$member->membership_type);	
			// message
			$message = $system_obj->get_template('payment_failed_email_template_body', $data, true);
		break;

		case MGM_STATUS_PENDING:
			// subject
			$subject = $system_obj->get_template('payment_pending_email_template_subject', array('blogname'=>$blogname), true);
			// data
			$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'email'=>$user->user_email, 'reason'=>$member->status_str,
						  'admin_email'=>$system_obj->get_setting('admin_email'),'membership_type'=>$member->membership_type);
			// body
			$message = $system_obj->get_template('payment_pending_email_template_body', $data, true);
		break;

		case MGM_STATUS_ERROR:
			// subject
			$subject = $system_obj->get_template('payment_error_email_template_subject', array('blogname'=>$blogname), true);	
			// data
			$data = array('blogname'=>$blogname, 'name'=>$user->display_name, 'email'=>$user->user_email,'reason'=>$member->status_str,
						  'admin_email'=>$system_obj->get_setting('admin_email'),'membership_type'=>$member->membership_type);			
			// body	
			$message = $system_obj->get_template('payment_error_email_template_body', $data, true);
		break;
	}

	// check
	if( isset($subject) && isset($message) ){
		// replace tags
		$subject = mgm_replace_email_tags($subject, $user->ID);
		$message = mgm_replace_email_tags($message, $user->ID);

		// return
		return @mgm_notify_user($user->user_email, $subject, $message, 'membership_purchase');
	}

	return false;
}

/**
 * Send Email Notification to User on Membership Cancellation
 *
 * @uses mgm_notify_user()
 * @param string $blogname
 * @param string $user
 * @param string $member
 * @param string $system_obj
 * @return bool $send
 */
function mgm_notify_user_membership_cancellation($blogname, $user, $member, $new_status, $system_obj){
	// subject
	$subject = $system_obj->get_template('subscription_cancelled_email_template_subject', array('blogname'=>$blogname), true);	
	// data
	$data = array('blogname'=>$blogname,'name'=>$user->display_name,'email'=>$user->user_email, 
				  'admin_email'=>$system_obj->get_setting('admin_email'),'membership_type'=>$member->membership_type);			
	// body	
	$message = $system_obj->get_template('subscription_cancelled_email_template_body', $data, true);

	//issue #862
	$subject = mgm_replace_email_tags($subject, $user->ID);
	$message = mgm_replace_email_tags($message, $user->ID);

	// mail
	return @mgm_notify_user($user->user_email, $subject, $message, 'membership_cancellation');	
}

/**
 * Send Email Notification to User on Gift Post
 *
 * @uses mgm_notify_user()
 * @param object $blogname
 * @param object $user
 * @param object $post
 * @param object $system_obj
 * @return bool $send
 */
function mgm_notify_user_gift_post($blogname, $user, $post, $system_obj){
	// data
	$data = array('blogname'=>$blogname,'name'=>$user->display_name,'email'=>$user->user_email, 
				  'admin_email'=>$system_obj->get_setting('admin_email'),'post_title'=>$post->post_title,
				  'post_link'=>get_permalink( $post->ID ));			
	// subject
	$subject = $system_obj->get_template('gift_post_email_template_subject', $data, true);		
	// body	
	$message = $system_obj->get_template('gift_post_email_template_body', $data, true);
	//replace email tags
	$subject = mgm_replace_email_tags($subject, $user->ID);
	$message = mgm_replace_email_tags($message, $user->ID);
	
	// mail
	return @mgm_notify_user($user->user_email, $subject, $message, 'gift_post');	
}

/**
 * Send Email Notification to User on registration welcone
 *
 * @uses mgm_notify_user()
 * @param int $user_id
 * @param string $user_pass
 * @return bool $sent
 */
function mgm_notify_user_registration_welcome($user_id, $user_pass){
	// mgm_system	
	$system_obj = mgm_get_class('system');		
	
	// code
	$tpl_code = 'registration_email_template_';
	
	// message
	$message = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'body', array(), true));	
	
	// check if available, other wise use default wp notification
	if( empty($message) ){
		return false;
	}	

	// subject
	$subject = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'subject', array(), true));
	
	// get user
	$user = new WP_User($user_id);	
	// mgm member
	$member = mgm_get_member($user_id);
	
	// user data
	$user_login = stripslashes($user->user_login);
    $user_email = stripslashes($user->user_email);

    // no pass
	if ( empty($user_pass) ) {
		return false;
	}

	// first name
	if(isset($user->first_name) && !empty($user->first_name)){
		$display_name = $user->first_name;
	}elseif(isset($member->custom_fields->first_name) && !empty($member->custom_fields->first_name)){
		$display_name = $member->custom_fields->first_name;
	}elseif( isset($user->display_name) && !empty($user->display_name) ){					
	 	$display_name = $user->display_name;
	}else{
		$display_name = $user_login;
	}
	// format
	$display_name = stripslashes($display_name);	

	// subject
	if( ! $subject ) $subject = sprintf(__('[%s] Your username and password','mgm'), get_option('blogname'));
	 
	// body
	$message = str_replace('[name]', $display_name, $message);
	$message = str_replace('[username]', $user_login, $message);
	//issue #1359
	$message = str_replace('[email]', $user_email, $message);
	if($system_obj->setting['enable_new_user_email_notifiction_password']=='Y') {
		$message = str_replace('[password]', $user_pass, $message);
	} else {
		$password_protect_msg = '**your selected password**';
		$message = str_replace('[password]', $password_protect_msg, $message);
	}
	// $message = str_replace('[expire_date]',$expire_date,$message);
	$message = str_replace('[login_url]', sprintf('<a href="%s">%s</a>', wp_login_url(), __('Login','mgm')), $message);	
	
	// replace tags
	$subject = mgm_replace_email_tags($subject, $user_id);
	$message = mgm_replace_email_tags($message, $user_id);
	
	//issue #1894
	$start_tag = '[user_account_is#'.$member->membership_type.']';
	$end_tag   = '[/user_account_is]';
	
	// subject
	if (strpos($subject, $start_tag) !== false) {
		$subject = mgm_extract_string($subject, $start_tag, $end_tag) ;
	}elseif (strpos($subject, "[user_account_is#default]") !== false){
		$subject = mgm_extract_string($subject, "[user_account_is#default]", $end_tag) ;
	}		

	// body	
	if (strpos($message,$start_tag) !== false) {
		$message = mgm_extract_string($message, $start_tag, $end_tag) ;
	}elseif (strpos($message, "[user_account_is#default]") !== false){
		$message = mgm_extract_string($message, "[user_account_is#default]", $end_tag) ;
	}
	
	// setup template
	$template = array('subject'=>$subject, 'message'=>$message);

	// apply filter
	$template = apply_filters('mgm_registration_email_template', $template, $user_id);
	
	// mail
	@mgm_notify_user($user_email, $template['subject'], $template['message'], 'registration_welcome');	

	// treat as true, since some mailserver does not return true on success, we have to force true
	// as otherwise user will get two emails, one from this and other form wp_new_user_notification() 
	return true;
}

/**
 * Send Email Notification to User on payment activation
 *
 * @uses mgm_notify_user()
 * @param int $user_id
 * @return bool $sent
 */
function mgm_notify_user_payment_activation($user_id){
	// mgm_system	
	$system_obj = mgm_get_class('system');	

	// code
	$tpl_code = 'payment_active_email_template_';

	// blogname
	$blogname = get_option('blogname');
	$userdata = get_userdata($user_id); 
	$user_email = $userdata->user_email;

	// template vars
	$vars = array('blogname'=>$blogname);

	// subject
	$subject = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'subject', $vars, true));	
	
	// template vars
	$vars = array('blogname'=>$blogname,'name'=>mgm_stripslashes_deep($userdata->display_name), 
				  'email'=>$userdata->user_email,'admin_email'=>$system_obj->get_setting('admin_email'));			
	// body	
	$message = mgm_stripslashes_deep($system_obj->get_template($tpl_code.'body', $vars, true));	

	//issue #862
	$subject = mgm_replace_email_tags($subject, $user_id) ;
	$message = mgm_replace_email_tags($message, $user_id) ;

	// mail
	return @mgm_notify_user($user_email, $subject, $message, 'payment_activation');	
}

/**
 * Send Email Notification to User subsctiption expiration
 *
 * @uses mgm_notify_user()
 * @param object $user
 * @param array $email_data
 * @return bool $sent
 */
function mgm_notify_user_expiration_reminder($user, $email_data){
	// assign
	$subject = $email_data['template_subject'];
	$message = $email_data['template_body'];
	
	// other
	$subscription_type = $email_data['subscription_type'];
	$date_format       = $email_data['date_format'];

	// Issue #1178
	$expire_date_fmt = mgm_translate_datestring($email_data['expire_date'], $date_format);

	// tags
	$replace_tags = array('[name]','[expire_date]','[subscription_type]');
	$replace_with = array($user->display_name, $expire_date_fmt, $subscription_type);
	// mail body
	$message = str_replace($replace_tags, $replace_with, $message);	

	//issue #862
	$subject = mgm_replace_email_tags($subject, $user->ID);
	$message = mgm_replace_email_tags($message, $user->ID);

	// setup template
	$template = array('subject'=>$subject,'message'=>$message);// change body to message
	
	// add filter
	$template = apply_filters('mgm_reminder_email_template', $template, $user, $email_data);

	// email
	$user_email = $user->user_email;
	
	// send mail
	return @mgm_notify_user($user_email, $template['subject'], $template['message'], 'expiration_reminder');		
}

/**
 * Admin license renewal reminder email
 */
function mgm_notify_admin_license_renewal_reminder() {	
	//subject
	$subject = ' Magic members license renewal reminder ';		
	//message		
	$message = "Hi, <br/>";
	$message .= "Your Magic Members service license is about to expire. If you like to receive updates and support, ";
	$message .= "please use the following link to purchase a service renewal: ";
	$message .= "https://www.magicmembers.com/products-page/magic-members/magic-members-service-renewal/ <br/>";
	$message .= "This is optional and not renewing your license won't disable any of the plugin's functionality.<br/>";
	$message .= "Best Regards, <br/>";
	$message .= "Magic Members Support Team	<br/>";			
	//context
	$context = 'License renewal reminder';
	//return
	return @mgm_notify_admin(null,$subject,$message,$context);
}

/**
 * Send Email Notification to Admin on general error
 *
 * @uses mgm_notify_admin()
 * @param string $module
 * @param string $subject
 * @param string $message
 * @return bool @send
 */
function mgm_notify_admin_general_error( $module, $subject, $message ){		
	// subject		
	$subject = sprintf('Error in %s: %s', ucwords($module), $subject);
	// message
	$message = sprintf('Error in %s: %s', ucwords($module), $message);
	// mail
	return @mgm_notify_admin(null, $subject, $message, 'general_error');	
}
// end file /core/libs/functions/mgm_email_functions.php