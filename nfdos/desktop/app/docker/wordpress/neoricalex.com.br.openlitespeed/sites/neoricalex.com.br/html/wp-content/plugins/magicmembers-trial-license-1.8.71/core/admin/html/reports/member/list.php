<?php mgm_box_top(__('Members Details', 'mgm'));?>
<?php
	$membership_count = mgm_get_membershiptype_users_count();
?>
<div class="table widefatDiv form-table">
	<div class="row headrow">
		<div class="cell theadDivCell width20 textalignleft ">
			<b><?php _e('Membership Type','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft ">
			<b><?php _e('Admin Created Users','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft ">
			<b><?php _e('Registered Users','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width15 textalignleft ">
			<b><?php _e('Total Count','mgm') ?></b>
		</div>
		<div class="cell theadDivCell width35 textalignleft ">
			<b><?php _e('Status','mgm') ?></b>
		</div>
	</div>
<?php
	$member_details = mgm_get_membershiptype_users_count();

	// get membership_types
	$membership_types = mgm_get_class('membership_types');
	//status
	$statuses = mgm_get_subscription_statuses(true);
	//loop	
	foreach ($membership_types->membership_types as $type_code=>$type_name) {
		
?>	
	<div class="brBottom row <?php echo ($alt = ($alt=='') ? 'alternate': '');?>">
		<div class="cell width20 textalignleft  paddingleftimp10px">
			<?php echo $type_name; ?>		   
		</div>
		<div class="cell width15 textalignleft  paddingleftimp10px">
			<?php echo $member_details[$type_code.'_by_admin']; ?>		   
		</div>
		<div class="cell width15 textalignleft  paddingleftimp10px">
			<?php echo $member_details[$type_code.'_by_user']; ?>		   
		</div>	
		<div class="cell width15 textalignleft  paddingleftimp10px">
			<?php echo $member_details[$type_code]; ?>		   
		</div>
		<div class="cell width35 textalignleft  paddingleftimp10px">
			<?php 
			 foreach ($member_details[$type_code.'_status'] as $status =>$count){
			 	$str = str_replace($type_code,'',$status);
			 	$cls = $str = str_replace('_',' ',$str);
			 	echo "<br/><span class='s".str_replace(' ','-',strtolower($cls))."'>".ucwords($str) .' - '.$count."</span>";
			 	//echo "<br/><span class='".mgm_get_status_css_class(ucwords($str))."'>".ucwords($str) .' - '.$count."</span>";
			 }
			?>
		</div>	
	</div>	
<?php } ?>	
	</div>
<p><div class="tips width90"><?php _e('Note : All free users consider as admin created user, we can differentiate only here paid members created by admin or register by user himself .','mgm'); ?></div></p>
<?php mgm_box_bottom();?>