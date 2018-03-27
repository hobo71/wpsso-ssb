<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbGplForumBbpress' ) ) {

	class WpssoSsbGplForumBbpress {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( class_exists( 'bbpress' ) ) {
				if ( ! empty( $this->p->avail['p_ext']['ssb'] ) ) {
					$classname = __CLASS__.'Sharing';
					if ( class_exists( $classname ) ) {
						$this->sharing = new $classname( $this->p );
					}
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoSsbGplForumBbpressSharing' ) ) {

	class WpssoSsbGplForumBbpressSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );

			if ( is_admin() && empty( $this->p->options['plugin_hide_pro'] ) ) {
				$this->p->util->add_plugin_filters( $this, array( 
					'ssb_buttons_show_on' => 2,
					'ssb_styles_tabs' => 1,
					'ssb_buttons_position_rows' => 2,
				) );
			}
		}

		public function filter_get_defaults( $opts_def ) {
			foreach ( $this->p->cf['opt']['cm_prefix'] as $id => $opt_pre ) {
				$opts_def[$opt_pre.'_on_bbp_single'] = 0;
			}
			$opts_def['buttons_pos_bbp_single'] = 'top';

			return $opts_def;
		}

		public function filter_ssb_buttons_show_on( $show_on = array(), $opt_pre = '' ) {
			switch ( $opt_pre ) {
				case 'pin':
					break;
				default:
					$show_on['bbp_single'] = 'bbPress Single';
					$this->p->options[$opt_pre.'_on_bbp_single:is'] = 'disabled';
					break;
			}
			return $show_on;
		}

		public function filter_ssb_styles_tabs( $tabs ) {
			$tabs['ssb-bbp_single'] = 'bbPress Single';
			$this->p->options['buttons_css_ssb-bbp_single:is'] = 'disabled';
			return $tabs;
		}

		public function filter_ssb_buttons_position_rows( $table_rows, $form ) {
			$table_rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';
			$table_rows['buttons_pos_bbp_single'] = $form->get_th_html( _x( 'Position in bbPress Single',
				'option label', 'wpsso-ssb' ), null, 'buttons_pos_bbp_single' ).
			'<td class="blank">'.$this->p->cf['sharing']['position'][$this->p->options['buttons_pos_bbp_single']].'</td>';
			return $table_rows;
		}
	}
}

