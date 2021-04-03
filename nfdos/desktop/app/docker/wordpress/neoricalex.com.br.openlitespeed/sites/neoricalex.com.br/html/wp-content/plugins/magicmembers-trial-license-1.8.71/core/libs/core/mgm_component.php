<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members components parent class
 * base class for modules, plugins, widgets etc
 *
 * @package MagicMembers
 * @since 2.5
 */
class mgm_component extends mgm_object{
	// loader
	public $loader = null; 
	public $processor = null;
	public $type = 'component';
	
	// construct
	public function __construct(){
		// php4 construct
		$this->mgm_component();
	}
	
	// php4 construct
	public function mgm_component(){
		// parent
		parent::__construct();		
		// loader
		$this->loader = new mgm_loader();		
		// processor
		$this->processor = new mgm_processor();
	}	
	
	// init
	public function init(){	
		// set instance
		$this->processor->set_instance($this);		
		// call
		$this->processor->call();
	}		
}
// end of file core/libs/core/mgm_component.php
