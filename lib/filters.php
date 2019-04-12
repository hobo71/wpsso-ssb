<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbFilters' ) ) {

	class WpssoSsbFilters {

		protected $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults'           => 1,
				'get_md_defaults'        => 1,
				'rename_options_keys'    => 1,
				'rename_md_options_keys' => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options'                   => 3,
					'option_type'                    => 2,
					'post_custom_meta_tabs'          => 3,
					'post_cache_transient_keys'      => 4,
					'settings_page_custom_style_css' => 1,
					'messages_info'                  => 2,
					'messages_tooltip'               => 2,
					'messages_tooltip_plugin'        => 2,
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 4,
					'status_pro_features' => 4,
				), 10, 'wpssossb' );
			}
		}

		public function filter_get_defaults( $def_opts ) {

			/**
			 * Add options using a key prefix array and post type names.
			 */
			$def_opts     = $this->p->util->add_ptns_to_opts( $def_opts, 'buttons_add_to', 1 );
			$rel_url_path = parse_url( WPSSOSSB_URLPATH, PHP_URL_PATH );	// Returns a relative URL.
			$styles       = apply_filters( $this->p->lca . '_ssb_styles', $this->p->cf[ 'sharing' ][ 'ssb_styles' ] );

			foreach ( $styles as $id => $name ) {

				$buttons_css_file = WPSSOSSB_PLUGINDIR . 'css/' . $id . '.css';

				/**
				 * CSS files are only loaded once (when variable is empty) into defaults to minimize disk I/O.
				 */
				if ( empty( $def_opts[ 'buttons_css_' . $id ] ) ) {

					if ( ! file_exists( $buttons_css_file ) ) {

						continue;

					} elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) ) {

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'failed to open the css file ' . $buttons_css_file . ' for reading' );
						}

						if ( is_admin() ) {
							$this->p->notice->err( sprintf( __( 'Failed to open the css file %s for reading.',
								'wpsso-ssb' ), $buttons_css_file ) );
						}

					} else {

						$buttons_css_data = fread( $fh, filesize( $buttons_css_file ) );

						fclose( $fh );

						if ( $this->p->debug->enabled ) {
							$this->p->debug->log( 'read css file ' . $buttons_css_file );
						}

						foreach ( array(
							'plugin_url_path' => $rel_url_path,
						) as $macro => $value ) {

							$buttons_css_data = preg_replace( '/%%' . $macro . '%%/', $value, $buttons_css_data );
						}

						$def_opts[ 'buttons_css_' . $id ] = $buttons_css_data;
					}
				}
			}

			return $def_opts;
		}

		public function filter_get_md_defaults( $md_defs ) {

			return array_merge( $md_defs, array(
				'email_title'      => '',	// Email Subject
				'email_desc'       => '',	// Email Message
				'twitter_desc'     => '',	// Tweet Text
				'pin_desc'         => '',	// Pinterest Caption Text
				'tumblr_img_desc'  => '',	// Tumblr Image Caption
				'tumblr_vid_desc'  => '',	// Tumblr Video Caption
				'buttons_disabled' => 0,	// Disable Sharing Buttons
			) );
		}

		public function filter_rename_options_keys( $options_keys ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$options_keys[ 'wpssossb' ] = array(
				14 => array(
					'buffer_js_loc'   => 'buffer_script_loc',
					'fb_js_loc'       => 'fb_script_loc',
					'gp_js_loc'       => 'gp_script_loc',
					'linkedin_js_loc' => 'linkedin_script_loc',
					'pin_js_loc'      => 'pin_script_loc',
					'stumble_js_loc'  => '',
					'tumblr_js_loc'   => 'tumblr_script_loc',
					'twitter_js_loc'  => 'twitter_script_loc',
				),
				16 => array(
					'email_cap_len'      => 'email_caption_max_len',
					'twitter_cap_len'    => 'twitter_caption_max_len',
					'pin_cap_len'        => 'pin_caption_max_len',
					'linkedin_cap_len'   => 'linkedin_caption_max_len',
					'reddit_cap_len'     => 'reddit_caption_max_len',
					'tumblr_cap_len'     => 'tumblr_caption_max_len',
					'email_cap_hashtags' => 'email_caption_hashtags',
				),
				20 => array(
					'gp_order'      => '',
					'gp_platform'   => '',
					'gp_script_loc' => '',
					'gp_lang'       => '',
					'gp_action'     => '',
					'gp_size'       => '',
					'gp_annotation' => '',
					'gp_expandto'   => '',
				),
			);

			$show_on = apply_filters( $this->p->lca . '_ssb_buttons_show_on', $this->p->cf[ 'sharing' ][ 'show_on' ], 'gp' );

			foreach ( $show_on as $opt_suffix => $short_desc ) {
				$options_keys[ 'wpssossb' ][ 20 ][ 'gp_on_' . $opt_suffix ] = '';
			}

			return $options_keys;
		}

		public function filter_rename_md_options_keys( $options_keys ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$options_keys[ 'wpssossb' ] = array(
			);

			return $options_keys;
		}

		public function filter_save_options( $opts, $options_name, $network ) {

			/**
			 * Update the combined and minified social stylesheet.
			 */
			if ( false === $network ) {
				WpssoSsbSocial::update_sharing_css( $opts );
			}

			return $opts;
		}

		public function filter_option_type( $type, $base_key ) {

			if ( ! empty( $type ) ) {
				return $type;
			}

			switch ( $base_key ) {

				/**
				 * Integer options that must be 1 or more (not zero).
				 */
				case ( preg_match( '/_order$/', $base_key ) ? true : false ):

					return 'pos_int';

					break;

				/**
				 * Text strings that can be blank.
				 */
				case 'buttons_force_prot':
				case 'gp_expandto':
				case 'pin_desc':
				case 'tumblr_img_desc':
				case 'tumblr_vid_desc':
				case 'twitter_desc':

					return 'ok_blank';

					break;

				/**
				 * Options that cannot be blank.
				 */
				case 'fb_platform': 
				case 'fb_script_loc': 
				case 'fb_lang': 
				case 'fb_button': 
				case 'fb_markup': 
				case 'fb_layout': 
				case 'fb_font': 
				case 'fb_colorscheme': 
				case 'fb_action': 
				case 'fb_share_markup': 
				case 'fb_share_layout': 
				case 'fb_share_size': 
				case 'gp_lang': 
				case 'gp_action': 
				case 'gp_size': 
				case 'gp_annotation': 
				case 'gp_expandto': 
				case 'twitter_count': 
				case 'twitter_size': 
				case 'managewp_type':
				case 'pin_button_lang':
				case 'pin_button_shape':
				case 'pin_button_color':
				case 'pin_button_height':
				case 'pin_count_layout':
				case 'pin_caption':
				case 'tumblr_button_style':
				case 'tumblr_caption':
				case ( strpos( $base_key, 'buttons_pos_' ) === 0 ? true : false ):
				case ( preg_match( '/^[a-z]+_script_loc$/', $base_key ) ? true : false ):

					return 'not_blank';

					break;
			}

			return $type;
		}

		public function filter_post_custom_meta_tabs( $tabs, $mod, $metabox_id ) {

			if ( $metabox_id === $this->p->cf[ 'meta' ][ 'id' ] ) {
				SucomUtil::add_after_key( $tabs, 'media', 'buttons',
					_x( 'Share Buttons', 'metabox tab', 'wpsso-ssb' ) );
			}

			return $tabs;
		}

		public function filter_post_cache_transient_keys( $transient_keys, $mod, $sharing_url, $mod_salt ) {

			$cache_md5_pre = $this->p->lca . '_b_';

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoSsbSocial::get_buttons(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => 'WpssoSsbSocial::get_buttons(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoSsbShortcodeSharing::do_shortcode(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => 'WpssoSsbShortcodeSharing::do_shortcode(' . $mod_salt . ')',
			);

			$transient_keys[] = array(
				'id'   => $cache_md5_pre . md5( 'WpssoSsbWidgetSharing::widget(' . $mod_salt . ')' ),
				'pre'  => $cache_md5_pre,
				'salt' => 'WpssoSsbWidgetSharing::widget(' . $mod_salt . ')',
			);

			return $transient_keys;
		}

		public function filter_settings_page_custom_style_css( $custom_style_css ) {

			$custom_style_css .= '

				.ssb_share_col {
					float:left;
					min-height:50px;
				}

				.max_cols_1.ssb_share_col {
					width:100%;
					min-width:100%;
					max-width:100%;
				}

				.max_cols_2.ssb_share_col {
					width:50%;
					min-width:50%;
					max-width:50%;
				}

				.max_cols_3.ssb_share_col {
					width:33.3333%;
					min-width:33.3333%;
					max-width:33.3333%;
				}

				.max_cols_4.ssb_share_col {
					width:25%;
					min-width:25%;
					max-width:25%;
				}

				.ssb_share_col .postbox {
					overflow-x:hidden;
				}

				.postbox-ssb_share {
					min-width:452px;
					overflow-y:auto;
				}

				.postbox-ssb_share .metabox-ssb_share {
					min-height:575px;
					overflow-y:auto;
				}

				/* Tabbed metabox */
				.postbox-ssb_share div.sucom-metabox-tabs div.sucom-tabset.active {
					min-height:533px;
				}

				.postbox-ssb_share.postbox-show_basic .metabox-ssb_share {
					min-height:435px;
				}

				/* Tabbed metabox */
				.postbox-ssb_share.postbox-show_basic div.sucom-metabox-tabs div.sucom-tabset.active {
					min-height:392px;
				}

				.postbox-ssb_share.closed,
				.postbox-ssb_share.closed .metabox-ssb_share,
				.postbox-ssb_share.postbox-show_basic.closed .metabox-ssb_share {
					height:auto;
					min-height:0;
					overflow:hidden;
				}
			';

			return $custom_style_css;
		}

		public function filter_messages_info( $text, $msg_key ) {

			if ( strpos( $msg_key, 'info-styles-ssb-' ) !== 0 ) {
				return $text;
			}

			$short = $this->p->cf[ 'plugin' ][ 'wpsso' ][ 'short' ];

			switch ( $msg_key ) {

				case 'info-styles-ssb-sharing':

					$text = '<p>'.$short.' uses the \'wpsso-ssb\' and \'ssb-buttons\' classes to wrap all its sharing buttons, and each button has it\'s own individual class name as well. This tab can be used to edit the CSS common to all sharing button locations.</p>';

					break;

				case 'info-styles-ssb-content':

					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.$this->p->util->get_admin_url( 'ssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-ssb-content\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'content', true );

					break;

				case 'info-styles-ssb-excerpt':

					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'ssb-buttons', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-ssb-excerpt\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'excerpt', true );

					break;

				case 'info-styles-ssb-sidebar':

					$text = '<p>Social sharing buttons added to the sidebar are assigned the \'#wpsso-ssb-sidebar-container\' CSS id, which itself contains \'#wpsso-ssb-sidebar-header\', \'#wpsso-ssb-sidebar\' and the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>
					<p>Example CSS:</p><pre>
#wpsso-ssb-sidebar-container
    #wpsso-ssb-sidebar-header {}

#wpsso-ssb-sidebar-container
    #wpsso-ssb-sidebar
        .ssb-buttons
	    .facebook-button {}</pre>';

					break;

				case 'info-styles-ssb-shortcode':

					$text = '<p>Social sharing buttons added from a shortcode are assigned the \'wpsso-ssb-shortcode\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'shortcode', true );

					break;

				case 'info-styles-ssb-widget':

					$text = '<p>Social sharing buttons within the social sharing buttons widget are assigned the \'wpsso-ssb-widget\' class, which itself contains the \'ssb-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 
					<p>Example CSS:</p><pre>
.wpsso-ssb-widget
    .ssb-buttons
        .facebook-button { }</pre>
					<p>The social sharing buttons widget also has an id of \'wpsso-ssb-widget-<em>#</em>\', and the buttons have an id of \'<em>name</em>-wpsso-ssb-widget-<em>#</em>\'.</p>
					<p>Example CSS:</p><pre>
#wpsso-ssb-widget-buttons-2
    .ssb-buttons
        #facebook-wpsso-widget-buttons-2 { }</pre>';

					break;

				case 'info-styles-ssb-admin_edit':

					$text = '<p>Social sharing buttons within the Admin Post / Page Edit metabox are assigned the \'wpsso-ssb-admin_edit\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'admin_edit', true );

					break;

				case 'info-styles-ssb-woo_short':

					$text = '<p>Social sharing buttons added to the WooCommerce Short Description are assigned the \'wpsso-ssb-woo_short\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'woo_short', true );

					break;

				case 'info-styles-ssb-bbp_single':

					$text = '<p>Social sharing buttons added at the top of bbPress Single templates are assigned the \'wpsso-ssb-bbp_single\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'bbp_single' );

					break;

				case 'info-styles-ssb-bp_activity':

					$text = '<p>Social sharing buttons added to BuddyPress Activities are assigned the \'wpsso-ssb-bp_activity\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.$this->get_info_css_example( 'bp_activity' );

					break;
			}

			return $text;
		}

		private function get_info_css_example( $type, $preset = false ) {

			$text = '<p>Example CSS:</p>
<pre>
.wpsso-ssb .wpsso-ssb-' . $type . '
    .ssb-buttons 
        .facebook-button {}
</pre>';

			if ( $preset ) {

				$styles = apply_filters( $this->p->lca . '_ssb_styles', $this->p->cf[ 'sharing' ][ 'ssb_styles' ] );

				$settings_page_link = $this->p->util->get_admin_url( 'ssb-buttons#sucom-tabset_ssb_buttons-tab_preset',
					_x( 'Buttons Presets', 'metabox tab', 'wpsso-ssb' ) );

				$text .= '<p>';
				
				$text .= sprintf( __( 'The %1$s social sharing buttons are subject to preset values selected on the %2$s settings page.',
					'wpsso-ssb' ), '<em>' . $styles[ 'ssb-' . $type ] . '</em>', $settings_page_link );
				
				$text .= '</p>';

				$text .= '<p><strong>Selected preset:</strong> ';

				$text .= empty( $this->p->options[ 'buttons_preset_ssb-' . $type ] ) ?
					_x( '[None]', 'option value', 'wpsso-ssb' ) : $this->p->options[ 'buttons_preset_ssb-' . $type ];
				
				$text .= '</p>';
			}

			return $text;
		}

		public function filter_messages_tooltip( $text, $msg_key ) {

			if ( strpos( $msg_key, 'tooltip-buttons_' ) !== 0 ) {
				return $text;
			}

			switch ( $msg_key ) {

				case ( strpos( $msg_key, 'tooltip-buttons_pos_' ) === false ? false : true ):

					$text = sprintf( __( 'Social sharing buttons can be added to the top, bottom, or both. Each sharing button must also be enabled below (see the <em>%s</em> options).', 'wpsso-ssb' ), _x( 'Show Button in', 'option label', 'wpsso-ssb' ) );

					break;

				case 'tooltip-buttons_on_index':

					$text = __( 'Add the social sharing buttons to each entry of an index webpage (blog front page, category, archive, etc.). Social sharing buttons are not included on index webpages by default.', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_on_front':

					$text = __( 'If a static Post or Page has been selected for the front page, you can add the social sharing buttons to that static front page as well (default is unchecked).', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_add_to':

					$text = __( 'Enabled social sharing buttons are added to the Post, Page, Media, and Product webpages by default. If your theme (or another plugin) supports additional custom post types, and you would like to include social sharing buttons on these webpages, check the appropriate option(s) here.', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_preset':

					$text = __( 'Select a pre-defined set of option values for sharing buttons in this location.', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_force_prot':

					$text = __( 'Modify URLs shared by the sharing buttons to use a specific protocol. This option can be useful to retain the share count of HTTP URLs after moving your site to HTTPS.', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_use_social_style':

					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minified</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minified stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-ssb' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-ssb' ), WpssoSsbSocial::$sharing_css_url, WpssoSsbSocial::$sharing_css_url );

					break;

				case 'tooltip-buttons_enqueue_social_style':

					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-ssb' );

					break;

				case 'tooltip-buttons_js_ssb-sidebar':

					$text = __( 'JavaScript added to webpages for the social sharing sidebar.' );

					break;

				case 'tooltip-buttons_add_via':

					$text = sprintf( __( 'Append the %1$s to the tweet (see <a href="%2$s">the Twitter options tab</a> in the %3$s settings page). The %1$s will be displayed and recommended after the webpage is shared.', 'wpsso-ssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-ssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General', 'lib file description', 'wpsso-ssb' ) );

					break;

				case 'tooltip-buttons_rec_author':

					$text = sprintf( __( 'Recommend following the author\'s Twitter @username after sharing a webpage. If the %1$s option (above) is also checked, the %2$s is suggested first.', 'wpsso-ssb' ), _x( 'Add via @username', 'option label (short)', 'wpsso-ssb' ), _x( 'Twitter Business @username', 'option label', 'wpsso-ssb' ) );

					break;
			}

			return $text;
		}

		public function filter_messages_tooltip_plugin( $text, $msg_key ) {

			switch ( $msg_key ) {

				case 'tooltip-plugin_sharing_buttons_cache_exp':

					$cache_exp_secs  = WpssoSsbConfig::$cf[ 'opt' ][ 'defaults' ][ 'plugin_sharing_buttons_cache_exp' ];
					$cache_exp_human = $cache_exp_secs ? human_time_diff( 0, $cache_exp_secs ) : _x( 'disabled', 'option comment', 'wpsso-ssb' );

					$text = __( 'The rendered HTML for social sharing buttons is saved to the WordPress transient cache to optimize performance.', 'wpsso-ssb' ) . ' ';
						
					$text .= sprintf( __( 'The suggested cache expiration value is %1$s seconds (%2$s).', 'wpsso-ssb' ), $cache_exp_secs, $cache_exp_human );

					break;

				case 'tooltip-plugin_social_file_cache_exp':

					$cache_exp_secs  = WpssoSsbConfig::$cf[ 'opt' ][ 'defaults' ][ 'plugin_social_file_cache_exp' ];
					$cache_exp_human = $cache_exp_secs ? human_time_diff( 0, $cache_exp_secs ) : _x( 'disabled', 'option comment', 'wpsso-ssb' );

					$text = __( 'The JavaScript of most social sharing buttons can be saved locally to cache folder in order to provide cached URLs instead of the originals.', 'wpsso-ssb' ) . ' ';
					
					$text .= __( 'If your hosting infrastructure performs reasonably well, this option can improve page load times significantly.', 'wpsso-ssb' ) . ' ';
					
					$text .= sprintf( __( 'The suggested cache expiration value is %1$s seconds (%2$s).', 'wpsso-ssb' ), $cache_exp_secs, $cache_exp_human );

					break;
			}

			return $text;
		}

		public function filter_status_gpl_features( $features, $ext, $info, $pkg ) {

			if ( ! empty( $info[ 'lib' ][ 'submenu' ][ 'ssb-styles' ] ) ) {
				$features[ '(sharing) Sharing Stylesheet' ] = array(
					'status' => empty( $this->p->options[ 'buttons_use_social_style' ] ) ? 'off' : 'on',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'shortcode' ][ 'sharing' ] ) ) {
				$features[ '(sharing) Sharing Shortcode' ] = array(
					'classname' => $ext . 'ShortcodeSharing',
				);
			}

			if ( ! empty( $info[ 'lib' ][ 'widget' ][ 'sharing' ] ) ) {
				$features[ '(sharing) Sharing Widget' ] = array(
					'classname' => $ext . 'WidgetSharing'
				);
			}

			return $features;
		}

		public function filter_status_pro_features( $features, $ext, $info, $pkg ) {

			$features[ '(feature) Sharing Styles Editor' ] = array( 
				'td_class' => $pkg[ 'pp' ] ? '' : 'blank',
				'purchase' => $pkg[ 'purchase' ],
				'status'   => $pkg[ 'pp' ] ? 'on' : 'rec',
			);

			return $features;
		}
	}
}
