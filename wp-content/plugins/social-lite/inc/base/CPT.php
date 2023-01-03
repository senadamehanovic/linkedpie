<?php

namespace ChadwickMarketing\SocialLite\base;

defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request

/**
 * Manage custom post types.
 */
class CPT {

    /**
     * Add new post type for link in bio pages
     */
    public function add_cpt() {
        register_post_type('cms-landingpages',
        [
            'labels'      => [
                'name'          => __('Bio Links', SOCIAL_LITE_TD),
            ],
            'show_ui'             => false,
            'show_in_menu'        => false,
            'show_in_nav_menus'   => false,
            'show_in_admin_bar'   => false,
			'public'      => true,
			'has_archive' => false,
            'capabilities' => [
                'edit_post' => 'manage_options',
                'edit_posts' => 'manage_options',
                'edit_others_posts' => 'manage_options',
                'publish_posts' => 'manage_options',
                'read_post' => 'manage_options',
                'read_private_posts' => 'manage_options',
                'delete_post' => 'manage_options',
            ],
        ]
        );
    }



     /**
     * New instance.
     */
    public static function instance() {
        return new CPT();
    }



}