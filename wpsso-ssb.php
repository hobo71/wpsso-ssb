<?php
/*
 * Plugin Name: WPSSO Social Sharing Buttons (WPSSO SSB)
 * Plugin Slug: wpsso-ssb
 * Text Domain: wpsso-ssb
 * Domain Path: /languages
 * Plugin URI: https://surniaulula.com/extend/plugins/wpsso-ssb/
 * Assets URI: https://surniaulula.github.io/wpsso-ssb/assets/
 * Author: JS Morisset
 * Author URI: https://surniaulula.com/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Description: WPSSO extension to add traditional Social Sharing Buttons with support for hashtags, short URLs, bbPress, BuddyPress, WooCommerce, and much more.
 * Requires At Least: 3.7
 * Tested Up To: 4.7
 * Version: 2.3.6-1
 * 
 * Version Numbering Scheme: {major}.{minor}.{bugfix}-{stage}{level}
 *
 *	{major}		Major code changes / re-writes or significant feature changes.
 *	{minor}		New features / options were added or improved.
 *	{bugfix}	Bugfixes or minor improvements.
 *	{stage}{level}	dev < a (alpha) < b (beta) < rc (release candidate) < # (production).
 *
 * See PHP's version_compare() documentation at http://php.net/manual/en/function.version-compare.php.
 * 
 * Copyright 2014-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsb' ) ) {

	class WpssoSsb {

		public $p;			// Wpsso
		public $reg;			// WpssoSsbRegister

		private static $instance;
		private static $have_min = true;

		public static function &get_instance() {
			if ( ! isset( self::$instance ) )
				self::$instance = new self;
			return self::$instance;
		}

		public function __construct() {

			require_once ( dirname( __FILE__ ).'/lib/config.php' );
			WpssoSsbConfig::set_constants( __FILE__ );
			WpssoSsbConfig::require_libs( __FILE__ );	// includes the register.php class library
			$this->reg = new WpssoSsbRegister();		// activate, deactivate, uninstall hooks

			if ( is_admin() ) {
				load_plugin_textdomain( 'wpsso-ssb', false, 'wpsso-ssb/languages/' );
				add_action( 'admin_init', array( &$this, 'required_check' ) );
			}

			add_filter( 'wpsso_get_config', array( &$this, 'wpsso_get_config' ), 30, 2 );
			add_action( 'wpsso_init_options', array( &$this, 'wpsso_init_options' ), 10 );
			add_action( 'wpsso_init_objects', array( &$this, 'wpsso_init_objects' ), 10 );
			add_action( 'wpsso_init_plugin', array( &$this, 'wpsso_init_plugin' ), 10 );
		}

		public function required_check() {
			if ( ! class_exists( 'Wpsso' ) )
				add_action( 'all_admin_notices', array( &$this, 'required_notice' ) );
		}

		public static function required_notice( $deactivate = false ) {
			$info = WpssoSsbConfig::$cf['plugin']['wpssossb'];

			if ( $deactivate === true ) {
				require_once( ABSPATH.'wp-admin/includes/plugin.php' );
				deactivate_plugins( $info['base'] );
				wp_die( '<p>'.sprintf( __( '%1$s is an extension for the %2$s plugin &mdash; please install and activate the %3$s plugin before activating the %4$s extension.', 'wpsso-ssb' ), $info['name'], $info['req']['name'], $info['req']['short'], $info['short'] ).'</p>' );
			} else echo '<div class="notice notice-error error"><p>'.
				sprintf( __( 'The %1$s extension requires the %2$s plugin &mdash; please install and activate the %3$s plugin.',
					'wpsso-ssb' ), $info['name'], $info['req']['name'], $info['req']['short'] ).'</p></div>';
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
			if ( method_exists( 'Wpsso', 'get_instance' ) )
				$this->p =& Wpsso::get_instance();
			else $this->p =& $GLOBALS['wpsso'];

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( self::$have_min === false )
				return;		// stop here

			$this->p->is_avail['ssb'] = true;

			if ( is_admin() )
				$this->p->is_avail['admin']['sharing'] = true;
		}

		public function wpsso_init_objects() {
			if ( self::$have_min === false )
				return;		// stop here

			$this->p->ssb_sharing = new WpssoSsbSharing( $this->p, __FILE__ );
		}

		public function wpsso_init_plugin() {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( self::$have_min === false )
				return $this->min_version_notice();
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
