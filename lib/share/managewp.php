<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuShareManagewp' ) ) {

	class WpssoSsbSubmenuShareManagewp {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_share_managewp_rows' => 3,
			) );
		}

		public function filter_ssb_share_managewp_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>' . $submenu->show_on_checkboxes( 'managewp' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Preferred Order', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'managewp_order', range( 1, count( $submenu->share ) ) ).'</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {
				$table_rows[] = $form->get_tr_hide( 'basic', 'managewp_platform' ).
				$form->get_th_html( _x( 'Allow for Platform', 'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'managewp_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Type', 'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'managewp_type', array(
				'small' => 'Small',
				'big'   => 'Big',
			) ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbShareManagewp' ) ) {

	class WpssoSsbShareManagewp {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'managewp_order'         => 10,
					'managewp_on_content'    => 0,
					'managewp_on_excerpt'    => 0,
					'managewp_on_sidebar'    => 0,
					'managewp_on_admin_edit' => 0,
					'managewp_platform'      => 'any',
					'managewp_type'          => 'small',
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
				$atts['title'] = $this->p->page->get_title( null, null, $mod, true, false, true, null );
			}

			$js_url = WpssoSsbSocial::get_file_cache_url( apply_filters( $this->p->lca.'_js_url_managewp', 
				SucomUtil::get_prot().'://managewp.org/share.js#'.SucomUtil::get_prot().'://managewp.org/share', '' ) );

			$html = '<!-- ManageWP Button -->'.
				'<div '.SucomUtil::get_atts_css_attr( $atts, 'managewp' ).'>'.
				'<script type="text/javascript" src="'.$js_url.'" data-url="'.$atts[ 'url' ].'" data-title="'.$atts['title'].'"'.
				( empty( $opts['managewp_type'] ) ? '' : ' data-type="'.$opts['managewp_type'].'"' ).'></script></div>';

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			}

			return $html;
		}
	}
}
