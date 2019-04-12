<?php
/**
 * Plugin Name: WPSSO Social Sharing Buttons
 * Plugin Slug: wpsso-ssb
 * Text Domain: wpsso-ssb
 * Domain Path: /languages
 * Plugin URI: https://wpsso.com/extend/plugins/wpsso-ssb/
 * Assets URI: https://surniaulula.github.io/wpsso-ssb/assets/
 * Author: JS Morisset
 * Author URI: https://wpsso.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: WPSSO Core add-on offers social sharing buttons with support for hashtags, short URLs, bbPress, BuddyPress, WooCommerce, and much more.
 * Requires At Least: 3.8
 * Tested Up To: 5.1.1
 * WC Tested Up To: 3.5
 * Version: 2.11.4-b.1
 * 
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *      {major}         Major structural code changes / re-writes or incompatible API changes.
 *      {minor}         New functionality was added or improved in a backwards-compatible manner.
 *      {bugfix}        Backwards-compatible bug fixes or small improvements.
 *      {stage}.{level} Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 * 
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsb' ) ) {

	class WpssoSsb {

		/**
		 * Wpsso plugin class object variable.
		 */
		public $p;		// Wpsso

		/**
		 * Library class object variables.
		 */
		public $actions;	// WpssoSsbActions
		public $filters;	// WpssoSsbFilters
		public $reg;		// WpssoSsbRegister
		public $script;		// WpssoSsbScript
		public $style;		// WpssoSsbStyle

		/**
		 * Reference Variables (config, options, modules, etc.).
		 */
		private $have_req_min = true;	// Have minimum wpsso version.

		private static $instance;

		public function __construct() {

			require_once ( dirname( __FILE__ ) . '/lib/config.php' );

			WpssoSsbConfig::set_constants( __FILE__ );
			WpssoSsbConfig::require_libs( __FILE__ );	// Includes the register.php class library.

			$this->reg = new WpssoSsbRegister();		// Activate, deactivate, uninstall hooks.

			if ( is_admin() ) {
				add_action( 'admin_init', array( __CLASS__, 'required_check' ) );
			}

			add_filter( 'wpsso_get_config', array( $this, 'wpsso_get_config' ), 30, 2 );	// Checks core version and merges config array.

			add_action( 'wpsso_init_textdomain', array( __CLASS__, 'wpsso_init_textdomain' ) );
			add_action( 'wpsso_init_options', array( $this, 'wpsso_init_options' ), 10 );	// Sets the $this->p reference variable.
			add_action( 'wpsso_init_objects', array( $this, 'wpsso_init_objects' ), 10 );
			add_action( 'wpsso_init_plugin', array( $this, 'wpsso_init_plugin' ), 10 );
		}

		public static function &get_instance() {

			if ( ! isset( self::$instance ) ) {
				self::$instance = new self;
			}

			return self::$instance;
		}

		public static function required_check() {
			if ( ! class_exists( 'Wpsso' ) ) {
				add_action( 'all_admin_notices', array( __CLASS__, 'required_notice' ) );
			}
		}

		/**
		 * Also called from the activate_plugin method with $deactivate = true.
		 */
		public static function required_notice( $deactivate = false ) {

			self::wpsso_init_textdomain();

			$info = WpssoSsbConfig::$cf[ 'plugin' ][ 'wpssossb' ];

			$die_msg = __( '%1$s is an add-on for the %2$s plugin &mdash; please install and activate the %3$s plugin before activating %4$s.', 'wpsso-ssb' );

			$error_msg = __( 'The %1$s add-on requires the %2$s plugin &mdash; install and activate the %3$s plugin or <a href="%4$s">deactivate the %5$s add-on</a>.', 'wpsso-ssb' );

			if ( true === $deactivate ) {

				if ( ! function_exists( 'deactivate_plugins' ) ) {
					require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/plugin.php';
				}

				deactivate_plugins( $info[ 'base' ], true );	// $silent is true

				wp_die( '<p>' . sprintf( $die_msg, $info[ 'name' ], $info[ 'req' ][ 'name' ], $info[ 'req' ][ 'short' ], $info[ 'short' ] ) . '</p>' );

			} else {

				$deactivate_url = html_entity_decode( wp_nonce_url( add_query_arg( array(
					'action'        => 'deactivate',
					'plugin'        => $info[ 'base' ],
					'plugin_status' => 'all',
					'paged'         => 1,
					's'             => '',
				), admin_url( 'plugins.php' ) ), 'deactivate-plugin_' . $info[ 'base' ] ) );

				echo '<div class="notice notice-error error"><p>';
				echo sprintf( $error_msg, $info[ 'name' ], $info[ 'req' ][ 'name' ], $info[ 'req' ][ 'short' ], $deactivate_url, $info[ 'short' ] );
				echo '</p></div>';
			}
		}

		public static function wpsso_init_textdomain() {
			load_plugin_textdomain( 'wpsso-ssb', false, 'wpsso-ssb/languages/' );
		}

		/**
		 * Checks the core plugin version and merges the extension / add-on config array.
		 */
		public function wpsso_get_config( $cf, $plugin_version = 0 ) {

			$info = WpssoSsbConfig::$cf[ 'plugin' ][ 'wpssossb' ];

			if ( version_compare( $plugin_version, $info[ 'req' ][ 'min_version' ], '<' ) ) {
				$this->have_req_min = false;
				return $cf;
			}

			return SucomUtil::array_merge_recursive_distinct( $cf, WpssoSsbConfig::$cf );
		}

		/**
		 * Sets the $this->p reference variable for the core plugin instance.
		 */
		public function wpsso_init_options() {

			$this->p =& Wpsso::get_instance();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! $this->have_req_min ) {
				$this->p->avail['p_ext']['ssb'] = false;	// Signal that this extension / add-on is not available.
				return;
			}

			$this->p->avail['p_ext']['ssb'] = true;	// Signal that this extension / add-on is available.

			if ( is_admin() ) {
				$this->p->avail['admin']['sharing'] = true;
			}
		}

		public function wpsso_init_objects() {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! $this->have_req_min ) {
				return;	// stop here
			}

			$this->actions = new WpssoSsbActions( $this->p );
			$this->filters = new WpssoSsbFilters( $this->p );
			$this->script  = new WpssoSsbScript( $this->p );
			$this->social  = new WpssoSsbSocial( $this->p );
			$this->style   = new WpssoSsbStyle( $this->p );
		}

		public function wpsso_init_plugin() {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! $this->have_req_min ) {

				$this->min_version_notice();

				return;	// stop here
			}
		}

		private function min_version_notice() {

			$info = WpssoSsbConfig::$cf[ 'plugin' ][ 'wpssossb' ];

			$error_msg = sprintf( __( 'The %1$s version %2$s add-on requires %3$s version %4$s or newer (version %5$s is currently installed).',
				'wpsso-ssb' ), $info[ 'name' ], $info[ 'version' ], $info[ 'req' ][ 'short' ], $info[ 'req' ][ 'min_version' ],
					$this->p->cf[ 'plugin' ][ 'wpsso' ][ 'version' ] );

			if ( is_admin() ) {

				$this->p->notice->err( $error_msg );

				if ( method_exists( $this->p->admin, 'get_check_for_updates_link' ) ) {
					$this->p->notice->inf( $this->p->admin->get_check_for_updates_link() );
				}
			}
		}
	}

        global $wpssossb;

	$wpssossb =& WpssoSsb::get_instance();
}
