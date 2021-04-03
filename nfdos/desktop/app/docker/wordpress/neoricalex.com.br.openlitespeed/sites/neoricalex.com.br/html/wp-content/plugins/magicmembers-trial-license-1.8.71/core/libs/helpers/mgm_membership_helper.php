<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members membership helpers
 *
 * @package MagicMembers
 * @version 1.0 
 * @since 2.6.0
 */

if( ! function_exists('mgm_add_membership_type') ){ 
	/**
	 * Magic Members add membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string name
	 * @param string login_redirect
	 * @param string logout_redirect 
	 * @return array membership type created
	 */
	 function mgm_add_membership_type($name, $login_redirect='', $logout_redirect=''){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->add($name, $login_redirect, $logout_redirect);
	 }
} 
 
if( ! function_exists('mgm_update_membership_type') ){ 
	/**
	 * Magic Members update membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string code
	 * @param string name
	 * @param string login_redirect
	 * @param string logout_redirect 
	 * @return array membership type updated
	 */
	 function mgm_update_membership_type($code, $name, $login_redirect, $logout_redirect){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->update($code, $name, $login_redirect, $logout_redirect);
	 }
} 
 
if( ! function_exists('mgm_delete_membership_type') ){ 
	/**
	 * Magic Members delete membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string code
	 * @return array membership type deleted
	 */
	 function mgm_delete_membership_type($code){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->delete($code);
	 }
} 
 
if( ! function_exists('mgm_delete_all_membership_type') ){
	/**
	 * Magic Members delete all membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param none
	 * @return bool success|failure
	 */
	 function mgm_delete_all_membership_type(){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->delete_all();
	 }
}	 
 
 if( ! function_exists('mgm_get_membership_type') ){
	/**
	 * Magic Members get membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string code
	 * @return array membership type
	 */
	 function mgm_get_membership_type($code){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->get($code);
	 }
}	 
 
if( ! function_exists('mgm_get_all_membership_type') ){ 
	/**
	 * Magic Members get all membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string none
	 * @return array subscription packages
	 */
	 function mgm_get_all_membership_type(){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->get_all();

		// mgm_get_class('membership_types')->membership_types
	 }
}	 

if( ! function_exists('mgm_get_all_membership_type_combo') ){ 
	/**
	 * Magic Members get all membership type for combo
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string none
	 * @return array subscription packages
	 */
	 function mgm_get_all_membership_type_combo($skip=array()){
	 	// object
		$membership_types = mgm_get_all_membership_type();
		// combo
		$combo = array();
		// loop
		if($membership_types){
			// loop
			foreach($membership_types as $membership_type){
				// skip
				if(in_array($membership_type['code'], $skip)) continue;
				// set
				$combo[$membership_type['code']] = $membership_type['name'];
			}
		}
		// return 
		return $combo;
	 }
}

if( ! function_exists('mgm_is_duplicate_membership_type') ){ 
	/**
	 * Magic Members check duplicate membership type
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string name
	 * @param string code
	 * @return bool
	 */
	 function mgm_is_duplicate_membership_type($name, $code=NULL){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->is_duplicate($name, $code);
	 }
}	 

if( ! function_exists('mgm_get_membership_type_name') ){ 
	/**
	 * Magic Members get membership type name
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string $code
	 * @return string $name
	 */
	function mgm_get_membership_type_name($code){
	 	// object
		$mt_obj = mgm_get_class('membership_types');
		// return 
		return $mt_obj->get_type_name($code);
	}
}

if( ! function_exists('mgm_get_member_subscribed_membership_types') ){
	/**
	 * Magic Members get member subscribed membership types
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $user_id
	 * @param object $member
	 * @return array $membership_types
	 */
	function mgm_get_member_subscribed_membership_types( $user_id=null, $member=null ){
	 	// user
		if( ! $user_id ) $user_id = get_current_user_id();

		// member
		if( ! $member ) $member = mgm_get_member($user_id);

		return $member->get_membership_types();
	}	
}

if( ! function_exists('mgm_member_has_membership_types') ){
	/**
	 * Magic Members check member has subscribed membership types
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param array $membership_types
	 * @param int $user_id
	 * @param object $member
	 * @return boolean
	 */
	function mgm_member_has_membership_types( $membership_types, $user_id=null, $member=null ){

	 	$subscribed_membership_types = mgm_get_member_subscribed_membership_types($user_id, $member);

	 	$matches = array_intersect((array)$membership_types, (array)$subscribed_membership_types);

	 	return ! empty($matches);
	}	

}

 // end file /core/libs/helpers/mgm_membership_helper.php
