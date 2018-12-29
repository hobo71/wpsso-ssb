<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbShortcodeSharing' ) ) {

	class WpssoSsbShortcodeSharing {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! is_admin() ) {
				if ( $this->p->avail['p_ext']['ssb'] ) {

					$this->check_wpautop();

					$this->add_shortcode();

					$this->p->util->add_plugin_actions( $this, array( 
						'pre_apply_filters_text'   => 1,
						'after_apply_filters_text' => 1,
					) );
				}
			}
		}

		/**
		 * Make sure wpautop() does not have a higher priority than 10, otherwise it will 
		 * format the shortcode output (shortcode filters are run at priority 11).
		 */
		public function check_wpautop() {

			$default_priority = 10;

			foreach ( array( 'get_the_excerpt', 'the_excerpt', 'the_content' ) as $filter_name ) {

				$filter_priority = has_filter( $filter_name, 'wpautop' );

				if ( false !== $filter_priority && $filter_priority > $default_priority ) {

					remove_filter( $filter_name, 'wpautop' );

					add_filter( $filter_name, 'wpautop' , $default_priority );

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( 'wpautop() priority changed from ' . $filter_priority . ' to ' . $default_priority );
					}
				}
			}
		}

		/**
		 * Remove our shortcode before applying a text filter.
		 */
		public function action_pre_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->remove_shortcode();
		}

		/**
		 * Re-add our shortcode after applying a text filter.
		 */
		public function action_after_apply_filters_text( $filter_name ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log_args( array( 
					'filter_name' => $filter_name,
				) );
			}

			$this->add_shortcode();
		}

		public function add_shortcode() {

			if ( shortcode_exists( WPSSOSSB_SHARING_SHORTCODE_NAME ) ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'cannot add [' . WPSSOSSB_SHARING_SHORTCODE_NAME . '] sharing shortcode - shortcode already exists' );
				}

				return false;
			}

        		add_shortcode( WPSSOSSB_SHARING_SHORTCODE_NAME, array( $this, 'do_shortcode' ) );

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( '[' . WPSSOSSB_SHARING_SHORTCODE_NAME . '] sharing shortcode added' );
			}

			return true;
		}

		public function remove_shortcode() {

			if ( shortcode_exists( WPSSOSSB_SHARING_SHORTCODE_NAME ) ) {

				remove_shortcode( WPSSOSSB_SHARING_SHORTCODE_NAME );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( '[' . WPSSOSSB_SHARING_SHORTCODE_NAME . '] sharing shortcode removed' );
				}

				return true;

			}
			
			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'cannot remove [' . WPSSOSSB_SHARING_SHORTCODE_NAME . '] sharing shortcode - shortcode does not exist' );
			}

			return false;
		}

		public function do_shortcode( $atts = array(), $content = null, $tag = '' ) { 

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( SucomUtil::is_amp() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: buttons not allowed in amp endpoint'  );
				}

				return $content;

			} elseif ( is_feed() ) {

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( 'exiting early: buttons not allowed in rss feeds'  );
				}

				return $content;
			}

			$ssb =& WpssoSsb::get_instance();

			if ( ! is_array( $atts ) ) {	// Empty string if no shortcode attributes.
				$atts = array();
			}

			$atts = (array) apply_filters( $this->p->lca . '_ssb_sharing_shortcode_atts', $atts, $content );

			if ( empty( $atts['buttons'] ) ) {	// nothing to do
				return '<!-- ' . $this->p->lca . ' sharing shortcode: no buttons defined -->' . "\n\n";
			}

			$atts[ 'use_post' ]  = SucomUtil::sanitize_use_post( $atts, true );	// $default = true
			$atts[ 'css_class' ] = empty( $atts[ 'css_class' ] ) ? 'ssb-shortcode' : $atts[ 'css_class' ];
			$atts[ 'preset_id' ] = empty( $atts[ 'preset_id' ] ) ? $this->p->options['buttons_preset_ssb-shortcode'] : $atts[ 'preset_id' ];
			$atts[ 'filter_id' ] = empty( $atts[ 'filter_id' ] ) ? 'shortcode' : $atts[ 'filter_id' ];

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'required call to get_page_mod()' );
			}

			$mod         = $this->p->util->get_page_mod( $atts[ 'use_post' ] );
			$type        = 'sharing_shortcode_' . WPSSOSSB_SHARING_SHORTCODE_NAME;
			$atts[ 'url' ] = empty( $atts[ 'url' ] ) ? $this->p->util->get_sharing_url( $mod ) : $atts[ 'url' ];

			$cache_md5_pre  = $this->p->lca . '_b_';
			$cache_exp_secs = $ssb->social->get_buttons_cache_exp();
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $atts[ 'url' ] ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $ssb->social->get_buttons_cache_index( $type, $atts );	// Returns salt with locale, mobile, wp_query, etc.
			$cache_array    = array();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = ' . $atts[ 'url' ] );
				$this->p->debug->log( 'cache expire = ' . $cache_exp_secs );
				$this->p->debug->log( 'cache salt = ' . $cache_salt );
				$this->p->debug->log( 'cache id = ' . $cache_id );
				$this->p->debug->log( 'cache index = ' . $cache_index );
			}

			if ( $cache_exp_secs > 0 ) {

				$cache_array = SucomUtil::get_transient_array( $cache_id );

				if ( isset( $cache_array[ $cache_index ] ) ) {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' cache index found in transient cache' );
					}

					return $cache_array[ $cache_index ];	// stop here

				} else {

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' cache index not in transient cache' );
					}

					if ( ! is_array( $cache_array ) ) {
						$cache_array = array();
					}
				}

			} elseif ( $this->p->debug->enabled ) {
				$this->p->debug->log( $type . ' buttons array transient cache is disabled' );
			}

			$ids = array_map( 'trim', explode( ',', $atts['buttons'] ) );

			unset ( $atts['buttons'] );

			/**
			 * Returns html or an empty string.
			 */
			$cache_array[ $cache_index ] = $ssb->social->get_html( $ids, $atts, $mod );

			if ( ! empty( $cache_array[ $cache_index ] ) ) {
				$cache_array[ $cache_index ] = '
<!-- ' . $this->p->lca . ' ' . $type . ' begin -->
<!-- generated on ' . date( 'c' ) . ' -->' . "\n" . 
$ssb->social->get_script( 'shortcode-header', $ids ) . 
'<div class="' . $this->p->lca . '-ssb ' . $this->p->lca . '-' . $atts[ 'css_class' ] . "\">\n" . 
$cache_array[ $cache_index ] . "\n" . 	// Buttons html is trimmed, so add newline.
'</div><!-- .' . $this->p->lca . '-' . $atts[ 'css_class' ] . ' -->' . "\n" . 
$ssb->social->get_script( 'shortcode-footer', $ids ) . 
'<!-- ' . $this->p->lca . ' ' . $type . ' end -->' . "\n\n";
			}

			if ( $cache_exp_secs > 0 ) {

				/**
				 * Update the cached array and maintain the existing transient expiration time.
				 */
				$expires_in_secs = SucomUtil::update_transient_array( $cache_id, $cache_array, $cache_exp_secs );

				if ( $this->p->debug->enabled ) {
					$this->p->debug->log( $type . ' buttons html saved to transient cache (expires in ' . $expires_in_secs . ' secs)' );
				}
			}

			return $cache_array[ $cache_index ];
		}
	}
}
