<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members uri utility class
 *
 * @package MagicMembers
 * @since 2.5
 */ 
class mgm_uri{
	// vars
	public $segments   = array();
	public $format     = 'html';// default
	public $uri_string = '';
	
	// construct
	public function __construct($uri_string=''){	
		// php4
		$this->mgm_uri($uri_string);
	}
	
	// php4	construct
	public function mgm_uri($uri_string=''){		
		// set
		$this->set_uri_string($uri_string);
		// parse
		$this->parse_uri();
	}
	
	// strip_suffix
	public function strip_suffix(){
		// basename
		$basename = basename($this->uri_string);
		// format from uri/ext
		$this->suffix = (strpos($basename,'.') !== FALSE) ? substr($basename,strrpos($basename, '.')+1) : '.html';
		// format
		$this->format = substr($this->suffix, 0, strpos($this->suffix, '?'));
		// replace uri_string
		return str_replace('.' . $this->suffix, '', $this->uri_string);
	}
	
	// parse uri
	public function parse_uri(){	
		// clean uri and take only uri path withput qs
		$this->clean_uri_string = $this->parse_routes();	
		// split
		$parts = explode('/', $this->clean_uri_string);			
		// loop and verify
		do{
			// clean - add sanitize
			$part = trim(array_shift($parts));
			// take
			if(!empty($part) && $part != '/'){
				$this->segments[] = basename($part);
			}	
		}while(count($parts)>0);		
	}	
	
	// segment
	public function segment($index,$default=''){
		// get 
		if(isset($this->segments[$index])){
			return $this->segments[$index];
		}
		// return default
		return $default;		
	}
	
	// segments
	public function segments($start_index=2){
		return array_slice($this->segments, $start_index);
	}	
	
	// return 
	public function uri_string(){
		// return 
		return $this->uri_string;
	}
	
	// set 
	public function set_uri_string($uri_string=''){
		// return 
		$this->uri_string = (!empty($uri_string)) ? $uri_string : $_SERVER['REQUEST_URI'];
	}
	
	// parse
	public function parse_routes(){
		global $mgm_routes;		
		// loop
		foreach($mgm_routes as $from => $to){
			// pattern
			$uri_pattern = "#{$from}#i";			
			// match
			if(preg_match($uri_pattern, $this->uri_string)){
				// reset
				$this->uri_string = preg_replace($uri_pattern, $to, $this->uri_string);
				// break;
				break;
			}
		}		
		// strip 	
		return $this->strip_suffix();
	}
	
	// uri_string
	public function get_pattern_regx($uri_string){
		// append end
		if(substr($uri_string,-1) == '*'){
			$uri_string = preg_quote(str_replace('*','',$uri_string), '/') . '(.*)';
		}elseif(substr($uri,-4) == ':any'){
			$uri_string = preg_quote(str_replace(':any','',$uri_string), '/') . '(.*)';
		}elseif(substr($uri,-4) == ':id'){
			$uri_string = preg_quote(str_replace(':any','',$uri_string), '/') . '(\d+)';
		}else{		
			$uri_string = preg_quote($uri_string,'/');
		}
		// return
		return $uri_string;
	}
}
// core/libs/utilities/mgm_uri.php