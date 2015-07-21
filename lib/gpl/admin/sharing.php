<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbGplAdminSharing' ) ) {

	class WpssoSsbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_cache_rows' => 3,	// advanced 'File and Object Cache' options
				'sharing_include_rows' => 2,	// social sharing 'Include Buttons' options
				'sharing_preset_rows' => 2,	// social sharing 'Preset Options' options
				'post_tabs' => 1,		// post 'Sharing Buttons' tab
				'post_sharing_rows' => 3,	// post 'Sharing Buttons' options
			), 30 );
		}

		public function filter_plugin_cache_rows( $rows, $form, $network = false ) {

			$rows[] = $this->p->util->get_th( 'Social File Cache Expiry', 'highlight', 'plugin_file_cache_exp' ).
			'<td nowrap class="blank">'.$this->p->cf['form']['file_cache_hrs'][$form->options['plugin_file_cache_exp']].' hours</td>'.
			$this->get_site_use( $form, $network, 'plugin_file_cache_exp' );

			return $rows;
		}

		public function filter_sharing_include_rows( $rows, $form ) {
			$checkboxes = '';

			foreach ( $this->p->util->get_post_types( 'frontend' ) as $post_type )
				$checkboxes .= '<p>'.$form->get_no_checkbox( 'buttons_add_to_'.$post_type->name ).' '.
					$post_type->label.' '.( empty( $post_type->description ) ? '' :
						'('.$post_type->description.')' ).'</p>';

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			$rows[] = $this->p->util->get_th( 'Include on Post Types', null, 'buttons_add_to' ).
				'<td class="blank">'.$checkboxes.'</td>';

			return $rows;
		}

		public function filter_sharing_preset_rows( $rows, $form ) {
			$presets = array();
			foreach ( SucomUtil::preg_grep_keys( '/^buttons_preset_/', $this->p->options, false, '' ) as $key => $val )
				$presets[$key] = ucwords( preg_replace( '/_/', ' ', $key ) );
			asort( $presets );

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			foreach( $presets as $filter_id => $filter_name )
				$rows[] = $this->p->util->get_th( $filter_name.' Preset', null, 'sharing_preset' ).
				'<td class="blank">'.$this->p->options['buttons_preset_'.$filter_id].'</td>';

			return $rows;
		}

		public function filter_post_tabs( $tabs ) {
			$new_tabs = array();
			foreach ( $tabs as $key => $val ) {
				$new_tabs[$key] = $val;
				if ( $key === 'media' )	// insert the social sharing tab after the media tab
					$new_tabs['sharing'] = 'Sharing Buttons';
			}
			return $new_tabs;
		}

		public function filter_post_sharing_rows( $rows, $form, $head_info ) {

			$lca = $this->p->cf['lca'];
			$post_status = get_post_status( $head_info['post_id'] );
			$size_info = $this->p->media->get_size_info( 'thumbnail' );

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			/*
			 * Pinterest
			 */
			list( $img_url ) = $this->p->og->get_the_media_urls( $lca.'-pinterest-button', $head_info['post_id'], 'rp', array( 'image' ) );
			$th = $this->p->util->get_th( 'Pinterest Image Caption', 'medium', 'post-pin_desc' );
			if ( ! empty( $img_url ) ) {
				list( $thumb_url ) = $this->p->og->get_the_media_urls( 'thumbnail', $head_info['post_id'], 'rp', array( 'image' ) );
				$rows[] = $th.'<td class="blank">'.
				$this->p->webpage->get_caption( $this->p->options['pin_caption'], $this->p->options['pin_cap_len'] ).'</td>'.
				'<td style="width:'.$size_info['width'].'px;"><img src="'.$thumb_url.'"
					style="max-width:'.$size_info['width'].'px;"></td>';
			} else $rows[] = $th.'<td class="blank"><em>Caption disabled - no suitable image found for the Pinterest button.</em></td>';

			/*
			 * Tumblr
			 */
			list( $img_url, $vid_url, $prev_url ) = $this->p->og->get_the_media_urls( $lca.'-tumblr-button', 
				$head_info['post_id'], 'og', array( 'image', 'video', 'preview' ) );
			$th = $this->p->util->get_th( 'Tumblr Image Caption', 'medium', 'post-tumblr_img_desc' );
			if ( ! empty( $img_url ) ) {
				list( $thumb_url ) = $this->p->og->get_the_media_urls( 'thumbnail', $head_info['post_id'], 'og', array( 'image' ) );
				$rows[] = $th.'<td class="blank">'.
				$this->p->webpage->get_caption( $this->p->options['tumblr_caption'], $this->p->options['tumblr_cap_len'] ).'</td>'.
				'<td style="width:'.$size_info['width'].'px;"><img src="'.$thumb_url.'"
					style="max-width:'.$size_info['width'].'px;"></td>';
			} else $rows[] = $th.'<td class="blank"><em>Caption disabled - no suitable image found for the Tumblr button.</em></td>';

			$th = $this->p->util->get_th( 'Tumblr Video Caption', 'medium', 'post-tumblr_vid_desc' );
			if ( ! empty( $vid_url ) ) {
				$rows[] = $th.'<td class="blank">'.
				$this->p->webpage->get_caption( $this->p->options['tumblr_caption'], $this->p->options['tumblr_cap_len'] ).'</td>'.
				'<td style="width:'.$size_info['width'].'px;"><img src="'.$prev_url.'" 
					style="max-width:'.$size_info['width'].'px;"></td>';
			} else $rows[] = $th.'<td class="blank"><em>Caption disabled - no suitable video found for the Tumblr button.</em></td>';

			/*
			 * Twitter
			 */
			$twitter_cap_len = $this->p->util->get_tweet_max_len( get_permalink( $head_info['post_id'] ) );
			$rows[] = $this->p->util->get_th( 'Tweet Text', 'medium', 'post-twitter_desc' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( $this->p->options['twitter_caption'], $twitter_cap_len,
				true, true, true ).'</td>';	// use_post = true, use_cache = true, add_hashtags = true

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->get_th( 'Disable Sharing Buttons', 'medium', 'post-buttons_disabled', $head_info ).
			'<td class="blank">&nbsp;</td>';

			return $rows;
		}

		protected function get_site_use( &$form, &$network, $opt ) {
			return $network === false ? '' : $this->p->util->get_th( 'Site Use', 'site_use' ).
				'<td class="site_use blank">'.$form->get_select( $opt.':use', 
					$this->p->cf['form']['site_option_use'], 'site_use', null, true, true ).'</td>';
		}
	}
}

?>
