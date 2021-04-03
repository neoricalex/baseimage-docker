<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members xls_writer utility class
 * @credit apples
   @url http://www.appservnetwork.com/modules.php?name=News&file=article&sid=8
 *  
 * @package MagicMembers
 * @since 2.5
 */  
class mgm_xls_writer{
	public $xls_string = '';	
	// construct
	public function __construct(){
		// php4 
		$this->mgm_xls_writer();
	}
		
	// php4 construct
	public function mgm_xls_writer(){
		// stuff
	}
	
	// BOF
	public function xls_bof() {
		$this->xls_string .= pack('ssssss', 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);  
		return;
	}
	
	// EOF
	public function xls_eof() {
		$this->xls_string .= pack('ss', 0x0A, 0x00);
		return;
	}
	
	// write number/value
	public function xls_write_value($row, $col, $value) {
		$this->xls_string .= pack('sssss', 0x203, 14, $row, $col, 0x0) . pack('d', $value);		
		return;
	}
	
	// write label/string
	public function xls_write_label($row, $col, $label) {
		$length = strlen($label);
		$this->xls_string .= pack('ssssss', 0x204, (8 + $length), $row, $col, 0x0, $length) . $label;		
		return;
	} 	
	
	// send output
	public function xls_output(){
		return $this->xls_string;
	}
}
// core/libs/utilities/mgm_xls_writer.php