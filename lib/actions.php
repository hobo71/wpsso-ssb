<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbActions' ) ) {

	class WpssoSsbActions {

		protected $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_actions( $this, array( 
				'pre_apply_filters_text'   => 1,
				'after_apply_filters_text' => 1,
			) );

			if ( is_admin() ) {

				$this->p->util->add_plugin_actions( $this, array( 
					'load_setting_page_reload_default_ssb_styles' => 4,
				) );
			}
		}

		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$ssb =& WpssoSsb::get_instance();

			$ssb->social->remove_buttons_filter( $filter_name );
		}

		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$ssb =& WpssoSsb::get_instance();

			$ssb->social->add_buttons_filter( $filter_name );
		}

		public function action_load_setting_page_reload_default_ssb_styles( $pagehook, $menu_id, $menu_name, $menu_lib ) {

			$def_opts = $this->p->opt->get_defaults();

			$styles = apply_filters( $this->p->lca . '_ssb_styles', $this->p->cf['sharing']['ssb_styles'] );

			foreach ( $styles as $id => $name ) {
				if ( isset( $this->p->options[ 'buttons_css_' . $id ] ) && isset( $def_opts[ 'buttons_css_' . $id ] ) ) {
					$this->p->options[ 'buttons_css_' . $id ] = $def_opts[ 'buttons_css_' . $id ];
				}
			}

			WpssoSsbSocial::update_sharing_css( $this->p->options );

			$this->p->opt->save_options( WPSSO_OPTIONS_NAME, $this->p->options, $network = false );

			$this->p->notice->upd( __( 'All sharing styles have been reloaded with their default value and saved.', 'wpsso-ssb' ) );
		}
	}
}
