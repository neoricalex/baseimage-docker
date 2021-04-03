<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members processor class
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_processor{
	// method
	private $_method;	
	
	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_processor();
	}
	
	// php4 construct
	public function mgm_processor(){
		// do something
		$this->_method = 'index';
		
		// ajax object
		$this->ajax = new mgm_ajax();
	}
	
	// set instance to call
	public function set_instance($instance){
		// set
		$this->instance = $instance;
	}
	
	// load method as called
	public function call($method='index', $args=null){
		// set default if set
		// $this->set_default_method($default);
		
		// when method empty, take from request
		if(empty($method) || is_null($method)){
			// what method to call
			if(isset($_REQUEST['method']) && !empty($_REQUEST['method'])){
				$method = $_REQUEST['method'];
			}else{
			// take default
				$method = $this->_method;
			}
		}		
			
		// check and call		
		if(method_exists($this->instance, $method)){
			// ajax output start
			$this->ajax->start_output();
			// method
			if( is_array($args) ){
				call_user_func_array( array($this->instance, $method), $args );
			}else{
				$this->instance->$method();
			}
			// ajax output end
			$this->ajax->end_output();
		}else{
			// error
			trigger_error("No such method {$this->instance}::{$method}", E_USER_ERROR);
		}
	}	
	
	// bypass and force call	
	public function forward($method=''){
		// load method
		$this->call($method);
	}
	
	// set default method
	public function set_default_method($method=null){
		// check
		if(empty($method) || is_null($method)) return;
		
		// set	
		$this->_method = $method;
	}	
}
// end of file core/libs/core/mgm_processor.php