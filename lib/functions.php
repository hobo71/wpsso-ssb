<?php
/*
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl.txt
Copyright 2014-2015 - Jean-Sebastien Morisset - http://surniaulula.com/
*/

if ( ! defined( 'ABSPATH' ) ) 
	die( 'These aren\'t the droids you\'re looking for...' );

if ( ! function_exists( 'wpssossb_get_sharing_buttons' ) ) {
	function wpssossb_get_sharing_buttons( $ids = array(), $atts = array() ) {
		global $wpsso;
		if ( $wpsso->is_avail['ssb'] ) {
			if ( $wpsso->is_avail['cache']['transient'] ) {
				$cache_salt = __METHOD__.'(lang:'.SucomUtil::get_locale().'_url:'.$wpsso->util->get_sharing_url().
					'_ids:'.( implode( '_', $ids ) ).'_atts:'.( implode( '_', $atts ) ).')';
				$cache_id = $wpsso->cf['lca'].'_'.md5( $cache_salt );
				$cache_type = 'object cache';
				$wpsso->debug->log( $cache_type.': transient salt '.$cache_salt );
				$html = get_transient( $cache_id );
				if ( $html !== false ) {
					$wpsso->debug->log( $cache_type.': html retrieved from transient '.$cache_id );
					return $wpsso->debug->get_html().$html;
				}
			}
			$html = '<!-- '.$wpsso->cf['lca'].' sharing buttons begin -->' .
				$wpsso->sharing->get_js( 'sharing-buttons-header', $ids ) .
				$wpsso->sharing->get_html( $ids, $atts ) .
				$wpsso->sharing->get_js( 'sharing-buttons-footer', $ids ) .
				'<!-- '.$wpsso->cf['lca'].' sharing buttons end -->';
	
			if ( $wpsso->is_avail['cache']['transient'] ) {
				set_transient( $cache_id, $html, $wpsso->cache->object_expire );
				$wpsso->debug->log( $cache_type.': html saved to transient '.$cache_id.' ('.$wpsso->cache->object_expire.' seconds)');
			}
		} else $html = '<!-- '.$wpsso->cf['lca'].' sharing sharing buttons disabled -->';
		return $wpsso->debug->get_html().$html;
	}
}

?>
