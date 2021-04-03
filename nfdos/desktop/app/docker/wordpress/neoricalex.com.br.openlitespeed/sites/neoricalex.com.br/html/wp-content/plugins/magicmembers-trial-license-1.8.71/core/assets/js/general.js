/**
 * General JS
 */
// timer 
var mtus_timer;
var mtus_counter;

/**
 * track stats
 */
mgm_track_upgrade_stats=function( start ){
	// start
	if( start != undefined ){
		mtus_counter = 1;
	}else{
		mtus_counter++;
	}
	// ajax
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: { action: 'mgm_admin_batch_upgrade_ajax_action', counter: mtus_counter },
		success: function( r ){
			// messag
			jQuery('.mgm-notice-box #waiting .spinner-text').text( r.message );
			// done
			if( r.percent_done == 100 ){
				// clear timer
				clearTimeout( mtus_timer );
				// highlight and hide
				setTimeout( function(){
					jQuery('.mgm-notice-box').fadeOut('slow');
				}, 1500);				
			}			
		}
	});
}

/**
 * start batch upgrade
 */
mgm_batch_upgrade_start=function(){
	// hide buttons
	jQuery('.mgm-notice-box #upgrade-buttons').hide();	
	jQuery('.mgm-notice-box #waiting').show();
	// start timer
	mtus_timer = setInterval(mgm_track_upgrade_stats, 5000);
	// call
	mgm_track_upgrade_stats( 1 );
}

/**
 * queue batch upgrade
 */
mgm_batch_upgrade_queue=function(){
	// ajax
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: { action: 'mgm_admin_batch_upgrade_ajax_action', queue: 'true' },
		beforeSend: function( xhr ){
			// hide buttons
			jQuery('.mgm-notice-box #upgrade-buttons').hide();	
			jQuery('.mgm-notice-box #waiting').show();
		},
		success: function( r ){
			// messag
			jQuery('.mgm-notice-box #waiting .spinner-text').text( r.message );
			// done
			if( r.status == 'success' ){
				// highlight and hide
				setTimeout( function(){
					jQuery('.mgm-notice-box').fadeOut('slow');
				}, 1500);				
			}			
		}
	});
}

/**
 * cancel batch upgrade
 */
mgm_batch_upgrade_cancel=function(){
	// ajax
	jQuery.ajax({
		url: ajaxurl,
		type: 'POST',
		dataType: 'json',
		data: { action: 'mgm_admin_batch_upgrade_ajax_action', cancel: 'true' },
		beforeSend: function( xhr ){
			// hide buttons
			jQuery('.mgm-notice-box #upgrade-buttons').hide();	
			//jQuery('.mgm-notice-box #waiting').show();
		},
		success: function( r ){
			// messag
			jQuery('.mgm-notice-box #waiting .spinner-text').text( r.message );
			// done
			if( r.status == 'success' ){
				// highlight and hide
				setTimeout( function(){
					jQuery('.mgm-notice-box').fadeOut('slow');
				}, 1500);				
			}			
		}
	});
}