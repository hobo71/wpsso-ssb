<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSharing' ) ) {

	class WpssoSsbSharing {

		protected $p;
		protected $website = array();
		protected $plugin_filepath = '';
		protected $buttons_for_type = array();		// cache for have_buttons_for_type()
		protected $post_buttons_disabled = array();	// cache for is_post_buttons_disabled()

		public static $sharing_css_name = '';
		public static $sharing_css_file = '';
		public static $sharing_css_url = '';

		public static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'buttons_on_index' => 0,
					'buttons_on_front' => 0,
					'buttons_add_to_post' => 1,
					'buttons_add_to_page' => 1,
					'buttons_add_to_attachment' => 1,
					'buttons_pos_content' => 'bottom',
					'buttons_pos_excerpt' => 'bottom',
					'buttons_use_social_css' => 1,
					'buttons_enqueue_social_css' => 1,
					'buttons_css_sharing' => '',		// all buttons
					'buttons_css_content' => '',		// post/page content
					'buttons_css_excerpt' => '',		// post/page excerpt
					'buttons_css_admin_edit' => '',
					'buttons_css_sidebar' => '',
					'buttons_css_shortcode' => '',
					'buttons_css_widget' => '',
					'buttons_js_sidebar' => '/* Save an empty style text box to reload the default javascript */

jQuery("#wpsso-sidebar").mouseenter( function(){ 
	jQuery("#wpsso-sidebar-buttons").css({
		display:"block",
		width:"auto",
		height:"auto",
		overflow:"visible",
		"border-style":"solid",
	}); } );
jQuery("#wpsso-sidebar").click( function(){ 
	jQuery("#wpsso-sidebar-buttons").toggle(); } );',
					'buttons_preset_content' => '',
					'buttons_preset_excerpt' => '',
					'buttons_preset_admin_edit' => 'small_share_count',
					'buttons_preset_sidebar' => 'large_share_vertical',
					'buttons_preset_shortcode' => '',
					'buttons_preset_widget' => '',
				),
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
					'sidebar' => 'Sidebar', 
					'admin_edit' => 'Adm Edit',
				),
				'style' => array(
					'sharing' => 'All Buttons',
					'content' => 'Content',
					'excerpt' => 'Excerpt',
					'sidebar' => 'CSS Sidebar',
					'admin_edit' => 'Admin Edit',
					'shortcode' => 'Shortcode',
					'widget' => 'Widget',
				),
			),
		);

		public function __construct( &$plugin, $plugin_filepath = WPSSOSSB_FILEPATH ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );
			$this->plugin_filepath = $plugin_filepath;
			self::$sharing_css_name = 'sharing-styles-id-'.get_current_blog_id().'.min.css';
			self::$sharing_css_file = WPSSO_CACHEDIR.self::$sharing_css_name;
			self::$sharing_css_url = WPSSO_CACHEURL.self::$sharing_css_name;
			$this->set_objects();

			add_action( 'wp_enqueue_scripts', array( &$this, 'wp_enqueue_styles' ) );
			add_action( 'wp_head', array( &$this, 'show_header' ), WPSSOSSB_HEAD_PRIORITY );
			add_action( 'wp_footer', array( &$this, 'show_footer' ), WPSSOSSB_FOOTER_PRIORITY );

			if ( $this->have_buttons_for_type( 'content' ) )
				$this->add_buttons_filter( 'the_content' );

			if ( $this->have_buttons_for_type( 'excerpt' ) ) {
				$this->add_buttons_filter( 'get_the_excerpt' );
				$this->add_buttons_filter( 'the_excerpt' );
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,		// add sharing options and css file contents to defaults
				'get_meta_defaults' => 2,	// add sharing options to post meta defaults
				'pre_filter_remove' => 2,	// remove the buttons filter from content, excerpt, etc.
				'post_filter_add' => 2,		// re-add the buttons filter to content, excerpt, etc.
			) );

			if ( is_admin() ) {
				if ( $this->have_buttons_for_type( 'admin_edit' ) )
					add_action( 'add_meta_boxes', array( &$this, 'add_post_buttons_metabox' ) );

				$this->p->util->add_plugin_filters( $this, array( 
					'save_options' => 3,		// update the sharing css file
					'option_type' => 4,		// identify option type for sanitation
					'post_cache_transients' => 4,	// clear transients on post save
					'tooltip_side' => 2,		// tooltip messages for side boxes
					'tooltip_post' => 3,		// tooltip messages for post social settings
				) );

				$this->p->util->add_plugin_filters( $this, array( 
					'status_gpl_features' => 3,	// include sharing, shortcode, and widget status
					'status_pro_features' => 3,	// include social file cache status
				), 10, 'wpssossb' );			// hook into the extension name instead
			}
			if ( $this->p->debug->enabled )
				$this->p->debug->mark( 'action / filter setup' );
		}

		private function set_objects() {
			foreach ( $this->p->cf['plugin']['wpssossb']['lib']['website'] as $id => $name ) {
				$classname = WpssoSsbConfig::load_lib( false, 'website/'.$id, 'wpssossbsharing'.$id );
				if ( $classname !== false && class_exists( $classname ) )
					$this->website[$id] = new $classname( $this->p );
			}
		}

		public function filter_get_meta_defaults( $opts_def, $mod ) {
			$meta_def = array(
				'twitter_desc' => '',
				'tumblr_img_desc' => '',
				'tumblr_vid_desc' => '',
				'buttons_disabled' => 0,
			);
			return array_merge( $opts_def, $meta_def );
		}

		public function filter_get_defaults( $opts_def ) {
			$opts_def = array_merge( $opts_def, self::$cf['opt']['defaults'] );
			$opts_def = $this->p->util->push_add_to_options( $opts_def, array( 'buttons' => 'frontend' ) );
			$plugin_dir = trailingslashit( realpath( dirname( $this->plugin_filepath ) ) );
			$url_path = parse_url( trailingslashit( plugins_url( '', $this->plugin_filepath ) ), PHP_URL_PATH );	// relative URL
			$style_tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', self::$cf['sharing']['style'] );

			foreach ( $style_tabs as $id => $name ) {
				$buttons_css_file = $plugin_dir.'css/'.$id.'-buttons.css';

				// css files are only loaded once (when variable is empty) into defaults to minimize disk i/o
				if ( empty( $opts_def['buttons_css_'.$id] ) ) {
					if ( ! file_exists( $buttons_css_file ) )
						continue;
					elseif ( ! $fh = @fopen( $buttons_css_file, 'rb' ) )
						$this->p->notice->err( 'Failed to open '.$buttons_css_file.' for reading.' );
					else {
						$css_data = fread( $fh, filesize( $buttons_css_file ) );
						fclose( $fh );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'read css from file '.$buttons_css_file );
						foreach ( array( 
							'plugin_url_path' => $url_path,
						) as $macro => $value )
							$css_data = preg_replace( '/%%'.$macro.'%%/', $value, $css_data );
						$opts_def['buttons_css_'.$id] = $css_data;
					}
				}
			}
			return $opts_def;
		}

		public function filter_save_options( $opts, $options_name, $network ) {
			// update the combined and minimized social stylesheet
			if ( $network === false )
				$this->update_sharing_css( $opts );
			return $opts;
		}

		public function filter_option_type( $type, $key, $network, $mod ) {
			if ( ! empty( $type ) )
				return $type;

			// remove localization for more generic match
			if ( strpos( $key, '#' ) !== false )
				$key = preg_replace( '/#.*$/', '', $key );

			switch ( $key ) {
				// integer options that must be 1 or more (not zero)
				case 'stumble_badge':
				case ( preg_match( '/_order$/', $key ) ? true : false ):
					return 'pos_num';
					break;
				// text strings that can be blank
				case 'gp_expandto':
				case 'pin_desc':
				case 'tumblr_img_desc':
				case 'tumblr_vid_desc':
				case 'twitter_desc':
					return 'ok_blank';
					break;
				// options that cannot be blank
				case 'fb_markup': 
				case 'gp_lang': 
				case 'gp_action': 
				case 'gp_size': 
				case 'gp_annotation': 
				case 'twitter_count': 
				case 'twitter_size': 
				case 'linkedin_counter':
				case 'managewp_type':
				case 'pin_button_lang':
				case 'pin_button_shape':
				case 'pin_button_color':
				case 'pin_button_height':
				case 'pin_count_layout':
				case 'pin_caption':
				case 'tumblr_button_style':
				case 'tumblr_caption':
				case ( strpos( $key, 'buttons_pos_' ) === 0 ? true : false ):
				case ( preg_match( '/^[a-z]+_script_loc$/', $key ) ? true : false ):
					return 'not_blank';
					break;
			}
			return $type;
		}

		public function filter_post_cache_transients( $transients, $post_id, $lang = 'en_US', $sharing_url ) {
			if ( ! empty( self::$cf['sharing']['show_on'] ) &&
				is_array( self::$cf['sharing']['show_on'] ) ) {

				$transients['WpssoSsbSharing::get_buttons'] = array();
				foreach( self::$cf['sharing']['show_on'] as $type_id => $type_name )
					$transients['WpssoSsbSharing::get_buttons'][$type_id] = 'lang:'.$lang.'_type:'.$type_id.'_post:'.$post_id;
			}
			return $transients;
		}

		public function filter_status_gpl_features( $features, $lca, $info ) {
			if ( ! empty( $info['lib']['submenu']['sharing'] ) )
				$features['Sharing Buttons'] = array( 'classname' => $lca.'Sharing' );

			if ( ! empty( $info['lib']['shortcode']['sharing'] ) )
				$features['Sharing Shortcode'] = array( 'classname' => $lca.'ShortcodeSharing' );

			if ( ! empty( $info['lib']['submenu']['style'] ) )
				$features['Sharing Stylesheet'] = array( 'status' => $this->p->options['buttons_use_social_css'] ? 'on' : 'off' );

			if ( ! empty( $info['lib']['widget']['sharing'] ) )
				$features['Sharing Widget'] = array( 'classname' => $lca.'WidgetSharing' );

			return $features;
		}

		public function filter_status_pro_features( $features = array(), $lca = '', $info = array() ) {
			if ( ! empty( $lca ) && ! empty( $info['lib']['submenu']['sharing'] ) ) {
				$aop = $this->p->check->aop( $lca );
				$features['Social File Cache'] = array( 
					'status' => ( empty( $this->options['plugin_file_cache_exp'] ) ?
						( $aop ? 'on' : 'rec' ) : 'off' ),
					'td_class' => $aop ? '' : 'blank',
				);
				$features['Sharing Styles Editor'] = array( 
					'status' => $aop ? 'on' : 'rec',
					'td_class' => $aop ? '' : 'blank',
				);
			}
			return $features;
		}

		public function filter_tooltip_side( $text, $idx ) {
			$lca = $this->p->cf['lca'];
			$short = $this->p->cf['plugin'][$lca]['short'];
			$short_pro = $short.' Pro';
			switch ( $idx ) {
				case 'tooltip-side-sharing-styles-editor':
					$text = 'When showing <em>All Plugin Options</em>, the Sharing Styles settings page includes an editor for the various social sharing buttons.';
					break;
				case 'tooltip-side-sharing-buttons':
					$text = 'Social sharing features include the '.$this->p->cf['menu'].' '.$this->p->util->get_admin_url( 'sharing', 'Buttons' ).' and '.$this->p->util->get_admin_url( 'style', 'Styles' ).' settings pages, the Social Settings -&gt; Sharing Buttons tab on Post or Page editing pages, along with the social sharing shortcode and widget. All social sharing features can be disabled using one of the available PHP <a href="http://surniaulula.com/codex/plugins/wpsso-ssb/notes/constants/" target="_blank">constants</a>.';
					break;
				case 'tooltip-side-sharing-shortcode':
					$text = 'Support for shortcode(s) can be enabled / disabled on the '.$this->p->util->get_admin_url( 'advanced', 'Advanced' ).' settings page. Shortcodes are disabled by default to optimize WordPress performance and content processing.';
					break;
				case 'tooltip-side-sharing-stylesheet':
					$text = 'A stylesheet can be included on all webpages for the social sharing buttons. Enable or disable the addition of the stylesheet from the '.$this->p->util->get_admin_url( 'style', 'Styles' ).' settings page.';
					break;
				case 'tooltip-side-sharing-widget':
					$text = 'The social sharing widget feature adds a \'Sharing Buttons\' widget in the WordPress Appearance - Widgets page. The widget can be used in any number of widget areas, to share the current webpage. The widget, along with all social sharing featured, can be disabled using an available <a href="http://surniaulula.com/codex/plugins/wpsso-ssb/notes/constants/" target="_blank">constant</a>.';
					break;
				case 'tooltip-side-social-file-cache':
					$text = $short_pro.' can save social sharing images and JavaScript to a cache folder, and provide URLs to these cached files instead of the originals. The current \'Social File Cache Expiry\' value, as defined on the '.$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_cache', 'Advanced' ).' settings page, is '.$this->p->options['plugin_file_cache_exp'].' seconds (the default value of 0 disables the social file caching feature).';
					break;
			}
			return $text;
		}

		public function filter_tooltip_post( $text, $idx, $atts ) {
			$ptn = empty( $atts['ptn'] ) ? 'Post' : $atts['ptn'];
			switch ( $idx ) {
				 case 'tooltip-post-pin_desc':
					$text = 'A custom caption text, used by the Pinterest social sharing button, for the custom Image ID, attached or featured image.';
				 	break;
				 case 'tooltip-post-tumblr_img_desc':
				 	$text = 'A custom caption, used by the Tumblr social sharing button, for the custom Image ID, attached or featured image.';
				 	break;
				 case 'tooltip-post-tumblr_vid_desc':
					$text = 'A custom caption, used by the Tumblr social sharing button, for the custom Video URL or embedded video.';
				 	break;
				 case 'tooltip-post-twitter_desc':
				 	$text = 'A custom Tweet text for the Twitter social sharing button. This text is in addition to any Twitter Card description.';
				 	break;
				 case 'tooltip-post-buttons_disabled':
					$text = 'Disable all social sharing buttons (content, excerpt, widget, shortcode) for this '.$ptn.'.';
				 	break;
			}
			return $text;
		}

		public function wp_enqueue_styles() {
			if ( ! empty( $this->p->options['buttons_use_social_css'] ) ) {
				if ( ! file_exists( self::$sharing_css_file ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'updating '.self::$sharing_css_file );
					$this->update_sharing_css( $this->p->options );
				}
				if ( ! empty( $this->p->options['buttons_enqueue_social_css'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'wp_enqueue_style = '.$this->p->cf['lca'].'_sharing_buttons' );
					wp_register_style( $this->p->cf['lca'].'_sharing_buttons', self::$sharing_css_url, 
						false, $this->p->cf['plugin'][$this->p->cf['lca']]['version'] );
					wp_enqueue_style( $this->p->cf['lca'].'_sharing_buttons' );
				} else {
					if ( ! is_readable( self::$sharing_css_file ) ) {
						if ( is_admin() )
							$this->p->notice->err( self::$sharing_css_file.' is not readable.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( self::$sharing_css_file.' is not readable' );
					} else {
						echo '<style type="text/css">';
						if ( ( $fsize = @filesize( self::$sharing_css_file ) ) > 0 &&
							$fh = @fopen( self::$sharing_css_file, 'rb' ) ) {
							echo fread( $fh, $fsize );
							fclose( $fh );
						}
						echo '</style>',"\n";
					}
				}
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'social css option is disabled' );
		}

		public function update_sharing_css( &$opts ) {
			if ( ! empty( $opts['buttons_use_social_css'] ) ) {
				$css_data = '';
				$style_tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', self::$cf['sharing']['style'] );

				foreach ( $style_tabs as $id => $name )
					if ( isset( $opts['buttons_css_'.$id] ) )
						$css_data .= $opts['buttons_css_'.$id];

				$classname = apply_filters( $this->p->cf['lca'].'_load_lib', 
					false, 'ext/compressor', 'SuextMinifyCssCompressor' );

				if ( $classname !== false && class_exists( $classname ) )
					$css_data = call_user_func( array( $classname, 'process' ), $css_data );
				else {
					if ( is_admin() )
						$this->p->notice->err( 'Failed to load minify class SuextMinifyCssCompressor.', true );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed to load minify class SuextMinifyCssCompressor' );
				}

				if ( $fh = @fopen( self::$sharing_css_file, 'wb' ) ) {
					if ( ( $written = fwrite( $fh, $css_data ) ) === false ) {
						if ( is_admin() )
							$this->p->notice->err( 'Failed writing to file '.self::$sharing_css_file.'.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( 'failed writing to '.self::$sharing_css_file );
					} elseif ( $this->p->debug->enabled ) {
						if ( is_admin() )
							$this->p->notice->inf( 'Updated CSS '.self::$sharing_css_file.' ('.$written.' bytes written)', true );
						$this->p->debug->log( 'updated css file '.self::$sharing_css_file.' ('.$written.' bytes written)' );
					}
					fclose( $fh );
				} else {
					if ( ! is_writable( WPSSO_CACHEDIR ) ) {
						if ( is_admin() )
							$this->p->notice->err( WPSSO_CACHEDIR.' is not writable.', true );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( WPSSO_CACHEDIR.' is not writable', true );
					}
					if ( is_admin() )
						$this->p->notice->err( 'Failed to open file '.self::$sharing_css_file.' for writing.', true );
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'failed opening '.self::$sharing_css_file.' for writing' );
				}
			} else $this->unlink_sharing_css();
		}

		public function unlink_sharing_css() {
			if ( file_exists( self::$sharing_css_file ) ) {
				if ( ! @unlink( self::$sharing_css_file ) ) {
					if ( is_admin() )
						$this->p->notice->err( 'Error removing minimized stylesheet file'.
							' &mdash; does the web server have sufficient privileges?', true );
				}
			}
		}

		public function add_post_buttons_metabox() {
			if ( ! is_admin() )
				return;

			// get the current object / post type
			if ( ( $obj = $this->p->util->get_post_object() ) === false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: invalid object type' );
				return;
			}
			$post_type = get_post_type_object( $obj->post_type );

			if ( ! empty( $this->p->options[ 'buttons_add_to_'.$post_type->name ] ) ) {
				// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
				add_meta_box( '_'.$this->p->cf['lca'].'_share', 'Sharing Buttons', 
					array( &$this, 'show_admin_sharing' ), $post_type->name, 'side', 'high' );
			}
		}

		public function filter_post_filter_add( $ret, $filter ) {
			return ( $this->add_buttons_filter( $filter ) ? true : $ret );
		}

		public function filter_pre_filter_remove( $ret, $filter ) {
			return ( $this->remove_buttons_filter( $filter ) ? true : $ret );
		}

		public function show_header() {
			echo $this->get_script_loader();
			echo $this->get_script( 'header' );

			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_footer() {

			if ( $this->have_buttons_for_type( 'sidebar' ) )
				echo $this->show_sidebar();
			elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'no buttons enabled for sidebar' );

			echo $this->get_script( 'footer' );

			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_sidebar() {
			$lca = $this->p->cf['lca'];
			$js = trim( preg_replace( '/\/\*.*\*\//', '', $this->p->options['buttons_js_sidebar'] ) );
			$text = '';	// variable must be passed by reference
			$text = $this->get_buttons( $text, 'sidebar', false );	// use_post = false
			if ( ! empty( $text ) ) {
				echo '<div id="'.$lca.'-sidebar">';
				echo '<div id="'.$lca.'-sidebar-header"></div>';
				echo $text;
				echo '</div>', "\n";
				echo '<script type="text/javascript">'.$js.'</script>', "\n";
			}
			if ( $this->p->debug->enabled )
				$this->p->debug->show_html( null, 'Debug Log' );
		}

		public function show_admin_sharing( $post ) {
			$post_type = get_post_type_object( $post->post_type );	// since 3.0
			$post_type_name = ucfirst( $post_type->name );
			$css_data = $this->p->options['buttons_css_admin_edit'];
			$classname = apply_filters( $this->p->cf['lca'].'_load_lib', false, 'ext/compressor', 'SuextMinifyCssCompressor' );
			if ( $classname !== false && class_exists( $classname ) )
				$css_data = call_user_func( array( $classname, 'process' ), $css_data );

			echo '<style type="text/css">'.$css_data.'</style>', "\n";
			echo '<table class="sucom-setting '.$this->p->cf['lca'].' side"><tr><td>';
			if ( get_post_status( $post->ID ) === 'publish' || 
				get_post_type( $post->ID ) === 'attachment' ) {

				$content = '';
				echo $this->get_script_loader();
				echo $this->get_script( 'header' );
				echo $this->get_buttons( $content, 'admin_edit' );
				echo $this->get_script( 'footer' );

				if ( $this->p->debug->enabled )
					$this->p->debug->show_html( null, 'Debug Log' );

			} else echo '<p class="centered">The '.$post_type_name.' must be published<br/>before it can be shared.</p>';
			echo '</td></tr></table>';
		}

		public function add_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = add_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSOSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.' added ('.( $ret  ? 'true' : 'false' ).')' );
			} elseif ( $this->p->debug->enabled )
				$this->p->debug->log( 'get_buttons_'.$type.' method is missing' );
			return $ret;
		}

		public function remove_buttons_filter( $type = 'the_content' ) {
			$ret = false;
			if ( method_exists( $this, 'get_buttons_'.$type ) ) {
				$ret = remove_filter( $type, array( &$this, 'get_buttons_'.$type ), WPSSOSSB_SOCIAL_PRIORITY );
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'buttons filter '.$type.' removed ('.( $ret  ? 'true' : 'false' ).')' );
			}
			return $ret;
		}

		public function get_buttons_the_excerpt( $text ) {
			$id = $this->p->cf['lca'].' excerpt-buttons';
			$text = preg_replace_callback( '/(<!-- '.$id.' begin -->.*<!-- '.$id.' end -->)(<\/p>)?/Usi', 
				array( __CLASS__, 'remove_paragraph_tags' ), $text );
			return $text;
		}

		public function get_buttons_get_the_excerpt( $text ) {
			return $this->get_buttons( $text, 'excerpt' );
		}

		public function get_buttons_the_content( $text ) {
			return $this->get_buttons( $text, 'content' );
		}

		public function get_buttons( &$text, $type = 'content', $use_post = true, $location = '' ) {

			// should we skip the sharing buttons for this content type or webpage?
			if ( is_admin() ) {
				if ( strpos( $type, 'admin_' ) !== 0 ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: '.$type.' ignored with is_admin()'  );
					return $text;
				}
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter skipped: no buttons allowed in rss feeds'  );
				return $text;
			} else {
				if ( ! is_singular() && empty( $this->p->options['buttons_on_index'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: index page without buttons_on_index enabled' );
					return $text;
				} elseif ( is_front_page() && empty( $this->p->options['buttons_on_front'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: front page without buttons_on_front enabled' );
					return $text;
				}
				if ( $this->is_post_buttons_disabled() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( $type.' filter skipped: sharing buttons disabled' );
					return $text;
				}
			}

			if ( ! $this->have_buttons_for_type( $type ) ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $type.' filter exiting early: no sharing buttons enabled' );
				return $text;
			}

			$lca = $this->p->cf['lca'];
			$obj = $this->p->util->get_post_object( $use_post );
			$post_id = empty( $obj->ID ) || empty( $obj->post_type ) || 
				( ! is_singular() && $use_post === false ) ? 0 : $obj->ID;
			$source_id = $this->p->util->get_source_id( $type );
			$html = false;

			if ( $this->p->is_avail['cache']['transient'] ) {
				$cache_salt = __METHOD__.'('.apply_filters( $lca.'_buttons_cache_salt', 
					'lang:'.SucomUtil::get_locale().'_type:'.$type.'_post:'.$post_id.
						( empty( $post_id ) ? '_url:'.$this->p->util->get_sharing_url( $use_post,
							true, $source_id ) : '' ), $type, $use_post ).')';
				$cache_id = $lca.'_'.md5( $cache_salt );
				$cache_type = 'object cache';
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': transient salt '.$cache_salt );
				$html = get_transient( $cache_id );
			}

			if ( $html !== false ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( $cache_type.': '.$type.' html retrieved from transient '.$cache_id );
			} else {
				// sort enabled sharing buttons by their preferred order
				$sorted_ids = array();
				foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
					if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) )
						$sorted_ids[ zeroise( $this->p->options[$pre.'_order'], 3 ).'-'.$id ] = $id;
				ksort( $sorted_ids );

				$atts['use_post'] = $use_post;
				$css_type = $atts['css_id'] = $type.'-buttons';
				if ( ! empty( $this->p->options['buttons_preset_'.$type] ) ) {
					$atts['preset_id'] = $this->p->options['buttons_preset_'.$type];
					$css_preset = $lca.'-preset-'.$atts['preset_id'];
				} else $css_preset = '';

				$buttons_html = $this->get_html( $sorted_ids, $atts );
				if ( trim( $buttons_html ) !== '' ) {
					$html = '
<!-- '.$lca.' '.$css_type.' begin -->
<div class="'.( $css_preset ? $css_preset.' ' : '' ).
	( $use_post ? $lca.'-'.$css_type.'">' : '" id="'.$lca.'-'.$css_type.'">' ).'
'.$buttons_html.'</div><!-- .'.$lca.'-'.$css_type.' -->
<!-- '.$lca.' '.$css_type.' end -->'."\n\n";

					if ( $this->p->is_avail['cache']['transient'] ) {
						set_transient( $cache_id, $html, $this->p->options['plugin_object_cache_exp'] );
						if ( $this->p->debug->enabled )
							$this->p->debug->log( $cache_type.': '.$type.' html saved to transient '.
							$cache_id.' ('.$this->p->options['plugin_object_cache_exp'].' seconds)' );
					}
				}
			}

			if ( empty( $location ) ) {
				$location = empty( $this->p->options['buttons_pos_'.$type] ) ? 
					'bottom' : $this->p->options['buttons_pos_'.$type];
			}

			switch ( $location ) {
				case 'top': 
					$text = $html.$text; 
					break;
				case 'bottom': 
					$text = $text.$html; 
					break;
				case 'both': 
					$text = $html.$text.$html; 
					break;
			}

			return $text.( $this->p->debug->enabled ? $this->p->debug->get_html() : '' );
		}

		// get_html() is called by the widget, shortcode, function, and perhaps some filter hooks
		public function get_html( &$ids = array(), &$atts = array() ) {

			$lca = $this->p->cf['lca'];
			$preset_id = empty( $atts['preset_id'] ) ? '' : 
				preg_replace( '/[^a-z0-9\-_]/', '', $atts['preset_id'] );

			$filter_id = empty( $atts['filter_id'] ) ? '' : 
				preg_replace( '/[^a-z0-9\-_]/', '', $atts['filter_id'] );

			// possibly dereference the opts variable to prevent passing on changes
			if ( empty( $preset_id ) && empty( $filter_id ) )
				$custom_opts =& $this->p->options;
			else $custom_opts = $this->p->options;

			// apply the presets to $custom_opts
			if ( ! empty( $preset_id ) && ! empty( self::$cf['opt']['preset'] ) ) {
				if ( array_key_exists( $preset_id, self::$cf['opt']['preset'] ) &&
					is_array( self::$cf['opt']['preset'][$preset_id] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'applying preset_id '.$preset_id.' to options' );
					$custom_opts = array_merge( $custom_opts, self::$cf['opt']['preset'][$preset_id] );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( $preset_id.' preset_id missing or not array'  );
			} 

			if ( ! empty( $filter_id ) ) {
				$filter_name = $lca.'_sharing_html_'.$filter_id.'_options';
				if ( has_filter( $filter_name ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'applying filter_id '.$filter_id.' to options ('.$filter_name.')' );
					$custom_opts = apply_filters( $filter_name, $custom_opts );
				} elseif ( $this->p->debug->enabled )
					$this->p->debug->log( 'no filter(s) found for '.$filter_name );
			}

			$html = '';
			foreach ( $ids as $id ) {
				$id = preg_replace( '/[^a-z]/', '', $id );	// sanitize the website object name
				if ( isset( $this->website[$id] ) &&
					method_exists( $this->website[$id], 'get_html' ) )
						$html .= $this->website[$id]->get_html( $atts, $custom_opts )."\n";
			}

			if ( trim( $html ) !== '' ) 
				$html = "<div class=\"".$lca."-buttons\">\n".$html."</div><!-- .".$lca."-buttons -->\n";

			return $html;
		}

		// add javascript for enabled buttons in content, widget, shortcode, etc.
		public function get_script( $pos = 'header', $ids = array() ) {

			// determine which (if any) sharing buttons are enabled
			// loop through the sharing button option prefixes (fb, gp, etc.)
			if ( empty( $ids ) ) {
				if ( is_admin() ) {
					if ( ( $obj = $this->p->util->get_post_object() ) === false  ||
						( get_post_status( $obj->ID ) !== 'publish' &&
							get_post_type( $obj->ID ) !== 'attachment' ) )
								return;
				} elseif ( is_singular() && $this->is_post_buttons_disabled() ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: buttons disabled' );
					return;
				}

				if ( class_exists( 'WpssoSsbWidgetSharing' ) ) {
					$widget = new WpssoSsbWidgetSharing();
		 			$widget_settings = $widget->get_settings();
				} else $widget_settings = array();

				if ( is_admin() ) {
					foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
						foreach ( SucomUtil::preg_grep_keys( '/^'.$pre.'_on_admin_/', $this->p->options ) as $key => $val )
							if ( ! empty( $val ) )
								$ids[] = $id;
					}
				} else {
					if ( is_singular() || 
						( ! is_singular() && ! empty( $this->p->options['buttons_on_index'] ) ) || 
						( is_front_page() && ! empty( $this->p->options['buttons_on_front'] ) ) ) {
	
						// exclude buttons enabled for admin editing pages
						foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
							foreach ( SucomUtil::preg_grep_keys( '/^'.$pre.'_on_/', $this->p->options ) as $key => $val )
								if ( strpos( $key, $pre.'_on_admin_' ) === false && ! empty( $val ) )
									$ids[] = $id;
						}
					}
					// check for enabled buttons in ACTIVE widget(s)
					foreach ( $widget_settings as $num => $instance ) {
						if ( is_object( $widget ) && is_active_widget( false, $widget->id_base.'-'.$num, $widget->id_base ) ) {
							foreach ( $this->p->cf['opt']['pre'] as $id => $pre ) {
								if ( array_key_exists( $id, $instance ) && 
									! empty( $instance[$id] ) )
										$ids[] = $id;
							}
						}
					}
				}
				if ( empty( $ids ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: no buttons enabled' );
					return;
				}
			}

			natsort( $ids );
			$ids = array_unique( $ids );
			$js = '<!-- '.$this->p->cf['lca'].' '.$pos.' javascript begin -->'."\n";

			if ( strpos( $pos, '-header' ) ) 
				$script_loc = 'header';
			elseif ( strpos( $pos, '-footer' ) ) 
				$script_loc = 'footer';
			else $script_loc = $pos;

			if ( ! empty( $ids ) ) {
				foreach ( $ids as $id ) {
					$id = preg_replace( '/[^a-z]/', '', $id );
					$opt_name = $this->p->cf['opt']['pre'][$id].'_script_loc';
					if ( isset( $this->website[$id] ) &&
						method_exists( $this->website[$id], 'get_script' ) && 
						isset( $this->p->options[$opt_name] ) && 
						$this->p->options[$opt_name] == $script_loc )
							$js .= $this->website[$id]->get_script( $pos );
				}
			}
			$js .= '<!-- '.$this->p->cf['lca'].' '.$pos.' javascript end -->'."\n";
			return $js;
		}

		public function get_script_loader( $pos = 'id' ) {
			$lang = empty( $this->p->options['gp_lang'] ) ? 'en-US' : $this->p->options['gp_lang'];
			$lang = apply_filters( $this->p->cf['lca'].'_lang', $lang, SucomUtil::get_pub_lang( 'gplus' ) );
			return '<script type="text/javascript" id="wpssossb-header-script">
	window.___gcfg = { lang: "'.$lang.'" };
	function '.$this->p->cf['lca'].'_insert_js( script_id, url, async ) {
		if ( document.getElementById( script_id + "-js" ) ) return;
		var async = typeof async !== "undefined" ? async : true;
		var script_pos = document.getElementById( script_id );
		var js = document.createElement( "script" );
		js.id = script_id + "-js";
		js.async = async;
		js.type = "text/javascript";
		js.language = "JavaScript";
		js.src = url;
		script_pos.parentNode.insertBefore( js, script_pos );
	};
</script>'."\n";
		}

		public function get_css( $css_name, &$atts = array(), $css_class_extra = '' ) {

			foreach ( array( 'css_class', 'css_id' ) as $key )
				if ( empty( $atts[$key] ) )
					$atts[$key] = 'button';

			$css_class = $css_name.'-'.$atts['css_class'];
			$css_id = $css_name.'-'.$atts['css_id'];

			if ( is_singular() || in_the_loop() ) {
				global $post;
				if ( ! empty( $post->ID ) )
					$css_id .= '-post-'.$post->ID;
			}

			if ( ! empty( $css_class_extra ) ) 
				$css_class = $css_class_extra.' '.$css_class;

			return 'class="'.$css_class.'" id="'.$css_id.'"';
		}

		public function have_buttons_for_type( $type ) {
			if ( isset( $this->buttons_for_type[$type] ) )
				return $this->buttons_for_type[$type];
			foreach ( $this->p->cf['opt']['pre'] as $id => $pre )
				if ( ! empty( $this->p->options[$pre.'_on_'.$type] ) )	// check if button is enabled
					return $this->buttons_for_type[$type] = true;
			return $this->buttons_for_type[$type] = false;
		}

		public function is_post_buttons_disabled() {
			global $post;
			$ret = false;
			
			if ( isset( $this->post_buttons_disabled[$post->ID] ) )
				return $this->post_buttons_disabled[$post->ID];

			if ( ! empty( $post ) ) {
				$post_type = $post->post_type;
				if ( $this->p->mods['util']['post']->get_options( $post->ID, 'buttons_disabled' ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'post '.$post->ID.': sharing buttons disabled by custom meta option' );
					$ret = true;
				} elseif ( ! empty( $post_type ) && empty( $this->p->options['buttons_add_to_'.$post_type] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'post '.$post->ID.': sharing buttons not enabled for post type '.$post_type );
					$ret = true;
				}
			}

			return $this->post_buttons_disabled[$post->ID] = apply_filters( $this->p->cf['lca'].'_post_buttons_disabled', $ret, $post->ID );
		}

		public function remove_paragraph_tags( $match = array() ) {
			if ( empty( $match ) || ! is_array( $match ) ) return;
			$text = empty( $match[1] ) ? '' : $match[1];
			$suff = empty( $match[2] ) ? '' : $match[2];
			$ret = preg_replace( '/(<\/*[pP]>|\n)/', '', $text );
			return $suff.$ret; 
		}

		public function get_defined_website_names() {
			$ids = array();
			foreach ( array_keys( $this->website ) as $id )
				$ids[$id] = $this->p->cf['*']['lib']['website'][$id];
			return $ids;
		}
	}
}

?>
