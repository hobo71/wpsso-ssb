<?php
/*
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2017 Jean-Sebastien Morisset (https://surniaulula.com/)
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbSubmenuWebsiteTwitter' ) ) {

	class WpssoSsbSubmenuWebsiteTwitter {

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 
				'ssb_website_twitter_rows' => 3,	// $table_rows, $form, $submenu
			) );
		}

		public function filter_ssb_website_twitter_rows( $table_rows, $form, $submenu ) {
			
			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$submenu->show_on_checkboxes( 'twitter' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_order', range( 1, count( $submenu->website ) ) ).'</td>';

			if ( ! SucomUtil::get_const( 'WPSSO_VARY_USER_AGENT_DISABLE' ) ) {
				$table_rows[] = '<tr class="hide_in_basic">'.
				$form->get_th_html( _x( 'Allow for Platform',
					'option label (short)', 'wpsso-ssb' ), 'short' ).
				'<td>'.$form->get_select( 'twitter_platform', $this->p->cf['sharing']['platform'] ).'</td>';
			}

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Language',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_lang', SucomUtil::get_pub_lang( 'twitter' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Size',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_size', array( 'medium' => 'Medium', 'large' => 'Large' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Source',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_input( 'twitter_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Do Not Track',
				'option label (short)', 'wpsso-ssb' ), 'short', null,
			__( 'Disable tracking for Twitter\'s tailored suggestions and ads feature.', 'wpsso-ssb' ) ).
			'<td>'.$form->get_checkbox( 'twitter_dnt' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Add via @username',
				'option label (short)', 'wpsso-ssb' ), 'short', 'buttons_add_via'  ).
			'<td>'.$form->get_checkbox( 'twitter_via' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Recommend Author',
				'option label (short)', 'wpsso-ssb' ), 'short', 'buttons_rec_author'  ).
			'<td>'.$form->get_checkbox( 'twitter_rel_author' ).'</td>';

			return $table_rows;
		}
	}
}

if ( ! class_exists( 'WpssoSsbWebsiteTwitter' ) ) {

	class WpssoSsbWebsiteTwitter {

		private static $cf = array(
			'opt' => array(				// options
				'defaults' => array(
					'twitter_order' => 3,
					'twitter_on_content' => 1,
					'twitter_on_excerpt' => 0,
					'twitter_on_sidebar' => 0,
					'twitter_on_admin_edit' => 1,
					'twitter_platform' => 'any',
					'twitter_script_loc' => 'header',
					'twitter_lang' => 'en',
					'twitter_caption' => 'title',
					'twitter_cap_len' => 140,
					'twitter_size' => 'medium',
					'twitter_via' => 1,
					'twitter_rel_author' => 1,
					'twitter_dnt' => 1,
				),
			),
		);

		protected $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			$this->p->util->add_plugin_filters( $this, array( 'get_defaults' => 1 ) );
		}

		public function filter_get_defaults( $def_opts ) {
			return array_merge( $def_opts, self::$cf['opt']['defaults'] );
		}

		public function get_html( array $atts, array $opts, array $mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$lca = $this->p->cf['lca'];

			$short_url = apply_filters( $lca.'_shorten_url', $atts['url'], $opts['plugin_shortener'] );

			if ( ! array_key_exists( 'lang', $atts ) ) {
				$atts['lang'] = empty( $opts['twitter_lang'] ) ? 'en' : $opts['twitter_lang'];
				$atts['lang'] = apply_filters( $lca.'_pub_lang', $atts['lang'], 'twitter', 'current' );
			}

			if ( array_key_exists( 'tweet', $atts ) )
				$atts['caption'] = $atts['tweet'];

			if ( ! array_key_exists( 'caption', $atts ) ) {
				if ( empty( $atts['caption'] ) ) {
					$caption_len = $this->p->ssb_sharing->get_tweet_max_len( 'twitter' );
					$atts['caption'] = $this->p->webpage->get_caption( $opts['twitter_caption'], $caption_len,
						$mod, true, true, true, 'twitter_desc' );
				}
			}

			if ( ! array_key_exists( 'via', $atts ) ) {
				if ( ! empty( $opts['twitter_via'] ) ) {
					$atts['via'] = preg_replace( '/^@/', '', 
						SucomUtil::get_locale_opt( 'tc_site', $opts ) );
				} else $atts['via'] = '';
			}

			if ( ! array_key_exists( 'related', $atts ) ) {
				if ( ! empty( $opts['twitter_rel_author'] ) && 
					! empty( $mod['post_author'] ) && $atts['use_post'] )
						$atts['related'] = preg_replace( '/^@/', '', 
							get_the_author_meta( $opts['plugin_cm_twitter_name'], $mod['post_author'] ) );
				else $atts['related'] = '';
			}

			// hashtags are included in the caption instead
			if ( ! array_key_exists( 'hashtags', $atts ) )
				$atts['hashtags'] = '';

			if ( ! array_key_exists( 'dnt', $atts ) ) 
				$atts['dnt'] = $opts['twitter_dnt'] ? 'true' : 'false';

			$html = '<!-- Twitter Button -->'.
			'<div '.SucomUtil::get_atts_css_attr( $atts, 'twitter' ).'>'.
			'<a href="'.SucomUtil::get_prot().'://twitter.com/share" class="twitter-share-button"'.
			' data-lang="'.$atts['lang'].'"'.
			' data-url="'.$short_url.'"'.
			' data-counturl="'.$atts['url'].'"'.
			' data-text="'.$atts['caption'].'"'.
			' data-via="'.$atts['via'].'"'.
			' data-related="'.$atts['related'].'"'.
			' data-hashtags="'.$atts['hashtags'].'"'.
			' data-size="'.$opts['twitter_size'].'"'.
			' data-dnt="'.$atts['dnt'].'"></a></div>';

			if ( $this->p->debug->enabled )
				$this->p->debug->log( 'returning html ('.strlen( $html ).' chars)' );

			return $html;
		}
		
		public function get_script( $pos = 'id' ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();
			$js_url = $this->p->ssb_sharing->get_social_file_cache_url( apply_filters( $this->p->cf['lca'].'_js_url_twitter',
				SucomUtil::get_prot().'://platform.twitter.com/widgets.js', $pos ) );

			return '<script type="text/javascript" id="twitter-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "twitter-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
