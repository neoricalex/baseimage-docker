<!--reports-->
<div id="member_detail">
	<form name="frmMemberDetail" id="frmsales" method="POST" action="admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.reports&method=member_detail_list" class="marginpading0px">
	<div id="report_member_detail"></div>			
	</form>
	<iframe id="ifrm_backup_two" 
			src="" 
			allowtransparency="true" 
			width="0" 
			height="0" 
			frameborder="0">
	</iframe>	
</div>

<script type="text/javascript">
	// onready
	jQuery(document).ready(function(){   

		// load list
		mgm_member_detail_list=function(){
			// hide old message
			mgm_hide_message('#member_detail');
			// load
			jQuery('#report_member_detail').load('admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.reports&method=member_detail_list'); 
		}

		// show list, keep the last query
		mgm_show_member_detail_list=function(m) {
			var _m = m || false;
			// post
			jQuery.ajax({url:'admin-ajax.php?action=mgm_admin_ajax_action&page=mgm.admin.reports&method=member_detail_list', type: 'POST', cache:false, data : jQuery("#member-detail-search-table :input").serialize(),
				beforeSend: function(){	
					// show message
					mgm_show_message('#member_detail', {status:'running', message:'<?php echo esc_js(__('Processing','mgm'));?>...'},true);														
				 },
				 success:function(data){																																		
					// append 
					jQuery('#report_member_detail').html(data);					
					// show message
					if(_m){
						mgm_show_message('#member_detail', {status:'success', message: jQuery('#last_search_message').html() }, true);
					}else{
						mgm_hide_message('#member_detail');
					}								 
				 }
			});
		}	
		
		mgm_member_detail_list();
	});
</script>