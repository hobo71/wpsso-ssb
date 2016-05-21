<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbShortcodeSharing' ) ) {

	class WpssoSsbShortcodeSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->debug->mark();
			if ( ! is_admin() ) {
				if ( $this->p->is_avail['ssb'] ) {
					$this->wpautop();
					$this->add();
				}
			}
		}

		public function wpautop() {
			// make sure wpautop() does not have a higher priority than 10, otherwise it will 
			// format the shortcode output (shortcode filters are run at priority 11).
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				$default_priority = 10;
				foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {
					$filter_priority = has_filter( $filter_name, 'wpautop' );
					if ( $filter_priority !== false && 
						$filter_priority > $default_priority ) {

						remove_filter( $filter_name, 'wpautop' );
						add_filter( $filter_name, 'wpautop' , $default_priority );
						$this->p->debug->log( 'wpautop() priority changed from '.$filter_priority.' to '.$default_priority );
					}
				}
			}
		}

		public function add() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
        			add_shortcode( WPSSOSSB_SHARING_SHORTCODE, array( &$this, 'shortcode' ) );
				$this->p->debug->log( '['.WPSSOSSB_SHARING_SHORTCODE.'] sharing shortcode added' );
			}
		}

		public function remove() {
			if ( ! empty( $this->p->options['plugin_shortcodes'] ) ) {
				remove_shortcode( WPSSOSSB_SHARING_SHORTCODE );
				$this->p->debug->log( '['.WPSSOSSB_SHARING_SHORTCODE.'] sharing shortcode removed' );
			}
		}

		public function shortcode( $atts, $content = null ) { 

			if ( $this->p->is_avail['amp_endpoint'] && is_amp_endpoint() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				return $content;
			} elseif ( is_feed() ) {
				if ( $this->p->debug->enabled )
					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				return $content;
			}

			$lca = $this->p->cf['lca'];
			$atts = apply_filters( $lca.'_shortcode_'.WPSSOSSB_SHARING_SHORTCODE, $atts, $content );
			$atts['url'] = empty( $atts['url'] ) ? $this->p->util->get_sharing_url( true ) : $atts['url'];
			$atts['css_class'] = empty( $atts['css_class'] ) ? 'ssb-shortcode' : $atts['css_class'];
			$atts['filter_id'] = empty( $atts['filter_id'] ) ? 'shortcode' : $atts['filter_id'];
			$atts['preset_id'] = empty( $atts['preset_id'] ) ? $this->p->options['buttons_preset_ssb-shortcode'] : $atts['preset_id'];
			$atts['use_post'] = SucomUtil::sanitize_use_post( $atts ); 
			$mod = $this->p->util->get_page_mod( $atts['use_post'] );

			$html = '';
			if ( ! empty( $atts['buttons'] ) ) {
				if ( $this->p->is_avail['cache']['transient'] ) {
					$keys = implode( '|', array_keys( $atts ) );
					$vals = preg_replace( '/[, ]+/', '_', implode( '|', array_values( $atts ) ) );
					$cache_salt = __METHOD__.'('.SucomUtil::get_mod_salt( $mod ).'_atts_keys:'.$keys. '_atts_vals:'.$vals.')';
					$cache_id = $lca.'_'.md5( $cache_salt );
					$cache_type = 'object cache';
					$this->p->debug->log( $cache_type.': transient salt '.$cache_salt );
					$html = get_transient( $cache_id );
					if ( $html !== false ) {
						$this->p->debug->log( $cache_type.': html retrieved from transient '.$cache_id );
						return $this->p->debug->get_html().$html;
					}
				}

				$ids = array_map( 'trim', explode( ',', $atts['buttons'] ) );
				unset ( $atts['buttons'] );

				$html .= '<!-- '.$lca.' '.$atts['css_class'].' begin -->'.
					$this->p->ssb->get_script( 'shortcode-header', $ids ).
					'<div class="'.$lca.'-ssb '.$lca.'-'.$atts['css_class']."\">\n".
					$this->p->ssb->get_html( $ids, $atts, $mod ).'</div>'.
					$this->p->ssb->get_script( 'shortcode-footer', $ids ).
					'<!-- '.$lca.' '.$atts['css_class'].' end -->';

				if ( $this->p->is_avail['cache']['transient'] ) {
					set_transient( $cache_id, $html, $this->p->options['plugin_object_cache_exp'] );
					$this->p->debug->log( $cache_type.': html saved to transient '.
						$cache_id.' ('.$this->p->options['plugin_object_cache_exp'].' seconds)');
				}
			}
			return $html.$this->p->debug->get_html();
		}
	}
}

?>