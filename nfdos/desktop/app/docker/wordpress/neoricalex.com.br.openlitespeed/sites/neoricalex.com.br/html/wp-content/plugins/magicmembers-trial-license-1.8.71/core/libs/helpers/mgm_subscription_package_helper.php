<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members subscription helpers
 *
 * @package MagicMembers
 * @version 1.0
 * @since 2.6.0
 */

if( ! function_exists('mgm_add_subscription_package') ){ 
	/**
	 * Magic Members add subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string $membership_type
	 * @param array $pack optional pack data
	 * @return array subscription package created
	 */
	 function mgm_add_subscription_package($membership_type, $pack=array()){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->add($membership_type, $pack);
	 }
} 

if( ! function_exists('mgm_update_subscription_package') ){ 
	/**
	 * Magic Members update subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $id
	 * @param array $pack
	 * @return array subscription package updated
	 */
	 function mgm_update_subscription_package($id, $pack=array()){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->update($id, $pack);
	 }
} 

if( ! function_exists('mgm_delete_subscription_package') ){ 
	/**
	 * Magic Members delete subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $id
	 * @return array subscription package deleted
	 */
	 function mgm_delete_subscription_package($id){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->delete($id);
	 }
} 

if( ! function_exists('mgm_delete_all_subscription_package') ){ 
	/**
	 * Magic Members delete all subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param none
	 * @return bool success
	 */
	 function mgm_delete_all_subscription_package(){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->delete_all();
	 } 
} 

if( ! function_exists('mgm_get_subscription_package') ){ 
	/**
	 * Magic Members get subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $id
	 * @return array subscription package
	 */
	 function mgm_get_subscription_package($id){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->get($id);
	 }
} 

if( ! function_exists('mgm_get_all_subscription_package') ){ 
	/**
	 * Magic Members get all subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param string none
	 * @return array subscription packages
	 */
	 function mgm_get_all_subscription_package(){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		return $sp_obj->get_all();
	 }
}	 

if( ! function_exists('mgm_get_default_subscription_package') ){ 
	/**
	 * Magic Members get subscription package
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param none
	 * @return array subscription package
	 */
	 function mgm_get_default_subscription_package(){
	 	// init
		$_pack = array();
	 	// get all packs
	 	$packs = mgm_get_all_subscription_package();
		// check
		if($packs){		
			// loop
			foreach($packs as $pack){
				// match
				if(isset($pack['default_assign']) && (bool)$pack['default_assign'] == true){
					$_pack = $pack; break;
				}
			}
		}
		// return 
		return $_pack;
	 }
}	 


if( ! function_exists('mgm_get_subscription_package_description') ){
	/**
	 * Magic Members get subscription package description
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $id
	 * @param array $pack
	 * @return string subscription package desc
	 */
	 function mgm_get_subscription_package_description($id=null, $pack=null){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');
		// return 
		if( ! $pack ) $pack = $sp_obj->get($id);
		// erturn 
		return $sp_obj->get_pack_desc( $pack );
	 }
} 

if( ! function_exists('mgm_get_member_subscribed_package') ){
	/**
	 * Magic Members get subscription package by member
	 *
	 * @package MagicMembers
	 * @since 2.6.0
	 * @param int $user_id
	 * @param object $member
	 * @return array subscription package
	 */
	 function mgm_get_member_subscribed_package( $user_id=null, $member=null ){
	 	// object
		$sp_obj = mgm_get_class('subscription_packs');

		// init
		$pack = array();

		// user
		if( ! $user_id ) $user_id = get_current_user_id();

		// member
		if( ! $member ) $member = mgm_get_member($user_id);

		if( isset($member->pack_id) ){
			// pack
			$pack = $sp_obj->get( $member->pack_id );

			// fetch
		 	if( ! $pack ){
		 		// init
				$pack = array();
		 		// nodes
				$vars = array(
					'trial_on','trial_cost','trial_duration','trial_duration_type','trial_num_cycles',
					'duration','duration_type','amount','currency','active_num_cycles','membership_type'
				);
				
				// loop
				foreach( $vars  as $var ){
					if( $var == 'active_num_cycles' ) {
						$name = 'num_cycles';
					}elseif( $var == 'amount' ) {
						$name = 'cost';
					}else{
						$name = $var;
					}	
					// set
					$pack[ $name ] = isset($data['member']->{$var})? $data['member']->{$var} : '';
				}
			}
		}	
		
		// return
		return $pack;
	 }
}	 
// end file /core/libs/helpers/mgm_subscription_package_helper.php
