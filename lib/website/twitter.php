<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2014-2016 Jean-Sebastien Morisset (http://surniaulula.com/)
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
			
			$table_rows[] = $form->get_th_html( _x( 'Preferred Order',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'twitter_order', 
				range( 1, count( $submenu->website ) ), 'short' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Show Button in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			( $submenu->show_on_checkboxes( 'twitter' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Allow for Platform',
				'option label (short)', 'wpsso-ssb' ), 'short' ).
			'<td>'.$form->get_select( 'twitter_platform',
				$this->p->cf['sharing']['platform'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'JavaScript in',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'twitter_script_loc', $this->p->cf['form']['script_locations'] ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Default Language',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'twitter_lang', SucomUtil::get_pub_lang( 'twitter' ) ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Button Size',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'twitter_size', array( 'medium' => 'Medium', 'large' => 'Large' ) ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Source',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_select( 'twitter_caption', $this->p->cf['form']['caption_types'] ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Tweet Text Length',
				'option label (short)', 'wpsso-ssb' ), 'short' ).'<td>'.
			$form->get_input( 'twitter_cap_len', 'short' ).' '.
				_x( 'characters or less', 'option comment', 'wpsso-ssb' ).'</td>';

			$table_rows[] = '<tr class="hide_in_basic">'.
			$form->get_th_html( _x( 'Do Not Track',
				'option label (short)', 'wpsso-ssb' ), 'short', null,
			__( 'Disable tracking for Twitter\'s tailored suggestions and ads feature.', 'wpsso-ssb' ) ).
			'<td>'.$form->get_checkbox( 'twitter_dnt' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Add via @username',
				'option label (short)', 'wpsso-ssb' ), 'short', null, 
			sprintf( __( 'Append the website\'s business @username to the tweet (see the <a href="%1$s">Twitter</a> options tab on the %2$s settings page). The website\'s @username will be displayed and recommended after the webpage is shared.', 'wpsso-ssb' ), $this->p->util->get_admin_url( 'general#sucom-tabset_pub-tab_twitter' ), _x( 'General', 'lib file description', 'wpsso-ssb' ) ) ).
			'<td>'.$form->get_checkbox( 'twitter_via' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Recommend Author',
				'option label (short)', 'wpsso-ssb' ), 'short', null, 
			sprintf( __( 'Recommend following the author\'s Twitter @username (from their profile) after sharing a webpage. If the <em>%1$s</em> option is also checked, the website\'s @username is suggested first.', 'wpsso-ssb' ), _x( 'Add via @username', 'option label (short)', 'wpsso-rrssb' ) ) ).
			'<td>'.$form->get_checkbox( 'twitter_rel_author' ).'</td>';

			$table_rows[] = $form->get_th_html( _x( 'Shorten URLs with',
				'option label (short)', 'wpsso-ssb' ), 'short', null, 
			sprintf( __( 'If you select a URL shortening service here, you must also enter its <a href="%1$s">%2$s</a> on the %3$s settings page.', 'wpsso-ssb' ), $this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys' ), _x( 'Service API Keys', 'metabox tab', 'wpsso-ssb' ), _x( 'Advanced', 'lib file description', 'wpsso-ssb' ) ) ).
			( $this->p->check->aop( $this->p->cf['lca'], true, $this->p->is_avail['aop'] ) ? 
				'<td>'.$form->get_select( 'plugin_shortener', $this->p->cf['form']['shorteners'], 'short' ).'&nbsp; ' :
				'<td class="blank">'.$this->p->cf['form']['shorteners'][$this->p->options['plugin_shortener']].' &mdash; ' ).
			sprintf( __( 'using these <a href="%1$s">%2$s</a>', 'wpsso-ssb' ),
				$this->p->util->get_admin_url( 'advanced#sucom-tabset_plugin-tab_apikeys' ),
				_x( 'Service API Keys', 'metabox tab', 'wpsso-ssb' ) ).'</td>';

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

		public function filter_get_defaults( $opts_def ) {
			return array_merge( $opts_def, self::$cf['opt']['defaults'] );
		}

		// do not use an $atts reference to allow for local changes
		public function get_html( array $atts, array &$opts, array &$mod ) {
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( empty( $opts ) ) 
				$opts =& $this->p->options;

			global $post; 

			$lca = $this->p->cf['lca'];
			$atts['use_post'] = isset( $atts['use_post'] ) ? $atts['use_post'] : true;
			$atts['add_page'] = isset( $atts['add_page'] ) ? $atts['add_page'] : true;	// get_sharing_url() argument

			$long_url = empty( $atts['url'] ) ? 
				$this->p->util->get_sharing_url( $mod, $atts['add_page'] ) : 
				apply_filters( $lca.'_sharing_url', $atts['url'], $mod, $atts['add_page'] );

			$short_url = apply_filters( $lca.'_shorten_url', $long_url, $opts['plugin_shortener'] );

			if ( ! array_key_exists( 'lang', $atts ) ) {
				$atts['lang'] = empty( $opts['twitter_lang'] ) ? 'en' : $opts['twitter_lang'];
				$atts['lang'] = apply_filters( $lca.'_pub_lang', $atts['lang'], 'twitter', 'current' );
			}

			if ( array_key_exists( 'tweet', $atts ) )
				$atts['caption'] = $atts['tweet'];

			if ( ! array_key_exists( 'caption', $atts ) ) {
				if ( empty( $atts['caption'] ) ) {
					$caption_len = $this->p->util->get_tweet_max_len( 'twitter' );
					$atts['caption'] = $this->p->webpage->get_caption( $opts['twitter_caption'], $caption_len,
						$mod, true, true, true, 'twitter_desc' );
				}
			}

			if ( ! array_key_exists( 'via', $atts ) ) {
				if ( ! empty( $opts['twitter_via'] ) ) {
					$key_locale = SucomUtil::get_key_locale( 'tc_site', $opts );
					$atts['via'] = preg_replace( '/^@/', '', $opts[$key_locale] );
				} else $atts['via'] = '';
			}

			if ( ! array_key_exists( 'related', $atts ) ) {
				if ( ! empty( $opts['twitter_rel_author'] ) && 
					! empty( $post ) && $atts['use_post'] )
						$atts['related'] = preg_replace( '/^@/', '', 
							get_the_author_meta( $opts['plugin_cm_twitter_name'], $post->author ) );
				else $atts['related'] = '';
			}

			// hashtags are included in the caption instead
			if ( ! array_key_exists( 'hashtags', $atts ) )
				$atts['hashtags'] = '';

			if ( ! array_key_exists( 'dnt', $atts ) ) 
				$atts['dnt'] = $opts['twitter_dnt'] ? 'true' : 'false';

			$html = '<!-- Twitter Button -->'.
			'<div '.WpssoSsbSharing::get_css_class_id( 'twitter', $atts ).'>'.
			'<a href="'.SucomUtil::get_prot().'://twitter.com/share" class="twitter-share-button"'.
			' data-lang="'.$atts['lang'].'"'.
			' data-url="'.$short_url.'"'.
			' data-counturl="'.$long_url.'"'.
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
			$js_url = $this->p->util->get_cache_file_url( apply_filters( $this->p->cf['lca'].'_js_url_twitter',
				SucomUtil::get_prot().'://platform.twitter.com/widgets.js', $pos ) );

			return '<script type="text/javascript" id="twitter-script-'.$pos.'">'.
				$this->p->cf['lca'].'_insert_js( "twitter-script-'.$pos.'", "'.$js_url.'" );</script>';
		}
	}
}

?>
