<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/*
 * This is a PHP library that handles calling reCAPTCHA.
 *    - Documentation and latest version
 *          http://recaptcha.net/plugins/php/
 *    - Get a reCAPTCHA API Key
 *          https://www.google.com/recaptcha/admin/create
 *    - Discussion group
 *          http://groups.google.com/group/recaptcha
 *
 * Copyright (c) 2007 reCAPTCHA -- http://recaptcha.net
 * AUTHORS:
 *   Mike Crawford
 *   Ben Maurer
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */
class mgm_recaptcha {
	//class constructor
	public function __construct() {
		// php4
		$this->mgm_recaptcha();
	}
	
	// php4 construct
	public function mgm_recaptcha(){
		// stuff
	}
	/**
	 * Gets the challenge HTML (javascript and non-javascript version).
	 * This is called from the browser, and the resulting reCAPTCHA HTML widget
	 * is embedded within the HTML form it was called from.
	 * @param string $pubkey A public key for reCAPTCHA
	 * @param string $error The error given by reCAPTCHA (optional, default is null)
	 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is false)
	
	 * @return string - The HTML to be embedded in the user's form.
	 */
	public function recaptcha_get_html($error = null) {
		
		$setting = mgm_get_class('system')->get_setting();
		
		//check
		if (bool_from_yn($setting['no_captcha_recaptcha'])) {       	
			//return
			return $this->no_captcha_recaptcha_get_html();
		}
				
		$use_ssl = (isset($_SERVER['https']) || isset($_SERVER['HTTPS'])) ? true : false ;
		
		if (empty($setting['recaptcha_private_key']) || empty($setting['recaptcha_public_key']) ) {
			return __("reCAPTCHA API keys are blank. ", 'mgm');
		}
		
		if ($use_ssl) {
	    	$server = $setting['recaptcha_api_secure_server'];
        } else {
        	$server = $setting['recaptcha_api_server'];
        }
	
        $errorpart = "";
        if ($error) {
           $errorpart = "&amp;error=" . $error;
        }
        $script = "\n".'<script type="text/javascript">'."\n".'var RecaptchaOptions = { 
			        custom_translations : {
			                        instructions_visual : "'.(__('Type the text', 'mgm')).':",
			                        instructions_audio : "'.(__('Type what you hear', 'mgm')).':",
			                        play_again : "'.(__('Play sound again', 'mgm')).'",
			                        cant_hear_this : "'.(__('Can\'t hear this', 'mgm')).'",
			                        visual_challenge : "'.(__('Get a visual challenge', 'mgm')).'",
			                        audio_challenge : "'.(__('Get an audio challenge', 'mgm')).'",
			                        refresh_btn : "'.(__('Get a new challenge', 'mgm')).'",
			                        help_btn : "'.(__('Help', 'mgm')).'",
			                        incorrect_try_again : "'.(__('Incorrect try again', 'mgm')).'",
			                },
			        lang : \''.(substr(get_locale(),0,2)).'\',        
			        theme : \'white\' };
			        </script>'."\n";       
        
        $script .= '<script type="text/javascript" src="'. $server . '/challenge?k=' . $setting['recaptcha_public_key'] . $errorpart . '"></script>

		<noscript>
	  		<iframe src="'. $server . '/noscript?k=' . $setting['recaptcha_public_key'] . $errorpart . '" height="300" width="500" frameborder="0"></iframe><br/>
	  		<textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
	  		<input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
		</noscript>';
        
        return $script;
	}
	/**
	 * gets a URL where the user can sign up for reCAPTCHA. If your application
	 * has a configuration page where you enter a key, you should provide a link
	 * using this function.
	 * @param string $domain The domain where the page is hosted
	 * @param string $appname The name of your application
	 */
	public function recaptcha_get_signup_url() {		
		return ("https://www.google.com/recaptcha/admin/create?" .  $this->_recaptcha_qsencode(array ('domains' => str_replace(array('http://','https://'),'',get_option('siteurl')), 'app' => 'Magic Members')));
	}
	
	/**
  * Calls an HTTP POST function to verify if the user's guess was correct
  * @param string $privkey
  * @param string $remoteip
  * @param string $challenge
  * @param string $response
  * @param array $extra_params an array of extra variables to post to the server
  * @return ReCaptchaResponse
  */
	public function recaptcha_check_answer($challenge, $response, $extra_params = array()) {
		//system settings
		$recaptcha_response = new stdClass;
		$recaptcha_response->error = null;
		
		$setting = mgm_get_class('system')->get_setting();
		
		$use_ssl = (isset($_SERVER['https'])) ? true : false ;
		$remoteip = mgm_get_client_ip_address();
		if (empty($setting['recaptcha_private_key']) || empty($setting['recaptcha_public_key']) ) {			
			$recaptcha_response->is_valid = false;
	        $recaptcha_response->error = __("reCAPTCHA API keys are blank. ", 'mgm');
	        return $recaptcha_response;
		}
		
		if ($remoteip == null || $remoteip == '') {			
			$recaptcha_response->is_valid = false;
	        $recaptcha_response->error = __("For security reasons, you must pass the remote IP to reCAPTCHA", 'mgm');
	        return $recaptcha_response;
		}	
		
		//discard spam submissions
		if ($challenge == null || strlen($challenge) == 0 || $response == null || strlen($response) == 0) {		       
		        $recaptcha_response->is_valid = false;
		        $recaptcha_response->error = __('The Captcha String isn\'t correct', 'mgm');
		        return $recaptcha_response;
		}
		
		$response = $this->_recaptcha_http_post( $setting['recaptcha_verify_server'], "/recaptcha/api/verify",
		                                  array (
		                                         'privatekey' => $setting['recaptcha_private_key'],
		                                         'remoteip' => $remoteip,
		                                         'challenge' => $challenge,
		                                         'response' => $response
		                                         ) + $extra_params
		                                  );
						
		
		$recaptcha_response->is_valid = $response['status'];
		
		if(isset($response['error'])){		        
			$recaptcha_response->error = __('The Captcha String isn\'t correct', 'mgm');
		}
		
		return $recaptcha_response;
	}
	//query string encode:
	public function _recaptcha_qsencode($data) {
        $req = "";
        foreach ( $data as $key => $value )
                $req .= $key . '=' . urlencode( stripslashes($value) ) . '&';

        // Cut the last '&'
        $req = substr($req,0,strlen($req)-1);
        
        return $req;
	}
	//request - socket
	public function _recaptcha_http_post($host, $path, $data, $port = 80) {
		$response = array('status' => false);
        $req = $this->_recaptcha_qsencode($data);

        $http_request  = "POST $path HTTP/1.0\r\n";
        $http_request .= "Host: $host\r\n";
        $http_request .= "Content-Type: application/x-www-form-urlencoded;\r\n";
        $http_request .= "Content-Length: " . strlen($req) . "\r\n";
        $http_request .= "User-Agent: reCAPTCHA/PHP\r\n";
        $http_request .= "\r\n";
        $http_request .= $req;

        $str_response = '';
       
        if( false == ( $fs = @fsockopen($host, $port, $errno, $errstr, 10) ) ) {
                //die ('Could not open socket');
                return $this->_recaptcha_curl_post($host . $path, $data);
        }

        @fwrite($fs, $http_request);

        while ( !feof($fs) )
                $str_response .= fgets($fs, 1160); // One TCP-IP packet
        @fclose($fs);
        
        $arr_response 	= explode("\r\n\r\n", $str_response, 2);
        $arr_response 		= explode ("\n", $arr_response[1]);
		
         if(isset($arr_response[0]) && trim($arr_response[0]) == 'true') {
	    	$response['status'] = true;
	    }else 
	    	$response['error'] = $arr_response[1];	   
	    	    
	    return $response;
	}
	//request - curl
	public function _recaptcha_curl_post($url, $data) {	
		$response = array('status' => false);
		$req = $this->_recaptcha_qsencode($data);	
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url );
		
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type' => 'application/x-www-form-urlencoded', 'User-Agent' => 'reCAPTCHA/PHP'));			
		curl_setopt($ch, CURLOPT_HEADER, 0);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($ch, CURLOPT_NOPROGRESS, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0); 
		curl_setopt($ch, CURLOPT_POST, 1); 
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 30); 		
		curl_setopt($ch, CURLOPT_REFERER, get_option('siteurl')); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);					
		
	    $str_response = curl_exec($ch);
	    $arr_response = explode("\n", $str_response, 2);
	    if(isset($arr_response[0]) && trim($arr_response[0]) == 'true') {
	    	$response['status'] = true;
	    }else 
	    	$response['error'] = $arr_response[1];	   
	   
	    return $response;
	}
 	
	/**
	  * Calls an file_get_contents to verify if the user's guess was correct
	  * @param string $privkey
	  * @param string $remoteip
	  * @param string $response
	  * @return ReCaptchaResponse
	 */	
	public function no_captcha_recaptcha_check_answer($response){
		
		//system settings
		$recaptcha_response = new stdClass;
		$recaptcha_response->error = null;
		
		//system obj
		$setting = mgm_get_class('system')->get_setting();
		//ip address
		$remoteip = mgm_get_client_ip_address();
		//check
		if (empty($setting['recaptcha_private_key']) || empty($setting['recaptcha_public_key']) ) {			
			$recaptcha_response->is_valid = false;
	        $recaptcha_response->error = __("reCAPTCHA API keys are blank. ", 'mgm');
	        return $recaptcha_response;
		}
		//check
		if ($remoteip == null || $remoteip == '') {			
			$recaptcha_response->is_valid = false;
	        $recaptcha_response->error = __("For security reasons, you must pass the remote IP to reCAPTCHA", 'mgm');
	        return $recaptcha_response;
		}
		
		//init url
		$url = "https://".$setting['recaptcha_verify_server']. "/recaptcha/api/siteverify";
		
		$url = add_query_arg(array ('secret' => $setting['recaptcha_private_key'],'remoteip' => $remoteip,'response' => $response),$url);
		
		//call - issue #2399
	    //$response=file_get_contents($url);
	    //call	    
	    $response= $this->file_get_contents_curl($url);	    
	    //check
	    if($response.success==false)  {
	    	$recaptcha_response->is_valid = false;
	    	$recaptcha_response->error = __('You are spammer ! Get the @$%K out', 'mgm');
	    }else {
	    	$recaptcha_response->is_valid = true;
	    	$recaptcha_response->error = null;
	    }	    
	    //return
		return $recaptcha_response;			
	}

	/**
	 * Gets the  HTML.
	 * This is called from the browser, and the resulting NO CAPTCHA reCAPTCHA HTML widget
	 * is embedded within the HTML form it was called from.
	 * @param string $pubkey A public key for reCAPTCHA
	 * @param string $error The error given by reCAPTCHA (optional, default is null)
	 * @param boolean $use_ssl Should the request be made over ssl? (optional, default is true)
	
	 * @return string - The HTML to be embedded in the user's form.
	 */
	public function no_captcha_recaptcha_get_html($error = null) {
		
		$setting = mgm_get_class('system')->get_setting();
			
		$use_ssl = (isset($_SERVER['https']) || isset($_SERVER['HTTPS'])) ? true : false ;
		
		if (empty($setting['recaptcha_private_key']) || empty($setting['recaptcha_public_key']) ) {
			return __("reCAPTCHA API keys are blank. ", 'mgm');
		}
		
		$use_ssl = true;
		
		if ($use_ssl) {
	    	$server = $setting['recaptcha_api_secure_server'];
        } else {
        	$server = $setting['recaptcha_api_server'];
        }
        
        $html  = '';
        
        $html  = sprintf("<script src='%s.js'></script>",$server);

		$html .= sprintf("<div class='g-recaptcha' data-sitekey='%s'></div>",$setting['recaptcha_public_key']);
		//return
		return $html;  	
	}

	/**
	  * Calls an file get contents using curl to verify if the user's guess was correct
	  * @param string $url
	  * @return ReCaptchaResponse
	 */		
	public function file_get_contents_curl($url) {
	 
		$ch = curl_init();
		 
		curl_setopt($ch, CURLOPT_HEADER, 0);
		 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //Set curl to return the data instead of printing it to the browser.
		
		curl_setopt($ch, CURLOPT_URL, $url);
		 
		$data = curl_exec($ch);
		 
		curl_close($ch);
		 
		return $data;	 
	}		
}
// core/libs/utilities/mgm_recaptcha.php