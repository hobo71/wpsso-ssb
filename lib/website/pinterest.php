<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuWebsitePinterest' ) ) {

	class WpssoSsbSubmenuWebsitePinterest {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'image_dimensions_general_rows' => 2,	// $table_rows, $form
				'ssb_website_pinterest_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		// add an option to the WordPress -> Settings -> Image Dimensions page
		public function filter_image_dimensions_general_rows( $table_rows, $form ) {

			$def_dimensions = $this->p->opt->get_defaults( 'pin_img_width' ).'x'.
				$this->p->opt->get_defaults( 'pin_img_height' ).' '.
				( $this->p->opt->get_defaults( 'pin_img_crop' ) == 0 ? 'uncropped' : 'cropped' );

			$table_rows['pin_img_dimensions'] = $form->get_th_html( _x( 'Pinterest <em>Sharing Button</em>', 'option label', 'wpsso-ssb' ), null, 'pin_img_dimensions', 'The image dimensions that the Pinterest Pin It button will share (defaults is '.$def_dimensions.'). Images in the Facebook / Open Graph meta tags are usually cropped, where-as images on Pinterest often look better in their original aspect ratio (uncropped) and/or cropped using portrait photo dimensions.' ).
			'<td>'.$form->get_image_dimensions_input( 'pin_img' ).'</td>';	// $use_opts = false

			return $table_rows;
		}

		public function filter_ssb_website_pinterest_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short', null ).
			'<td>'.$submenu->show_on_checkboxes( 'pin' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( ! SucomUtil::get_const( 'WPSSO_VARY_USER_AGENT_DISABLE' ) ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform',
					'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'pin_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Height',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_button_height', array( 'small' => 'Small', 'large' => 'Large' ) );

			$table_rows[] = $form->get_th_html( _x( 'Button Shape',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_button_shape', array( 'rect' => 'Rectangular', 'round' => 'Circular' ) );

			$table_rows[] = $form->get_th_html( _x( 'Button Color',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_button_color', array( 'gray' => 'Gray', 'red' => 'Red', 'white' => 'White' ) );

			$table_rows[] = $form->get_th_html( _x( 'Button Language',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_button_lang', SucomUtil::get_pub_lang( 'pinterest' ) );

			$table_rows[] = $form->get_th_html( _x( 'Show Pin Count',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_count_layout', array( 
				'none' => 'Not Shown',
				'beside' => 'Beside the Button',
				'above' => 'Above the Button',
			) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Image Dimensions',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_image_dimensions_input( 'pin_img', false, true ).'</td>';	// $use_opts = false, $narrow = true

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Caption Text',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'pin_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Caption Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_input( 'pin_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsitePinterest' ) ) {

	class WpssoSsbWebsitePinterest {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'pin_order' => 6,
					'pin_on_content' => 0,
					'pin_on_excerpt' => 0,
					'pin_on_sidebar' => 0,
					'pin_on_admin_edit' => 1,
					'pin_platform' => 'any',
					'pin_script_loc' => 'footer',
					'pin_button_lang' => 'en',
					'pin_button_shape' => 'rect',
					'pin_button_color' => 'gray',
					'pin_button_height' => 'small',
					'pin_count_layout' => 'beside',
					'pin_img_width' => 800,
					'pin_img_height' => 1600,
					'pin_img_crop' => 0,
					'pin_img_crop_x' => 'center',
					'pin_img_crop_y' => 'center',
					'pin_caption' => 'excerpt',
					'pin_cap_len' => 400,
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
			$sizes['pin_img'] = array(
				'name' => 'pinterest-button',
				'label' => _x( 'Pinterest Sharing Button', 'image size label', 'wpsso-ssb' ),
			);
			return $sizes;
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$href_query = '?url='.urlencode( $atts['url'] );

			if ( empty( $atts['size'] ) ) 
				$atts['size'] = $this->p->cf['lca'].'-pinterest-button';

			if ( ! empty( $atts['pid'] ) ) {
				$force_regen = $this->p->util->is_force_regen( $mod, 'p' );	// false by default

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
				$media_info = $this->p->og->get_media_info( $atts['size'], array( 'img_url' ), $mod, 'p' );
				$atts['photo'] = $media_info['img_url'];
				if ( empty( $atts['photo'] ) ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: no photo available' );
					return '<!-- Pinterest Button: no photo available -->';	// abort
				}
			}
			$href_query .= '&amp;media='.rawurlencode( $atts['photo'] );

			if ( empty( $atts['caption'] ) ) {
				$atts['caption'] = $this->p->webpage->get_caption( $opts['pin_caption'], $opts['pin_cap_len'],
					$mod, true, true, false, 'pin_desc' );
			}

			// use rawurlencode() for mobile devices (encodes a space as '%20' instead of '+')
			$href_query .= '&amp;description='.rawurlencode( $atts['caption'] );

			switch ( $opts['pin_button_shape'] ) {
				case 'rect':
					$pin_img_width = $opts['pin_button_height'] == 'small' ? 40 : 56;
					$pin_img_height = $opts['pin_button_height'] == 'small' ? 20 : 28;
					$pin_img_url = SucomUtil::get_prot().'://assets.pinterest.com/images/pidgets/pinit_fg_'.
						$opts['pin_button_lang'].'_'.$opts['pin_button_shape'].'_'.
						$opts['pin_button_color'].'_'.$pin_img_height.'.png';
					break;
				case 'round':
					$pin_img_width = $pin_img_height = $opts['pin_button_height'] == 'small' ? 16 : 32;
					$pin_img_url = SucomUtil::get_prot().'://assets.pinterest.com/images/pidgets/pinit_fg_'.
						'en_'.$opts['pin_button_shape'].'_'.
						'red_'.$pin_img_height.'.png';
					break;
				default:
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: unknown pinterest button shape' );
					return $html;
					break;
			}
			$pin_img_url = $this->p->ssb_sharing->get_social_file_cache_url( $pin_img_url );

			$html = '<!-- Pinterest Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, 'pinterest' ).'>'.
			'<a href="'.SucomUtil::get_prot().'://pinterest.com/pin/create/button/'.$href_query.'" '.
			'data-pin-do="buttonPin" '.
			'data-pin-zero="true" '.
			'data-pin-lang="'.$opts['pin_button_lang'].'" '.
			'data-pin-shape="'.$opts['pin_button_shape'].'" '.
			'data-pin-color="'.$opts['pin_button_color'].'" '.
			'data-pin-height="'.$pin_img_height.'" '.
			'data-pin-config="'.$opts['pin_count_layout'].'">'.
			'<img border="0" alt="Pin It" src="'.$pin_img_url.'" width="'.$pin_img_width.'" height="'.$pin_img_height.'" /></a></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );

			return $html;
		}

		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_pinterest', 
				SucomUtil::get_prot().'://assets.pinterest.com/js/pinit.js', $pos ) );

			return '<script type="text/javascript" id="pinterest-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "pinterest-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
