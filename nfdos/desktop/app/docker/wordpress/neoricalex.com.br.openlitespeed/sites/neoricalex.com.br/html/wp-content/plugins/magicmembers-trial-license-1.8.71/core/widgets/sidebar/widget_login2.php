<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
class MGM_Login_Widget2 extends WP_Widget {	

	// var
	var $default_text    = array();	

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
			'mgm_login_widget2', // Base ID
			__('Magic Members Login Widget2', 'mgm'), //Name
			array( 'description' => __( 'Magic Members Login Widget2', 'mgm' ), ) // Args
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
		global $user_ID, $current_user;	
		// if hide on custom login page 
		$post_id = get_the_ID();
		// post custom register	
		if($post_id>0){
			// if match
			if( get_permalink($post_id) == mgm_get_custom_url('login') )
				return "";
		}		
		// actual widget
		extract($args, EXTR_SKIP);
		// home url
		$home_url                 = home_url();
		
		// get options
		$title_logged_in          = (isset($instance['title_logged_in']) ? $instance['title_logged_in']:__('Magic Membership Details','mgm'));
		$title_logged_out         = (isset($instance['title_logged_out']) ? $instance['title_logged_out']:__('Login','mgm'));
		$profile_text 		      = (isset($instance['profile_text']) ? $instance['profile_text']:__('Profile','mgm'));
		$membership_details_text  = (isset($instance['membership_details_text']) ? $instance['membership_details_text']:__('Membership Details','mgm'));
		$membership_contents_text = (isset($instance['membership_contents_text']) ? $instance['membership_contents_text']:__('Membership Contents','mgm'));
		$logout_text              = (isset($instance['logout_text']) ? $instance['logout_text']:__('Logout','mgm'));
		$register_text            = (isset($instance['register_text']) ? $instance['register_text']:__('Register','mgm'));
		$lostpassword_text        = (isset($instance['lostpassword_text']) ? $instance['lostpassword_text']:__('Lost your Password?','mgm'));
		$logged_out_intro         = (isset($instance['logged_out_intro']) ? $instance['logged_out_intro']:'');
		
		// logged in user view
		if ($user_ID) {
			echo $before_widget;
			
			//init member
			$member = mgm_get_member($user_ID);
			//check
			if (isset($member->custom_fields->first_name) || isset($member->custom_fields->last_name)) {				
				$first_name = (isset($member->custom_fields->first_name)) ? $member->custom_fields->first_name :'';
				$last_name = (isset($member->custom_fields->last_name)) ? $member->custom_fields->last_name :'';							
				$name = sprintf("%s %s",$first_name,$last_name);
				
			}else {
				$name = '';
			}
			//replace
			$title_logged_in = str_replace('[name]', $name, $title_logged_in);
		
			if (trim($title_logged_in)) {
				echo $before_title . $title_logged_in . $after_title;
			}
			
			//>=WP2.7 = DB9872
			if (get_option('db_version') >= 9872) {
				$logout_url = wp_logout_url($home_url);
			} else {
				//$logout_url = trailingslashit($home_url) . 'wp-login.php?action=logout';
				$logout_url = add_query_arg(array('action' => 'logout'), mgm_get_custom_field_array('login'));
			}
			
			// @todo check the actual reason
			$membership_details_link 	= mgm_get_custom_url('membership_details');
			$membership_contents_link 	= mgm_get_custom_url('membership_contents');
			$profile_link 				= mgm_get_custom_url('profile');

			// set tmpl
			$logged_in_template = (isset($instance['logged_in_template']) ? $instance['logged_in_template'] : $this->default_text['logged_in_template']);
			$logged_in_template = str_replace('[display_name]', $current_user->display_name, $logged_in_template);
			$logged_in_template = str_replace('[membership_details_url]', $membership_details_link, $logged_in_template);		
			$logged_in_template = str_replace('[membership_details_link]', sprintf('<a href="%s">%s</a>',$membership_details_link, $membership_details_text), $logged_in_template);		
			$logged_in_template = str_replace('[membership_contents_url]', $membership_contents_link, $logged_in_template);		
			$logged_in_template = str_replace('[membership_contents_link]', sprintf('<a href="%s">%s</a>',$membership_contents_link, $membership_contents_text), $logged_in_template);		
			$logged_in_template = str_replace('[profile_url]', $profile_link, $logged_in_template);		
			$logged_in_template = str_replace('[profile_link]', sprintf('<a href="%s">%s</a>',$profile_link, $profile_text), $logged_in_template);
	
			//replace
			$logged_in_template = str_replace('[logout_url]', $logout_url, $logged_in_template);
			$logged_in_template = str_replace('[logout_link]', '<a href="' . $logout_url . '">' . $logout_text . '</a>', $logged_in_template);
			$logged_in_template = str_replace('[name]', $name, $logged_in_template);
			
			echo $logged_in_template;
	
			echo $after_widget;
		} else {
			echo $before_widget;
			
			if (trim($title_logged_out)) {
				echo $before_title . $title_logged_out . $after_title;
			}		
	
			echo $logged_out_intro;
	
			echo mgm_sidebar_user_login_form($register_text, $lostpassword_text);
	
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
		$title_logged_in          = isset($instance['title_logged_in']) ? $instance['title_logged_in'] : __('Membership Details','mgm');
		$title_logged_out         = isset($instance['title_logged_out']) ? $instance['title_logged_out'] : __('Login','mgm');	
		$profile_text 			  = isset($instance['profile_text']) ? $instance['profile_text'] : __('Profile','mgm');
		$membership_details_text  = isset($instance['membership_details_text']) ? $instance['membership_details_text'] : __('Membership Details','mgm');
		$membership_contents_text = isset($instance['membership_contents_text']) ? $instance['membership_contents_text'] : __('Membership Contents','mgm');	
		$logout_text              = isset($instance['logout_text']) ? $instance['logout_text'] : __('Logout','mgm');
		$register_text            = isset($instance['register_text']) ? $instance['register_text'] : __('Register','mgm');
		$lostpassword_text        = isset($instance['lostpassword_text']) ? $instance['lostpassword_text'] : __('Lost your Password?','mgm');
		$logged_out_intro         = isset($instance['logged_out_intro']) ? $instance['logged_out_intro'] : ''; 			
		$logged_in_template       = isset($instance['logged_in_template']) ? $instance['logged_in_template'] : $this->default_text['logged_in_template'];			
		
		// print
		$html = '<p>' . __('When logged out the user will see a login form. Removing the text from the "Register link text" or "Lost password link text" will subsequently remove the links they produce.', 'mgm') . '</p>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'title_logged_in' ).'"><strong>' . __('Widget Title (Logged in):','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $title_logged_in . '" id="'. $this->get_field_id( 'title_logged_in' ).'" name="'. $this->get_field_name( 'title_logged_in' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'title_logged_out' ).'"><strong>' . __('Widget Title (Logged out):','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $title_logged_out . '" id="'. $this->get_field_id( 'title_logged_out' ).'" name="'. $this->get_field_name( 'title_logged_out' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'profile_text' ).'"><strong>' . __('Profile link text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $profile_text . '" id="'. $this->get_field_id( 'profile_text' ).'" name="'. $this->get_field_name( 'profile_text' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'membership_details_text' ).'"><strong>' . __('Membership Details link text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $membership_details_text . '" id="'. $this->get_field_id( 'membership_details_text' ).'" name="'. $this->get_field_name( 'membership_details_text' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'membership_contents_text' ).'"><strong>' . __('Membership Contents link text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $membership_contents_text . '" id="'. $this->get_field_id( 'membership_contents_text' ).'" name="'. $this->get_field_name( 'membership_contents_text' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'logout_text' ).'"><strong>' . __('Logout text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $logout_text . '" id="'. $this->get_field_id( 'logout_text' ).'" name="'. $this->get_field_name( 'logout_text' ).'" />
			</label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'register_text' ).'"><strong>' . __('Register link text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' . $register_text . '" id="'. $this->get_field_id( 'register_text' ).'" name="'. $this->get_field_name( 'register_text' ).'" />
			</label>
		</div>
		<div class="mgm_margin_bottom_5px">
			<div><label for="'. $this->get_field_id( 'lostpassword_text' ).'"><strong>' . __('Lost password link text:','mgm') . '</strong></div>
			<input class="mgm_width_300px" value="' .$lostpassword_text . '" id="'. $this->get_field_id( 'lostpassword_text' ).'"	name="'. $this->get_field_name( 'lostpassword_text' ).'" /></label>
		</div>
		<div class="mgm_margin_bottom_5px">				
			<label for="'. $this->get_field_id( 'logged_out_intro' ).'">
				<div><strong>' . __('Logged Out Introduction','mgm') . '</strong></div>
				<textarea rows="2" cols="30" id="'. $this->get_field_id( 'logged_out_intro' ).'" name="'. $this->get_field_name( 'logged_out_intro' ).'">' . esc_html($logged_out_intro) . '</textarea>
			</label>
		</div>
		<div class="mgm_margin_bottom_5px">				
			<label for="'. $this->get_field_id( 'logged_in_template' ).'">
				<div><strong>' . __('Logged In Template','mgm') . '</strong> - Use the following hooks: [[name], display_name], [profile_url], [profile_link], [membership_details_url], [membership_details_link],[membership_contents_url], [membership_contents_link], [logout_url], [logout_link]</div>
				<textarea rows="6" cols="30" id="'. $this->get_field_id( 'logged_in_template' ).'" name="'. $this->get_field_name( 'logged_in_template' ).'">' . $logged_in_template . '</textarea>
			</label>
		</div>';	
	
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
		$instance['title_logged_in'] 			=   $new_instance['title_logged_in'];
		$instance['title_logged_out'] 			=   $new_instance['title_logged_out'];
		$instance['profile_text'] 				=   $new_instance['profile_text'];
		$instance['membership_details_text'] 	=   $new_instance['membership_details_text'];
		$instance['membership_contents_text']  	=   $new_instance['membership_contents_text'];
		$instance['logout_text']  				=   $new_instance['logout_text'];
		$instance['register_text']  			=   $new_instance['register_text'];
		$instance['lostpassword_text']  		=   $new_instance['lostpassword_text'];
		$instance['logged_out_intro']  			=   $new_instance['logged_out_intro'];
		$instance['logged_in_template']  		= 	$new_instance['logged_in_template'];
				
		//return
		return $instance;
	}
	
	/**
	 * Login widget default texts.
	 * @return  
	 */
	public function default_texts(){
		// inactive_intro
		// logged_in_template		
		$this->default_text['logged_in_template'] = '<p>Welcome [display_name]</p><ul><li>[membership_details_link]</li><li>[logout_link]</li></ul>';		
	}
}