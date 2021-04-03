<?php
/** 
 * Objects merge/update
 */ 

/** 
 * Batch Upgrade 1.0.7 moved here due it should run immediately (also very small update).
 * Update capability:  mgm_other , mgm_redirection  to admin role moved from batch to upgrade
 */ 
$obj_role = new mgm_roles();

$obj_role->update_capability_role('administrator', 'mgm_redirection', true);
$obj_role->update_capability_role('administrator', 'mgm_other', true);

/** 
 * Batch Upgrade 1.0.8 moved here due it should run immediately (also very small update).
 * Update packs:   don't expire the user after the last billing cycle setting.
 */
//pack obj
$obj_packs = mgm_get_option('subscription_packs');
//init
$arr_packs = array();
//loop
foreach ($obj_packs->packs as $pack) {
	//init
	$pack['allow_expire']=1;
	//assign
	$arr_packs[] = $pack;	
}
//check
if(!empty($arr_packs)) {
	//assign
	$obj_packs->packs = $arr_packs;
	//update
	update_option('mgm_subscription_packs', $obj_packs);
}

/** 
 * Batch Upgrade 1.1.0 moved here due it should run immediately (also very small update).
 * Update saved custom fields with middle name field
 */

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