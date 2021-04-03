<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
// base class for admin, modules, plugins
// die('deprecated');
class mgm_base{
	// private attributes
	private $_method = 'index';
	private $_tmpl_dir;
	
	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_base();
	}
	
	// php4 construct
	public function mgm_base(){
		// set default template path
		$this->set_tmpl_path();
		
		// get ajax object 
		$this->get_ajax();		
	}	
	
	// get ajax object 
	public function get_ajax(){
		// check
		if(!isset($this->ajax)){
			// ajax object
			$this->ajax = new mgm_ajax();
		}
		// return 
		return $this->ajax;
	}
	
	// load method as called
	public function load($method='index', $args=null){
		// set default if set
		// $this->set_default_method($default);
		
		// when method empty, take from request
		if(empty($method)){
			// what method to call
			if(isset($_REQUEST['method']) && !empty($_REQUEST['method'])){
				$method = $_REQUEST['method'];
			}else{
			// take default
				$method = $this->_method;
			}
		}		
			
		// check and call		
		if(method_exists($this, $method)){
			// ajax output start
			$this->get_ajax()->start_output();
			// method
			if( is_array($args) ){
				call_user_func_array( array($this, $method), $args );
			}else{
				$this->$method();
			}
			// ajax output end
			$this->get_ajax()->end_output();
		}else{
			// error
			trigger_error("Method missing [$method]", E_USER_ERROR);
		}
	}	
	
	// bypass and force call	
	public function forward($method='')
	{
		// load method
		$this->load($method,'');
	}
	
	// set default method
	public function set_default_method($method){
		// check
		if(empty($method))
			return;
		
		// set	
		$this->_method = $method;
	}
	
	// load template file
	public function load_template($name, $data='', $return=false){
		// make data available
		if(is_array($data)) extract($data);
		
		// set template	
		$template = $this->get_tmpl_path() . $name . '.php';			
		
		// file exists
		if(is_file($template)){
			// output start
			$this->get_ajax()->start_output();
			// html
			if($return){
				return $this->_get_include($template, $data);
			}else{
				@include($template);				
			}
			// output end
			$this->get_ajax()->end_output();			
		}else{
			// error
			trigger_error("Template Html missing [$template]", E_USER_ERROR);
		}	
	}
	
	// ste template path	
	public function set_tmpl_path($path=''){	
		// set tpl dir			
		$this->_tmpl_dir = (!empty($path) ? $path : (MGM_CORE_DIR . 'admin' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR));			
	}	
	
	// get template path
	public function get_tmpl_path(){
		// set if not done 
		if(!$this->_tmpl_dir)
			$this->set_tmpl_path();
			
		// return	
		return $this->_tmpl_dir;
	}
		
	// get include
	public function _get_include($template, $data=false){
		// make data available
		if(is_array($data)) extract($data);
		
		// buffer start
		ob_start();		
		@include($template);
		return ob_get_clean();	
		// end	
	}
	
	// get message 
	public function _get_message($head='Record'){
		// message
		switch($_GET['s']){
			case 1:
				return sprintf(__('%s added successfully.','mgm'),$head);
			break;
			case 2:
				return sprintf(__('%s updated successfully.','mgm'),$head);
			break;
			case 3:
				return sprintf(__('%s deleted successfully.','mgm'),$head);
			break;
		}
	}	
}
// end file
