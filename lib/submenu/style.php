<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuStyle' ) && class_exists( 'WpssoAdmin' ) ) {

	class WpssoSsbSubmenuStyle extends WpssoAdmin {

		public function __construct( &$plugin, $id, $name, $lib ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
			$this->menu_lib = $lib;
			$this->p->util->add_plugin_filters( $this, array( 
				'messages_tooltip' => 2,	// tooltip messages filter
				'messages_info' => 2,		// info messages filter
			) );
		}

		public function filter_messages_tooltip( $text, $idx ) {
			if ( strpos( $idx, 'tooltip-buttons_' ) !== 0 )
				return $text;

			switch ( $idx ) {
				case 'tooltip-buttons_use_social_css':
					$text = sprintf( __( 'Add the CSS of all <em>%1$s</em> to webpages (default is checked). The CSS will be <strong>minimized</strong>, and saved to a single stylesheet with a URL of <a href="%2$s">%3$s</a>. The minimized stylesheet can be enqueued or added directly to the webpage HTML.', 'wpsso-ssb' ), _x( 'Sharing Styles', 'lib file description', 'wpsso-ssb' ), WpssoSsbSharing::$sharing_css_url, WpssoSsbSharing::$sharing_css_url );
					break;
	
				case 'tooltip-buttons_js_ssb-sidebar':
					$text = __( 'JavaScript added to webpages for the social sharing sidebar.' );
					break;

				case 'tooltip-buttons_enqueue_social_css':
					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );
					break;
			}
			return $text;
		}

		public function filter_messages_info( $text, $idx ) {
			if ( strpos( $idx, 'info-style-ssb-' ) !== 0 )
				return $text;
			$short = $this->p->cf['plugin']['wpsso']['short'];
			switch ( $idx ) {

				case 'info-style-ssb-sharing':
					$text = '<p>'.$short.' uses the \'wpsso-ssb\' and \'ssb-buttons\' classes to wrap all its sharing buttons, and each button has it\'s own individual class name as well. This tab can be used to edit the CSS common to all sharing button locations.</p>';
					break;

				case 'info-style-ssb-content':
					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.$this->p->util->get_admin_url( 'sharing', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-ssb-content\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'content', true );
					break;

				case 'info-style-ssb-excerpt':
					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'sharing', 'Sharing Buttons' ).' settings page, are assigned the \'wpsso-ssb-excerpt\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'excerpt', true );
					break;

				case 'info-style-ssb-sidebar':
					$text = '<p>Social sharing buttons added to the sidebar are assigned the \'#wpsso-ssb-sidebar-container\' CSS id, which itself contains \'#wpsso-ssb-sidebar-header\', \'#wpsso-ssb-sidebar\' and the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>
					<p>Example:</p><pre>
#wpsso-ssb-sidebar-container
    #wpsso-ssb-sidebar-header {}

#wpsso-ssb-sidebar-container
    #wpsso-ssb-sidebar
        .ssb-buttons
	    .facebook-button {}</pre>';
					break;

				case 'info-style-ssb-shortcode':
					$text = '<p>Social sharing buttons added from a shortcode are assigned the \'wpsso-ssb-shortcode\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'shortcode', true );
					break;

				case 'info-style-ssb-widget':
					$text = '<p>Social sharing buttons within the '.$this->p->cf['menu'].' Sharing Buttons widget are assigned the \'wpsso-ssb-widget\' class, which itself contains the \'ssb-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 
					<p>Example:</p><pre>
.wpsso-ssb-widget
    .ssb-buttons
        .facebook-button { }</pre>
					<p>The '.$this->p->cf['menu'].' Sharing Buttons widget also has an id of \'wpsso-ssb-widget-<em>#</em>\', and the buttons have an id of \'<em>name</em>-wpsso-ssb-widget-<em>#</em>\'.</p>
					<p>Example:</p><pre>
#wpsso-ssb-widget-buttons-2
    .ssb-buttons
        #facebook-wpsso-widget-buttons-2 { }</pre>';
					break;

				case 'info-style-ssb-admin_edit':
					$text = '<p>Social sharing buttons within the Admin Post / Page Edit metabox are assigned the \'wpsso-ssb-admin_edit\' class, which itself contains the \'sso-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'admin_edit', true );
					break;

				case 'info-style-ssb-woo_short':
					$text = '<p>Social sharing buttons added to the WooCommerce Short Description are assigned the \'wpsso-ssb-woo_short\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'woo_short', true );
					break;

				case 'info-style-ssb-bbp_single':
					$text = '<p>Social sharing buttons added at the top of bbPress Single templates are assigned the \'wpsso-ssb-bbp_single\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'bbp_single' );
					break;

				case 'info-style-ssb-bp_activity':
					$text = '<p>Social sharing buttons added on BuddyPress Activities are assigned the \'wpsso-ssb-bp_activity\' class, which itself contains the \'ssb-buttons\' class -- a common class for all buttons (see the All Buttons tab).</p>'.
					$this->get_css_example( 'bp_activity' );
					break;

			}
			return $text;
		}

		protected function get_css_example( $type, $preset = false ) {
			$text = '<p>Example:</p><pre>
.wpsso-ssb .wpsso-ssb-'.$type.'
    .ssb-buttons 
        .facebook-button {}</pre>';
			if ( $preset ) {
				$text .= '<p>The '.$this->p->cf['sharing']['style']['ssb-'.$type].' social sharing buttons are subject to preset values selected on the '.$this->p->util->get_admin_url( 'sharing#sucom-tabset_sharing-tab_preset', 'Sharing Buttons' ).' settings page.</p>
					<p><strong>Selected preset:</strong> '.
						( empty( $this->p->options['buttons_preset_ssb-'.$type] ) ? '[none]' :
							$this->p->options['buttons_preset_ssb-'.$type] ).'</p>';
			}
			return $text;
		}

		protected function add_meta_boxes() {
			// add_meta_box( $id, $title, $callback, $post_type, $context, $priority, $callback_args );
			add_meta_box( $this->pagehook.'_style',
				_x( 'Social Sharing Styles', 'metabox title', 'wpsso-ssb' ),
					array( &$this, 'show_metabox_style' ), $this->pagehook, 'normal' );
		}

		public function show_metabox_style() {
			$metabox = 'style';

			if ( file_exists( WpssoSsbSharing::$sharing_css_file ) &&
				( $fsize = filesize( WpssoSsbSharing::$sharing_css_file ) ) !== false )
					$css_min_msg = ' <a href="'.WpssoSsbSharing::$sharing_css_url.'">minimized css is '.$fsize.' bytes</a>';
			else $css_min_msg = '';

			$this->p->util->do_table_rows( array( 
				$this->p->util->get_th( _x( 'Use the Social Stylesheet',
					'option label', 'wpsso-ssb' ), 'highlight', 'buttons_use_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',

				$this->p->util->get_th( _x( 'Enqueue the Stylesheet',
					'option label', 'wpsso-ssb' ), null, 'buttons_enqueue_social_css' ).
				'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
			) );

			$tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', 
				$this->p->cf['sharing']['style'] );
			$rows = array();
			foreach ( $tabs as $key => $title ) {
				$tabs[$key] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// translate the tab title
				$rows[$key] = array_merge( $this->get_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			}
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {

			$rows['buttons_css_'.$key] = '<th class="textinfo">'.$this->p->msgs->get( 'info-style-'.$key ).'</th>'.
			'<td'.( isset( $this->p->options['buttons_css_'.$key.':is'] ) &&
				$this->p->options['buttons_css_'.$key.':is'] === 'disabled' ? ' class="blank"' : '' ).'>'.
			$this->form->get_textarea( 'buttons_css_'.$key, 'tall code' ).'</td>';

			switch ( $key ) {
				case 'ssb-sidebar':
					$rows[] = '<tr class="hide_in_basic">'.
					$this->p->util->get_th( _x( 'Sidebar Javascript',
						'option label', 'wpsso-ssb' ), null, 'buttons_js_ssb-sidebar' ).
					'<td>'.$this->form->get_textarea( 'buttons_js_ssb-sidebar', 'average code' ).'</td>';
					break;
			}

			return $rows;
		}
	}
}

?>
