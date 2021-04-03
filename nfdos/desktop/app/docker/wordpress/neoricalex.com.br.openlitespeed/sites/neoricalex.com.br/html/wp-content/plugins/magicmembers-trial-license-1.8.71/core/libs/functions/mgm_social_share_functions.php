<?php if ( !defined('ABSPATH') ) exit('No direct script access allowed');
// -----------------------------------------------------------------------
/**
 * Magic Members social share functions
 *
 * @package MagicMembers
 * @subpackage Social Share
 * @since 2.6
 */

/**
 * process facebook socail share callback
 * @return mixed/object error
 */	
function mgm_facebook_share_callback ($share_url,$coupon) {
	//inti
	$url = mgm_get_custom_url('register');
	//redirect
	$redirect_url  = strpos($url,'?') !== false ? $url : $url . '?';
	//encode coupon
	$redirect_url .= "social_token=".base64_encode($coupon);
	//fb id
	$app_id = mgm_get_class('system')->get_setting('facebook_id');
	//build link
	$link = sprintf("<a href=https://www.facebook.com/dialog/feed?app_id=%s&link=%s&redirect_uri=%s>Share on Face Book</a>",$app_id,$share_url,$redirect_url);		
	//return
	return $link;
}

/**
 * process twitter socail share callback
 * @return mixed/object error
 */	
function mgm_twitter_share_callback ($share_url,$coupon) {
	//inti
	$url = mgm_get_custom_url('register');
	//redirect
	$redirect_url  = strpos($url,'?') !== false ? $url : $url . '?';
	//encode coupon
	$redirect_url .= "social_token=".base64_encode($coupon);
	//html
	$html ='';
	//link
	$link = sprintf('<a href="https://twitter.com/share" class="twitter-share-button" data-url="%s" data-size="large" data-count="none">Tweet</a>',$share_url);
	$html .= $link;
	//twitter js
	$twitter_js 	= '<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>';
	$twitter_js    .= '<script> window.onload = function() {  twttr.events.bind("tweet", function(event) { window.location = "'.$redirect_url.'";});}</script>';
	
	$html .= $twitter_js;
	//return
	return $html;
}

/**
 * process google socail share callback
 * @return mixed/object error
 */	
function mgm_google_share_callback ($share_url,$coupon) {
	//inti
	$url = mgm_get_custom_url('register');
	//redirect
	$redirect_url  = strpos($url,'?') !== false ? $url : $url . '?';
	//encode coupon
	$redirect_url .= "social_token=".base64_encode($coupon);
	//html
	$html ='';	
	//link
	$link = '<div 
	  class="g-plusone"
	  data-expandto="bottom"
	  data-onendinteraction="onEnded"
	  data-onstartinteraction="onStarted"
	  data-recommendations="false"
	  data-annotation="none"
	  data-height="25"
	  data-autoclose="true"
	  data-href ="'.$share_url.'"
	  data-callback="onCallback" 
	  data-width="300"></div>';
	
	$gplus_js 	= '<script src="https://apis.google.com/js/plusone.js"></script>';
	
	$gplus_js 	.= '<script>
		function onStarted(args){ console.log("started");}
		function onEnded(args){if(args.type =="confirm"){ window.location = "'.$redirect_url.'";}	}
		function onCallback(args){console.log("callback");console.log(args);}
		</script>';
	
	$html .= $gplus_js;
	$html .= $link;
	$html .= $gplus_js;	
	//return
	return $html;
}

/**
 * process linkedin socail share callback
 * @return mixed/object error
 */	
function mgm_linkedin_share_callback ($share_url,$coupon) {
	//inti
	$url = mgm_get_custom_url('register');
	//redirect
	$redirect_url  = strpos($url,'?') !== false ? $url : $url . '?';
	//encode coupon
	$redirect_url .= "social_token=".base64_encode($coupon);	
	
	//incomplete - due to data-onsuccess bug not at fixed - https://developer.linkedin.com/thread/2805
	
	$html = "";
	
	$ln_js = '<script src="//platform.linkedin.com/in.js" type="text/javascript">lang: en_US</script>';
	
	$html .= $ln_js;
	
	$ln_btn ='<script type="IN/Share" data-onsuccess="lnksuccess" data-onerror="error" data-url="'.$share_url.'"></script>';
	
	$html .= $ln_btn;
	
	$ln_callback = '<script>
    function linkedinSuccess(url) {
        alert("url = " + url + "  shared successfully");
    }
    function lnkerror(url){
      alert("something goes wrong in url sharing");
    }
	</script>';
	
	$html .= $ln_callback;
	//return
	return $html;
}

/**
 * add social share links to short code.
 *
 * @param array 
 * @return string links
 */	
function mgm_social_share($args){
	//share link
	$share_url 	= (isset($args['share_url'])) ? html_entity_decode($args['share_url']) : null;
	//encode share link
	$share_url 	= urldecode($share_url);
	//social networks
	$networks  	= (isset($args['network'])) ? $args['network'] : null;
	//coupon code
	$coupon 	=  (isset($args['coupon'])) ? $args['coupon'] : null;;
	//init
	$links  ='';		
	//check
	if(!empty($networks) && $networks != null) {
		//explode
		$networks = explode(',',$networks);
		//filter
		$networks = array_filter($networks);
		//check
		if(!empty($networks) ){
			//loop
			foreach ($networks as $network){	
						
				// check first callback by name
				if(function_exists('mgm_'.$network.'_share_callback')){
					//call back
					$links  .= "<br/>". call_user_func_array( 'mgm_'.$network.'_share_callback', array($share_url,$coupon));
				}			
			}
		}		
	}
	//return		
	return $links;	
}