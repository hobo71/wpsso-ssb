<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbGplForumBbpress' ) ) {

	class WpssoSsbGplForumBbpress {

		private $p;
		private $sharing;
		private $has_setup = false;
		private $post_id;
		private $post_type;
		private $topic_type = 'topic';
		private $forum_type = 'forum';
		private $reply_type = 'reply';

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( class_exists( 'bbpress' ) ) {	// is_bbpress() is not available here
				if ( ! empty( $this->p->is_avail['ssb'] ) ) {
					$classname = __CLASS__.'Sharing';
					if ( class_exists( $classname ) )
						$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoSsbGplForumBbpressSharing' ) ) {

	class WpssoSsbGplForumBbpressSharing {

		private $p;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			$this->p->util->add_plugin_filters( $this, array( 
				'get_defaults' => 1,
			) );

			if ( is_admin() ) {
				$this->p->util->add_plugin_filters( $this, array( 
					'sharing_show_on' => 2,
					'style_tabs' => 1,
					'style_bbp_single_rows' => 2,
					'sharing_position_rows' => 2,
				) );
			}
		}

		/* Purpose: Create default options for the sanitation process, so it doesn't strip-out non-existing options */
		public function filter_get_defaults( $opts_def ) {
			$opts_def['buttons_css_bbp_single'] = '/* Save an empty style text box to reload the default example styles.
 * These styles are provided as examples only - modifications may be 
 * necessary to customize the layout for your website. Social sharing
 * buttons can be aligned vertically, horizontally, floated, etc.
 */

.wpsso-bbp_single-buttons { 
	display:block;
	margin:10px auto;
	text-align:center;
}';
			// the default 'Show Button in' for 'bbPress Single' is unchecked
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_bbp_single'] = 0;

			$opts_def['buttons_pos_bbp_single'] = 'top';

			return $opts_def;
		}

		/* Purpose: Include the 'bbPress Single' checkbox in the 'Show Button in' options */
		public function filter_sharing_show_on( $show_on = array(), $prefix ) {
			switch ( $prefix ) {
				case 'pin':
					break;
				default:
					$show_on['bbp_single'] = 'bbPress Single';
					$this->p->options[$prefix.'_on_bbp_single:is'] = 'disabled';
					break;
			}
			return $show_on;
		}

		/* Purpose: Add a 'bbPress Single' tab to the Style settings */
		public function filter_style_tabs( $tabs ) {
			$tabs['bbp_single'] = 'bbPress Single';
			$this->p->options['buttons_css_bbp_single:is'] = 'disabled';
			return $tabs;
		}

		/* Purpose: Add css input textarea for the 'bbPress Single' style tab */
		public function filter_style_bbp_single_rows( $rows, $form ) {

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';

			$rows[] = '<th class="textinfo">
			<p>Social sharing buttons added at the top of bbPress Single Templates are assigned the \'wpsso-bbp_single-buttons\' class, which itself contains the \'wpsso-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 

			<p>Example:</p><pre>
.wpsso-bbp_single-buttons 
    .wpsso-buttons
        .facebook-button { }</pre></th><td><textarea disabled="disabled" class="tall code">'.
				$this->p->options['buttons_css_bbp_single'].'</textarea></td>';
			return $rows;
		}

		public function filter_sharing_position_rows( $rows, $form ) {
			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', array( 'lca' => 'wpssossb' ) ).'</td>';
			$rows['buttons_pos_bbp_single'] = $this->p->util->get_th( _x( 'Position in bbPress Single',
				'option label', 'wpsso-ssb' ), null, 'buttons_pos_bbp_single' ).
			'<td class="blank">'.WpssoSsbSharing::$cf['sharing']['position'][$this->p->options['buttons_pos_bbp_single']].'</td>';
			return $rows;
		}
	}
}

?>
