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

			if ( $this->p->check->aop() )
				$rows[] = '<td colspan="'.( $network === false ? 2 : 4 ).'" align="center">'.
					$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			$rows[] = $this->p->util->th( 'Social File Cache Expiry', 'highlight', 'plugin_file_cache_hrs' ).
			'<td nowrap class="blank">'.$form->get_no_input( 'plugin_file_cache_hrs', 'short' ).' hours</td>'.
			( $network === false ? '' : $this->p->util->th( 'Site Use', 'narrow' ).
				'<td class="blank">'.$form->get_select( 'plugin_file_cache_hrs:use', 
					$this->p->cf['form']['site_option_use'], 'site_use', null, true, true ).'</td>' );

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->th( 'Verify SSL Certificates', null, 'plugin_verify_certs' ).
			'<td class="blank">'.$form->get_no_checkbox( 'plugin_verify_certs' ).'</td>'.
			( $network === false ? '' : $this->p->util->th( 'Site Use', 'narrow' ).
				'<td class="blank">'.$form->get_select( 'plugin_verify_certs:use', 
					$this->p->cf['form']['site_option_use'], 'site_use', null, true, true ).'</td>' );

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

			$rows[] = $this->p->util->th( 'Include on Post Types', null, 'buttons_add_to' ).
				'<td class="blank">'.$checkboxes.'</td>';

			return $rows;
		}

		public function filter_sharing_preset_rows( $rows, $form ) {
			$presets = array();
			foreach ( SucomUtil::preg_grep_keys( '/^buttons_preset_/', $this->p->options, false, '' ) as $key => $val )
				$presets[$key] = ucwords( preg_replace( '/_/', ' ', $key ) );
			asort( $presets );

			$rows[] = '<td colspan="2" align="center">'.$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			foreach( $presets as $filter_id => $filter_name )
				$rows[] = $this->p->util->th( $filter_name.' Preset', null, 'sharing_preset' ).
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

			$twitter_cap_len = $this->p->util->get_tweet_max_len( get_permalink( $head_info['id'] ) );
			list( $pid, $video_url ) = $this->p->sharing->get_sharing_media( $head_info['id'] );

			$rows[] = '<td colspan="2" align="center">'.$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';

			$th = $this->p->util->th( 'Pinterest Image Caption', 'medium', 'post-pin_desc' );
			if ( ! empty( $pid ) ) {
				$img = $this->p->media->get_attachment_image_src( $pid, $this->p->cf['lca'].'-pinterest-button', false );
				if ( empty( $img[0] ) )
					$rows[] = $th.'<td class="blank"><em>Caption disabled - image ID '.
						$pid.' is too small for the Pinterest button Image Dimensions.</em></td>';
				else $rows[] = $th.'<td class="blank">'.
					$this->p->webpage->get_caption( $this->p->options['pin_caption'], $this->p->options['pin_cap_len'] ).'</td>';
			} else $rows[] = $th.'<td class="blank"><em>Caption disabled - no custom Image ID, featured or attached image found.</em></td>';

			$th = $this->p->util->th( 'Tumblr Image Caption', 'medium', 'post-tumblr_img_desc' );
			if ( empty( $this->p->options['tumblr_photo'] ) ) {
				$rows[] = $th.'<td class="blank"><em>\'Use Featured Image\' option is disabled.</em></td>';
			} elseif ( ! empty( $pid ) ) {
				$img = $this->p->media->get_attachment_image_src( $pid, $this->p->cf['lca'].'-tumblr-button', false );
				if ( empty( $img[0] ) )
					$rows[] = $th.'<td class="blank"><em>Caption disabled - image ID '.
						$pid.' is too small for the Tumblr button Image Dimensions.</em></td>';
				else $rows[] = $th.'<td class="blank">'.
					$this->p->webpage->get_caption( $this->p->options['tumblr_caption'], $this->p->options['tumblr_cap_len'] ).'</td>';
			} else $rows[] = $th.'<td class="blank"><em>Caption disabled - no custom Image ID, featured or attached image found.</em></td>';

			$th = $this->p->util->th( 'Tumblr Video Caption', 'medium', 'post-tumblr_vid_desc' );
			if ( ! empty( $vid_url ) )
				$rows[] = $th.'<td class="blank">'.
				$this->p->webpage->get_caption( $this->p->options['tumblr_caption'], $this->p->options['tumblr_cap_len'] ).'</td>';
			else $rows[] = $th.'<td class="blank"><em>Caption disabled - no custom Video URL or embedded video found.</em></td>';

			$rows[] = $this->p->util->th( 'Tweet Text', 'medium', 'post-twitter_desc' ). 
			'<td class="blank">'.$this->p->webpage->get_caption( $this->p->options['twitter_caption'], $twitter_cap_len,
				true, true, true ).'</td>';	// use_post = true, use_cache = true, add_hashtags = true

			$rows[] = '<tr class="hide_in_basic">'.
			$this->p->util->th( 'Disable Sharing Buttons', 'medium', 'post-buttons_disabled', $head_info ).
			'<td class="blank">&nbsp;</td>';

			return $rows;
		}
	}
}

?>
