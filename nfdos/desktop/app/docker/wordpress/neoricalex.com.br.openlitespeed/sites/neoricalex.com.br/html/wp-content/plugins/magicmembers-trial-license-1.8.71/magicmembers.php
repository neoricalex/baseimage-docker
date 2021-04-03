<?php
/*
Plugin Name: Magic Members
Plugin URI: https://www.magicmembers.com/
Description: Magic Members is a premium Wordpress Membership Plugin that turn your WordPress blog into a powerful, fully automated membership site.
Author: Magical Media Group
Author URI: http://www.magicalmediagroup.com/
Text Domain: mgm
Version: 1.8.71
Build: 2.9.0
Distribution: 01/05/2021
Requires: Atleast WP 5.0+, Tested upto WP 5.6
*/
// buffer for ajax, pdf output issue when open, logout issue when closed, introduced new send_headers hook in mgm_init
// if((isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"]  == 'XMLHttpRequest') || isset($_FILES)) ob_start();
// versioned core: for loading different versions from single installation
// buffer for ajax

if( ( ( isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && $_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') || isset($_FILES) ) && ! headers_sent() ) @ob_start();

$core = 'core';
// reset
if($version = get_option('mgm_core_version')) $core = 'core-'.$version;
// load init class
$mgm_init_cls = @include_once( $core . '/mgm_init.php');
// init
$mgm_init = new $mgm_init_cls;
// setup
$mgm_init->start();
