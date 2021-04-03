<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
class MGM_Status_Widget2 extends WP_Widget {
	// var
	var $default_text    = array();
	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'mgm_status_widget2', // Base ID
			__('Magic Members Status Widget2', 'mgm'), //Name
			array( 'description' => __( 'Magic Members Status Widget2', 'mgm' ), ) // Args
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
		
		//init
		$title            = (isset($instance['title']) ? $instance['title']:__('Magic Members','mgm'));
		$logged_out_intro = (isset($instance['logged_out_intro']) ? stripslashes($instance['logged_out_intro']):$this->default_text['logged_out_intro']);
		$hide_logged_out  = (isset($instance['hide_logged_out']) ? stripslashes($instance['hide_logged_out']):false);
	
		// packs
		$packs = mgm_get_class('subscription_packs');		
		
		if ($user_ID) {		
	
			echo $before_widget;
			//init member
			$member = mgm_get_member($user_ID);
			//check
			if (isset($member->custom_fields->first_name) || isset($member->custom_fields->last_name)) {
				$name = sprintf("%s %s",$member->custom_fields->first_name,$member->custom_fields->last_name);
			}else {
				$name = '';
			}
			//replace
			$title = str_replace('[name]', $name, $title);
			
			//check
			if (trim($title)) {
				echo $before_title . $title . $after_title;
			}
			
			// check pack
			$subs_pack = null;		
	
			if($member->pack_id){
				$subs_pack = $packs->get_pack($member->pack_id);
			}
			
			$uat = $member->membership_type;
			if (!$uat) {
				$uat = 'free';
			}
			
			$user_status = $member->status;
			
			//issue #2555
			if (!in_array($user_status,array(MGM_STATUS_ACTIVE,MGM_STATUS_AWAITING_CANCEL)) || strtolower($uat) == 'free') {				
				$inactive_intro = (isset($instance['inactive_intro']) ? $instance['inactive_intro']:$this->default_text['inactive_intro']);
				echo $inactive_intro;
				mgm_sidebar_register_links();	
			} else {
				if ($expiry = $member->expire_date) {
					$sformat = mgm_get_date_format('date_format_short');
					$expiry   = date($sformat, strtotime($expiry));
				} else {
					$expiry = __('None', 'mgm');
				}
	
				$active_intro = $this->default_text['active_intro'];
				if (isset($instance['active_intro'])) {
					$active_intro = $instance['active_intro'];
				}
					
				$active_intro = str_replace('[membership_type]', mgm_get_class('membership_types')->get_type_name($uat), $active_intro);
				$active_intro = str_replace('[expiry_date]', $expiry, $active_intro);
				$active_intro = str_replace('[name]', $name, $active_intro);
				
				// check hidden subscription pack
				if((isset($subs_pack['hidden']) && $subs_pack['hidden'] != 1 ) || !isset($subs_pack['hidden'])){
					
					echo $active_intro;
				}
	
				$this->mgm_render_my_purchased_posts($user_ID);
			}
			
			echo $after_widget;
		} else {
			if (!$hide_logged_out) {
				echo $before_widget;
				
				if (trim($title)) {
					echo $before_title . $title . $after_title;
				}
			
				echo $logged_out_intro;
				echo mgm_get_login_register_links();
				echo $after_widget;
			}
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
		$title         	  = isset($instance['title']) ? stripslashes($instance['title']) : __('Membership Status','mgm');;
		$active_intro     = isset($instance['active_intro']) ? stripslashes($instance['active_intro']) : trim($this->default_text['active_intro']);
		$inactive_intro   = isset($instance['inactive_intro']) ? stripslashes($instance['inactive_intro']) : trim($this->default_text['inactive_intro']);	
		$logged_out_intro = isset($instance['logged_out_intro']) ? stripslashes($instance['logged_out_intro']) : trim($this->default_text['logged_out_intro']);
		$hide_logged_out  = isset($instance['hide_logged_out']) ? (int)$instance['hide_logged_out'] : false;
	
	
		// html
		$html = '<p>
					<div class="mgm_margin_bottom_5px">
					<label for="'. $this->get_field_id( 'title' ).'">
						<div><strong>' . __('Widget Title','mgm') . '</strong></div>
						<input class="mgm_width_300px" type="text" value="' . $title . '" id="'. $this->get_field_id( 'title' ).'" name="'. $this->get_field_name( 'title' ).'" />
					</label>
					</div>
					<div class="mgm_margin_bottom_5px">
					<label for="'. $this->get_field_id( 'active_intro' ).'">
						<div><strong>' . __('User Active Introduction','mgm') . '</strong> - Use [membership_type] and [expiry_date]</div>
						<textarea rows="6" cols="30" id="'. $this->get_field_id( 'active_intro' ).'" name="'. $this->get_field_name( 'active_intro' ).'">' . $active_intro . '</textarea>
					</label>
					</div>
					<div class="mgm_margin_bottom_5px">				
					<label for="'. $this->get_field_id( 'inactive_intro' ).'">
						<div><strong>' . __('User Inactive Introduction','mgm') . '</strong></div>
						<textarea rows="6" cols="30" id="'. $this->get_field_id( 'inactive_intro' ).'" name="'. $this->get_field_name( 'inactive_intro' ).'">' . $inactive_intro . '</textarea>
					</label>
					</div>
					<div class="mgm_margin_bottom_5px">				
					<label for="'. $this->get_field_id( 'logged_out_intro' ).'">
						<div><strong>' . __('User Logged Out Introduction','mgm') . '</strong></div>
						<textarea rows="6" cols="30" id="'. $this->get_field_id( 'logged_out_intro' ).'" name="'. $this->get_field_name( 'logged_out_intro' ).'">' . $logged_out_intro . '</textarea>
					</label>
					</div>
					<div class="mgm_margin_bottom_5px">				
					<label for="'. $this->get_field_id( 'hide_logged_out' ).'">
						<div><strong>' . __('Hide widget when user logged out?','mgm') . '</strong>
						<input type="checkbox" id="'. $this->get_field_id( 'hide_logged_out' ).'" name="'. $this->get_field_name( 'hide_logged_out' ).'" value="1" ' . ($hide_logged_out ? 'checked="checked"':'') . ' />
					</div>
					</label>
					</div>				
				</p>';
		// print
		print $html;
		
    }
	
    /**
	 * Status widget default texts.
	 * @return  
	 */
	function default_texts(){
		// inactive_intro
		$this->default_text['inactive_intro'] = '<p>You can subscribe to this blog using the buttons below.</p><p>You will be taken to a payment gateway and then returned to the site as a subscribed member.</p><p style="font-weight:bold;">Choose From:</p>';		
		
		// active_intro
		$this->default_text['active_intro'] = '<p>You are a subscribed member.</p><div>Subscription Level: [membership_type]</div><div style="margin-bottom: 5px;">Expiry Date: [expiry_date]</div>';
		
		// logged_out_intro		
		$this->default_text['logged_out_intro'] =	'<p>You need to be logged in to be able to subscribe to this blog or purchase any of its posts.</p><p>Use the link below to login or register.</p>';
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
		$instance['active_intro'] = strip_tags( $new_instance['active_intro'] );
		$instance['inactive_intro'] = strip_tags( $new_instance['inactive_intro'] );
		$instance['logged_out_intro']  = strip_tags( $new_instance['logged_out_intro'] );
		$instance['hide_logged_out']  = strip_tags( $new_instance['hide_logged_out'] );
		//return
		return $instance;
	}

	/**
	 * Render user purchsed posts.
	 * @param integer $user_id
	 * @param integer $sidebar
	 * @param boolean $return
	 * @return post data
	 */
	function mgm_render_my_purchased_posts($user_id, $sidebar=true, $return=false) {
		global $wpdb;
	
		$html = '';
		
		$prefix = $wpdb->prefix;
		$sql = "SELECT pp.post_id, p.post_title AS title
				FROM `" . TBL_MGM_POST_PURCHASES . "` pp 
				JOIN " . $prefix . "posts p ON (p.id = pp.post_id)
				WHERE pp.user_id = '{$user_id}'";
		//echo $sql;		
		$results = $wpdb->get_results($sql,'ARRAY_A');
	
		if (!$sidebar) {
			if (isset($results[0]) && count($results[0])) {
				$html .= '<div class="div_table"><div class="row"><div class="cell">'.__('Post Title', 'mgm').'</div></div>';
	
				foreach ($results as $result) {
					$link = get_permalink($result['post_id']);
					$title = $result['title'];
					if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
						$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
					}
	
					$html .= '<div class="row"><div class="cell"><a href="' . $link . '">' . $title . '</a></div></div>';
				}
	
				$html .= '</div>';
	
			}
		} else {
			if (isset($results[0]) && count($results[0]) > 0) {
				$html .= '<div class="mgm_render_my_purchased_posts_div">'.__('Purchased Posts','mgm').'</div>';
				
				foreach ($results as $result) {
					$link = get_permalink($result['post_id']);
	
					$title = $result['title'];
					if (function_exists('qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage')) {
						$title = qtrans_useCurrentLanguageIfNotFoundUseDefaultLanguage($title);
					}
	
					$html .= '<div><a href="' . $link . '">' . $title . '</a></div>';
				}
			}
		}
		//return
		if ($return) {
			return $html;
		} else {
			echo $html;
		}
	}	
}// end of class