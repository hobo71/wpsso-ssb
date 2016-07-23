<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbGplAdminSharing' ) ) {

	class WpssoSsbGplAdminSharing {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'plugin_cache_rows' => 3,		// $table_rows, $form, $network
				'ssb_buttons_include_rows' => 2,	// $table_rows, $form
				'ssb_buttons_preset_rows' => 2,		// $table_rows, $form
				'post_buttons_rows' => 4,		// $table_rows, $form, $head, $mod
			), 30 );
		}

		public function filter_plugin_cache_rows( $table_rows, $form, $network = false ) {

			$table_rows['plugin_file_cache_exp'] = $form->get_th_html( _x( 'Social File Cache Expiry',
				'option label', 'wpsso-ssb' ), 'highlight', 'plugin_file_cache_exp' ).
			'<td nowrap class="blank">'.$form->get_no_select( 'plugin_file_cache_exp', 
				$this->p->cf['form']['file_cache_hrs'], 'medium', '', true ).
					_x( 'hours', 'option comment', 'wpsso-ssb' ).'</td>'.
			$this->p->admin->get_site_use( $form, $network, 'plugin_file_cache_exp' );

			return $table_rows;
		}

		public function filter_ssb_buttons_include_rows( $table_rows, $form ) {

			$add_to_checkboxes = '';
			foreach ( $this->p->util->get_post_types() as $post_type )
				$add_to_checkboxes .= '<p>'.$form->get_no_checkbox( 'buttons_add_to_'.$post_type->name ).' '.
					$post_type->label.' '.( empty( $post_type->description ) ? '' :
						'('.$post_type->description.')' ).'</p>';

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';

			$table_rows['buttons_add_to'] = $form->get_th_html( _x( 'Include on Post Types',
				'option label', 'wpsso-ssb' ), null, 'buttons_add_to' ).
				'<td class="blank">'.$add_to_checkboxes.'</td>';

			return $table_rows;
		}

		public function filter_ssb_buttons_preset_rows( $table_rows, $form ) {

			$presets = array( 'shortcode' => 'Shortcode', 'widget' => 'Widget' );
			$show_on = apply_filters( $this->p->cf['lca'].'_ssb_buttons_show_on', 
				$this->p->cf['sharing']['show_on'], '' );
			foreach ( $show_on as $type => $label )
				$presets[$type] = $label;
			asort( $presets );

			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';

			foreach( $presets as $filter_id => $filter_name )
				$table_rows[] = $form->get_th_html( sprintf( _x( '%s Preset',
					'option label', 'wpsso-ssb' ), $filter_name ), null, 'buttons_preset' ).
				'<td class="blank">'.$form->get_no_select( 'buttons_preset_ssb-'.$filter_id, 
					array_merge( array( '' ), array_keys( $this->p->cf['opt']['preset'] ) ) ).'</td>';

			return $table_rows;
		}

		public function filter_post_buttons_rows( $table_rows, $form, $head, $mod ) {

			if ( empty( $mod['post_status'] ) || $mod['post_status'] === 'auto-draft' ) {
				$table_rows['save_a_draft'] = '<td><blockquote class="status-info"><p class="centered">'.
					sprintf( __( 'Save a draft version or publish the %s to display these options.',
						'wpsso-ssb' ), ucfirst( $mod['post_type'] ) ).'</p></td>';
				return $table_rows;	// abort
			}

			$size_info = SucomUtil::get_size_info( 'thumbnail' );
			$title_caption = $this->p->webpage->get_caption( 'title', 0, $mod, true, false );

			$table_rows[] = '<td colspan="3" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';

			/*
			 * Email
			 */
			$caption_len = $this->p->options['email_cap_len'];
			$caption_text = $this->p->webpage->get_caption( 'excerpt', $caption_len, 
				$mod, true, $this->p->options['email_cap_hashtags'], true, 'none' );

			$form_rows['email_title'] = array(
				'label' => _x( 'Email Subject', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_title', 'td_class' => 'blank',
				'content' => $form->get_no_input_value( $title_caption, 'wide' ),
			);
			$form_rows['email_desc'] = array(
				'label' => _x( 'Email Message', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-email_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
			);

			/*
			 * Twitter
			 */
			$caption_len = $this->p->ssb->get_tweet_max_len();
			$caption_text = $this->p->webpage->get_caption( $this->p->options['twitter_caption'],
				$caption_len, $mod, true, true );	// $use_cache = true, $add_hashtags = true

			$form_rows['twitter_desc'] = array(
				'label' => _x( 'Tweet Text', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-twitter_desc', 'td_class' => 'blank',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ),
			);

			/*
			 * Pinterest
			 */
			$caption_len = $this->p->options['pin_cap_len'];
			$caption_text = $this->p->webpage->get_caption( $this->p->options['pin_caption'],
				$caption_len, $mod );

			$media = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-pinterest-button',
				array( 'pid', 'img_url' ), $mod, 'rp' );

			if ( ! empty( $media['pid'] ) )
				list( $media['img_url'], $img_width, $img_height,
					$img_cropped ) = $this->p->media->get_attachment_image_src( $media['pid'],
						'thumbnail', false ); 
			
			$form_rows['pin_desc'] = array(
				'label' => _x( 'Pinterest Caption Text', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-pin_desc', 'td_class' => 'blank top',
				'content' => $form->get_no_textarea_value( $caption_text, '', '', $caption_len ).
					( empty( $media['img_url'] ) ? '' : '</td><td class="top" style="width:'.
					$size_info['width'].'px;"><img src="'.$media['img_url'].'" style="max-width:'.
					$size_info['width'].'px;">' ),
			);

			/*
			 * Tumblr
			 */
			$caption_len = $this->p->options['tumblr_cap_len'];
			$caption_text = $this->p->webpage->get_caption( $this->p->options['tumblr_caption'],
				$caption_len, $mod );

			$media = $this->p->og->get_the_media_info( $this->p->cf['lca'].'-tumblr-button',
				array( 'pid', 'img_url' ), $mod, 'og' );

			if ( ! empty( $media['pid'] ) )
				list( $media['img_url'], $img_width, $img_height,
					$img_cropped ) = $this->p->media->get_attachment_image_src( $media['pid'],
						'thumbnail', false ); 

			$form_rows['tumblr_img_desc'] = array(
				'label' => _x( 'Tumblr Image Caption', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-tumblr_img_desc', 'td_class' => 'blank top',
				'content' => ( empty( $media['img_url'] ) ?
					'<em>'.sprintf( __( 'Caption disabled - no suitable image found for the %s button',
						'wpsso-ssb' ), 'Tumblr' ).'</em>' :
					$form->get_no_textarea_value( $caption_text, '', '', $caption_len ).
					'</td><td class="top" style="width:'.$size_info['width'].'px;"><img src="'.
					$media['img_url'].'" style="max-width:'.$size_info['width'].'px;">' ),
			);

			$form_rows['tumblr_vid_desc'] = array(
				'label' => _x( 'Tumblr Video Caption', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-tumblr_vid_desc', 'td_class' => 'blank',
				'content' => '<em>'.sprintf( __( 'Caption disabled - no suitable video found for the %s button',
					'wpsso-ssb' ), 'Tumblr' ).'</em>',
			);

			/*
			 * Disable Buttons Checkbox
			 */
			$form_rows['buttons_disabled'] = array(
				'label' => _x( 'Disable Sharing Buttons', 'option label', 'wpsso-ssb' ),
				'th_class' => 'medium', 'tooltip' => 'post-buttons_disabled', 'td_class' => 'blank',
				'content' => $form->get_no_checkbox( 'buttons_disabled' ),
			);

			return $form->get_md_form_rows( $table_rows, $form_rows, $head, $mod );
		}
	}
}

?>
