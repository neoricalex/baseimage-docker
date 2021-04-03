<?php
/** 
 * Objects merge/update
 * Add New capability: member detail report options to admin role
 */ 
$obj_role = new mgm_roles();
$obj_role->update_capability_role('administrator', 'mgm_member_detail', true);
// end file