<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuShareLinkedin' ) ) {

	class WpssoSsbSubmenuShareLinkedin {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_share_linkedin_rows' => 3,
			) );
		}

		public function filter_ssb_share_linkedin_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $submenu->show_on_checkboxes( 'linkedin' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Preferred Order', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'linkedin_order', range( 1, count( $submenu->share ) ) ) . '</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {
				$table_rows[] = $form->get_tr_hide( 'basic', 'linkedin_platform' ) . 
				$form->get_th_html( _x( 'Allow for Platform', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
				'<td>' . $form->get_select( 'linkedin_platform', $this->p->cf['sharing']['platform'] ) . '</td>';
			}

			$table_rows[] = $form->get_tr_hide( 'basic', 'linkedin_script_loc' ) . 
			$form->get_th_html( _x( 'JavaScript in', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'linkedin_script_loc', $this->p->cf['form']['script_locations'] ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbShareLinkedin' ) ) {

	class WpssoSsbShareLinkedin {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'linkedin_order'         => 7,
					'linkedin_on_content'    => 0,
					'linkedin_on_excerpt'    => 0,
					'linkedin_on_sidebar'    => 0,
					'linkedin_on_admin_edit' => 1,
					'linkedin_platform'      => 'any',
					'linkedin_script_loc'    => 'header',
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

			$html = '<!-- LinkedIn Button -->' . 
			'<div ' . SucomUtil::get_atts_css_attr( $atts, 'linkedin' ) . '>' . 
			'<script type="IN/Share" data-url="' . $atts[ 'url' ] . '">' .
			'</script></div>';

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			}

			return $html;
		}
		
		public function get_script( $pos = 'id' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$js_url = WpssoSsbSocial::get_file_cache_url( apply_filters( $this->p->lca . '_js_url_linkedin',
				SucomUtil::get_prot() . '://platform.linkedin.com/in.js', $pos ) );

			return  '<script type="text/javascript" id="linkedin-script-' . $pos . '">' . 
				$this->p->lca . '_insert_js( "linkedin-script-' . $pos . '", "' . $js_url . '" );</script>';
		}
	}
}
