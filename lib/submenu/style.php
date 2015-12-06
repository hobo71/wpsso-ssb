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

		public function __construct( &$plugin, $id, $name ) {
			$this->p =& $plugin;
			$this->menu_id = $id;
			$this->menu_name = $name;
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
	
				case 'tooltip-buttons_js_sidebar':
					$text = __( 'JavaScript that is added to webpages for the social sharing sidebar.' );
					break;

				case 'tooltip-buttons_enqueue_social_css':
					$text = __( 'Have WordPress enqueue the social stylesheet instead of adding the CSS to in the webpage HTML (default is unchecked). Enqueueing the stylesheet may be desirable if you use a plugin to concatenate all enqueued styles into a single stylesheet URL.', 'wpsso-rrssb' );
					break;
			}
			return $text;
		}

		public function filter_messages_info( $text, $idx ) {
			if ( strpos( $idx, 'info-style-' ) !== 0 )
				return $text;

			$lca =  $this->p->cf['lca'];
			$short = $this->p->cf['plugin'][$lca]['short'];

			switch ( $idx ) {

				case 'info-style-sharing':

					$notes_url = $this->p->cf['plugin'][$lca]['url']['notes'];
					$text = '<p>'.$short.' uses the \''.$lca.'-buttons\' class to wrap all its sharing buttons, and each button has it\'s own individual class name as well. Refer to the <a href="'.$notes_url.'" target="_blank">Notes</a> webpage for additional stylesheet information, including how to hide the sharing buttons for specific Posts, Pages, categories, tags, etc.</p>';
					break;

				case 'info-style-content':

					$text = '<p>Social sharing buttons, enabled / added to the content text from the '.$this->p->util->get_admin_url( 'sharing', 'Buttons settings page' ).', are assigned the \''.$lca.'-content-buttons\' class, which itself contains the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 
					
					<p>Example:</p><pre>
.'.$lca.'-content-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;

				case 'info-style-excerpt':
					$text = '<p>Social sharing buttons, enabled / added to the excerpt text from the '.$this->p->util->get_admin_url( 'sharing', 'Buttons settings page' ).', are assigned the \''.$lca.'-excerpt-buttons\' class, which itself contains the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 

					<p>Example:</p><pre>
.'.$lca.'-excerpt-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;

				case 'info-style-sidebar':
					$text = '<p>Social sharing buttons added to the sidebar are assigned the \'#'.$lca.'-sidebar\' CSS id, which itself contains \'#'.$lca.'-sidebar-header\', \'#'.$lca.'-sidebar-buttons\', and the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p>
					<p>Example:</p><pre>
#'.$lca.'-sidebar
    #'.$lca.'-sidebar-header { }

#'.$lca.'-sidebar
    #'.$lca.'-sidebar-buttons
        .'.$lca.'-buttons
	    .facebook-button { }</pre>';
					break;

				case 'info-style-shortcode':
					$text = '<p>Social sharing buttons added from a shortcode are assigned the \''.$lca.'-shortcode-buttons\' class, which itself contains the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 
					
					<p>Example:</p><pre>
.'.$lca.'-shortcode-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;

				case 'info-style-widget':
					$text = '<p>Social sharing buttons within the '.$this->p->cf['menu'].' Sharing Buttons widget are assigned the \''.$lca.'-widget-buttons\' class, which itself contains the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 
					
					<p>Example:</p><pre>
.'.$lca.'-widget-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>
					<p>The '.$this->p->cf['menu'].' Sharing Buttons widget also has an id of \''.$lca.'-widget-buttons-<em>#</em>\', and the buttons have an id of \'<em>name</em>-'.$lca.'-widget-buttons-<em>#</em>\'.</p>

					<p>Example:</p><pre>
#'.$lca.'-widget-buttons-2
    .'.$lca.'-buttons
        #facebook-'.$lca.'-widget-buttons-2 { }</pre>';
					break;

				case 'info-style-admin_edit':
					$text = '<p>Social sharing buttons within the Admin Post / Page Edit metabox are assigned the \''.$lca.'-admin_edit-buttons\' class, which itself contains the \''.$lca.'-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 

					<p>Example:</p><pre>
.'.$lca.'-admin_edit-buttons 
    .'.$lca.'-buttons
        .facebook-button { }</pre>';
					break;
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

			$this->p->util->do_table_rows( 
				array( 
					$this->p->util->get_th( _x( 'Use the Social Stylesheet',
						'option label', 'wpsso-ssb' ), 'highlight', 'buttons_use_social_css' ).
					'<td>'.$this->form->get_checkbox( 'buttons_use_social_css' ).$css_min_msg.'</td>',
	
					$this->p->util->get_th( _x( 'Enqueue the Stylesheet',
						'option label', 'wpsso-ssb' ), null, 'buttons_enqueue_social_css' ).
					'<td>'.$this->form->get_checkbox( 'buttons_enqueue_social_css' ).'</td>',
				)
			);

			$tabs = apply_filters( $this->p->cf['lca'].'_style_tabs', 
				WpssoSsbSharing::$cf['sharing']['style'] );
			$rows = array();
			foreach ( $tabs as $key => $title ) {
				$tabs[$key] = _x( $title, 'metabox tab', 'wpsso-ssb' );	// translate the tab title
				$rows[$key] = array_merge( $this->get_rows( $metabox, $key ), 
					apply_filters( $this->p->cf['lca'].'_'.$metabox.'_'.$key.'_rows', array(), $this->form ) );
			}
			$this->p->util->do_tabs( $metabox, $tabs, $rows );
		}

		protected function get_rows( $metabox, $key ) {
			return array();
		}
	}
}

?>
