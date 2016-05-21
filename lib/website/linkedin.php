<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteLinkedin' ) ) {

	class WpssoSsbSubmenuWebsiteLinkedin {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_website_linkedin_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		public function filter_ssb_website_linkedin_rows( $table_rows, $form, $submenu ) {

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'linkedin_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'linkedin' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'linkedin_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'linkedin_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Counter Mode',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'linkedin_counter', 
				array( 
					'none' => '',
					'right' => 'Horizontal',
					'top' => 'Vertical',
				)
			).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Zero in Counter',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_checkbox( 'linkedin_showzero' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteLinkedin' ) ) {

	class WpssoSsbWebsiteLinkedin {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'linkedin_order' => 7,
					'linkedin_on_content' => 0,
					'linkedin_on_excerpt' => 0,
					'linkedin_on_sidebar' => 0,
					'linkedin_on_admin_edit' => 1,
					'linkedin_platform' => 'any',
					'linkedin_script_loc' => 'header',
					'linkedin_counter' => 'right',
					'linkedin_showzero' => 1,
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// get_sharing_url() argument

			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $atts['use_post'], $atts['add_page'] ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'], $atts['use_post'], $atts['add_page'] );

			$html = '<!-- LinkedIn Button -->'.
			'<div '.WpssoSsbSharing::get_css_class_id( 'linkedin', $atts ).'>'.
			'<script type="IN/Share" data-url="'.$atts['url'].'"'.
				( empty( $opts['linkedin_counter'] ) ? '' : ' data-counter="'.$opts['linkedin_counter'].'"' ).
				( empty( $opts['linkedin_showzero'] ) ? '' : ' data-showzero="true"' ).'>'.
			'</script></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );
			return $html;
		}
		
		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_linkedin',
				SucomUtil::get_prot().'://platform.linkedin.com/in.js', $pos ) );

			return  '<script type="text/javascript" id="linkedin-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "linkedin-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>