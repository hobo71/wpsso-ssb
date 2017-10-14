<?php
/*
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
 * Description: WPSSO extension to add Social Sharing Buttons with support for hashtags, short URLs, bbPress, BuddyPress, WooCommerce, and much more.
 * Requires At Least: 3.7
 * Tested Up To: 4.8.2
 * Requires PHP: 5.3
 * Version: 2.4.7-rc.10
 * 
 * Version Numbering: {major}.{minor}.{bugfix}[-{stage}.{level}]
 *
 *	{major}		Major structural code changes / re-writes or incompatible API changes.
 *	{minor}		New functionality was added or improved in a backwards-compatible manner.
 *	{bugfix}	Backwards-compatible bug fixes or small improvements.
 *	{stage}.{level}	Pre-production release: dev < a (alpha) < b (beta) < rc (release candidate).
 * 
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsb' ) ) {

	class WpssoSsb {

		public $p;			// Wpsso
		public $reg;			// WpssoSsbRegister

		private static $instance;
		private static $have_min = true;	// have minimum wpsso version

		public function __construct() {

			require_once ( dirname( __FILE__ ).'/lib/config.php' );
			WpssoSsbConfig::set_constants( __FILE__ );
			WpssoSsbConfig::require_libs( __FILE__ );	// includes the register.php class library
			$this->reg = new WpssoSsbRegister();		// activate, deactivate, uninstall hooks

			if ( is_admin() ) {
				add_action( 'admin_init', array( __CLASS__, 'required_check' ) );
				add_action( 'wpsso_init_textdomain', array( __CLASS__, 'wpsso_init_textdomain' ) );
			}

			add_filter( 'wpsso_get_config', array( &$this, 'wpsso_get_config' ), 30, 2 );
			add_action( 'wpsso_init_options', array( &$this, 'wpsso_init_options' ), 10 );
			add_action( 'wpsso_init_objects', array( &$this, 'wpsso_init_objects' ), 10 );
			add_action( 'wpsso_init_plugin', array( &$this, 'wpsso_init_plugin' ), 10 );
		}

		public static function &get_instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self;
			return self::$instance;
		}

		public static function required_check() {
			if ( ! class_exists( 'Wpsso' ) ) {
				add_action( 'all_admin_notices', array( __CLASS__, 'required_notice' ) );
			}
		}

		// also called from the activate_plugin method with $deactivate = true
		public static function required_notice( $deactivate = false ) {
			self::wpsso_init_textdomain();
			$info = WpssoSsbConfig::$cf['plugin']['wpssossb'];
			$die_msg = __( '%1$s is an extension for the %2$s plugin &mdash; please install and activate the %3$s plugin before activating %4$s.', 'wpsso-ssb' );
			$err_msg = __( 'The %1$s extension requires the %2$s plugin &mdash; install and activate the %3$s plugin or <a href="%4$s">deactivate the %5$s extension</a>.', 'wpsso-ssb' );
			if ( $deactivate === true ) {
				if ( ! function_exists( 'deactivate_plugins' ) ) {
					require_once trailingslashit( ABSPATH ).'wp-admin/includes/plugin.php';
				}
				deactivate_plugins( $info['base'], true );	// $silent = true
				wp_die( '<p>'.sprintf( $die_msg, $info['name'], $info['req']['name'], $info['req']['short'], $info['short'] ).'</p>' );
			} else {
				$deactivate_url = wp_nonce_url( 'plugins.php?action=deactivate&amp;'.
					'plugin='.$info['base'].'&amp;plugin_status=active&amp;paged=1&amp;s=',
						'deactivate-plugin_'.$info['base'] );
				echo '<div class="notice notice-error error"><p>'.
					sprintf( $err_msg, $info['name'], $info['req']['name'],
						$info['req']['short'], $deactivate_url, $info['short'] ).'</p></div>';
			}
		}

		public static function wpsso_init_textdomain() {
			load_plugin_textdomain( 'wpsso-ssb', false, 'wpsso-ssb/languages/' );
		}

		public function wpsso_get_config( $cf, $plugin_version = 0 ) {
			$info = WpssoSsbConfig::$cf['plugin']['wpssossb'];

			if ( version_compare( $plugin_version, $info['req']['min_version'], '<' ) ) {
				self::$have_min = false;
				return $cf;
			}

			return SucomUtil::array_merge_recursive_distinct( $cf, WpssoSsbConfig::$cf );
		}

		public function wpsso_init_options() {
			if ( method_exists( 'Wpsso', 'get_instance' ) ) {
				$this->p =& Wpsso::get_instance();
			} else {
				$this->p =& $GLOBALS['wpsso'];
			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( self::$have_min ) {
				$this->p->avail['p_ext']['ssb'] = true;
				if ( is_admin() ) {
					$this->p->avail['admin']['sharing'] = true;
				}
			} else {
				$this->p->avail['p_ext']['ssb'] = false;	// just in case
			}
		}

		public function wpsso_init_objects() {
			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( self::$have_min ) {
				$this->p->ssb_sharing = new WpssoSsbSharing( $this->p, __FILE__ );
			}
		}

		public function wpsso_init_plugin() {
			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! self::$have_min ) {
				return $this->min_version_notice();	// stop here
			}
		}

		private function min_version_notice() {
			$info = WpssoSsbConfig::$cf['plugin']['wpssossb'];
			$wpsso_version = $this->p->cf['plugin']['wpsso']['version'];

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( $info['name'].' requires '.$info['req']['short'].' v'.
					$info['req']['min_version'].' or newer ('.$wpsso_version.' installed)' );
			}

			if ( is_admin() ) {
				$this->p->notice->err( sprintf( __( 'The %1$s extension v%2$s requires %3$s v%4$s or newer (v%5$s currently installed).',
					'wpsso-ssb' ), $info['name'], $info['version'], $info['req']['short'],
						$info['req']['min_version'], $wpsso_version ) );
			}
		}
	}

        global $wpssossb;
	$wpssossb =& WpssoSsb::get_instance();
}

?>
