<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuShareFacebook' ) ) {

	class WpssoSsbSubmenuShareFacebook {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_share_facebook_tabs'       => 1,
				'ssb_share_facebook_all_rows'   => 3,
				'ssb_share_facebook_like_rows'  => 3,
				'ssb_share_facebook_share_rows' => 3,
			) );
		}

		public function filter_ssb_share_facebook_tabs( $tabs ) {
			return array( 
				'all'   => _x( 'All Buttons', 'metabox tab', 'wpsso-ssb' ),
				'like'  => _x( 'Like and Send', 'metabox tab', 'wpsso-ssb' ),
				'share' => _x( 'Share', 'metabox tab', 'wpsso-ssb' ),
			);
		}

		public function filter_ssb_share_facebook_all_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label (short)', 'wpsso-ssb' ), 'short' ) .
			'<td>' . $submenu->show_on_checkboxes( 'fb' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Preferred Order', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {
				$table_rows[] = $form->get_tr_hide( 'basic', 'fb_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'fb_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = $form->get_tr_hide( 'basic', 'fb_script_loc' ).
			$form->get_th_html( _x( 'JavaScript in', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'. $form->get_select( 'fb_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Language', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>' . $form->get_select( 'fb_lang', SucomUtil::get_pub_lang( 'facebook' ) ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Type', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_button', array(
				'like'  => 'Like and Send',
				'share' => 'Share',
			) ).'</td>';

			return $table_rows;
		}

		public function filter_ssb_share_facebook_like_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Markup Language', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_markup', array(
				'html5' => 'HTML5',
				'xfbml' => 'XFBML',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Include Send', 'option label (short)', 'wpsso-ssb' ), 'short', '', 
				'The Send button is only available in combination with the XFBML <em>Markup Language</em>.' ).
			'<td>'.$form->get_checkbox( 'fb_send' ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Layout', 'option label (short)', 'wpsso-ssb' ), 'short', '',
				'The Standard layout displays social text to the right of the button and friends\' profile photos below (if <em>Show Faces</em> is also checked). The Button Count layout displays the total number of likes to the right of the button, and the Box Count layout displays the total number of likes above the button. See the <a href="https://developers.facebook.com/docs/plugins/like-button#faqlayout">Facebook Layout Settings FAQ</a> for details.' ).
			'<td>'.$form->get_select( 'fb_layout', array(
				'standard'     => 'Standard',
				'button'       => 'Button',
				'button_count' => 'Button Count',
				'box_count'    => 'Box Count',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Faces', 'option label (short)', 'wpsso-ssb' ), 'short', '',
				'Show profile photos below the Standard button (Standard <em>Button Layout</em> only).' ).
			'<td>'.$form->get_checkbox( 'fb_show_faces' ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Font', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_font', array( 
				'arial'         => 'Arial',
				'lucida grande' => 'Lucida Grande',
				'segoe ui'      => 'Segoe UI',
				'tahoma'        => 'Tahoma',
				'trebuchet ms'  => 'Trebuchet MS',
				'verdana'       => 'Verdana',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Color Scheme', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_colorscheme', array(
				'light' => 'Light',
				'dark'  => 'Dark',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Action Name', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_action', array(
				'like'      => 'Like',
				'recommend' => 'Recommend',
			) ).'</td>';

			return $table_rows;
		}

		public function filter_ssb_share_facebook_share_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Markup Language', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_share_markup', array(
				'html5' => 'HTML5',
				'xfbml' => 'XFBML',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Layout', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_share_layout', array(
				'button'       => 'Button',
				'button_count' => 'Button Count',
				'box_count'    => 'Box Count',
				'icon_link'    => 'Icon Link',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Size', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'fb_share_size', array(
				'small' => 'Small',
				'large' => 'Large',
			) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Mobile iFrame', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_checkbox( 'fb_share_mobile_iframe' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbShareFacebook' ) ) {

	class WpssoSsbShareFacebook {

		private $p;
		private $sdk_version = 'v2.6';
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'fb_order'               => 4,
					'fb_on_content'          => 1,
					'fb_on_excerpt'          => 0,
					'fb_on_sidebar'          => 0,
					'fb_on_admin_edit'       => 1,
					'fb_platform'            => 'any',
					'fb_script_loc'          => 'header',
					'fb_lang'                => 'en_US',
					'fb_button'              => 'like',
					'fb_markup'              => 'xfbml',
					'fb_send'                => 1,
					'fb_layout'              => 'button_count',
					'fb_show_faces'          => 0,
					'fb_font'                => 'arial',
					'fb_colorscheme'         => 'light',
					'fb_action'              => 'like',
					'fb_share_markup'        => 'xfbml',
					'fb_share_layout'        => 'button_count',
					'fb_share_size'          => 'small',
					'fb_share_mobile_iframe' => 1,
				),
			),
		);

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array(
				'get_defaults' => 1,
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$atts['send'] = $opts['fb_send'] ? 'true' : 'false';
			$atts['show_faces'] = $opts['fb_show_faces'] ? 'true' : 'false';

			$html = '';

			switch ( $opts['fb_button'] ) {

				case 'like':

					switch ( $opts['fb_markup'] ) {

						case 'xfbml':

							$html .= '<!-- Facebook Like / Send Button (XFBML) -->'.
							'<div '.SucomUtil::get_atts_css_attr( $atts, 'facebook', 'fb-like' ).'>'.
							'<fb:like href="'.$atts[ 'url' ].'" send="'.$atts['send'].'" '.
							'layout="'.$opts['fb_layout'].'" show_faces="'.$atts['show_faces'].'" '.
							'font="'.$opts['fb_font'].'" colorscheme="'.$opts['fb_colorscheme'].'" '.
							'action="'.$opts['fb_action'].'"></fb:like></div>';

							break;

						case 'html5':

							$html .= '<!-- Facebook Like / Send Button (HTML5) -->'.
							'<div '.SucomUtil::get_atts_css_attr( $atts, 'facebook', 'fb-like' ) . ' ' . 
							'data-href="'.$atts[ 'url' ].'" data-send="'.$atts['send'].'" '.
							'data-layout="'.$opts['fb_layout'].'" data-show-faces="'.$atts['show_faces'].'" '.
							'data-font="'.$opts['fb_font'].'" data-colorscheme="'.$opts['fb_colorscheme'].'" '.
							'data-action="'.$opts['fb_action'].'"></div>';

							break;
					}

					break;

				case 'share':

					switch ( $opts['fb_markup'] ) {

						case 'xfbml':

							$html .= '<!-- Facebook Share Button (XFBML) -->'.
							'<div '.SucomUtil::get_atts_css_attr( $atts, 'fb-share', 'fb-share' ).'>'.
							'<fb:share-button href="'.$atts[ 'url' ].'" layout="'.$opts['fb_share_layout'].'" '.
							'mobile_iframe="'.( empty( $opts['fb_share_mobile_iframe'] ) ? 'false' : 'true' ).'" '.
							'size="'.$opts['fb_share_size'].'"></fb:share-button></div>';

							break;

						case 'html5':

							$html .= '<!-- Facebook Share Button (HTML5) -->'.
							'<div '.SucomUtil::get_atts_css_attr( $atts, 'fb-share', 'fb-share' ) . ' ' . 
							'data-href="'.$atts[ 'url' ].'" data-layout="'.$opts['fb_share_layout'].'" '.
							'data-mobile_iframe="'.( empty( $opts['fb_share_mobile_iframe'] ) ? 'false' : 'true' ).'" '.
							'data-size="'.$opts['fb_share_size'].'"></div>';

							break;
					}

					break;
			}

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			}

			return $html;
		}

		public function get_script( $pos = 'id' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$fb_app_id = empty( $this->p->options[ 'fb_app_id' ] ) ? '' : $this->p->options['fb_app_id'];
			$fb_lang   = empty( $this->p->options[ 'fb_lang' ] ) ? 'en_US' : $this->p->options['fb_lang'];
			$fb_lang   = apply_filters( $this->p->lca . '_pub_lang', $fb_lang, 'facebook', 'current' );

			/**
			 * Do not use WpssoSsbSocial::get_file_cache_url() since the facebook javascript does not work when hosted locally.
			 */
			$js_url = apply_filters( $this->p->lca.'_js_url_facebook', 
				SucomUtil::get_prot().'://connect.facebook.net/'.$fb_lang.'/sdk.js#xfbml=1&version='.
					$this->sdk_version.'&appId='.$fb_app_id, $pos );

			$html = '<script type="text/javascript" id="fb-script-'.$pos.'">'.
				$this->p->lca.'_insert_js( "fb-script-'.$pos.'", "'.$js_url.'" );</script>';

			return $html;
		}
	}
}
