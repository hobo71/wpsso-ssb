<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2015 - Jean-Sebastien Morisset - http://wpsso.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbConfig' ) ) {

	class WpssoSsbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssossb' => array(
					'version' => '2.0.1',	// plugin version
					'short' => 'WPSSO SSB',
					'name' => 'WPSSO Social Sharing Buttons (WPSSO SSB)',
					'desc' => 'WPSSO extension to add traditional Social Sharing Buttons with support for hashtags, short URLs, bbPress, BuddyPress, WooCommerce, and much more.',
					'slug' => 'wpsso-ssb',
					'base' => 'wpsso-ssb/wpsso-ssb.php',
					'update_auth' => 'tid',
					'text_domain' => 'wpsso-ssb',
					'domain_path' => '/languages',
					'img' => array(
						'icon_small' => 'images/icon-128x128.png',
						'icon_medium' => 'images/icon-256x256.png',
					),
					'url' => array(
						// wordpress
						'download' => 'https://wordpress.org/plugins/wpsso-ssb/',
						'review' => 'https://wordpress.org/support/view/plugin-reviews/wpsso-ssb?filter=5&rate=5#postform',
						'readme' => 'https://plugins.svn.wordpress.org/wpsso-ssb/trunk/readme.txt',
						'wp_support' => 'https://wordpress.org/support/plugin/wpsso-ssb',
						// surniaulula
						'update' => 'http://wpsso.com/extend/plugins/wpsso-ssb/update/',
						'purchase' => 'http://wpsso.com/extend/plugins/wpsso-ssb/',
						'changelog' => 'http://wpsso.com/extend/plugins/wpsso-ssb/changelog/',
						'codex' => 'http://wpsso.com/codex/plugins/wpsso-ssb/',
						'faq' => 'http://wpsso.com/codex/plugins/wpsso-ssb/faq/',
						'notes' => '',
						'feed' => 'http://wpsso.com/category/application/wordpress/wp-plugins/wpsso-ssb/feed/',
						'pro_support' => 'http://wpsso-ssb.support.wpsso.com/',
					),
					'lib' => array(
						'submenu' => array (
							'wpssossb-separator-0' => 'SSB Extension',
							'sharing' => 'Sharing Buttons',
							'style' => 'Sharing Styles',
						),
						'website' => array(
							'email' => 'Email',
							'twitter' => 'Twitter',
							'facebook' => 'Facebook', 
							'gplus' => 'GooglePlus',
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
					),
				),
			),
			'opt' => array(				// options
				'preset' => array(
					'small_share_count' => array(
						'fb_button' => 'share',
						'fb_send' => 0,
						'fb_show_faces' => 0,
						'fb_action' => 'like',
						'fb_type' => 'button_count',
						'gp_action' => 'share',
						'gp_size' => 'medium',
						'gp_annotation' => 'bubble',
						'gp_expandto' => '',
						'twitter_size' => 'medium',
						'twitter_count' => 'horizontal',
						'linkedin_counter' => 'right',
						'linkedin_showzero' => 1,
						'pin_button_shape' => 'rect',
						'pin_button_height' => 'small',
						'pin_count_layout' => 'beside',
						'buffer_count' => 'horizontal',
						'reddit_type' => 'static-wide',
						'managewp_type' => 'small',
						'tumblr_button_style' => 'share_1',
						'stumble_badge' => 1,
					),
					'large_share_vertical' => array(
						'fb_button' => 'share',
						'fb_send' => 0,
						'fb_show_faces' => 0,
						'fb_action' => 'like',
						'fb_type' => 'box_count',
						'fb_layout' => 'box_count',
						'gp_action' => 'share',
						'gp_size' => 'tall',
						'gp_annotation' => 'vertical-bubble',
						'gp_expandto' => '',
						'twitter_size' => 'medium',
						'twitter_count' => 'vertical',
						'linkedin_counter' => 'top',
						'linkedin_showzero' => '1',
						'pin_button_shape' => 'rect',
						'pin_button_height' => 'large',
						'pin_count_layout' => 'above',
						'buffer_count' => 'vertical',
						'reddit_type' => 'static-tall-text',
						'managewp_type' => 'big',
						'tumblr_button_style' => 'share_2',
						'stumble_badge' => 5,
					),
				),
			),
			'sharing' => array(
				'show_on' => array( 
					'content' => 'Content', 
					'excerpt' => 'Excerpt', 
					'sidebar' => 'CSS Sidebar', 
					'admin_edit' => 'Admin Edit',
				),
				'style' => array(
					'ssb-sharing' => 'All Buttons',
					'ssb-content' => 'Content',
					'ssb-excerpt' => 'Excerpt',
					'ssb-sidebar' => 'CSS Sidebar',
					'ssb-admin_edit' => 'Admin Edit',
					'ssb-shortcode' => 'Shortcode',
					'ssb-widget' => 'Widget',
				),
				'position' => array(
					'top' => 'Top',
					'bottom' => 'Bottom',
					'both' => 'Both Top and Bottom',
				),
			),
		);

		public static function set_constants( $plugin_filepath ) { 
			define( 'WPSSOSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSOSSB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_filepath ) ) ) );
			define( 'WPSSOSSB_PLUGINBASE', self::$cf['plugin']['wpssossb']['base'] );	// wpsso-ssb/wpsso-ssb.php
			define( 'WPSSOSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );
			self::set_variable_constants();
		}

		public static function set_variable_constants() { 
			foreach ( self::get_variable_constants() as $name => $value )
				if ( ! defined( $name ) )
					define( $name, $value );
		}

		public static function get_variable_constants() { 
			$var_const = array();

			$var_const['WPSSOSSB_SHARING_SHORTCODE'] = 'ssb';

			/*
			 * WPSSO SSB hook priorities
			 */
			$var_const['WPSSOSSB_HEAD_PRIORITY'] = 10;
			$var_const['WPSSOSSB_SOCIAL_PRIORITY'] = 100;
			$var_const['WPSSOSSB_FOOTER_PRIORITY'] = 100;

			foreach ( $var_const as $name => $value )
				if ( defined( $name ) )
					$var_const[$name] = constant( $name );	// inherit existing values

			return $var_const;
		}

		public static function require_libs( $plugin_filepath ) {
			require_once( WPSSOSSB_PLUGINDIR.'lib/register.php' );
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
