<?php
/*
Plugin Name: Social Influencer Links
Description: Create a landing page of links for quick use on social media profiles, like Instagram.
Author: WP Maintainer
Author URI: https://wpmaintainer.com/products/landing-links/
Version: 0.2.6
*/

if ( !\defined( 'ABSPATH' ) ) return;

/*
------------------------------------------------
ACF (Free) Inclusion
------------------------------------------------
*/

// for loading field syncs
\define( 'WPM_SIL_ACF_SAVE_PATH', __DIR__ . '/acf-json' );

// Include the ACF plugin.
include_once __DIR__ . '/lib/acf/acf.php';

// Customize the url setting to fix incorrect asset URLs.
\add_filter('acf/settings/url', function( $url ) {
    return \plugins_url( '/lib/acf/', __FILE__ );
});

// (Optional) Hide the ACF admin menu item.
if ( !\defined( 'WPM_SIL_DEVELOPER' ) ) 
{
    \add_filter('acf/settings/show_admin', function( $show_admin ) {
        return \apply_filters( 'wpm_sil_show_acf_admin', false );
    });
}

/*
------------------------------------------------
Initialize all plugin code:
------------------------------------------------
*/

foreach ( \glob( __DIR__ . '/lib/*.php' ) as $file ) include $file;