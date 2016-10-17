<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteManagewp' ) ) {

	class WpssoSsbSubmenuWebsiteManagewp {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_website_managewp_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		public function filter_ssb_website_managewp_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'managewp_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'managewp' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'managewp_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Type',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'managewp_type', 
				array( 
					'small' => 'Small',
					'big' => 'Big',
				)
			).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteManagewp' ) ) {

	class WpssoSsbWebsiteManagewp {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'managewp_order' => 10,
					'managewp_on_content' => 0,
					'managewp_on_excerpt' => 0,
					'managewp_on_sidebar' => 0,
					'managewp_on_admin_edit' => 1,
					'managewp_platform' => 'any',
					'managewp_type' => 'small',
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $atts['title'] ) )
				$atts['title'] = $this->p->webpage->get_title( null, null, $mod, true, false, true, null );

			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_managewp', 
				SucomUtil::get_prot().'://managewp.org/share.js#'.SucomUtil::get_prot().'://managewp.org/share', '' ) );

			$html = '<!-- ManageWP Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, 'managewp' ).'>'.
			'<script type="text/javascript" src="'.$js_url.'" data-url="'.$atts['url'].'" data-title="'.$atts['title'].'"'.
			( empty( $opts['managewp_type'] ) ? '' : ' data-type="'.$opts['managewp_type'].'"' ).'></script></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );

			return $html;
		}
	}
}

?>
