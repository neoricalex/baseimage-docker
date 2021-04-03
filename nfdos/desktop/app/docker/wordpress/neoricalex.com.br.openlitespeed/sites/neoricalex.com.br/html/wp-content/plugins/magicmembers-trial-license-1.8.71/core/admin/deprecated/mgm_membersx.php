/**
	 * members data export
	 * 
	 * @deprecated
	 */ 
	function member_export_old() {		
		global $wpdb;
		// error -- use WP_DEBUG with WP_DEBUG_LOG 
		// if(!WP_DEBUG) error_reporting(0);
		// extract
		extract($_POST);
		// log
		// mgm_log($_POST, __FUNCTION__);
		
		// get format	
		$sformat = mgm_get_date_format('date_format_short');	
		
		// process
		if(isset($export_member_info)){
			// init
			$success = 0;
			// type			
			$membership_type = (isset($bk_membership_type)) ? $bk_membership_type : 'all';			
			// status		
			$membership_status = (isset($bk_membership_status)) ? $bk_membership_status : 'all';			
			// date
			$date_start = (isset($bk_date_start)) ? $bk_date_start : '';	
			$date_end   = (isset($bk_date_end)) ? $bk_date_end : '';
			// query inut
			$query = '';
			// selected only
			if(isset($bk_only_selected)){
				// check
				if(isset($bk_selected_members) && is_array($bk_selected_members)){
					$query = " AND `id` IN(" . mgm_map_for_in($bk_selected_members) .")";
				}
			}
			
			// start date
			if($date_start){
				// Issue #700
				// convert to mysql date
				$date_start = strtotime(mgm_format_inputdate_to_mysql($date_start,$sformat));	
				// end date			
				if($date_end){		
					// Issue #700
					// convert to mysql date			
					$date_end = mgm_format_inputdate_to_mysql($date_end,$sformat);					
					$date_end = strtotime($date_end);
					// issue#" 492
					$query .= " AND UNIX_TIMESTAMP(user_registered) >= '{$date_start}' 
					            AND UNIX_TIMESTAMP(DATE_FORMAT(user_registered, '%Y-%m-%d')) <= '{$date_end}'";
				}else{
					$query .= " AND UNIX_TIMESTAMP(user_registered) >= '{$date_start}'";
				}
			}else if($date_end){
				// Issue #700
				// convert to mysql date
				$date_end = strtotime(mgm_format_inputdate_to_mysql($date_end,$sformat));
				// query
				$query .= " AND UNIX_TIMESTAMP(DATE_FORMAT(user_registered, '%Y-%m-%d')) <= '{$date_end}' ";
			}
			// all users	
			$sql = 'SELECT ID, user_login, user_email, user_registered, display_name FROM `' . $wpdb->users . '` 
			        WHERE ID <> 1 ' . $query . ' ORDER BY `user_registered` ASC';		
			// users
			$users = $wpdb->get_results($sql);			
			// filter
			$export_users = array();
			// date
			$current_date = time();	

			//issue #844 	
			$skip_fields = array('subscription_introduction','coupon','privacy_policy','payment_gateways','terms_conditions',
								 'subscription_options','autoresponder','captcha');
			// check - issue #1382
			if(isset($bk_users_to_import)){	
				$custom_fields = mgm_get_class('member_custom_fields')->get_fields_where(array('display'=>array('on_register'=>true,'on_profile'=>true)));
				$import_user_fields = array('user_login','user_email','pack_id','membership_type');
				foreach($custom_fields as $field){ 
					if(!in_array($field['name'],$skip_fields))
						$import_user_fields[]=$field['name'];
				}
			}			
			// Custom fields	
			$cf_profile_pg = mgm_get_class('member_custom_fields');
			$to_unserialize = array();	
			foreach (array_unique($cf_profile_pg->sort_orders) as $id) :
				foreach($cf_profile_pg->custom_fields as $field):
					// issue #954: show the field only if it is enabled for profile page
					if ($field['id'] == $id && $field['type'] == 'checkbox'):
						$to_unserialize[]= $field['name'];
					endif;
				endforeach;
			endforeach;					 
			// loop
			foreach ($users as $user) {
				// user cloned
				$user_obj = clone $user;
				
				// member
				$member = mgm_get_member($user->ID);	
				
				// check 
				if(!isset($bk_inactive)) $bk_inactive = false;
									
				// check search parameters:
				if($this->_get_membership_details($member, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {
					// merge 
					if(method_exists($member,'merge_fields')){					
						$user = $member->merge_fields($user);
					}		

					// log
					// mgm_log($user, __FUNCTION__);
			
					// issue #844 	
					foreach ($skip_fields as $skip_field){
						unset($user->{$skip_field});
					}

					// format dates
					$user->user_registered = date($sformat, strtotime($user->user_registered));	
					$user->last_pay_date   = ( isset($user->last_pay_date) && (int)$user->last_pay_date > 0 )? date($sformat, strtotime($user->last_pay_date)) : 'N/A';	
					$user->expire_date     = ( isset($user->expire_date) && !empty($user->expire_date)) ? date($sformat, strtotime($user->expire_date)) : 'N/A';		
					$user->join_date       = ( isset($user->join_date) && (int)$user->join_date > 0 ) ? date($sformat, $user->join_date) : 'N/A';		
					
					// issue#: 672
					// DO not show actual password: #1002
					// $user->user_password = mgm_decrypt_password($member->user_password, $user->ID);					
					$user->rss_token   	   = ( isset($member->rss_token) && !empty($member->rss_token ) ) ? $member->rss_token : 'N/A';
					
					// unset password
					unset($user->password,$user->password_conf);
					
					// unserialize checkbox values
					if (count($to_unserialize)) {
						foreach( $to_unserialize as $chkname) {
							if (isset($user->{$chkname}) && !empty($user->{$chkname})) {
								$chk_val = @unserialize($user->{$chkname});
								if (is_array($chk_val)) {
									$user->{$chkname} = implode("|", $chk_val);
								}
							}
						}
					}
					// check - issue #1382
					if(isset($bk_users_to_import)){						
						$importuser = new stdClass();												
						foreach ($import_user_fields as $import_user_field){						
							if(isset($user->{$import_user_field})) 
								$importuser->{$import_user_field} = $user->{$import_user_field};
							if($import_user_field =='pack_id') 
								$importuser->{$import_user_field} = $member->pack_id;
						}
						$export_users[] = $importuser;
						unset($importuser);					
					}else {
						$export_users[] = $user;
					}						
				}
				
				// consider multiple memberships as well:
				if(isset($member->other_membership_types) && is_array($member->other_membership_types) && count($member->other_membership_types) > 0) {
					// loop
					foreach ($member->other_membership_types as $key => $memtypes) {
						// types
						if(is_array($memtypes)) $memtypes = mgm_convert_array_to_memberobj($memtypes, $user->ID);
						// check search parameters:
						if($this->_get_membership_details($memtypes, $bk_msexp_dur_unit, $bk_msexp_dur, $membership_type, $current_date, $bk_inactive, $membership_status )) {
							// copy
							$user_mem = clone $user_obj;	
							// add custom fields as well:
							if(!empty($member->custom_fields)) {
								// loop
								foreach ($member->custom_fields as $index => $val) {
									// custom field
									if($index == 'birthdate' && !empty($val)) {
										// convert saved date to input field format
										$val = mgm_get_datepicker_format('date', $val);
									}
									// set
									$user_mem->{$index} = $val;
								}
							}
							
							// check types
							if( is_object($memtypes) && method_exists($memtypes,'merge_fields')){
							// merge		
								$user_mem = $memtypes->merge_fields($user_mem);	
							}else {
							// convert to array
								$data = mgm_object2array($memtypes);
								// check payment
								if(isset($memtypes->payment_info) && count($memtypes->payment_info) > 0) {
									// loop payments
									foreach ($memtypes->payment_info as $index => $val){
									// set
										$data['payment_info_' . $index] = str_replace('mgm_', '', $val);
									}	
								}
								// loop data
								foreach ($data as $index => $val) $user_mem->{$index} = $val;
							}

							//issue #844 	
							foreach ($skip_fields as $skip_field){
								unset($user->{$skip_field});
							}
							
							// format dates
							$user_mem->user_registered = date($sformat, strtotime($user_mem->user_registered));	
							$user_mem->last_pay_date   = (int)$memtypes->last_pay_date>0 ? date($sformat, strtotime($memtypes->last_pay_date)) : 'N/A';	
							$user_mem->expire_date     = (!empty($memtypes->expire_date)) ? date($sformat, strtotime($memtypes->expire_date)) : 'N/A';		
							$user_mem->join_date       = (int)$memtypes->join_date > 0 ? date($sformat, $memtypes->join_date) : 'N/A';		

							// check - issue #1382
							if(isset($bk_users_to_import)){						
								$importuser = new stdClass();												
								foreach ($import_user_fields as $import_user_field){						
									if($user_mem->{$import_user_field}) 
										$importuser->{$import_user_field} = $user_mem->{$import_user_field};
									if($import_user_field =='pack_id') 
										$importuser->{$import_user_field} = $memtypes->pack_id;
								}
								$export_users[] = $importuser;
								unset($importuser);				
							}else {
								$export_users[] = $user_mem;
							}	
							// unset 
							unset($user_mem);
						}
					}
				}				
				
			}// end for	
			
			//mgm_log('export_users : '.mgm_array_dump($export_users,true));
	
			
			// default response
			$response = array('status'=>'error','message' => __('Error while exporting members. Could not find any member with requested search parameters.', 'mgm'));
			
			// check
			if (($expcount = count($export_users))>0) {
				// Issue #1559: standardization of Membership type
				for($k =0; $k < $expcount; $k++) {
					if (isset($export_users[$k]->membership_type)) {
						$export_users[$k]->membership_type = strtolower($export_users[$k]->membership_type);
					}
				}
				// success
				$success = count($export_users);
				// create
				if($bk_export_format == 'csv'){
					$filename= mgm_create_csv_file($export_users, 'export_users');			
				}else{
					$filename= mgm_create_xls_file($export_users, 'export_users');			
				}
				// src
				$file_src = MGM_FILES_EXPORT_URL . $filename;				
				// message
				$response['message'] = sprintf(__('Successfully exported %d %s.', 'mgm'), $success, ($success>1 ? 'users' : 'user'));
				$response['status']  = 'success';
				$response['src']     = $file_src;// for download iframe 
			}
			// return response
			echo json_encode($response); exit();
		}	
		
		// data
		$data = array();							
		// load template view
		$this->loader->template('members/member/export', array('data'=>$data));	
	}