<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
class MGM_Register_Widget2 extends WP_Widget {
	// var
	var $default_text    = array();
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'mgm_register_widget2', // Base ID
			__('Magic Members Register Widget2', 'mgm'), //Name
			array( 'description' => __( 'Magic Members Register Widget2', 'mgm' ), ) // Args
		);
		
		// default texts
		$this->default_texts();
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		
		global $wpdb, $user_ID, $current_user;
		
		extract($args, EXTR_SKIP);

		//skip widget if BUDDYPRESS is loaded
		if(defined('BP_VERSION')) {
			return;
		}			
		//skip registation page:
		if(in_array(trailingslashit(mgm_current_url()),array(trailingslashit(mgm_get_custom_url('register'))), trailingslashit(mgm_get_custom_url('register', true))) )	{
			return;
		}	
		// skip if on transactions page:	
		foreach(mgm_get_payment_page_query_vars() as $query_var) {
			// set if
			if( $isset_query_var = mgm_get_query_var($query_var) ){
				return;
			}
		}
		// check
		if((isset($_GET['method']) && preg_match('/payment_/', $_GET['method'] ))) {
			return;
		}	
		//check
		if (!wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js')) {
			// custom scripts
			wp_enqueue_script('mgm-helpers', MGM_ASSETS_URL . 'js/helpers.js');
		}				
		// set vars
		$title             = (isset($instance['title']) ? $instance['title'] : __('Magic Members - Register','mgm'));
		$intro             = (isset($instance['intro']) ? $instance['intro'] : '');
		$use_custom_fields = (isset($instance['use_custom_fields']) ? $instance['use_custom_fields']: true);
		$default_subscription_pack = (isset($instance['default_subscription_pack']) ? $instance['default_subscription_pack']: false);
		
		// user looged in
		if (!$user_ID) {
			// if hide on custom register page 
			$post_id = get_the_ID();
			// post custom register	
			if($post_id>0){
				// if match
				if( get_permalink($post_id) == mgm_get_custom_url('register') )
					return "";
			}	
			
			// start actual widget
			echo $before_widget;
			
			if ($title) {
				echo $before_title . $title . $after_title;
			}
						
			// echo $intro;
			
			echo mgm_sidebar_user_register_form($use_custom_fields, $default_subscription_pack);
			
			echo $after_widget;
		}	
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */

	public function form( $instance ) {
		
		// set vars
		$title         	  			= isset($instance['title']) ? stripslashes($instance['title']) : 	__('Register','mgm');
		$intro         	  			= isset($instance['intro']) ? stripslashes($instance['intro']) :	trim($this->default_text['active_intro']);
		$use_custom_fields         	= isset($instance['use_custom_fields']) ? stripslashes($instance['use_custom_fields']) :	false;
		$default_subscription_pack	= isset($instance['default_subscription_pack']) ? stripslashes($instance['default_subscription_pack']) :	'free';
	
		// subscription pack list
		$subscription_pack_list = sprintf('<option value="-">%s</option>', __('Select','mgm'));
		//loop
		foreach ($packages = mgm_get_subscription_packages() as $pack) {	
			if($default_subscription_pack == $pack['key']){
				$subscription_pack_list	.= sprintf('<option selected="selected" value="%s">%s</option>', $pack['key'], $pack['label']);
			}else {		
				$subscription_pack_list	.= sprintf('<option value="%s">%s</option>', $pack['key'], $pack['label']);
			}
		}
		
		// generate html
		$html = '<p>
					<div class="mgm_margin_bottom_5px">
						<label for="mgm_register_sidebar_widget_title">
							<div><strong>' . __('Widget Title','mgm') . '</strong></div>
							<input class="mgm_width_300px" type="text" name="'. $this->get_field_name( 'title' ).'" id="'. $this->get_field_id( 'title' ).'"  value="' . $title . '"/>
						</label>
					</div>
					<div class="mgm_margin_bottom_5px">
						<label for="mgm_register_sidebar_widget_use_custom_fields">						
							<input class="mgm_width_30px" type="checkbox" ' . ($use_custom_fields? 'checked="checked"':'') . ' 
							name="'. $this->get_field_name( 'use_custom_fields' ).'" id="'. $this->get_field_id( 'use_custom_fields' ).'" value="1"/>
							<strong>' . __('Use Custom Fields in form?','mgm') . '</strong>
						</label>
					</div>
					<div class="mgm_margin_bottom_5px" id="cusFldDropdown_'.$this->get_field_id( 'default_subscription_pack' ).'">
						<label for="mgm_register__widget_default_subscription_pack">						
							<div><strong>' . __('Select default subscription pack ','mgm') . '</strong></div>
							<select class="mgm_width_300px" name="'. $this->get_field_name( 'default_subscription_pack' ).'" 
							id="'. $this->get_field_id( 'default_subscription_pack' ).'">'.$subscription_pack_list.'</select>
						</label>
					</div>
					<div class="mgm_margin_bottom_5px">
						<label for="mgm_register_sidebar_widget_active_intro">
							<div><strong>' . __('Introduction','mgm') . '</strong></div>
							<textarea rows="6" cols="30" name="'. $this->get_field_name( 'intro' ).'" id="'. $this->get_field_id( 'intro' ).'" >' . $intro . '</textarea>
						</label>
					</div>
				 </p>';
	
		// script
		$html .= '<script type="text/javascript">
		jQuery(document).ready(function(){			
			if(jQuery("#'. $this->get_field_id( 'use_custom_fields' ).'").val() == 1){
				//check
				if(jQuery("'. $this->get_field_id( 'use_custom_fields' ).'").is(":checked")){			
					jQuery("#cusFldDropdown_'.$this->get_field_id( 'default_subscription_pack' ).'").hide();
				}
			}else{
				jQuery("#cusFldDropdown_'.$this->get_field_id( 'default_subscription_pack' ).'").show();
			}
		
			jQuery("#'. $this->get_field_id( 'use_custom_fields' ).'").click(function() {			
				if(this.checked){	
					jQuery("#cusFldDropdown_'.$this->get_field_id( 'default_subscription_pack' ).'").hide();
					jQuery("#'. $this->get_field_id( 'use_custom_fields' ).'").val(1);
				}else{
					jQuery("#cusFldDropdown_'.$this->get_field_id( 'default_subscription_pack' ).'").show();
					jQuery("#'. $this->get_field_id( 'use_custom_fields' ).'").val(1);				  
				}
			});
		});	
		</script>';	
		
		// print
		print $html;		
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */             

	public function update( $new_instance, $old_instance ) {
		//init
		$instance = array();
		//set var
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['intro'] = strip_tags( $new_instance['intro'] );
		$instance['use_custom_fields'] = strip_tags( $new_instance['use_custom_fields'] );
		$instance['default_subscription_pack']  = strip_tags( $new_instance['default_subscription_pack'] );
		//return
		return $instance;
	}

	/**
	 * Register widget default texts.
	 * @return  
	 */
	function default_texts(){
		// active_intro
		$this->default_text['active_intro'] = '<p>You are a subscribed member.</p><div>Subscription Level: [membership_type]</div><div style="margin-bottom: 5px;">Expiry Date: [expiry_date]</div>';
	}		
}// end of class