<?php
/**
 * Thrive Themes - https://thrivethemes.com
 *
 * @package thrive-dashboard
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden
}

require_once TVE_DASH_PATH . '/inc/ttw-account/traits/trait-singleton.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/traits/trait-magic-methods.php';
require_once TVE_DASH_PATH . '/inc/ttw-account/traits/trait-ttw-utils.php';

class TD_TTW_Update_Manager {

	use TD_Singleton;

	use TD_TTW_Utils;

	const NAME = 'tve_dash_ttw_account';

	const SUITE_URL = 'https://thrivethemes.com/suite/';

	/**
	 * @var array
	 */
	protected $_errors = array();

	/**
	 * TD_TTW_Update_Manager constructor.
	 */
	private function __construct() {

		$this->init();
	}

	public function init() {

		$this->_includes();
		$this->_actions();
	}

	/**
	 * Handler for tve_dash_ttw_account section
	 */
	public function tve_dash_ttw_account() {

		if ( ! TD_TTW_Connection::get_instance()->is_connected() ) {
			TD_TTW_Connection::get_instance()->render();
		} else {
			TD_TTW_User_Licenses::get_instance()->render();
		}
	}

	/**
	 * Loads needed files
	 */
	private function _includes() {

		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-connection.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-user-licenses.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-license.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-request.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-proxy-request.php';
		require_once TVE_DASH_PATH . '/inc/ttw-account/classes/class-td-ttw-messages-manager.php';
	}

	/**
	 * Add needed action for ttw section
	 */
	private function _actions() {

		add_action( 'admin_menu', array( $this, 'register_section' ), 10 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ), PHP_INT_MAX );
		add_action( 'current_screen', array( $this, 'try_process_connection' ) );
		add_action( 'current_screen', array( $this, 'try_set_url' ) );
		add_action( 'current_screen', array( $this, 'try_logout' ) );
		add_action( 'admin_init', array( $this, 'ensure_license_details' ) );
	}

	/**
	 * Ensure license details
	 */
	public function ensure_license_details() {

		/** @var $connection TD_TTW_Connection */
		$connection = TD_TTW_Connection::get_instance();
		/** @var $licenses TD_TTW_User_Licenses */
		$licenses = TD_TTW_User_Licenses::get_instance();

		if ( $connection->is_connected() && empty( $licenses->get() ) ) {
			$licenses->get_licenses_details();
		}
	}

	/**
	 * Register ttw section
	 */
	public function register_section() {

		if ( empty( $_REQUEST['page'] ) || self::NAME !== $_REQUEST['page'] ) {
			return;
		}

		add_submenu_page(
			null,
			null,
			null,
			'manage_options',
			self::NAME,
			array( $this, 'tve_dash_ttw_account' )
		);
	}

	/**
	 * Process ttw connection
	 */
	public function try_process_connection() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		/**  @var $connection TD_TTW_Connection */
		$connection = TD_TTW_Connection::get_instance();

		if ( ! empty( $_REQUEST['td_token'] ) ) {

			$processed = $connection->process_request();

			if ( true === $processed ) {

				/** @var $licenses TD_TTW_User_Licenses */
				$licenses = TD_TTW_User_Licenses::get_instance();

				delete_transient( TD_TTW_User_Licenses::NAME );
				$licenses->get_licenses_details(); //get licenses details

				if ( $licenses->has_membership() && $licenses->is_membership_active() ) {
					$connection->push_message( 'Your account has been successfully connected.', 'success' );
				}

				wp_redirect( $this->get_admin_url() );
				die();
			}
		}
	}

	/**
	 * Log out ttw account
	 */
	public function try_logout() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		if ( ! empty( $_REQUEST['td_disconnect'] ) ) {

			$connection = TD_TTW_Connection::get_instance();

			$params  = array(
				'website' => get_site_url(),
			);
			$request = new TD_TTW_Request( '/api/v1/public/disconnect/' . $connection->ttw_id, $params );
			$request->set_header( 'Authorization', $connection->ttw_salt );

			$proxy_request = new TD_TTW_Proxy_Request( $request );
			$proxy_request->execute( '/tpm/proxy' );

			$connection->disconnect();

			wp_redirect( admin_url( 'admin.php?page=' . TD_TTW_Update_Manager::NAME ) );
			die;
		}
	}

	public function try_set_url() {

		if ( ! TD_TTW_Connection::is_debug_mode() || ! $this->is_known_page() ) {
			return;
		}

		if ( ! empty( $_REQUEST['url'] ) && ! empty( $_REQUEST['td_action'] ) && $_REQUEST['td_action'] === 'set_url' ) {

			update_option( 'tpm_ttw_url', $_REQUEST['url'] );

			wp_redirect( $this->get_admin_url() );
			die;
		}
	}

	/**
	 * @return string|void
	 */
	public function get_admin_url() {

		return admin_url( 'admin.php?page=' . self::NAME );
	}

	/**
	 * Enqueue scripts
	 */
	public function enqueue_scripts() {

		if ( ! $this->is_known_page() ) {
			return;
		}

		wp_enqueue_style( 'td-ttw-style', $this->url( 'css/admin.css' ), array(), uniqid() );
	}

	/**
	 * Check if the screen is ttw account screen
	 *
	 * @return bool
	 */
	public function is_known_page() {

		return isset( $_REQUEST['page'] ) && $_REQUEST['page'] === self::NAME;
	}
}

TD_TTW_Update_Manager::get_instance();
