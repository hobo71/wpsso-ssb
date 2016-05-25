<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteBuffer' ) ) {

	class WpssoSsbSubmenuWebsiteBuffer {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'image_dimensions_general_rows' => 2,	// $table_rows, $form
				'ssb_website_buffer_rows' => 3,		// $table_rows, $form, $submenu
			) );
		}

		// add an option to the WordPress -> Settings -> Image Dimensions page
		public function filter_image_dimensions_general_rows( $table_rows, $form ) {

			$def_dimensions = $this->p->opt->get_defaults( 'buffer_img_width' ).'x'.
				$this->p->opt->get_defaults( 'buffer_img_height' ).' '.
				( $this->p->opt->get_defaults( 'buffer_img_crop' ) == 0 ? 'uncropped' : 'cropped' );

			$table_rows['buffer_img_dimensions'] = $form->get_th_html( _x( 'Buffer <em>Sharing Button</em>', 'option label', 'wpsso-ssb' ), null, 'buffer_img_dimensions', 'The image dimensions that the Buffer button will share (defaults is '.$def_dimensions.'). Note that original images in the WordPress Media Library and/or NextGEN Gallery must be larger than your chosen image dimensions.' ).
			'<td>'.$form->get_image_dimensions_input( 'buffer_img' ).'</td>';

			return $table_rows;
		}

		public function filter_ssb_website_buffer_rows( $table_rows, $form, $submenu ) {
			
			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'buffer_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'buffer' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'buffer_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'buffer_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Count Position',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'buffer_count', array( 'none' => '', 
			'horizontal' => 'Horizontal', 'vertical' => 'Vertical' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Image Dimensions',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_image_dimensions_input( 'buffer_img', false, true ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Source',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'buffer_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_input( 'buffer_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Add via @username',
				'option label (short)', 'wpsso-ssb' ), 'short', null,
			sprintf( __( 'Append the website\'s business @username to the tweet (see the <a href="%1$s">Twitter</a> options tab on the %2$s settings page).', 'wpsso-ssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General', 'lib file description', 'wpsso-ssb' ) ) ).
			'<td>'.$form->get_checkbox( 'buffer_via' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteBuffer' ) ) {

	class WpssoSsbWebsiteBuffer {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'buffer_order' => 8,
					'buffer_on_content' => 0,
					'buffer_on_excerpt' => 0,
					'buffer_on_sidebar' => 0,
					'buffer_on_admin_edit' => 1,
					'buffer_platform' => 'any',
					'buffer_script_loc' => 'footer',
					'buffer_count' => 'horizontal',
					'buffer_img_width' => 600,
					'buffer_img_height' => 600,
					'buffer_img_crop' => 1,
					'buffer_img_crop_x' => 'center',
					'buffer_img_crop_y' => 'center',
					'buffer_caption' => 'title',
					'buffer_cap_len' => 140,
					'buffer_via' => 1,
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_image_sizes' => 1,
				'get_defaults' => 1,
			) );
		}

		public function filter_plugin_image_sizes( $sizes ) {
			$sizes['buffer_img'] = array(
				'name' => 'buffer-button',
				'label' => _x( 'Buffer Sharing Button', 'image size label', 'wpsso-ssb' ),
			);
			return $sizes;
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$lca = $this->p->cf['lca'];
			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// get_sharing_url() argument
			$atts['size'] = isset( $atts['size'] ) ? $atts['size'] : $lca.'-buffer-button';

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $mod, $atts['add_page'] ) : 
				apply_filters( $lca.'_sharing_url', $atts['url'], $mod, $atts['add_page'] );

			if ( ! empty( $atts['pid'] ) )
				list(
					$atts['photo'],
					$atts['width'],
					$atts['height'],
					$atts['cropped']
				) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );

			if ( empty( $atts['photo'] ) ) {
				$media_info = $this->p->og->get_the_media_info( $atts['size'], array( 'img_url' ), $mod, 'og' );
				$atts['photo'] = $media_info['img_url'];
			}

			if ( array_key_exists( 'tweet', $atts ) )
				$atts['caption'] = $atts['tweet'];

			if ( ! array_key_exists( 'caption', $atts ) ) {
				if ( empty( $atts['caption'] ) ) {
					$caption_len = $this->p->util->get_tweet_max_len( 'buffer' );
					$atts['caption'] = $this->p->webpage->get_caption( $opts['buffer_caption'], $caption_len,
						$mod, true, true, true, 'twitter_desc' );
				}
			}

			if ( ! array_key_exists( 'via', $atts ) ) {
				if ( ! empty( $opts['buffer_via'] ) ) {
					$key_locale = SucomUtil::get_key_locale( 'tc_site', $opts );
					$atts['via'] = preg_replace( '/^@/', '', $opts[$key_locale] );
				} else $atts['via'] = '';
			}

			// hashtags are included in the caption instead
			if ( ! array_key_exists( 'hashtags', $atts ) )
				$atts['hashtags'] = '';

			$html = '<!-- Buffer Button -->'.
			'<div '.WpssoSsbSharing::get_css_class_id( 'buffer', $atts ).'>'.
			'<a href="'.SucomUtil::get_prot().'://bufferapp.com/add" class="buffer-add-button"'.
			' data-url="'.$atts['url'].'"'.
			' data-count="'.$opts['buffer_count'].'"'.
				( empty( $atts['photo'] ) ? '' : ' data-picture="'.$atts['photo'].'"' ).
				( empty( $atts['caption'] ) ? '' : ' data-text="'.$atts['caption'].'"' ).
				( empty( $atts['via'] ) ? '' : ' data-via="'.$atts['via'].'"' ).'></a></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}
		
		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_buffer',
				SucomUtil::get_prot().'://d389zggrogs7qo.cloudfront.net/js/button.js', $pos ) );

			return '<script type="text/javascript" id="buffer-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "buffer-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
