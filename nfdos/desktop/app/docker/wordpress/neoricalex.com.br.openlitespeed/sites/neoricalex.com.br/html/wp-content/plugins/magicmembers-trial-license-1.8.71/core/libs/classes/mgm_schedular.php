<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members schedular class
 * extends object to save options to database
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_schedular extends mgm_object{
	// var
	var $events    = array();
	var $schedules = array();
	
	// construct
	function __construct(){
		// php4
		$this->mgm_schedular();
	}
	
	// construct
	function mgm_schedular(){
		// parent
		parent::__construct(); 
		// defaults
		$this->_set_defaults();			
		// read vars from db
		$this->read();// read and sync		
	}
	
	/**
	 * defaults
	 */
	function _set_defaults(){
		// code
		$this->code        = __CLASS__;
		// name
		$this->name        = 'Schedular Lib';
		// description
		$this->description = 'Schedular Lib';
		// init
		$this->events      = array();
		// schedules
		$this->schedules   = array();
		// init
		foreach( $this->get_defined_schedules() as $schedule_name => $event_name ){
			$this->schedules[$schedule_name] = $this->events[$schedule_name] = array();
		}
	}
	
	/**
	 * get defined schedules
	 */
	function get_defined_schedules(){
	 	// wp event/schedule name => mgm event name
		return array(
			'every15minute'  => 'mgm_every15minute_schedule',// every 15 minutes
			'hourly'         => 'mgm_hourly_schedule', // every hour
	        'every2ndhourly' => 'mgm_every2ndhourly_schedule', // every 2 hours			         
            'daily'          => 'mgm_daily_schedule', // once a day
            'twicedaily'     => 'mgm_twicedaily_schedule' // twice a day
        ); 		
	}
	
	/**
	 * push event
	 * 
	 * @param string $recurrence
	 * @param string $callback
	 * @return void
	 */ 
	function push_event( $recurrence, $callback ){
		// event_callback
		$event_callback = $recurrence . '_' . $callback; // mgm_schedular::daily_reminder_mailer	
		// trigger class method	
		if( method_exists($this, $event_callback) ){ 												
			// run
			$return = call_user_func( array($this, $event_callback) );							
		}else{
			// trigger function
			$event_callback = 'mgm_' . $event_callback;// // mgm_daily_reminder_mailer
			// method
			if( function_exists($event_callback) ){
				// run
				$return = call_user_func( $event_callback );				
			}			
		}

		// log executed
		if( isset($return) && $return == true ){
			// time
			$current_date = mgm_get_current_datetime('Y-m-d H:i:s');// with time part #1023 issue
			// last run set in events
			$this->events[$recurrence][$event_callback] = $current_date['timestamp'];
		}	
	}
	
	/**
	 * add schedule
	 * 
	 * @param string $recurrence
	 * @param string $callback
	 * @param bool $push
	 * @return array $schedules
	 */
	function add_schedule($recurrence='daily', $callback, $push=false){			
		// set array if not set
		if( ! isset( $this->schedules[$recurrence] ) || ( isset($this->schedules[$recurrence]) && ! is_array($this->schedules[$recurrence]))) {			
			$this->schedules[$recurrence] = array();
		}
			
		// push schedule
		if( ! in_array($callback, $this->schedules[$recurrence]) ) {			
			array_push($this->schedules[$recurrence], $callback);
		}

		// push event
		if( $push ){
			$this->push_event( $recurrence, $callback );	
		}

		// return
		return $this->schedules;
	}

	/**
	 * remove schedule
	 * 
	 * @param string $recurrence
	 * @param string $callback
	 * @return array $schedules
	 */
	function remove_schedule( $recurrence='daily', $callback ){
		// push
		if( in_array($callback, $this->schedules[$recurrence]) ) {			
			unset($this->schedules[$recurrence][$callback]);
		}

		// return
		return $this->schedules;
	}
	
	/**
	 * run
	 * 
	 * @param string $recurrence
	 * @return void
	 */
	function run($recurrence='daily'){	
		// check
		if( isset($this->schedules[$recurrence])){					
			// check
			if( ! empty($this->schedules[$recurrence]) ){
				// loop
				foreach($this->schedules[$recurrence] as $callback){
					$this->push_event( $recurrence, $callback );		
				}	
			}		
			
		}	
		
		// update option 
		$this->save();
	}
		
	/** 
	 * fix object data
	 */
	function apply_fix($old_obj){
		// to be copied vars
		$vars = array('events','schedules');
		// set
		foreach($vars as $var){
			// var
			$this->{$var} = (isset( $old_obj->{$var} ) ) ? $old_obj->{$var} : '';
		}				
		// save
		$this->save();	
	}
	
	/**
	 * prepare save, define the object vars to be saved
	 * internally called by object->save()
	 */
	function _prepare(){		
		// init array
		$this->options = array();
		// to be saved vars
		$vars = array('events','schedules');
		// set
		foreach($vars as $var){
			// var
			$this->options[$var] = $this->{$var};
		}	
	}
}
// core/libs/classes/mgm_schedular.php