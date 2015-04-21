<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbConfig' ) ) {

	class WpssoSsbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssossb' => array(
					'version' => '1.3.2',	// plugin version
					'short' => 'WPSSO SSB',
					'name' => 'WPSSO Social Sharing Buttons (WPSSO SSB)',
					'desc' => 'WPSSO extension to provide fast and accurate Social Sharing Buttons, including support for hashtags, shortening, bbPress, BuddyPress, and WooCommerce.',
					'slug' => 'wpsso-ssb',
					'base' => 'wpsso-ssb/wpsso-ssb.php',
					'update_auth' => 'tid',
					'img' => array(
						'icon_small' => 'images/icon-128x128.png',
						'icon_medium' => 'images/icon-256x256.png',
					),
					'url' => array(
						// wordpress
						'download' => 'https://wordpress.org/plugins/wpsso-ssb/',
						'review' => 'https://wordpress.org/support/view/plugin-reviews/wpsso-ssb#postform',
						'readme' => 'https://plugins.svn.wordpress.org/wpsso-ssb/trunk/readme.txt',
						'wp_support' => 'https://wordpress.org/support/plugin/wpsso-ssb',
						// surniaulula
						'update' => 'http://surniaulula.com/extend/plugins/wpsso-ssb/update/',
						'purchase' => 'http://surniaulula.com/extend/plugins/wpsso-ssb/',
						'changelog' => 'http://surniaulula.com/extend/plugins/wpsso-ssb/changelog/',
						'codex' => 'http://surniaulula.com/codex/plugins/wpsso-ssb/',
						'faq' => 'http://surniaulula.com/codex/plugins/wpsso-ssb/faq/',
						'notes' => '',
						'feed' => 'http://surniaulula.com/category/application/wordpress/wp-plugins/wpsso-ssb/feed/',
						'pro_support' => 'http://support.wpsso-ssb.surniaulula.com/',
						'pro_ticket' => 'http://ticket.wpsso-ssb.surniaulula.com/',
					),
					'lib' => array(
						'submenu' => array (
							'wpssossb-separator-0' => 'SSB Extension',
							'sharing' => 'Sharing Buttons',
							'style' => 'Sharing Styles',
						),
						'website' => array(
							'facebook' => 'Facebook', 
							'gplus' => 'GooglePlus',
							'twitter' => 'Twitter',
							'pinterest' => 'Pinterest',
							'linkedin' => 'LinkedIn',
							'buffer' => 'Buffer',
							'reddit' => 'Reddit',
							'managewp' => 'ManageWP',
							'stumbleupon' => 'StumbleUpon',
							'tumblr' => 'Tumblr',
							'youtube' => 'YouTube',
							'skype' => 'Skype',
						),
						'shortcode' => array(
							'sharing' => 'Sharing',
						),
						'widget' => array(
							'sharing' => 'Sharing',
						),
						'gpl' => array(
							'admin' => array(
								'sharing' => 'Button Settings',
								'style' => 'Style Settings',
								'apikeys' => 'API Key Settings',
							),
							'ecom' => array(
								'woocommerce' => 'WooCommerce',
							),
							'forum' => array(
								'bbpress' => 'bbPress',
							),
							'social' => array(
								'buddypress' => 'BuddyPress',
							),
						),
						'pro' => array(
							'admin' => array(
								'sharing' => 'Button Settings',
								'style' => 'Style Settings',
								'apikeys' => 'API Key Settings',
							),
							'ecom' => array(
								'woocommerce' => 'WooCommerce',
							),
							'forum' => array(
								'bbpress' => 'bbPress',
							),
							'social' => array(
								'buddypress' => 'BuddyPress',
							),
							'util' => array(
								'shorten' => 'URL Shortener',
							),
						),
					),
				),
			),
			'form' => array(
				'shorteners' => array( 'none' => '[none]', 'bitly' => 'Bit.ly', 'googl' => 'Goo.gl' ),
			),
		);

		public static function set_constants( $plugin_filepath ) { 
			$lca = 'wpssossb';
			$slug = self::$cf['plugin'][$lca]['slug'];

			define( 'WPSSOSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSOSSB_PLUGINDIR', trailingslashit( plugin_dir_path( $plugin_filepath ) ) );
			define( 'WPSSOSSB_PLUGINBASE', plugin_basename( $plugin_filepath ) );
			define( 'WPSSOSSB_TEXTDOM', $slug );
			define( 'WPSSOSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );

			/*
			 * Allow some constants to be pre-defined in wp-config.php
			 */
			if ( ! defined( 'WPSSOSSB_SHARING_SHORTCODE' ) )
				define( 'WPSSOSSB_SHARING_SHORTCODE', 'ssb' );

			/*
			 * WPSSO SSB hook priorities
			 */
			if ( ! defined( 'WPSSOSSB_HEAD_PRIORITY' ) )
				define( 'WPSSOSSB_HEAD_PRIORITY', 10 );

			if ( ! defined( 'WPSSOSSB_SOCIAL_PRIORITY' ) )
				define( 'WPSSOSSB_SOCIAL_PRIORITY', 100 );
			
			if ( ! defined( 'WPSSOSSB_FOOTER_PRIORITY' ) )
				define( 'WPSSOSSB_FOOTER_PRIORITY', 100 );
		}

		public static function require_libs( $plugin_filepath ) {
			if ( ! is_admin() )
				require_once( WPSSOSSB_PLUGINDIR.'lib/functions.php' );

			add_filter( 'wpssossb_load_lib', array( 'WpssoSsbConfig', 'load_lib' ), 10, 3 );
		}

		// gpl / pro library loader
		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {
			if ( $ret === false && ! empty( $filespec ) ) {
				$filepath = WPSSOSSB_PLUGINDIR.'lib/'.$filespec.'.php';
				if ( file_exists( $filepath ) ) {
					require_once( $filepath );
					if ( empty( $classname ) )
						return 'wpssossb'.str_replace( array( '/', '-' ), '', $filespec );
					else return $classname;
				}
			}
			return $ret;
		}
	}
}

?>
