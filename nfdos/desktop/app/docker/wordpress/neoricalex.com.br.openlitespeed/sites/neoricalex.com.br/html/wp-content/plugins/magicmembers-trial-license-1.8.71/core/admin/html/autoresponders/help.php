<?php
	//init api
	require_once(MGM_LIBRARY_DIR.'third_party/aweber_api/aweber_api.php');	
	
	// Step 1: assign these values from https://labs.aweber.com/apps
	$consumerKey = $data['consumer_key'];
	$consumerSecret = $data['consumer_secret'];
	
	// Step 2: load this PHP file in a web browser, and follow the instructions to set
	// the following variables:
	$accessKey = '';
	$accessSecret = '';
	$list_id = '';
	
	if (!$consumerKey || !$consumerSecret){
	    print "You need to assign \$consumerKey and \$consumerSecret at the top of this script and reload.<br><br>" .
	        "These are listed on <a href='https://labs.aweber.com/apps' target=_blank>https://labs.aweber.com/apps</a><br>\n";
	    exit;
	}
	
	$aweber = new AWeberAPI($consumerKey, $consumerSecret);
	
	if (!$accessKey || !$accessSecret){
	    mgm_aweber_display_access_tokens($aweber);
	}
	
	try { 
	    $account = $aweber->getAccount($accessKey, $accessSecret);
	    $account_id = $account->id;
	
	    if (!$list_id){
	        mgm_aweber_display_available_lists($account);
	        exit;
	    }
	
	    print "You script is configured properly! " . 
	        "You can now start to develop your API calls, see the example in this script.<br><br>" .
	        "Be sure to set \$test_email if you are going to use the example<p>";
		
	} catch(AWeberAPIException $exc) { 
	    print "<h3>AWeberAPIException:</h3>"; 
	    print " <li> Type: $exc->type <br>"; 
	    print " <li> Msg : $exc->message <br>"; 
	    print " <li> Docs: $exc->documentation_url <br>"; 
	    print "<hr>"; 
	    exit(1); 
	}
	
	function mgm_aweber_get_self(){
	    return set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
	}
	
	function mgm_aweber_display_available_lists($account){
	    print "Please add one for the lines of PHP Code below to the top of your script for the proper list<br>" .
	            "then click <a href='" . get_self() . "'>here</a> to continue<p>";
	
	    $listURL ="/accounts/{$account->id}/lists/"; 
	    $lists = $account->loadFromUrl($listURL);
	    foreach($lists->data['entries'] as $list ){
	        print "<pre>\$list_id = '{$list['id']}'; // list name:{$list['name']}\n</pre>";
	    }
	}
	
	function mgm_aweber_display_access_tokens($aweber){
	    if (isset($_GET['oauth_token']) && isset($_GET['oauth_verifier'])){
	
	        $aweber->user->requestToken = $_GET['oauth_token'];
	        $aweber->user->verifier = $_GET['oauth_verifier'];
	        $aweber->user->tokenSecret = $_COOKIE['secret'];
	
	        list($accessTokenKey, $accessTokenSecret) = $aweber->getAccessToken();
	
	        print "Please add these lines of code to the magic members Aweber settings:<br>" .
	                "<pre>" .
	                "\n<h3><span style='color:green'>Access Token Key </span><span  style='color:red'>= '{$accessTokenKey}';</span>\n" . 
	                "\n<span  style='color:green'>Access Token Secret</span><span  style='color:red'> = '{$accessTokenSecret}';</span>\n</h3>" .
	                "</pre>";
	        exit;
	    }
	
	    if(!isset($_SERVER['HTTP_USER_AGENT'])){
	        print "This request must be made from a web browser\n";
	        exit;
	    }
	
	    $callbackURL = mgm_aweber_get_self();
	    list($key, $secret) = $aweber->getRequestToken($callbackURL);
	    $authorizationURL = $aweber->getAuthorizeUrl();
	
	    setcookie('secret', $secret);
	
	    header("Location: $authorizationURL");
	    exit();
	}