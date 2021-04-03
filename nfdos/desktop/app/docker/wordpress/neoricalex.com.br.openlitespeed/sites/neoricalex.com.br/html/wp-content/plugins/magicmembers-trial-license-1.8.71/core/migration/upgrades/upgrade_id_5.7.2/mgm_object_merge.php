<?php
/** 
 * Objects merge/update
 * Add subscription cancellation mode to paypal module settings
 */ 
 // read  
$paypal = mgm_get_module('paypal','payment');

//check
if(isset($paypal->setting['subs_cancel'])) {
	$paypal->setting['subs_cancel'] 	= 'delayed';		
	$paypal->save();
}
?>