<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) )
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuSharingFacebook' ) && class_exists( 'WpssoSsbSubmenuSharing' ) ) {

	class WpssoSsbSubmenuSharingFacebook extends WpssoSsbSubmenuSharing {

		public $id = '';
		public $name = '';
		public $form = '';

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->id = $id;
			$this->name = $name;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		public function show_metabox_website() {
			$metabox = 'fb';
			$tabs = array( 
				'all' => 'All Buttons',
				'like' => 'Like and Send',
				'share' => 'Share',
			);
			$rows = array();
			foreach ( $tabs as $key => $title )
				$rows[$key] = $this->get_rows( $metabox, $key );
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {
			$rows = array();
			switch ( $metabox.'-'.$key ) {

				case 'fb-all':

					$rows[] = $this->p->util->get_th( 'Show Button in', 'short' ).
					'<td>'.( $this->show_on_checkboxes( 'fb' ) ).'</td>';

					$rows[] = $this->p->util->get_th( 'Preferred Order', 'short' ).
					'<td>'.$this->form->get_select( 'fb_order', 
						range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 'short' ).'</td>';
	
					$rows[] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( 'JavaScript in', 'short' ).
					'<td>'. $this->form->get_select( 'fb_js_loc', $this->p->cf['form']['js_locations'] ).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Default Language', 'short' ).
					'<td>'.$this->form->get_select( 'fb_lang', SucomUtil::get_pub_lang( 'facebook' ) ).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Button Type', 'short' ).
					'<td>'.$this->form->get_select( 'fb_button', array( 'like' => 'Like and Send', 'share' => 'Share' ) ).'</td>';

					break;

				case 'fb-like':

					$rows[] = $this->p->util->get_th( 'Markup Language', 'short' ).
					'<td>'.$this->form->get_select( 'fb_markup', 
						array( 
							'html5' => 'HTML5', 
							'xfbml' => 'XFBML',
						) 
					).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Include Send', 'short', null, 
					'The Send button is only available in combination with the XFBML <em>Markup Language</em>.' ).
					'<td>'.$this->form->get_checkbox( 'fb_send' ).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Layout', 'short', null, 
					'The Standard layout displays social text to the right of the button, and friends\' profile photos below (if <em>Show Faces</em> is also checked). The Button Count layout displays the total number of likes to the right of the button, and the Box Count layout displays the total number of likes above the button.' ).
					'<td>'.$this->form->get_select( 'fb_layout', 
						array(
							'standard' => 'Standard',
							'button_count' => 'Button Count',
							'box_count' => 'Box Count',
						) 
					).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Show Faces', 'short', null, 
					'Show profile photos below the Standard button (Standard button <em>Layout</em> only).' ).
					'<td>'.$this->form->get_checkbox( 'fb_show_faces' ).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Font', 'short' ).'<td>'.
					$this->form->get_select( 'fb_font', 
						array( 
							'arial' => 'Arial',
							'lucida grande' => 'Lucida Grande',
							'segoe ui' => 'Segoe UI',
							'tahoma' => 'Tahoma',
							'trebuchet ms' => 'Trebuchet MS',
							'verdana' => 'Verdana',
						) 
					).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Color Scheme', 'short' ).'<td>'.
					$this->form->get_select( 'fb_colorscheme', 
						array( 
							'light' => 'Light',
							'dark' => 'Dark',
						)
					).'</td>';
	
					$rows[] = $this->p->util->get_th( 'Action Name', 'short' ).'<td>'.
					$this->form->get_select( 'fb_action', 
						array( 
							'like' => 'Like',
							'recommend' => 'Recommend',
						)
					).'</td>';

					break;
	
				case 'fb-share':

					$rows[] = $this->p->util->get_th( 'Layout', 'short' ).'<td>'.
					$this->form->get_select( 'fb_type', 
						array(
							'button' => 'Button',
							'button_count' => 'Button Count',
							'box_count' => 'Box Count',
							'icon' => 'Small Icon',
							'link' => 'Text Link',
						) 
					).'</td>';

					break;
			}
			return $rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbSharingFacebook' ) ) {

	class WpssoSsbSharingFacebook {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'fb_on_content' => 1,
					'fb_on_excerpt' => 0,
					'fb_on_sidebar' => 0,
					'fb_on_admin_edit' => 1,
					'fb_order' => 1,
					'fb_js_loc' => 'header',
					'fb_button' => 'like',
					'fb_markup' => 'xfbml',
					'fb_send' => 1,
					'fb_layout' => 'button_count',
					'fb_font' => 'arial',
					'fb_show_faces' => 0,
					'fb_colorscheme' => 'light',
					'fb_action' => 'like',
					'fb_type' => 'button_count',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		public function get_html( $atts = array(), &$opts = array() ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			if ( empty( $opts ) ) 
				$opts =& $this->p->options;
			$use_post = array_key_exists( 'use_post', $atts ) ? $atts['use_post'] : true;
			$send = $opts['fb_send'] ? 'true' : 'false';
			$show_faces = $opts['fb_show_faces'] ? 'true' : 'false';
			$source_id = 'facebook';
			switch ( $opts['fb_button'] ) {
				case 'like': $source_id = $this->p->util->get_source_id( 'facebook', $atts ); break;
				case 'share': $source_id = $this->p->util->get_source_id( 'fb-share', $atts ); break;
			}
			$atts['add_page'] = array_key_exists( 'add_page', $atts ) ? $atts['add_page'] : true;	// get_sharing_url argument
			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $use_post, $atts['add_page'], $source_id ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], 
					$use_post, $atts['add_page'], $source_id );

			$html = '';
			switch ( $opts['fb_button'] ) {
				case 'like':
					switch ( $opts['fb_markup'] ) {
						case 'xfbml':
							// XFBML
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							$this->p->sharing->get_css( 'facebook', $atts, 'fb-like' ).'><fb:like href="'.
							$atts['url'].'" send="'.$send.'" layout="'.$opts['fb_layout'].'" show_faces="'.
							$show_faces.'" font="'.$opts['fb_font'].'" action="'.
							$opts['fb_action'].'" colorscheme="'.$opts['fb_colorscheme'].'"></fb:like></div>';
							break;
						case 'html5':
							// HTML5
							$html .= '<!-- Facebook Like / Send Button(s) --><div '.
							$this->p->sharing->get_css( 'facebook', $atts, 'fb-like' ).' data-href="'.
							$atts['url'].'" data-send="'.$send.'" data-layout="'.
							$opts['fb_layout'].'" data-show-faces="'.$show_faces.'" data-font="'.
							$opts['fb_font'].'" data-action="'.$opts['fb_action'].'" data-colorscheme="'.
							$opts['fb_colorscheme'].'"></div>';
							break;
					}
					break;
				case 'share':
					$html .= '<!-- Facebook Share Button --><div '.
					$this->p->sharing->get_css( 'fb-share', $atts, 'fb-share' ).'><fb:share-button href="'.
					$atts['url'].'" font="'.$opts['fb_font'].'" type="'.$opts['fb_type'].'"></fb:share-button></div>';
					break;
			}

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html."\n";
		}
		
		public function get_js( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$prot = empty( $_SERVER['HTTPS'] ) ? 'http:' : 'https:';
			$app_id = empty( $this->p->options['fb_app_id'] ) ? '' : $this->p->options['fb_app_id'];

			$lang = empty( $this->p->options['fb_lang'] ) ? 'en_US' : $this->p->options['fb_lang'];
			$lang = apply_filters( $this->p->cf['lca'].'_lang', $lang, SucomUtil::get_pub_lang( 'facebook' ) );

			// do not use get_cache_file_url() since the facebook javascript does not work when hosted locally
			$js_url = apply_filters( $this->p->cf['lca'].'_js_url_facebook', 
				$prot.'//connect.facebook.net/'.$lang.'/sdk.js#xfbml=1&version=v2.3&appId='.$app_id, $pos );

			$html = '<script type="text/javascript" id="fb-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "fb-script-'.$pos.'", "'.$js_url.'" );</script>'."\n";

			return $html;
		}
	}
}

?>
