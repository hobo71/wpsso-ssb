<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbConfig' ) ) {

	class WpssoSsbConfig {

		public static $cf = array(
			'plugin' => array(
				'wpssossb' => array(			// Plugin acronym.
					'version'     => '2.11.0-dev.5',	// Plugin version.
					'opt_version' => '21',		// Increment when changing default option values.
					'short'       => 'WPSSO SSB',	// Short plugin name.
					'name'        => 'WPSSO Social Sharing Buttons',
					'desc'        => 'WPSSO Core add-on offers social sharing buttons with support for hashtags, short URLs, bbPress, BuddyPress, WooCommerce, and much more.',
					'slug'        => 'wpsso-ssb',
					'base'        => 'wpsso-ssb/wpsso-ssb.php',
					'update_auth' => 'tid',
					'text_domain' => 'wpsso-ssb',
					'domain_path' => '/languages',
					'req' => array(
						'short'       => 'WPSSO Core',
						'name'        => 'WPSSO Core',
						'min_version' => '4.23.0-dev.5',
					),
					'img' => array(
						'icons' => array(
							'low'  => 'images/icon-128x128.png',
							'high' => 'images/icon-256x256.png',
						),
					),
					'lib' => array(
						'gpl' => array(
							'admin' => array(
								'sharing' => 'Extend Sharing Settings',
							),
							'ecom' => array(
								'woocommerce' => '(plugin) WooCommerce',
							),
							'forum' => array(
								'bbpress' => '(plugin) bbPress',
							),
							'social' => array(
								'buddypress' => '(plugin) BuddyPress',
							),
						),
						'pro' => array(
							'admin' => array(
								'sharing' => 'Extend Sharing Settings',
							),
							'ecom' => array(
								'woocommerce' => '(plugin) WooCommerce',
							),
							'forum' => array(
								'bbpress' => '(plugin) bbPress',
							),
							'social' => array(
								'buddypress' => '(plugin) BuddyPress',
							),
						),
						'share' => array(
							'email'       => 'Email',
							'twitter'     => 'Twitter',
							'facebook'    => 'Facebook', 
							'pinterest'   => 'Pinterest',
							'linkedin'    => 'LinkedIn',
							'buffer'      => 'Buffer',
							'reddit'      => 'Reddit',
							'managewp'    => 'ManageWP',
							'tumblr'      => 'Tumblr',
							'youtube'     => 'YouTube',
							'skype'       => 'Skype',
							'whatsapp'    => 'WhatsApp',
						),
						'shortcode' => array(
							'sharing' => 'Sharing Shortcode',
						),
						'submenu' => array(	// Note that submenu elements must have unique keys.
							'ssb-buttons' => 'Sharing Buttons',
							'ssb-styles'  => 'Sharing Styles',
						),
						'widget' => array(
							'sharing' => 'Sharing Widget',
						),
					),
				),
			),
			'opt' => array(				// options
				'defaults' => array(

					/**
					 * Advanced Settings
					 */
					'plugin_sharing_buttons_cache_exp' => WEEK_IN_SECONDS,	// Sharing Buttons HTML Cache Expiry (7 days)
					'plugin_social_file_cache_exp'     => 0,		// Get Social JS Files Cache Expiry

					/**
					 * Sharing Buttons
					 */
					'buttons_on_index'              => 0,
					'buttons_on_front'              => 0,
					'buttons_add_to_post'           => 1,
					'buttons_add_to_page'           => 1,
					'buttons_add_to_attachment'     => 1,
					'buttons_pos_content'           => 'bottom',
					'buttons_pos_excerpt'           => 'bottom',
					'buttons_preset_ssb-content'    => '',
					'buttons_preset_ssb-excerpt'    => '',
					'buttons_preset_ssb-admin_edit' => 'small_share_count',
					'buttons_preset_ssb-sidebar'    => 'large_share_vertical',
					'buttons_preset_ssb-shortcode'  => '',
					'buttons_preset_ssb-widget'     => '',
					'buttons_force_prot'            => '',

					/**
					 * Sharing Styles
					 */
					'buttons_use_social_style'     => 1,
					'buttons_enqueue_social_style' => 1,
					'buttons_css_ssb-admin_edit'   => '',
					'buttons_css_ssb-content'      => '',		// post/page content
					'buttons_css_ssb-excerpt'      => '',		// post/page excerpt
					'buttons_css_ssb-sharing'      => '',		// all buttons
					'buttons_css_ssb-shortcode'    => '',
					'buttons_css_ssb-sidebar'      => '',
					'buttons_css_ssb-widget'       => '',
					'buttons_js_ssb-sidebar' => '/* Save an empty style text box to reload the default javascript */
jQuery("#wpsso-ssb-sidebar-container").mouseenter( function(){ 
	jQuery("#wpsso-ssb-sidebar").css({
		"display":"block",
		"width":"auto",
		"height":"auto",
		"overflow":"visible",
		"border-style":"none",
	}); } );
jQuery("#wpsso-ssb-sidebar-header").click( function(){ 
	jQuery("#wpsso-ssb-sidebar").toggle(); } );',
				),	// end of defaults
				'site_defaults' => array(

					/**
					 * Advanced Settings
					 */
					'plugin_sharing_buttons_cache_exp'     => WEEK_IN_SECONDS,	// Sharing Buttons HTML Cache Expiry (7 days)
					'plugin_sharing_buttons_cache_exp:use' => 'default',
					'plugin_social_file_cache_exp'         => 0,			// Get Social JS Files Cache Expiry
					'plugin_social_file_cache_exp:use'     => 'default',
				),	// end of site defaults
				'preset' => array(
					'small_share_count' => array(
						'twitter_size'      => 'medium',
						'twitter_count'     => 'horizontal',
						'fb_button'         => 'share',		// Button Type
						'fb_send'           => 0,		// Like and Send: Include Send
						'fb_layout'         => 'button_count',	// Like and Send: Layout
						'fb_show_faces'     => 0,		// Like and Send: Show Faces
						'fb_action'         => 'like',		// Like and Send: Action Name
						'fb_share_layout'   => 'button_count',	// Share: Layout
						'fb_share_size'     => 'small',		// Share: Button Size
						'pin_button_shape'  => 'rect',
						'pin_button_height' => 'small',
						'pin_count_layout'  => 'beside',
						'buffer_count'      => 'horizontal',
						'reddit_type'       => 'static-wide',
						'managewp_type'     => 'small',
						'tumblr_counter'    => 'right',
					),
					'large_share_vertical' => array(
						'twitter_size'      => 'medium',
						'twitter_count'     => 'vertical',
						'fb_button'         => 'share',			// Facebook Button Type
						'fb_send'           => 0,				// Like and Send: Include Send
						'fb_layout'         => 'box_count',		// Like and Send: Layout
						'fb_show_faces'     => 0,			// Like and Send: Show Faces
						'fb_action'         => 'like',			// Like and Send: Action Name
						'fb_share_layout'   => 'box_count',	// Share: Layout
						'fb_share_size'     => 'small',		// Share: Button Size
						'pin_button_shape'  => 'rect',
						'pin_button_height' => 'large',
						'pin_count_layout'  => 'above',
						'buffer_count'      => 'vertical',
						'reddit_type'       => 'static-tall-text',
						'managewp_type'     => 'big',
						'tumblr_counter'    => 'top',
					),
				),
			),
			'wp' => array(				// WordPress
				'transient' => array(
					'wpsso_b_' => array(
						'label'       => 'Buttons HTML',
						'text_domain' => 'wpsso-ssb',
						'opt_key'     => 'plugin_sharing_buttons_cache_exp',
						'filter'      => 'wpsso_cache_expire_sharing_buttons',
					),
				),
			),
			'sharing' => array(
				'show_on' => array( 
					'content'    => 'Content', 
					'excerpt'    => 'Excerpt', 
					'sidebar'    => 'CSS Sidebar', 
					'admin_edit' => 'Admin Edit',
				),
				'force_prot' => array( 
					'http'  => 'HTTP',
					'https' => 'HTTPS',
				),
				'ssb_styles' => array(
					'ssb-sharing'    => 'All Buttons',
					'ssb-content'    => 'Content',
					'ssb-excerpt'    => 'Excerpt',
					'ssb-sidebar'    => 'CSS Sidebar',
					'ssb-admin_edit' => 'Admin Edit',
					'ssb-shortcode'  => 'Shortcode',
					'ssb-widget'     => 'Widget',
				),
				'position' => array(
					'top'    => 'Top',
					'bottom' => 'Bottom',
					'both'   => 'Top and Bottom',
				),
				'platform' => array(
					'desktop' => 'Desktop Only',
					'mobile'  => 'Mobile Only',
					'any'     => 'Any Platform',
				),
			),
		);

		public static function get_version( $add_slug = false ) {

			$ext  = 'wpssossb';
			$info =& self::$cf[ 'plugin' ][$ext];

			return $add_slug ? $info[ 'slug' ] . '-' . $info[ 'version' ] : $info[ 'version' ];
		}

		public static function set_constants( $plugin_filepath ) { 

			if ( defined( 'WPSSOSSB_VERSION' ) ) {	// Define constants only once.
				return;
			}

			define( 'WPSSOSSB_FILEPATH', $plugin_filepath );						
			define( 'WPSSOSSB_PLUGINBASE', self::$cf[ 'plugin' ][ 'wpssossb' ][ 'base' ] );	// wpsso-ssb/wpsso-ssb.php
			define( 'WPSSOSSB_PLUGINDIR', trailingslashit( realpath( dirname( $plugin_filepath ) ) ) );
			define( 'WPSSOSSB_PLUGINSLUG', self::$cf[ 'plugin' ][ 'wpssossb' ][ 'slug' ] );	// wpsso-ssb
			define( 'WPSSOSSB_URLPATH', trailingslashit( plugins_url( '', $plugin_filepath ) ) );
			define( 'WPSSOSSB_VERSION', self::$cf[ 'plugin' ][ 'wpssossb' ][ 'version' ] );						

			self::set_variable_constants();
		}

		public static function set_variable_constants( $var_const = null ) {

			if ( null === $var_const ) {
				$var_const = self::get_variable_constants();
			}

			foreach ( $var_const as $name => $value ) {
				if ( ! defined( $name ) ) {
					define( $name, $value );
				}
			}
		}

		public static function get_variable_constants() { 

			$var_const = array();

			$var_const['WPSSOSSB_SHARING_SHORTCODE_NAME'] = 'ssb';

			/**
			 * WPSSO SSB hook priorities
			 */
			$var_const['WPSSOSSB_HEAD_PRIORITY'] = 10;
			$var_const['WPSSOSSB_SOCIAL_PRIORITY'] = 100;
			$var_const['WPSSOSSB_FOOTER_PRIORITY'] = 100;

			foreach ( $var_const as $name => $value ) {
				if ( defined( $name ) ) {
					$var_const[$name] = constant( $name );	// inherit existing values
				}
			}

			return $var_const;
		}

		public static function require_libs( $plugin_filepath ) {

			require_once WPSSOSSB_PLUGINDIR . 'lib/actions.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/filters.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/functions.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/register.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/script.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/social.php';
			require_once WPSSOSSB_PLUGINDIR . 'lib/style.php';

			add_filter( 'wpssossb_load_lib', array( 'WpssoSsbConfig', 'load_lib' ), 10, 3 );
		}

		public static function load_lib( $ret = false, $filespec = '', $classname = '' ) {

			if ( false === $ret && ! empty( $filespec ) ) {

				$filepath = WPSSOSSB_PLUGINDIR . 'lib/' . $filespec . '.php';

				if ( file_exists( $filepath ) ) {

					require_once $filepath;

					if ( empty( $classname ) ) {
						return SucomUtil::sanitize_classname( 'wpssossb' . $filespec, $allow_underscore = false );
					} else {
						return $classname;
					}
				}
			}

			return $ret;
		}
	}
}
