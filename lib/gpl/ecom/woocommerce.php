<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbGplEcomWoocommerce' ) ) {

	class WpssoSsbGplEcomWoocommerce {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! empty( $this->p->avail['p_ext']['ssb'] ) ) {
				$classname = __CLASS__.'Sharing';
				if ( class_exists( $classname ) ) {
					$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoSsbGplEcomWoocommerceSharing' ) ) {

	class WpssoSsbGplEcomWoocommerceSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );

			if ( is_admin() && empty( $this->p->options['plugin_hide_pro'] ) ) {
				$this->p->util->add_plugin_filters( $this, array( 
					'ssb_buttons_show_on'       => 2,
					'ssb_styles_tabs'           => 1,
					'ssb_buttons_position_rows' => 2,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre.'_on_woo_short'] = 0;
			}
			$opts_def['buttons_pos_woo_short'] = 'bottom';
			$opts_def['buttons_preset_ssb-woo_short'] = '';

			return $opts_def;
		}

		public function filter_ssb_buttons_show_on( $show_on = array(), $opt_pre ) {
			$show_on['woo_short'] = 'Woo Short';
			$this->p->options[$opt_pre.'_on_woo_short:is'] = 'disabled';
			return $show_on;
		}

		public function filter_ssb_styles_tabs( $tabs ) {
			$tabs['ssb-woo_short'] = 'Woo Short';
			$this->p->options['buttons_css_ssb-woo_short:is'] = 'disabled';
			return $tabs;
		}

		public function filter_ssb_buttons_position_rows( $table_rows, $form ) {

			$table_rows[] = '<td colspan="2">' . $this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ) . '</td>';

			$table_rows['buttons_pos_woo_short'] = $form->get_th_html( _x( 'Position in Woo Short Text',
				'option label', 'wpsso-ssb' ), null, 'buttons_pos_woo_short' ).
			'<td class="blank">'.$this->p->cf['sharing']['position'][$this->p->options['buttons_pos_woo_short']].'</td>';

			return $table_rows;
		}
	}
}

