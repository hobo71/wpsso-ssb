<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuSsbStyles' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoSsbSubmenuSsbStyles extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib, $ext ) {
			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->menu_ext = $ext;	// lowercase acronyn for plugin or extension
		}

		protected function add_plugin_hooks() {
			$this->p->util->add_plugin_filters( $this, array(
				'action_buttons' => 1,
			) );
		}

		// called by the extended WpssoAdmin class
		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_styles',
				_x( 'Social Sharing Styles', 'metabox title', 'wpsso-ssb' ),
					array( &$this, 'show_metabox_styles' ), $this->pagehook, 'normal' );
		}

		public function filter_action_buttons( $action_buttons ) {
			$action_buttons[0]['reload_default_sharing_ssb_styles'] = _x( 'Reload Default Styles',
				'submit button', 'wpsso-ssb' );
			return $action_buttons;
		}

		public function show_metabox_styles() {
			$metabox_id = 'styles';

			if ( file_exists( WpssoSsbSharing::$sharing_css_file ) &&
				( $fsize = filesize( WpssoSsbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' <a href="'.WpssoSsbSharing::$sharing_css_url.'">minimized css is '.$fsize.' bytes</a>';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->form->get_th_html( _x( 'Use the Social Stylesheet',
					'option label', 'wpsso-ssb' ), 'highlight', 'buttons_use_social_style' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_style' ).$css_min_msg.'</td>',

				$this->form->get_th_html( _x( 'Enqueue the Stylesheet',
					'option label', 'wpsso-ssb' ), null, 'buttons_enqueue_social_style' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_style' ).'</td>',
			) );

			$table_rows = array();
			$tabs = apply_filters( $this->p->cf['lca'].'_ssb_styles_tabs', $this->p->cf['sharing']['ssb_styles'] );

			foreach ( $tabs as $key => $title ) {
				$tabs[$key] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// translate the tab title
				$table_rows[$key] = array_merge( $this->get_table_rows( $metabox_id, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox_id.'_'.$key.'_rows', array(), $this->form ) );
			}
			$this->p->util->do_metabox_tabs( $metabox_id, $tabs, $table_rows );
		}

		protected function get_table_rows( $metabox_id, $key ) {

			$table_rows['buttons_css_'.$key] = '<th class="textinfo">'.$this->p->msgs->get( 'info-styles-'.$key ).'</th>'.
			'<td'.( isset( $this->p->options['buttons_css_'.$key.':is'] ) &&
				$this->p->options['buttons_css_'.$key.':is'] === 'disabled' ? ' class="blank"' : '' ).'>'.
			$this->form->get_textarea( 'buttons_css_'.$key, 'tall code' ).'</td>';

			switch ( $key ) {
				case 'ssb-sidebar':
					$table_rows[] = $this->form->get_tr_hide( 'basic', 'buttons_js_ssb-sidebar' ).
					$this->form->get_th_html( _x( 'Sidebar Javascript', 'option label', 'wpsso-ssb' ), '', 'buttons_js_ssb-sidebar' ).
					'<td>'.$this->form->get_textarea( 'buttons_js_ssb-sidebar', 'average code' ).'</td>';
					break;
			}

			return $table_rows;
		}
	}
}

