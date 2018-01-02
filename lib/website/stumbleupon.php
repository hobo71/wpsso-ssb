<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteStumbleupon' ) ) {

	class WpssoSsbSubmenuWebsiteStumbleupon {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_website_stumbleupon_rows' => 3,
			) );
		}

		public function filter_ssb_website_stumbleupon_rows( $table_rows, $form, $submenu ) {

			$badge_html = '
				<style type="text/css">
					.badge { 
						display:block;
						background: url("'.
							$this->p->ssb_sharing->get_social_file_cache_url( SucomUtil::get_prot().
								'://b9.sustatic.com/7ca234_0mUVfxHFR0NAk1g', '.png' ).'") no-repeat transparent; 
						min-width:100px;
						margin:5px 0 5px 0;
					}
					.badge-col-left {
						float:left;
						display:inline-block;
						margin-right:20px;
					}
					.badge-col-right {
						display:inline-block;
					}
					#badge-1 { height:20px; background-position:25px 0px; }
					#badge-2 { height:20px; background-position:25px -100px; }
					#badge-3 { height:20px; background-position:25px -200px; }
					#badge-4 { height:60px; background-position:25px -300px; }
					#badge-5 { height:30px; background-position:25px -400px; }
					#badge-6 { height:20px; background-position:25px -500px; }
				</style>
			';

			$badge_html .= '<div class="badge-col-left">';
			$badge_number = empty( $this->p->options['stumble_badge'] ) ? 1 : $this->p->options['stumble_badge'];
			foreach ( array( 1, 2, 3, 6 ) as $i ) {
				$badge_html .= '<div class="badge" id="badge-'.$i.'">';
				$badge_html .= '<input type="radio" name="'.$form->get_options_name().'[stumble_badge]" 
					value="'.$i.'" '.checked( $i, $badge_number, false ).'/>';
				$badge_html .= '</div>';
			}
			$badge_html .= '</div><div class="badge-col-right">';
			foreach ( array( 4, 5 ) as $i ) {
				$badge_html .= '<div class="badge" id="badge-'.$i.'">';
				$badge_html .= '<input type="radio" name="'.$form->get_options_name().'[stumble_badge]" 
					value="'.$i.'" '.checked( $i, $badge_number, false ).'/>';
				$badge_html .= '</div>';
			}
			$badge_html .= '</div>';

			$table_rows[] = '<td colspan="2"><strong><center>'.
				__( 'Please note that StumbleUpon does not currently provide<br/>an HTTPS compatible share button.',
					'wpsso-ssb' ).'</center></strong></td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$submenu->show_on_checkboxes( 'stumble' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'stumble_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( $this->p->avail['*']['vary_ua'] ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform',
					'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'stumble_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'stumble_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Style',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$badge_html.'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteStumbleupon' ) ) {

	class WpssoSsbWebsiteStumbleupon {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'stumble_order' => 11,
					'stumble_on_content' => 0,
					'stumble_on_excerpt' => 0,
					'stumble_on_sidebar' => 0,
					'stumble_on_admin_edit' => 0,
					'stumble_platform' => 'any',
					'stumble_script_loc' => 'footer',	// header or footer
					'stumble_badge' => 1,
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
			) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$html = '<!-- StumbleUpon Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, 'stumbleupon', 'stumble-button' ).'>'.
			'<su:badge layout="'.$opts['stumble_badge'].'" location="'.$atts['url'].'"></su:badge></div>';

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			}

			return $html;
		}

		public function get_script( $pos = 'id' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_stumbleupon',
				SucomUtil::get_prot().'://platform.stumbleupon.com/1/widgets.js', $pos ) );

			return '<script type="text/javascript" id="stumbleupon-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "stumbleupon-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

