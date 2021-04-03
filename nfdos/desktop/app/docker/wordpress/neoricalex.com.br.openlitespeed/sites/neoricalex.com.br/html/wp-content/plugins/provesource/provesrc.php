<?php
/**
 * @package: provesrc-plugin
 */

/**
 * Plugin Name: ProveSource
 * Description: ProveSource is a social proof marketing platform that works with your Wordpress and WooCommerce websites out of the box
 * Version: 2.2.2
 * Author: ProveSource LTD
 * Author URI: https://provesrc.com
 * License: GPLv3 or later
 * Text Domain: provesrc-plugin
 */

if (!defined('ABSPATH')) {
    die;
}

/** constants */
class PSConstants
{
    public static $debug = true;

    public static function options_group()
    {
        return 'provesrc_options';
    }

    public static function legacy_option_api_key()
    {
        return 'api_key';
    }

    public static function option_api_key() {
        return 'ps_api_key';
    }

    public static function host() {
        return 'https://api.provesrc.com';
    }

    public static function version() {
        return '2.2.2';
    }
}

/* hooks */
add_action('admin_menu', 'provesrc_admin_menu'); //1.5.0
add_action('admin_init', 'provesrc_admin_init'); //2.5.0
add_action('admin_notices', 'provesrc_admin_notice_html'); //3.1.0
add_action('wp_head', 'provesrc_inject_code'); //1.2.0
// add_action('woocommerce_checkout_create_order', 'provesrc_woocommerce_order_created', 20, 2);
add_action('woocommerce_checkout_order_processed', 'provesrc_order_processed', 999, 3);
register_uninstall_hook(__FILE__, 'provesrc_uninstall_hook');
register_activation_hook(__FILE__, 'provesrc_activation_hook');
register_deactivation_hook(__FILE__, 'provesrc_deactivation_hook');
add_action('update_option_' . PSConstants::option_api_key(), 'provesrc_api_key_updated', 999, 0);
add_action('add_option_' . PSConstants::option_api_key(), 'provesrc_api_key_updated', 999, 0);

// if (provesrc_has_woocommerce()) {
//     add_action('woocommerce_created_customer', 'provesrc_woo_user_register', 999, 3);
// } else {
//     add_action('user_register', 'provesource_user_register', 999);
// }

function provesrc_admin_menu()
{
    add_menu_page('ProveSource Settings', 'ProveSource', 'manage_options', 'provesrc', 'provesrc_admin_menu_page_html', 'dashicons-provesrc');
}

function provesrc_admin_init()
{
    wp_enqueue_style('provesrc_admin_style', plugin_dir_url(__FILE__).'style.css');
    register_setting(PSConstants::options_group(), PSConstants::option_api_key());
    register_setting(PSConstants::options_group(), PSConstants::legacy_option_api_key());
    wp_register_style('dashicons-provesrc', plugin_dir_url(__FILE__).'/assets/css/dashicons-provesrc.css');
    wp_enqueue_style('dashicons-provesrc');
}

function provesrc_inject_code()
{
    $version = PSConstants::version();
    $apiKey = provesrc_get_api_key(); ?>

    <!-- Start of Async ProveSource Code (Wordpress / Woocommerce v<?php echo $version; ?>) --><script>!function(o,i){window.provesrc&&window.console&&console.error&&console.error("ProveSource is included twice in this page."),provesrc=window.provesrc={dq:[],display:function(){this.dq.push(arguments)}},o._provesrcAsyncInit=function(){provesrc.init({apiKey:"<?php echo $apiKey; ?>",v:"0.0.4"})};var r=i.createElement("script");r.type="text/javascript",r.async=!0,r["ch"+"ar"+"set"]="UTF-8",r.src="https://cdn.provesrc.com/provesrc.js";var e=i.getElementsByTagName("script")[0];e.parentNode.insertBefore(r,e)}(window,document);</script><!-- End of Async ProveSource Code -->

    <?php
}

function provesrc_order_processed($id, $data, $order) {
    try {
        if(!isset($id) || $id < 1) {
            provesrc_log('woocommerce order event (no id)', $order);
            provesrc_send_webhook($order);
        } else {
            provesrc_log('woocommerce order event (with id)', ['id' => $id, 'order' => $order]);
            provesrc_send_webhook(wc_get_order($id));
        }    
    } catch(Exception $err) {
        provesrc_handle_error('failed to process order', $err, ['orderId' => $id]);
    }
}

function provesource_user_register($id)
{
    try {
        $user = new WP_User($id);
        $meta = get_user_meta($id);

        provesrc_log('wp user event, user id:', $id);
        provesrc_send_user($user->user_email, $meta);
    } catch(Exception $err) {
        provesrc_handle_error('failed to process WP user register', $err);
    }
}

function provesrc_woo_user_register($id, $data, $pass)
{
    try {
        // $user = new WP_User($id);
        $meta = get_user_meta($id);
        provesrc_log('woo user event, user id:', $id);
        provesrc_send_user($data['user_email'], $meta);
    } catch(Exception $err) {
        provesrc_handle_error('failed to process Woocommerce user register', $err);
    }
}

function provesrc_uninstall_hook() 
{
    if(!current_user_can('activate_plugins')) {
        return;
    }
    $data = array(
        'email' => get_option('admin_email'),
        'siteUrl' => get_site_url(),
        'siteName' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'woocommerce' => provesrc_has_woocommerce(),
        'event' => 'uninstall',
    );
    return provesrc_send_request('/wp/uninstall', $data, true);
}

function provesrc_activation_hook()
{
    if(!current_user_can('activate_plugins')) {
        return;
    }
    $data = array(
        'email' => get_option('admin_email'),
        'siteUrl' => get_site_url(),
        'siteName' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'woocommerce' => provesrc_has_woocommerce(),
        'event' => 'activated',
    );
    return provesrc_send_request('/wp/state', $data, true);
}

function provesrc_deactivation_hook()
{
    if(!current_user_can('activate_plugins')) {
        return;
    }
    $data = array(
        'email' => get_option('admin_email'),
        'siteUrl' => get_site_url(),
        'siteName' => get_bloginfo('name'),
        'description' => get_bloginfo('description'),
        'woocommerce' => provesrc_has_woocommerce(),
        'event' => 'deactivated',
    );
    return provesrc_send_request('/wp/state', $data, true);
}

function provesrc_api_key_updated() 
{
    try {
        $apiKey = provesrc_get_api_key();
        if($apiKey == null) {
            provesrc_log('bad api key update');
            return;
        }
        provesrc_log('api key updated');
    
        $orders = [];
        if(provesrc_has_woocommerce()) {
            $wcOrders = wc_get_orders(array(
                'limit' => 30,
                'orderby' => 'date',
                'order' => 'DESC'
            ));
            foreach($wcOrders as $wco) {
                array_push($orders, provesrc_get_order_payload($wco));
            }
        }
        
        $data = array(
            'secret' => 'simple-secret',
            'woocommerce' => provesrc_has_woocommerce(),
            'email' => get_option('admin_email'),
            'siteUrl' => get_site_url(),
            'siteName' => get_bloginfo('name'),
            'description' => get_bloginfo('description'),
            'orders' => $orders
        );
        provesrc_log('sending setup data' . '(' . count($orders) . ' orders)');
        provesrc_send_request('/wp/setup', $data);
    } catch(Exception $err) {
        provesrc_handle_error('failed updating api key', $err);
    }
}

/** hooks - END */

/** helpers */

function provesrc_send_user($email, $meta)
{
    $data = array(
        'email' => $email,
        'siteUrl' => get_site_url(),
    );
    try {    
        if (isset($meta['first_name'][0])) {
            $data['firstName'] = $meta['first_name'][0];
        } elseif (isset($_POST['first_name']) && strlen($_POST['first_name']) > 0) {
            $data['firstName'] = $_POST['first_name'];
        }
    
        if (isset($meta['last_name'][0])) {
            $data['lastName'] = $meta['last_name'][0];
        } elseif (isset($_POST['last_name']) && strlen($_POST['last_name' > 0])) {
            $data['lastName'] = $_POST['last_name'];
        }
    
        $ips = provesrc_get_ips();
        if (!(empty($ips))) {
            $data['ips'] = $ips;
            $data['ip'] = $ips[0];
        }
    
        return provesrc_send_request('/webhooks/track/wordpress', $data);
    } catch(Exception $err) {
        provesrc_handle_error('failed to send user', $err, array('data' => $data, 'meta' => $meta));
    }
}

function provesrc_send_webhook($order)
{
    try {
        $data = provesrc_get_order_payload($order);
        return provesrc_send_request('/webhooks/track/woocommerce', $data);
    } catch(Exception $err) {
        provesrc_handle_error('failed to send webhook', $err, $order);
    }
}

function provesrc_get_order_payload($order) {
    $payload = array(
        'orderId' => $order->get_id(),
        'firstName' => $order->get_billing_first_name(),
        'lastName' => $order->get_billing_last_name(),
        'email' => $order->get_billing_email(),
        'ip' => $order->get_customer_ip_address(),
        'ips' => provesrc_get_ips(),
        'siteUrl' => get_site_url(),
        'total' => (int) $order->get_total(),
        'currency' => $order->get_currency(),
        'products' => provesrc_get_products_array($order),
    );
    if(method_exists($order, 'get_date_created')) {
        $date = $order->get_date_created();
        if(!empty($date) && method_exists($date, 'getTimestamp')) {
            $payload['date'] = $order->get_date_created()->getTimestamp() * 1000;
        }
    }        
    return $payload;
}

function provesrc_get_products_array($order) 
{
    $items = $order->get_items();
    $products = array();
    foreach ($items as $item) {
        try {
            $quantity = $item->get_quantity();
            $product = $item->get_product();
            if(!is_object($product)) {
                $p = array(
                    'id' => $item->get_id(),
                    'name' => $item->get_name(),
                );
            } else {
                $images_arr = wp_get_attachment_image_src($product->get_image_id(), array('72', '72'), false);
                $image = null;
                if ($images_arr !== null && $images_arr[0] !== null) {
                    $image = $images_arr[0];
                    if (is_ssl()) {
                        $image = str_replace('http', 'https', $image);
                    }
                }
                $p = array(
                    'id' => $product->get_id(),
                    'quantity' => (int) $quantity,
                    'price' => (int) $product->get_price(),
                    'name' => $product->get_title(),
                    'link' => get_permalink($product->get_id()),
                    'image' => $image,
                );
            }
            array_push($products, $p);
        } catch(Exception $err) {
            provesrc_log('failed processing line item', $err);
        }
    }
    return $products;
}

function provesrc_send_error($message, $err, $data = null)
{
    try {
        $payload = array(
            'message' => $message,
            'err' => provesrc_encode_exception($err),
            'data' => $data,
        );
        $apiKey = provesrc_get_api_key();
        $headers = array(
            'Content-Type' => 'application/json',
            'x-plugin-version' => PSConstants::version(),
            'x-site-url' => get_site_url(),
            'Authorization' => "Bearer $apiKey"
        );
        return wp_remote_post(PSConstants::host() . '/webhooks/wp-error', array(
            'headers' => $headers,
            'body' => json_encode($payload),
        ));
    } catch(Exception $err) {
        provesrc_log('failed sending error', $err);
    }
}

function provesrc_send_request($path, $data, $ignoreAuth = false) {
    try {
        $headers = array(
            'Content-Type' => 'application/json',
            'x-plugin-version' => PSConstants::version(),
            'x-site-url' => get_site_url(),
            'x-wp-version' => get_bloginfo('version'),
        );

        $apiKey = provesrc_get_api_key();
        if (!$ignoreAuth && $apiKey == null) {
            return;
        } else if(!empty($apiKey)) {
            $headers['authorization'] = "Bearer $apiKey";
        }

        if(provesrc_has_woocommerce()) {
            $headers['x-woo-version'] = WC()->version;
        }

        $url = PSConstants::host() . $path;
        $data = array(
            'headers' => $headers,
            'body' => json_encode($data),
        );
        provesrc_log('sending request', ['url' => $url]);
        $res = wp_remote_post($url, $data);
        provesrc_log('got response', ['url' => $url]);
        return $res;
    } catch(Exception $err) {
        provesrc_handle_error('failed sending request', $err, $data);
    }
}

function provesrc_handle_error($message, $err, $data = null)
{
    provesrc_log($message, $err);
    provesrc_send_error($message, $err, $data);
}

function provesrc_get_api_key()
{
    $legacyKey = get_option(PSConstants::legacy_option_api_key());
    if(provesrc_isvalid_api_key($legacyKey)) {
        return $legacyKey;
    }
    $apiKey = get_option(PSConstants::option_api_key());
    if(provesrc_isvalid_api_key($apiKey)) {
        return $apiKey;
    }
    return null;
}

function provesrc_isvalid_api_key($apiKey) {
    if (isset($apiKey) && strlen($apiKey) > 30) {
        $start = strpos($apiKey, '.');
        $end = strpos($apiKey, '.', $start + 1);
        $substr = substr($apiKey, $start + 1, $end - $start - 1);
        $json = json_decode(base64_decode($substr));

        if (is_object($json) && isset($json->accountId)) {
            return true;
        }
    }
    return false;
}

function provesrc_get_ips() 
{
    $ips = [];
    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        array_push($ips, $_SERVER['HTTP_CLIENT_IP']);
    } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        array_push($ips, $_SERVER['HTTP_X_FORWARDED_FOR']);
    } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
        array_push($ips, $_SERVER['HTTP_X_FORWARDED']);
    } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        array_push($ips, $_SERVER['HTTP_FORWARDED_FOR']);
    } else if (isset($_SERVER['HTTP_FORWARDED'])) {
        array_push($ips, $_SERVER['HTTP_FORWARDED']);
    } else if (isset($_SERVER['REMOTE_ADDR'])) {
        array_push($ips, $_SERVER['REMOTE_ADDR']);
    }
    return $ips;
}

function provesrc_has_woocommerce() {
    return in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')));
}

function provesrc_admin_menu_page_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $apiKey = provesrc_get_api_key(); ?>

	<div class="wrap" id="ps-settings">
		<!-- <h1><?=esc_html(get_admin_page_title()); ?></h1> -->
		<a href="https://provesrc.com">
			<img class="top-logo" src="<?php echo plugin_dir_url(__FILE__).'assets/top-logo.png'; ?>">
		</a>
		<form action="options.php" method="post">
			<?php
				settings_fields(PSConstants::options_group());
				do_settings_sections(PSConstants::options_group()); 
			?>

			<div class="ps-settings-container">
				<?php if ($apiKey != null) { ?>
					<div class="ps-success">ProveSource is Installed</div>
                    <div class="ps-warning">
                        If you still see <strong>"waiting for data..."</strong> open your website in <strong>incognito</strong> or <strong>clear cache</strong>
                        <br>If you have <strong>cache or security plugins</strong>, please <a href="http://help.provesrc.com/en/articles/4206151-common-wordpress-woocommerce-issues">see this guide</a> about possible issues and how to solve them
                    </div>
				<?php } else { ?>
                    <div class="ps-red-warning">Add your API Key below</div>
					<div class="account-link">If you don't have an account - <a href="https://console.provesrc.com/?utm_source=woocommerce&utm_medium=plugin&utm_campaign=woocommerce-signup#/signup" target="_blank">signup here!</a></div>
				<?php } ?>

				<div class="label">Your API Key:</div>
				<input type="text" placeholder="required" name="<?php echo PSConstants::option_api_key(); ?>" value="<?php echo esc_attr($apiKey); ?>" />
				<div class="m-t"><a href="https://console.provesrc.com/#/settings" target="_blank">Where is my API Key?</a></div>
			</div>

			<?php submit_button('Save'); ?>
		
        </form>
    </div>

    <?php
}

function provesrc_admin_notice_html()
{
    $apiKey = provesrc_get_api_key();
    if ($apiKey != null) {
        return;
    }

    // $screen = get_current_screen();
    // if($screen !== null && strpos($screen->id, 'provesrc') > 0) return;

    ?>

	<div class="notice notice-error is-dismissible">
        <p class="ps-error">ProveSource is not configured! <a href="admin.php?page=provesrc">Click here</a></p>
    </div>

	<?php
}

function provesrc_log($message, $data = null)
{
    $log = current_time("Y-m-d\TH:i:s.u ");
    if (isset($data)) {
        $log .= "[ProveSource] " . $message . ": " . print_r($data, true);
    } else {
        $log .= "[ProveSource] " . $message;
    }
    $log .= "\n";
    error_log($log);

    if(PSConstants::$debug) {
        $pluginlog = plugin_dir_path(__FILE__).'debug.log';
        error_log($log, 3, $pluginlog);
    }
}

function provesrc_var_dump_str($data)
{
    ob_start();
    var_dump($data);

    return ob_get_clean();
}

function provesrc_encode_exception($err) {
    if(!isset($err) || is_null($err)) {
        return [];
    }
    return [
        'message' => $err->getMessage(),
        'code' => $err->getCode(),
        'file' => $err->getFile() . ':' . $err->getLine(),
        'trace' => substr($err->getTraceAsString(), 0, 500),
    ];
}

/* helpers - END */

?>
