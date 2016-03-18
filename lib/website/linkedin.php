<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuSharingLinkedin' ) && class_exists( 'WpssoSsbSubmenuSharing' ) ) {

	class WpssoSsbSubmenuSharingLinkedin extends WpssoSsbSubmenuSharing {

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->website_id = $id;
			$this->website_name = $name;

			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
		}

		protected function get_table_rows( $metabox, $key ) {
			$table_rows = array();

			$table_rows[] = $this->form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'linkedin_order', 
				range( 1, count( $this->p->admin->submenu['sharing']->website ) ), 'short' ).'</td>';

			$table_rows[] = $this->form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			( $this->show_on_checkboxes( 'linkedin' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$this->form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$this->form->get_select( 'linkedin_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$this->form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'linkedin_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $this->form->get_th_html( _x( 'Counter Mode',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_select( 'linkedin_counter', 
				array( 
					'none' => '',
					'right' => 'Horizontal',
					'top' => 'Vertical',
				)
			).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$this->form->get_th_html( _x( 'Zero in Counter',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$this->form->get_checkbox( 'linkedin_showzero' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbSharingLinkedin' ) ) {

	class WpssoSsbSharingLinkedin {

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
			$atts['source_id'] = isset( $atts['source_id'] ) ?
				$atts['source_id'] : $this->p->util->get_source_id( 'linkedin', $atts );
			$atts['url'] = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $atts['use_post'], $atts['add_page'], $atts['source_id'] ) : 
				apply_filters( $this->p->cf['lca'].'_sharing_url', $atts['url'],
					$atts['use_post'], $atts['add_page'], $atts['source_id'] );

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
