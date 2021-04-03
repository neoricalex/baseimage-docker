<?php
/** 
 * Batch Upgrade
 * $Id 1.0.9
 */ 	
// current batch
$current_batch = '1.1.0';

// start
mgm_start_batch_upgrade( $current_batch );

// moved to upgrades due to object merge should run immediately

/*
	// system object updates
	$member_custom_fields_cached = mgm_get_class('member_custom_fields');
	$arr_len = count($member_custom_fields_cached->custom_fields);
	$new_id = $arr_len+1;  
	
	$new_field =array('id'     => $new_id,
					  'name'       => 'middle_name',
					  'label'      => __('Middle Name','mgm'),
					  'type'       => 'text',
					  'system'     => true, 
					  'value'      => '',
					  'options'    => false,
					  'display'    => array('on_register'=> true,'on_profile'=> true,'on_payment'=> true,'on_public_profile' => true),		
					  'attributes' => array('required'=> true,'readonly'=> false,'hide_label'=> false,'to_autoresponder'=> true,
					  						'capture_only'=> false,'capture_field_alias'=> '','profile_by_membership_types'=> false,
					  						'profile_membership_types_field_alias'=> '','register_by_membership_types'=> false,
					  						'register_membership_types_field_alias'=> '','admin_only'=> false,'placeholder'=> false)
					  );	 

	//check
	if(!$member_custom_fields_cached->get_field_by_name($new_field['name'],'bool')) {  	
		$member_custom_fields_cached->custom_fields = array_merge($member_custom_fields_cached->custom_fields, array($arr_len=>$new_field) ); 	
		$member_custom_fields_cached->next_id = $new_id+1;  	
		$member_custom_fields_cached->save();
	}
*/
// end
mgm_end_batch_upgrade( $current_batch );
// end batch $Id 1.1.0 