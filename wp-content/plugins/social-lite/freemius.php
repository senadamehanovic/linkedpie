<?php

/**
 * Function for the Freemius SDK.
 *
 * @since 1.1.0
 */

if ( !function_exists( 'social_fs' ) ) {
    // Create a helper function for easy SDK access.
    function social_fs()
    {
        /* @var  $social_fs Freemius */
        global  $social_fs ;
        if ( !defined( 'WP_FS__PRODUCT_10702_MULTISITE' ) ) {
            define( 'WP_FS__PRODUCT_10702_MULTISITE', true );
        }
        require_once SOCIAL_LITE_PATH . '/vendor/freemius/wordpress-sdk/start.php';
        $social_fs = fs_dynamic_init( [
            'id'              => '10702',
            'slug'            => 'social-lite',
            'premium_slug'    => 'social-pro',
            'type'            => 'plugin',
            'public_key'      => 'pk_b8bb9e62381f312b76f0633cd602a',
            'is_premium'      => false,
            'premium_suffix'  => 'Pro',
            'has_addons'      => false,
            'has_paid_plans'  => true,
            'has_affiliation' => 'selected',
            'menu'            => [
            'slug'        => 'social-lite-root',
            'first-path'  => 'admin.php?page=social-lite-root',
            'contact'     => false,
            'support'     => false,
            'affiliation' => false,
        ],
            'is_live'         => true,
        ] );
        return $social_fs;
    }
    
    social_fs();
    social_fs()->add_filter(
        'show_admin_notice',
        function ( $show, $msg ) {
        if ( 'affiliate_program' == $msg['id'] || 'connect_account' == $msg['id'] || 'trial_promotion' == $msg['id'] || 'activation_pending' == $msg['id'] && strpos( get_current_screen()->base, SOCIAL_LITE_SLUG ) !== false || 'premium_activated' == $msg['id'] ) {
            return false;
        }
        return $show;
    },
        10,
        2
    );
    social_fs()->add_filter(
        'connect_message',
        function () {
        return '<img class="object-contain border-none rounded-2xl" src="' . trailingslashit( plugins_url( 'public', SOCIAL_LITE_FILE ) ) . 'images/logos/logo--optin.png"/>' . '<h1>' . __( 'Help us make Social better.', SOCIAL_LITE_TD ) . '</h1>' . '<p>' . __( 'Never miss an important update – opt-in to our security and feature updates notifications, and non-sensitive diagnostic tracking.', SOCIAL_LITE_TD ) . '</p>';
    },
        10,
        6
    );
    social_fs()->add_filter(
        'connect-message_on-premium',
        function () {
        return '<img class="object-contain border-none rounded-2xl" src="' . trailingslashit( plugins_url( 'public', SOCIAL_LITE_FILE ) ) . 'images/logos/logo--optin.png"/>' . '<h1>' . __( 'Welcome to Social', SOCIAL_LITE_TD ) . '</h1>' . '<p>' . __( 'To get started, please enter your license key:', SOCIAL_LITE_TD ) . '</p>';
    },
        10,
        6
    );
    social_fs()->add_filter( 'connect/after', function () {
        echo  '<div class="social-opt-in-right float-right flex items-center justify-center h-screen">
                        <img class="object-contain border-none" draggable="false" src="' . trailingslashit( plugins_url( 'public', SOCIAL_LITE_FILE ) ) . 'images/guide/guide--optin.gif"/>
                  </div>' ;
    } );
    do_action( 'social_fs_loaded' );
}
