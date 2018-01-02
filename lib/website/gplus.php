<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteGplus' ) ) {

	class WpssoSsbSubmenuWebsiteGplus {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_website_gplus_rows' => 3,
			) );
		}

		public function filter_ssb_website_gplus_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$submenu->show_on_checkboxes( 'gp' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( $this->p->avail['*']['vary_ua'] ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform',
					'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'gp_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Language',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_lang', SucomUtil::get_pub_lang( 'gplus' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Type',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_action', array( 'plusone' => 'G +1', 'share' => 'G+ Share' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Size',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_size', array( 
				'small' => 'Small [ 15px ]',
				'medium' => 'Medium [ 20px ]',
				'standard' => 'Standard [ 24px ]',
				'tall' => 'Tall [ 60px ]',
			) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Annotation',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_annotation', array( 
				'none' => 'none',
				'inline' => 'Inline',
				'bubble' => 'Bubble',
				'vertical-bubble' => 'Vertical Bubble',
			) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Expand to',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'gp_expandto', array( 
				'none' => 'none',
				'top' => 'Top',
				'bottom' => 'Bottom',
				'left' => 'Left',
				'right' => 'Right',
				'top,left' => 'Top Left',
				'top,right' => 'Top Right',
				'bottom,left' => 'Bottom Left',
				'bottom,right' => 'Bottom Right',
			) ).'</td>';
	
			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteGplus' ) ) {

	class WpssoSsbWebsiteGplus {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'gp_order' => 5,
					'gp_on_content' => 1,
					'gp_on_excerpt' => 0,
					'gp_on_sidebar' => 0,
					'gp_on_admin_edit' => 1,
					'gp_platform' => 'any',
					'gp_script_loc' => 'header',
					'gp_lang' => 'en-US',
					'gp_action' => 'plusone',
					'gp_size' => 'medium',
					'gp_annotation' => 'bubble',
					'gp_expandto' => 'none',
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
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$html = '<!-- GooglePlus Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, ( $opts['gp_action'] == 'share' ? 'gplus' : 'gplusone' ) ).'>'.
			'<span '.( $opts['gp_action'] == 'share' ? 'class="g-plus" data-action="share"' : 'class="g-plusone"' ).
			' data-size="'.$opts['gp_size'].'" data-annotation="'.$opts['gp_annotation'].'" data-href="'.$atts['url'].'"'.
			( empty( $opts['gp_expandto'] ) || $opts['gp_expandto'] == 'none' ? '' : ' data-expandTo="'.$opts['gp_expandto'].'"' ).'>'.
			'</span></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );

			return $html;
		}
		
		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_gplus',
				SucomUtil::get_prot().'://apis.google.com/js/plusone.js', $pos ) );

			return '<script type="text/javascript" id="gplus-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "gplus-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

