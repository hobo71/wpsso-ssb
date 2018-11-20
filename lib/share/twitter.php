<?php
/**
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2018 Jean-Sebastien Morisset (https://wpsso.com/)
 */

if ( ! defined( 'ABSPATH' ) ) {
	die( 'These aren\'t the droids you\'re looking for...' );
}

if ( ! class_exists( 'WpssoSsbSubmenuShareTwitter' ) ) {

	class WpssoSsbSubmenuShareTwitter {

		private $p;

		public function __construct( &$plugin ) {

			$this->p =& $plugin;

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_share_twitter_rows' => 3,
			) );
		}

		public function filter_ssb_share_twitter_rows( $table_rows, $form, $submenu ) {
			
			$table_rows[] = '' .
			$form->get_th_html( _x( 'Show Button in', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $submenu->show_on_checkboxes( 'twitter' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Preferred Order', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'twitter_order', range( 1, count( $submenu->share ) ) ) . '</td>';

			if ( $this->p->avail[ '*' ]['vary_ua'] ) {
				$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_platform' ) . 
				$form->get_th_html( _x( 'Allow for Platform', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
				'<td>' . $form->get_select( 'twitter_platform', $this->p->cf['sharing']['platform'] ) . '</td>';
			}

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_script_loc' ) . 
			$form->get_th_html( _x( 'JavaScript in', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'twitter_script_loc', $this->p->cf['form']['script_locations'] ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Language', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'twitter_lang', SucomUtil::get_pub_lang( 'twitter' ) ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Button Size', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'twitter_size', array(
				'medium' => 'Medium',
				'large'  => 'Large',
			) ) . '</td>';

			$table_rows[] = '' . 
			$form->get_th_html( _x( 'Tweet Text Source', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_select( 'twitter_caption', $this->p->cf['form']['caption_types'] ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_caption_max_len' ) . 
			$form->get_th_html( _x( 'Tweet Text Length', 'option label (short)', 'wpsso-ssb' ), 'short' ) . 
			'<td>' . $form->get_input( 'twitter_caption_max_len', 'short' ) . ' ' . 
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ) . '</td>';

			$table_rows[] = $form->get_tr_hide( 'basic', 'twitter_dnt' ) . 
			$form->get_th_html( _x( 'Do Not Track', 'option label (short)', 'wpsso-ssb' ), 'short', '',
				__( 'Disable tracking for Twitter\'s tailored suggestions and ads feature.', 'wpsso-ssb' ) ) . 
			'<td>' . $form->get_checkbox( 'twitter_dnt' ) . '</td>';

			$table_rows[] = '' . 
			$form->get_th_html( _x( 'Append Hashtags to Tweet', 'option label', 'wpsso-ssb' ) ) . 
			'<td>' . $form->get_select( 'twitter_caption_hashtags', range( 0, $this->p->cf['form']['max_hashtags'] ), 'short', '', true ) . ' ' . 
				_x( 'tag names', 'option comment', 'wpsso-ssb' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Add via @username', 'option label (short)', 'wpsso-ssb' ), 'short', 'buttons_add_via'  ) . 
			'<td>' . $form->get_checkbox( 'twitter_via' ) . '</td>';

			$table_rows[] = '' .
			$form->get_th_html( _x( 'Recommend Author', 'option label (short)', 'wpsso-ssb' ), 'short', 'buttons_rec_author'  ) . 
			'<td>' . $form->get_checkbox( 'twitter_rel_author' ) . '</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbShareTwitter' ) ) {

	class WpssoSsbShareTwitter {

		private $p;
		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'twitter_order'            => 3,
					'twitter_on_content'       => 1,
					'twitter_on_excerpt'       => 0,
					'twitter_on_sidebar'       => 0,
					'twitter_on_admin_edit'    => 1,
					'twitter_platform'         => 'any',
					'twitter_script_loc'       => 'header',
					'twitter_lang'             => 'en',
					'twitter_size'             => 'medium',
					'twitter_caption'          => 'excerpt',
					'twitter_caption_max_len'  => 280,	// changed from 140 to 280 on 2017/11/17
					'twitter_caption_hashtags' => 3,
					'twitter_via'              => 1,
					'twitter_rel_author'       => 1,
					'twitter_dnt'              => 1,
				),
			),
		);

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

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			if ( ! isset( $atts['lang'] ) ) {
				$atts['lang'] = empty( $opts['twitter_lang'] ) ? 'en' : $opts['twitter_lang'];
				$atts['lang'] = apply_filters( $this->p->lca . '_pub_lang', $atts['lang'], 'twitter', 'current' );
			}

			if ( ! isset( $atts['add_hashtags'] ) ) {
				$atts['add_hashtags'] = empty( $this->p->options['twitter_caption_hashtags'] ) ?
					false : $this->p->options['twitter_caption_hashtags'];
			}

			if ( ! isset( $atts['tweet'] ) ) {
				$atts['tweet'] = WpssoSsbSocial::get_tweet_text( $mod, $atts, 'twitter', 'twitter' );
			}

			if ( ! isset( $atts['hashtags'] ) ) {
				$atts['hashtags'] = '';
			}

			if ( ! isset( $atts['via'] ) ) {
				if ( ! empty( $opts['twitter_via'] ) ) {
					$atts['via'] = preg_replace( '/^@/', '', SucomUtil::get_key_value( 'tc_site', $opts ) );
				} else {
					$atts['via'] = '';
				}
			}

			if ( ! isset( $atts['related'] ) ) {
				if ( ! empty( $opts['twitter_rel_author'] ) && ! empty( $mod['post_author'] ) && $atts['use_post'] ) {
					$atts['related'] = preg_replace( '/^@/', '', get_the_author_meta( $opts['plugin_cm_twitter_name'], $mod['post_author'] ) );
				} else {
					$atts['related'] = '';
				}
			}

			if ( ! array_key_exists( 'dnt', $atts ) ) {
				$atts['dnt'] = $opts['twitter_dnt'] ? 'true' : 'false';
			}

			$short_url = apply_filters( $this->p->lca . '_get_short_url', $atts['url'], $this->p->options[ 'plugin_shortener' ], $mod );

			$html = '<!-- Twitter Button -->' . 
				'<div ' . SucomUtil::get_atts_css_attr( $atts, 'twitter' ) . '>' . 
				'<a href="' . SucomUtil::get_prot() . '://twitter.com/share" class="twitter-share-button"' . 
				' data-lang="' . $atts['lang'] . '"' . 
				' data-url="' . $short_url . '"' . 
				' data-counturl="' . $atts['url'] . '"' . 
				' data-text="' . $atts['tweet'] . '"' . 
				' data-via="' . $atts['via'] . '"' . 
				' data-related="' . $atts['related'] . '"' . 
				' data-hashtags="' . $atts['hashtags'] . '"' . 
				' data-size="' . $opts['twitter_size'] . '"' . 
				' data-dnt="' . $atts['dnt'] . '"></a></div>';

			if ( $this->p->debug->enabled ) {
				$this->p->debug->log( 'returning html (' . strlen( $html ) . ' chars)' );
			}

			return $html;
		}
		
		public function get_script( $pos = 'id' ) {

			if ( $this->p->debug->enabled ) {
				$this->p->debug->mark();
			}

			$js_url = WpssoSsbSocial::get_file_cache_url( apply_filters( $this->p->lca . '_js_url_twitter',
				SucomUtil::get_prot() . '://platform.twitter.com/widgets.js', $pos ) );

			return '<script type="text/javascript" id="twitter-script-' . $pos . '">' . 
				$this->p->lca . '_insert_js( "twitter-script-' . $pos . '", "' . $js_url . '" );</script>';
		}
	}
}
