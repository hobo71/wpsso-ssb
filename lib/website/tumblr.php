<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuSharingTumblr' ) && class_exists( 'WpssoSsbSubmenuSharing' ) ) {

	class WpssoSsbSubmenuSharingTumblr extends WpssoSsbSubmenuSharing {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$this->p->util->add_plugin_filters( $this, array( 
				'image-dimensions_general_rows' => 2,
			) );
		}

		// add an option to the WordPress -> Settings -> Image Dimensions page
		public function filter_image_dimensions_general_rows( $rows, $form ) {

			$def_dimensions = $this->p->opt->get_defaults( 'tumblr_img_width' ).'x'.
				$this->p->opt->get_defaults( 'tumblr_img_height' ).' '.
				( $this->p->opt->get_defaults( 'tumblr_img_crop' ) == 0 ? 'uncropped' : 'cropped' );

			$rows[] = $this->p->util->get_th( _x( 'Tumblr <em>Sharing Button</em>', 'option label', 'wpsso-ssb' ), null, 'tumblr_img_dimensions', 'The image dimensions that the Tumblr button will share (defaults is '.$def_dimensions.').' ).
			'<td>'.$form->get_image_dimensions_input( 'tumblr_img' ).'</td>';

			return $rows;
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();

			$rows[] = $this->p->util->get_th( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'tumblr_order', 
				range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 'short' ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short', null ).
			'<td>'.$this->show_on_checkboxes( 'tumblr' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_select( 'tumblr_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'tumblr_script_loc',
				$this->p->cf['form']['script_locations'] ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Button Language',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_select( 'tumblr_lang', 
				SucomUtil::get_pub_lang( 'tumblr' ) );

			$rows[] = $this->p->util->get_th( _x( 'Button Color',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_select( 'tumblr_color', 
				array( 'blue' => 'Blue', 'black' => 'Black', 'white' => 'White' ) );

			$rows[] = $this->p->util->get_th( _x( 'Show Counter',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_select( 'tumblr_counter', 
				array( 
					'none' => 'Not Shown',
					'top' => 'Above the Button',
					'right' => 'Right of the Button',
				)
			).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Add Attribution',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_checkbox( 'tumblr_show_via' ).'</td>';

			$rows[] = $this->p->util->get_th( _x( 'Image Dimensions',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_image_dimensions_input( 'tumblr_img', false, true ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Media Caption',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'tumblr_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Caption Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_input( 'tumblr_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';
	
			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( _x( 'Link Description',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_input( 'tumblr_desc_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbSharingTumblr' ) ) {

	class WpssoSsbSharingTumblr {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'tumblr_order' => 12,
					'tumblr_on_content' => 0,
					'tumblr_on_excerpt' => 0,
					'tumblr_on_sidebar' => 0,
					'tumblr_on_admin_edit' => 1,
					'tumblr_platform' => 'any',
					'tumblr_script_loc' => 'header',
					'tumblr_lang' => 'en_US',
					'tumblr_color' => 'blue',
					'tumblr_counter' => 'right',
					'tumblr_show_via' => 1,
					'tumblr_img_width' => 600,
					'tumblr_img_height' => 600,
					'tumblr_img_crop' => 0,
					'tumblr_img_crop_x' => 'center',
					'tumblr_img_crop_y' => 'center',
					'tumblr_caption' => 'excerpt',
					'tumblr_cap_len' => 400,
					'tumblr_desc_len' => 300,
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
			$sizes['tumblr_img'] = array(
				'name' => 'tumblr-button',
				'label' => _x( 'Tumblr Sharing Button',
					'image size label', 'wpsso-ssb' ),
			);
			return $sizes;
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$lca = $this->p->cf['lca'];
			$use_post = isset( $atts['use_post'] ) ?
				$atts['use_post'] : true;
			$src_id = $this->p->util->get_source_id( 'tumblr', $atts );

			$atts['add_page'] = isset( $atts['add_page'] ) ?
				$atts['add_page'] : true;	// get_sharing_url argument

			if ( ! array_key_exists( 'lang', $atts ) )
				$atts['lang'] = empty( $opts['tumblr_lang'] ) ?
					'en_US' : $opts['tumblr_lang'];
			$atts['lang'] = apply_filters( $lca.'_pub_lang', $atts['lang'], 'tumblr' );

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $use_post, $atts['add_page'], $src_id ) : 
				apply_filters( $lca.'_sharing_url', $atts['url'], 
					$use_post, $atts['add_page'], $src_id );

			if ( SucomUtil::is_post_page( $use_post ) ) {
				if ( ( $post_obj = $this->p->util->get_post_object( $use_post ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: invalid object type' );
					return false;
				}
				$post_id = empty( $post_obj->ID ) || 
					empty( $post_obj->post_type ) ? 0 : $post_obj->ID;
			} else $post_id = 0;

			if ( empty( $atts['size'] ) ) 
				$atts['size'] = $lca.'-tumblr-button';

			if ( ! empty( $atts['pid'] ) )
				list( $atts['photo'], $atts['width'], $atts['height'], 
					$atts['cropped'] ) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );

			if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) ) {
				extract( $this->p->og->get_the_media_info( $atts['size'],
					$post_id, 'og', array( 'img_url', 'vid_url' ) ) );
				if ( empty( $atts['photo'] ) )
					$atts['photo'] = $img_url;
				if ( empty( $atts['embed'] ) )
					$atts['embed'] = $vid_url;
			}

			if ( $post_id > 0 ) {
				// if no image or video, then check for a 'quote'
				if ( empty( $atts['photo'] ) && empty( $atts['embed'] ) && empty( $atts['quote'] ) )
					if ( get_post_format( $post_id ) === 'quote' ) 
						$atts['quote'] = $this->p->webpage->get_quote( $post_id );
				$atts['tags'] = implode( ', ', $this->p->webpage->get_tags( $post_id ) );
			}

			// we only need the caption, title, or description for some types of shares
			if ( ! empty( $atts['photo'] ) || ! empty( $atts['embed'] ) ) {
				// html encode param is false to use url encoding instead
				if ( empty( $atts['caption'] ) ) 
					$atts['caption'] = $this->p->webpage->get_caption( $opts['tumblr_caption'], $opts['tumblr_cap_len'],
						$use_post, true, false, false, ( ! empty( $atts['photo'] ) ?
							'tumblr_img_desc' : 'tumblr_vid_desc' ), $src_id );

			} else {
				if ( empty( $atts['title'] ) ) 
					$atts['title'] = $this->p->webpage->get_title( null, null,
						$use_post, true, false, false, null, $src_id );

				if ( empty( $atts['description'] ) ) 
					$atts['description'] = $this->p->webpage->get_description( $opts['tumblr_desc_len'], '...',
						$use_post, true, false, false, null, $src_id );
			}

			// define the button, based on what we have
			if ( ! empty( $atts['photo'] ) ) {

				$atts['posttype'] = 'photo';
				$atts['content'] = $atts['photo'];
				// uses $atts['caption']

			} elseif ( ! empty( $atts['embed'] ) ) {

				$atts['posttype'] = 'video';
				$atts['content'] = $atts['embed'];
				// uses $atts['caption']

			} elseif ( ! empty( $atts['quote'] ) ) {

				$atts['posttype'] = 'quote';
				$atts['content'] = $atts['quote'];
				$atts['caption'] = $atts['title'];

				unset( $atts['title'] );

			} elseif ( ! empty( $atts['url'] ) ) {

				$atts['posttype'] = 'link';
				$atts['content'] = $atts['url'];
				$atts['caption'] = $atts['description'];

			} else {
			
				$atts['posttype'] = 'text';
				$atts['content'] = $atts['description'];
				// uses $atts['title']
			}

			$html = '<!-- Tumblr Button -->'.
			'<div '.WpssoSsbSharing::get_css_class_id( 'tumblr', $atts ).'>'.
			'<a href="'.SucomUtil::get_prot().'://www.tumblr.com/share" class="tumblr-share-button"'.
			' data-posttype="'.$atts['posttype'].'"'.
			' data-content="'.$atts['content'].'"'.
			( isset( $atts['title'] ) ? ' data-title="'.$atts['title'].'"' : '' ).
			( isset( $atts['caption'] ) ? ' data-caption="'.$atts['caption'].'"' : '' ).
			( isset( $atts['tags'] ) ? ' data-tags="'.$atts['tags'].'"' : '' ).
			' data-locale="'.$opts['tumblr_lang'].'"'.
			' data-color="'.$opts['tumblr_color'].'"'.
			' data-notes="'.$opts['tumblr_counter'].'"'.
			' data-show-via="'.$opts['tumblr_show_via'].'"></a></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}

		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_tumblr',
				SucomUtil::get_prot().'://assets.tumblr.com/share-button.js', $pos ) );

			return '<script type="text/javascript" id="tumblr-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "tumblr-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
