<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2019 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbWidgetSharing' ) && class_exists( 'WP_Widget' ) ) {

	class WpssoSsbWidgetSharing extends WP_Widget {

		private $p;

		public function __construct() {

			$this->p =& Wpsso::get_instance();

			if ( ! is_object( $this->p ) ) {
				return;
			}

			$short        = $this->p->cf[ 'plugin' ][ 'wpssossb' ][ 'short' ];
			$name         = $this->p->cf[ 'plugin' ][ 'wpssossb' ][ 'name' ];
			$widget_name  = $short;
			$widget_class = $this->p->lca . '-ssb-widget';
			$widget_ops   = array( 
				'classname'   => $widget_class,
				'description' => sprintf( __( 'The %s widget.', 'wpsso-ssb' ), $name ),
			);

			parent::__construct( $widget_class, $widget_name, $widget_ops );
		}
	
		public function widget( $args, $instance ) {

			if ( is_feed() ) {
				return;
			}

			$ssb =& WpssoSsb::get_instance();

			extract( $args );

			$atts = array( 
				'use_post'  => false,		// Don't use the post ID on indexes.
				'css_id'    => $args[ 'widget_id' ],
				'preset_id' => $this->p->options[ 'buttons_preset_ssb-widget' ],
				'filter_id' => 'widget',	// used by get_html() to filter atts and opts
			);

			$widget_title = apply_filters( 'widget_title', $instance[ 'title' ], $instance, $this->id_base );

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'required call to get_page_mod()' );
			}

			$mod         = $this->p->util->get_page_mod( $atts[ 'use_post' ] );
			$type        = 'sharing_widget_' . $this->id;
			$sharing_url = $this->p->util->get_sharing_url( $mod );

			$cache_md5_pre  = $this->p->lca . '_b_';
			$cache_exp_secs = $ssb->social->get_buttons_cache_exp();
			$cache_salt     = __METHOD__ . '(' . SucomUtil::get_mod_salt( $mod, $sharing_url ) . ')';
			$cache_id       = $cache_md5_pre . md5( $cache_salt );
			$cache_index    = $ssb->social->get_buttons_cache_index( $type, $atts );	// Returns salt with locale, mobile, wp_query, etc.
			$cache_array    = array();

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'sharing url = ' . $sharing_url );
				$this->p->debug->log( 'cache expire = ' . $cache_exp_secs );
				$this->p->debug->log( 'cache salt = ' . $cache_salt );
				$this->p->debug->log( 'cache id = ' . $cache_id );
				$this->p->debug->log( 'cache index = ' . $cache_index );
			}

			if ( $cache_exp_secs > 0 ) {

				$cache_array = SucomUtil::get_transient_array( $cache_id );

				if ( isset( $cache_array[ $cache_index ] ) ) {	// can be an empty string

					if ( $this->p->debug->enabled ) {
						$this->p->debug->log( $type . ' cache index found in transient cache' );
					}

					echo $cache_array[ $cache_index ];	// stop here

					return;

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

			/**
			 * Sort enabled sharing buttons by their preferred order.
			 */
			$sorted_ids = array();

			foreach ( $this->p->cf[ 'opt' ][ 'cm_prefix' ] as $id => $opt_pre ) {
				if ( array_key_exists( $id, $instance ) && (int) $instance[ $id ] ) {
					$sorted_ids[ zeroise( $this->p->options[ $opt_pre . '_order' ], 3 ) . '-' . $id ] = $id;
				}
			}

			ksort( $sorted_ids );

			/**
			 * Returns html or an empty string.
			 */
			$cache_array[ $cache_index ] = $ssb->social->get_html( $sorted_ids, $atts, $mod );

			if ( ! empty( $cache_array[ $cache_index ] ) ) {
				$cache_array[ $cache_index ] = '
<!-- ' . $this->p->lca . ' sharing widget ' . $args[ 'widget_id' ] . ' begin -->
<!-- generated on ' . date( 'c' ) . ' -->' . 
$before_widget . 
( empty( $widget_title ) ? '' : $before_title . $widget_title . $after_title ) . 
$cache_array[ $cache_index ] . "\n" . 	// Buttons html is trimmed, so add newline.
$after_widget . 
'<!-- ' . $this->p->lca . ' sharing widget ' . $args[ 'widget_id' ] . ' end -->' . "\n\n";
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

			echo $cache_array[ $cache_index ];
		}
	
		public function update( $new_instance, $old_instance ) {

			$ssb =& WpssoSsb::get_instance();

			$instance = $old_instance;

			$instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );

			$share_ids = $ssb->social->get_share_ids();

			foreach ( $share_ids as $share_id => $share_title ) {
				$instance[ $share_id ] = empty( $new_instance[ $share_id ] ) ? 0 : 1;
			}

			return $instance;
		}
	
		public function form( $instance ) {

			$ssb =& WpssoSsb::get_instance();

			$widget_title = isset( $instance[ 'title' ] ) ? esc_attr( $instance[ 'title' ] ) : _x( 'Share It', 'option value', 'wpsso-ssb' );
	
			echo "\n" . '<p><label for="' . $this->get_field_id( 'title' ) . '">' . 
				_x( 'Widget Title (leave blank for no title)', 'option label', 'wpsso-ssb' ) . ':</label>' . 
				'<input class="widefat" id="' . $this->get_field_id( 'title' ) . '" name="' . 
					$this->get_field_name( 'title' ) . '" type="text" value="' . $widget_title . '"/></p>' . "\n";

			$share_ids = $ssb->social->get_share_ids();

			foreach ( $share_ids as $share_id => $share_title ) {

				$share_title = $share_title === 'GooglePlus' ? 'Google+' : $share_title;

				echo '<p><label for="' . $this->get_field_id( $share_id ) . '">' . 
					'<input id="' . $this->get_field_id( $share_id ) . 
					'" name="' . $this->get_field_name( $share_id ) . 
					'" value="1" type="checkbox" ';

				if ( ! empty( $instance[ $share_id ] ) ) {
					echo checked( 1, $instance[ $share_id ] );
				}

				echo '/> ' . $share_title . '</label></p>', "\n";
			}
		}
	}
}
