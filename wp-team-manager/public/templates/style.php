<?php
use DWL\Wtm\Classes;
/**
 * Generate custom css
 *
 */
// Do not allow directly accessing this file.
if ( ! defined( 'ABSPATH' ) ) {
	exit( 'This script cannot be accessed directly.' );
}
$all_settings  = get_post_meta( $scID );
$selector = '#dwl-team-wrapper-' . $scID;

$card_background_collor = isset($all_settings['dwl_team_team_background_color'][0]) ? $all_settings['dwl_team_team_background_color'][0] : 'none';
$icon_background_collor = isset($all_settings['dwl_team_social_icon_color'][0]) ? $all_settings['dwl_team_social_icon_color'][0] : '#3F88C5';

$css = '';

$css .= "$selector .team-member-info-content {";
$css .= 'background-color:' . esc_html($card_background_collor) . ' !important ;';
$css .= '}';

$css .= "$selector .team-member-socials a{";
$css .= 'background-color:' . esc_html($icon_background_collor) . ' !important ;';
$css .= '}';

$css .= "$selector .team-member-other-info .fas{";
$css .= 'color:' . esc_html($icon_background_collor) . ' !important ;';
$css .= '}';


	
if( $css ){
	echo wp_strip_all_tags( $css );
}
