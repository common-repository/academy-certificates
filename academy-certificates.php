<?php
/*
 * Plugin Name:		Academy Certificates
 * Plugin URI:		http://demo.academylms.net
 * Description:		Award your students with a certificate after finishing a course.
 * Version:			1.0.7
 * Author:			Academy LMS
 * Author URI:		http://academylms.net
 * License:			GPL-3.0+
 * License URI:		http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:		academy-certificates
 * Domain Path:		/languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class AcademyCertificates {

	private function __construct() {
		$this->define_constants();
		$this->load_dependency();
		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );
	}

	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}
	public function define_constants() {
		/**
		 * Defines CONSTANTS for Whole plugins.
		 */
		define( 'ACADEMY_CERTIFICATES_VERSION', '1.0.7' );
		define( 'ACADEMY_CERTIFICATES_DB_VERSION', '1.0' );
		define( 'ACADEMY_CERTIFICATES_SETTINGS_NAME', 'academy_certificates_settings' );
		define( 'ACADEMY_CERTIFICATES_PLUGIN_FILE', __FILE__ );
		define( 'ACADEMY_CERTIFICATES_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
		define( 'ACADEMY_CERTIFICATES_PLUGIN_SLUG', 'academy-certificates' );
		define( 'ACADEMY_CERTIFICATES_PLUGIN_ROOT_URI', plugins_url( '/', __FILE__ ) );
		define( 'ACADEMY_CERTIFICATES_ROOT_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'ACADEMY_CERTIFICATES_INCLUDES_DIR_PATH', ACADEMY_CERTIFICATES_ROOT_DIR_PATH . 'includes/' );
		define( 'ACADEMY_CERTIFICATES_ASSETS_DIR_PATH', ACADEMY_CERTIFICATES_ROOT_DIR_PATH . 'assets/' );
		define( 'ACADEMY_CERTIFICATES_ASSETS_URI', ACADEMY_CERTIFICATES_PLUGIN_ROOT_URI . 'assets/' );
		define( 'ACADEMY_CERTIFICATES_TEMPLATE_DEBUG_MODE', false );
	}

	/**
	 * Initialize the plugin
	 *
	 * @return void
	 */
	public function init_plugin() {
		$Dependency = new AcademyCertificates\Admin\Dependency();
		$Dependency->dispatch_notice();
		if ( $Dependency->is_missing_dependency ) {
			return;
		}
		$this->load_textdomain();
		$this->dispatch_hooks();
	}

	public function dispatch_hooks() {
		AcademyCertificates\Database::init();
		AcademyCertificates\Assets::init();
		AcademyCertificates\Migration::init();
		AcademyCertificates\Certificate::init();

		if ( is_admin() ) {
			AcademyCertificates\Admin::init();
		} else {
			AcademyCertificates\Frontend::init();
		}
	}

	public function load_textdomain() {
		load_plugin_textdomain(
			'academy-certificates',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);
	}

	public function load_dependency() {
		require_once ACADEMY_CERTIFICATES_INCLUDES_DIR_PATH . 'autoload.php';
	}

	public function activate() {
		AcademyCertificates\Installer::init();
	}
}

/**
 * Initializes the main plugin
 *
 * @return \academy-certificates
 */
function Academy_Certificates_Start() {
	return AcademyCertificates::init();
}

// Plugin Start
Academy_Certificates_Start();
