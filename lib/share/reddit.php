<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuShareReddit' ) ) {

	class WpssoSsbSubmenuShareReddit {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_share_reddit_rows' => 3,
			) );
		}

		public function filter_ssb_share_reddit_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.( $submenu->show_on_checkboxes( 'reddit' ) ).'</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Preferred Order', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'reddit_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {
				$table_rows[] = $form->get_tr_hide( 'basic', 'reddit_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'reddit_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Type', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'reddit_type', array( 
				'static-wide'      => 'Interactive Wide',
				'static-tall-text' => 'Interactive Tall Text',
				'static-tall-logo' => 'Interactive Tall Logo',
			) ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbShareReddit' ) ) {

	class WpssoSsbShareReddit {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'reddit_order'         => 9,
					'reddit_on_content'    => 0,
					'reddit_on_excerpt'    => 0,
					'reddit_on_sidebar'    => 0,
					'reddit_on_admin_edit' => 1,
					'reddit_platform'      => 'any',
					'reddit_type'          => 'static-wide',
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

			if ( empty( $atts['title'] ) ) {
				$atts['title'] = $this->p->page->get_title( 0, '', $mod, true, false, false, null );	// $encode = false
			}

			switch ( $opts['reddit_type'] ) {
				case 'static-tall-text':
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button2.js';
					break;
				case 'static-tall-logo':
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button3.js';
					break;
				case 'static-wide':
				default:	// just in case
					$js_url = SucomUtil::get_prot().'://www.reddit.com/static/button/button1.js';
					break;
			}

			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->lca.'_js_url_reddit', $js_url, '' ) );

			$html = '<!-- Reddit Button -->'.
				'<script type="text/javascript">reddit_url="'.esc_url_raw( $atts['url'] ).'"; reddit_title="'.esc_attr( $atts['title'] ).'";</script>'.
				'<div '.SucomUtil::get_atts_css_attr( $atts, 'reddit' ).'>'.
				'<script type="text/javascript" src="'.$js_url.'"></script></div>';

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			}

			return $html;
		}
	}
}
