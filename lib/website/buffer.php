<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuSharingBuffer' ) && class_exists( 'WpssoSsbSubmenuSharing' ) ) {

	class WpssoSsbSubmenuSharingBuffer extends WpssoSsbSubmenuSharing {

		public $id = '';
		public $name = '';
		public $form = '';

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->id = $id;
			$this->name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$this->p->util->add_plugin_filters( $this, array( 
				'image-dimensions_general_rows' => 2,
			) );
		}

		// add an option to the WordPress -> Settings -> Image Dimensions page
		public function filter_image_dimensions_general_rows( $rows, $form ) {

			$rows[] = $this->p->util->th( 'Buffer <em>Sharing Button</em>', null, 'buffer_img_dimensions',
			'The image dimensions that the Buffer button will share (defaults is '.$this->p->opt->get_defaults( 'buffer_img_width' ).'x'.$this->p->opt->get_defaults( 'buffer_img_height' ).' '.( $this->p->opt->get_defaults( 'buffer_img_crop' ) == 0 ? 'un' : '' ).'cropped). Note that original images in the WordPress Media Library and/or NextGEN Gallery must be larger than your chosen image dimensions.' ).
			'<td>'.$form->get_image_dimensions_input( 'buffer_img' ).'</td>';

			return $rows;
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();
			
			$rows[] = $this->p->util->th( 'Show Button in', 'short' ).'<td>'.
			( $this->show_on_checkboxes( 'buffer' ) ).'</td>';

			$rows[] = $this->p->util->th( 'Preferred Order', 'short' ).'<td>'.
			$this->form->get_select( 'buffer_order', 
				range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 
					'short' ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->th( 'JavaScript in', 'short' ).'<td>'.
			$this->form->get_select( 'buffer_js_loc', $this->p->cf['form']['js_locations'] ).'</td>';

			$rows[] = $this->p->util->th( 'Count Position', 'short' ).'<td>'.
			$this->form->get_select( 'buffer_count', array( 'none' => '', 
			'horizontal' => 'Horizontal', 'vertical' => 'Vertical' ) ).'</td>';

			$rows[] = $this->p->util->th( 'Image Dimensions', 'short' ).
			'<td>'.$this->form->get_image_dimensions_input( 'buffer_img', false, true ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->th( 'Tweet Text Source', 'short' ).'<td>'.
			$this->form->get_select( 'buffer_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->th( 'Tweet Text Length', 'short' ).'<td>'.
			$this->form->get_input( 'buffer_cap_len', 'short' ).' characters or less</td>';

			$rows[] = $this->p->util->th( 'Add via @username', 'short', null,
			'Append the website\'s @username to the tweet (see the '.$this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter', 'Twitter options tab' ).' on the General settings page).' ).
			( $this->p->check->aop() == true ? 
				'<td>'.$this->form->get_checkbox( 'buffer_via' ).'</td>' :
				'<td class="blank">'.$this->form->get_no_checkbox( 'buffer_via' ).'</td>' );

			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbSharingBuffer' ) ) {

	class WpssoSsbSharingBuffer {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'buffer_on_content' => 0,
					'buffer_on_excerpt' => 0,
					'buffer_on_sidebar' => 0,
					'buffer_on_admin_edit' => 1,
					'buffer_order' => 6,
					'buffer_js_loc' => 'footer',
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
			$sizes['buffer_img'] = array( 'name' => 'buffer-button', 'label' => 'Buffer Sharing Button' );
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
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http:' : 'https:';
			$use_post = array_key_exists( 'use_post', $atts ) ? $atts['use_post'] : true;
			$source_id = $this->p->util->get_source_id( 'buffer', $atts );
			$atts['add_page'] = array_key_exists( 'add_page', $atts ) ? $atts['add_page'] : true;	// get_sharing_url argument

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $use_post, $atts['add_page'], $source_id ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], 
					$use_post, $atts['add_page'], $source_id );

			$post_id = 0;
			if ( is_singular() || $use_post !== false ) {
				if ( ( $obj = $this->p->util->get_post_object( $use_post ) ) === false ) {
					if ( $this->p->debug->enabled )
						$this->p->debug->log( 'exiting early: invalid object type' );
					return false;
				}
				$post_id = empty( $obj->ID ) || empty( $obj->post_type ) ? 0 : $obj->ID;
			}

			if ( empty( $atts['size'] ) ) 
				$atts['size'] = $this->p->cf['lca'].'-buffer-button';

			if ( empty( $atts['photo'] ) ) {
				if ( empty( $atts['pid'] ) && $post_id > 0 ) {
					// check for meta, featured, and attached images
					$pid = $this->p->mods['util']['post']->get_options( $post_id, 'og_img_id' );
					$pre = $this->p->mods['util']['post']->get_options( $post_id, 'og_img_id_pre' );
					if ( ! empty( $pid ) )
						$atts['pid'] = $pre == 'ngg' ? 'ngg-'.$pid : $pid;
					elseif ( ( is_attachment( $post_id ) || get_post_type( $post_id ) === 'attachment' ) &&
						wp_attachment_is_image( $post_id ) )
							$atts['pid'] = $post_id;
					elseif ( $this->p->is_avail['postthumb'] == true && has_post_thumbnail( $post_id ) )
						$atts['pid'] = get_post_thumbnail_id( $post_id );
					else $atts['pid'] = $this->p->media->get_first_attached_image_id( $post_id );
				}
				if ( ! empty( $atts['pid'] ) )
					list( $atts['photo'], $atts['width'], $atts['height'],
						$atts['cropped'] ) = $this->p->media->get_attachment_image_src( $atts['pid'], $atts['size'], false );
			}

			if ( array_key_exists( 'tweet', $atts ) )
				$atts['caption'] = $atts['tweet'];

			if ( ! array_key_exists( 'caption', $atts ) ) {
				if ( empty( $atts['caption'] ) ) {
					$cap_len = $this->p->util->get_tweet_max_len( $atts['url'], 'buffer' );	// get_tweet_max_len() needs the long URL as input
					$atts['caption'] = $this->p->webpage->get_caption( 
						$opts['buffer_caption'],	// title, excerpt, both
						$cap_len,			// max caption length 
						$use_post,			// 
						true,				// use_cache
						true, 				// add_hashtags
						true, 				// encode
						'twitter_desc',			// custom post meta
						$source_id			// 
					);
				}
			}

			if ( ! array_key_exists( 'via', $atts ) ) {
				if ( ! empty( $opts['buffer_via'] ) && $this->p->check->aop() )
					$atts['via'] = preg_replace( '/^@/', '', $opts['tc_site'] );
				else $atts['via'] = '';
			}

			// hashtags are included in the caption instead
			if ( ! array_key_exists( 'hashtags', $atts ) )
				$atts['hashtags'] = '';

			$html = '<!-- Buffer Button --><div '.$this->p->sharing->get_css( 'buffer', $atts ).'>';
			$html .= '<a href="'.$prot.'//bufferapp.com/add" class="buffer-add-button" ';
			$html .= 'data-url="'.$atts['url'].'" ';
			$html .= empty( $atts['photo'] ) ? '' : 'data-picture="'.$atts['photo'].'" ';
			$html .= empty( $atts['caption'] ) ? '' : 'data-text="'.$atts['caption'].'" ';	// html encoded
			$html .= empty( $atts['vis'] ) ? '' : 'data-via="'.$atts['via'].'" ';
			$html .= 'data-count="'.$opts['buffer_count'].'"></a></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html."\n";
		}
		
		public function get_js( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http:' : 'https:';
			$js_url = $this->p->util->get_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_buffer',
				$prot.'//d389zggrogs7qo.cloudfront.net/js/button.js', $pos ) );

			return '<script type="text/javascript" id="buffer-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "buffer-script-'.$pos.'", "'.$js_url.'" );</script>'."\n";
		}
	}
}

?>
