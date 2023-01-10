<?php
namespace WPM;

class Influencer_Links 
{
    public static $instance;

    public static $social_fields = [
        'facebook' => 'wpmll_opt_url-facebook',
        'instagram' => 'wpmll_opt_url-instagram',
        'linkedin' => 'wpmll_opt_url-linkedin',
        'snapchat' => 'wpmll_opt_url-snapchat',
        'tiktok' => 'wpmll_opt_url-tiktok',
        'twitter' => 'wpmll_opt_url-twitter',
    ];

    public static $social_brands = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'linkedin' => 'LinkedIn',
        'snapchat' => 'Snapchat',
        'tiktok' => 'TikTok',
        'twitter' => 'Twitter',
    ];

    public static function init()
    {
        null === self::$instance && self::$instance = new self();
        return self::$instance;
    }

    private function __construct()
    {
        \add_action( 'init', [ $this, 'register' ], 10 );
        \add_filter( 'template_include', [ $this, 'template_include' ], 10 );
        \add_filter( 'admin_notices', [ $this, 'admin_notices' ], 999 );
        \add_filter( 'wp_enqueue_scripts', [ $this, 'wp_enqueue_scripts' ], 9999 );
    }

    public function wp_enqueue_scripts()
    {
        // should we disable CSS?
        if ( \get_field( 'wpmll_opt_disable_css' ) ) 
        {
            $wp_styles = \wp_styles();
            foreach ( $wp_styles->registered as $wp_style ) 
            {
                if ( 'wpm-sil' !== \substr( $wp_style->handle, 0, 7 ) )
                {
                    wp_deregister_style( $wp_style->handle );
                }
            }
        }
        if ( \get_field( 'wpmll_activate_page' ) )
        {
            \wp_enqueue_style( 'wpm-sil', 'https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;0,700;1,300;1,400;1,500;1,700&display=swap', false, false, 'all' );
            \wp_enqueue_style( 'wpm-sil-style', \plugins_url( '/assets/style.css', \dirname( __FILE__ ) ), false, false, 'all' );
        }
    }

    public function admin_notices()
    {
        $screen = \get_current_screen();
        if ( 'edit-wpm-landing-link' == $screen->id && !class_exists( 'Simple_Page_Ordering' ) )
        {
            echo '<div class="notice" style="border-color:#0682B8;"><p>Did you want to re-order these links with a drag and drop UI? Try <a href="http://10up.com/plugins/simple-page-ordering-wordpress/" target="_blank">Simple Page Ordering</a> by <a href="https://10up.com" target="_blank">10up</a></p></div>';
        }
    }

    public function register()
    {
        \add_post_type_support( 'page', 'post-thumbnails' );
        
        \add_image_size( 'wpm-ll-thumb', 300, 300, true );

        \register_post_type( 'wpm-landing-link', [
            'labels' => [
                'singular_name' => 'Influencer Link',
                'name' => 'Influencer  Links',
                'add_new' => 'Add New Link',
                'add_new_item' => 'Add New Link',
                'edit_item' => 'Edit Link',
                'search_items' => 'Search Links',
            ],
            'menu_icon' => 'dashicons-external',
            'public' => false,
            'hierarchical' => true,
            'supports' => [ 'title' ],
            'show_ui' => true,
            'show_admin_ui' => true
        ]);
    }

    public function template_include( $file )
    {
        if ( \get_field( 'wpmll_activate_page' ) )
        {
            return __DIR__ . '/../template-landing-links.php';
        }

        return $file;
    }

    public static function get_social_links( $post_id = false )
    {
        if ( !$post_id ) $post_id = \get_the_ID();

        foreach ( self::$social_fields as $network => $key )
        {
            if ( $value = $value = \get_field( $key ) )
                $social[ $network ] = $value;
        }

        return $social;
    }

    public static function get_social_label( $network )
    {
        return self::$social_brands[ $network ] ?? false;
    }

}

Influencer_Links::init();