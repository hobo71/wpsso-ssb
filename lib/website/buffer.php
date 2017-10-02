<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteBuffer' ) ) {

	class WpssoSsbSubmenuWebsiteBuffer {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

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
			'<td>'.$form->get_input_image_dimensions( 'buffer_img' ).'</td>';	// $use_opts = false

			return $table_rows;
		}

		public function filter_ssb_website_buffer_rows( $table_rows, $form, $submenu ) {
			
			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$submenu->show_on_checkboxes( 'buffer' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'buffer_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( $this->p->avail['*']['vary_ua'] ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform',
					'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'buffer_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'buffer_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Count Position',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'buffer_count', array( 'none' => 'none', 'horizontal' => 'Horizontal', 'vertical' => 'Vertical' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Image Dimensions',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_input_image_dimensions( 'buffer_img', false, true ).'</td>';	// $use_opts = false, $narrow = true

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Source',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'buffer_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_input( 'buffer_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Add via @username',
				'option label (short)', 'wpsso-ssb' ), 'short', 'buttons_add_via'  ).
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

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
				'plugin_image_sizes' => 1,
			) );
		}

		public function filter_plugin_image_sizes( $sizes ) {
			$sizes['buffer_img'] = array(
				'name' => 'buffer-button',
				'label' => _x( 'Buffer Sharing Button', 'image size label', 'wpsso-ssb' ),
			);
			return $sizes;
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];

			$atts['size'] = isset( $atts['size'] ) ?
				$atts['size'] : $lca.'-buffer-button';

			if ( ! empty( $atts['pid'] ) ) {
				$force_regen = $this->p->util->is_force_regen( $mod, 'og' );	// false by default

				list(
					$atts['photo'],
					$atts['width'],
					$atts['height'],
					$atts['cropped'],
					$atts['pid']
				) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false, $force_regen );

				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'returned image '.$atts['photo'].' ('.$atts['width'].'x'.$atts['height'].')' );
			}

			if ( empty( $atts['photo'] ) ) {
				$media_info = $this->p->og->get_media_info( $atts['size'], array( 'img_url' ), $mod, 'og' );
				$atts['photo'] = $media_info['img_url'];
			}

			if ( array_key_exists( 'tweet', $atts ) )
				$atts['caption'] = $atts['tweet'];

			if ( ! array_key_exists( 'caption', $atts ) ) {
				if ( empty( $atts['caption'] ) ) {
					$caption_len = $this->p->ssb_sharing->get_tweet_max_len( 'buffer' );
					$atts['caption'] = $this->p->page->get_caption( $opts['buffer_caption'], $caption_len,
						$mod, true, true, true, 'twitter_desc' );
				}
			}

			if ( ! array_key_exists( 'via', $atts ) ) {
				if ( ! empty( $opts['buffer_via'] ) ) {
					$atts['via'] = preg_replace( '/^@/', '', 
						SucomUtil::get_locale_opt( 'tc_site', $opts ) );
				} else $atts['via'] = '';
			}

			// hashtags are included in the caption instead
			if ( ! array_key_exists( 'hashtags', $atts ) )
				$atts['hashtags'] = '';

			$html = '<!-- Buffer Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, 'buffer' ).'>'.
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
			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_buffer',
				SucomUtil::get_prot().'://d389zggrogs7qo.cloudfront.net/js/button.js', $pos ) );

			return '<script type="text/javascript" id="buffer-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "buffer-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
