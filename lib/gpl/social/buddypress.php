<?php
/*
 * License: GPLv3
 * License URI: http://www.gnu.org/licenses/gpl.txt
 * Copyright 2012-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
 */

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! class_exists( 'WpssoSsbGplSocialBuddypress' ) ) {

	class WpssoSsbGplSocialBuddypress {

		private $p;
		private $sharing;

		public function __construct( &$plugin ) {
			$this->p =& $plugin;
			if ( $this->p->debug->enabled )
				$this->p->debug->mark();

			if ( is_admin() || bp_current_component() ) {
				if ( ! empty( $this->p->is_avail['ssb'] ) ) {
					$classname = __CLASS__.'Sharing';
					if ( class_exists( $classname ) )
						$this->sharing = new $classname( $this->p );
				}
			}
		}
	}
}

if ( ! class_exists( 'WpssoSsbGplSocialBuddypressSharing' ) ) {

	class WpssoSsbGplSocialBuddypressSharing {

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
					'style_bp_activity_rows' => 2,
				) );
			}
		}

		/* Purpose: Create default options for the sanitation process, so it doesn't strip-out non-existing options */
		public function filter_get_defaults( $opts_def ) {
			$opts_def['buttons_css_bp_activity'] = '/* Save an empty style text box to reload the default example styles.
 * These styles are provided as examples only - modifications may be 
 * necessary to customize the layout for your website. Social sharing
 * buttons can be aligned vertically, horizontally, floated, etc.
 */

.wpsso-bp_activity-buttons { 
	display:block;
	margin:10px auto;
	text-align:center;
}';
			// the default 'Show Button in' for 'BP Activity' is unchecked
			foreach ( $this->p->cf['opt']['pre'] as $name => $prefix )
				$opts_def[$prefix.'_on_bp_activity'] = 0;
			return $opts_def;
		}


		/* Purpose: Include the 'BP Activity' checkbox in the 'Show Button in' options */
		public function filter_sharing_show_on( $show_on = array(), $prefix ) {
			switch ( $prefix ) {
				case 'pin':
					break;
				default:
					$show_on['bp_activity'] = 'BP Activity';
					$this->p->options[$prefix.'_on_bp_activity:is'] = 'disabled';
					break;
			}
			return $show_on;
		}
		/* Purpose: Add a 'BP Activity' tab to the Style settings */
		public function filter_style_tabs( $tabs ) {
			$tabs['bp_activity'] = 'BP Activity';
			$this->p->options['buttons_css_bp_activity:is'] = 'disabled';
			return $tabs;
		}

		/* Purpose: Add css input textarea for the 'BP Activity' style tab */
		public function filter_style_bp_activity_rows( $rows, $form ) {

			$rows[] = '<td colspan="2" align="center">'.
				$this->p->msgs->get( 'pro-feature-msg', 
					array( 'lca' => 'wpssossb' ) ).'</td>';

			$rows[] = '<th class="textinfo">
			<p>Social sharing buttons added to BuddyPress Activities are assigned the \'wpsso-bp_activity-buttons\' class, which itself contains the \'wpsso-buttons\' class -- a common class for all the sharing buttons (see the All Buttons tab).</p> 

			<p>Example:</p><pre>
.wpsso-bp_activity-buttons 
    .wpsso-buttons
        .facebook-button { }</pre></th><td><textarea disabled="disabled" class="tall code">'.
			$this->p->options['buttons_css_bp_activity'].'</textarea></td>';

			return $rows;
		}
	}
}

?>
