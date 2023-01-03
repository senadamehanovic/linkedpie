<?php

namespace ChadwickMarketing\SocialLite;

use  ChadwickMarketing\SocialLite\base\UtilsProvider ;
use  ChadwickMarketing\Utils\Assets as UtilsAssets ;
use  ChadwickMarketing\SocialLite\base\data\BioLinkData ;
use  ChadwickMarketing\SocialLite\base\woocommerce\BioLinkWooCommerce ;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Asset management for frontend scripts and styles.
 */
class Assets
{
    use  UtilsProvider ;
    use  UtilsAssets ;
    /**
     * Enqueue scripts and styles depending on the type. This function is called
     * from both admin_enqueue_scripts and wp_enqueue_scripts. You can check the
     * type through the $type parameter. In this function you can include your
     * external libraries from src/public/lib, too.
     *
     * @param string $type The type (see utils Assets constants)
     * @param string $hook_suffix The current admin page
     */
    public function enqueue_scripts_and_styles( $type, $hook_suffix = null )
    {
        // Generally check if an entrypoint should be loaded
        if ( !in_array( $type, [ self::$TYPE_ADMIN, self::$TYPE_FRONTEND ], true ) ) {
            return;
        }
        // Your assets implementation here... See utils Assets for enqueue* methods
        // $useNonMinifiedSources = $this->useNonMinifiedSources(); // Use this variable if you need to differ between minified or non minified sources
        // Our utils package relies on jQuery, but this shouldn't be a problem as the most themes still use jQuery (might be replaced with https://github.com/github/fetch)
        // Enqueue external utils package
        $scriptDeps = $this->enqueueUtils();
        // Enqueue plugin entry points
        
        if ( $type === self::$TYPE_ADMIN ) {
            $handle = $this->enqueueScript( 'admin', 'admin.js', $scriptDeps );
            
            if ( $this->isScreenBase( 'toplevel_page_social-lite-root' ) || $this->isScreenBase( 'social_page_social-lite-root-pricing' ) || $this->isScreenBase( 'social_page_social-lite-root-account' ) ) {
                $this->enqueueStyle( 'admin', 'admin.css' );
                $this->enqueueStyle( 'bio-link', 'bio-link.css' );
                wp_enqueue_media();
            }
        
        } elseif ( $type === self::$TYPE_FRONTEND ) {
            global  $post ;
            if ( BioLinkData::instance()->isBioLinkAvailable() ) {
                
                if ( BioLinkData::instance()->getBioLinkData()['meta']['homepage'] && (is_front_page() || is_home()) || isset( $post->post_type ) && $post->post_type === 'cms-landingpages' && !is_search() ) {
                    foreach ( wp_scripts()->registered as $wp_script ) {
                        // only dequeue if this script isn't from social-lite
                        if ( strpos( $wp_script->src, SOCIAL_LITE_SLUG ) === false ) {
                            wp_dequeue_script( $wp_script->handle );
                        }
                    }
                    foreach ( wp_styles()->registered as $wp_style ) {
                        // only dequeue if this style isn't from social-lite
                        if ( strpos( $wp_style->src, SOCIAL_LITE_SLUG ) === false ) {
                            wp_dequeue_style( $wp_style->handle );
                        }
                    }
                    // dequeue block styles
                    wp_dequeue_style( 'global-styles' );
                    wp_dequeue_style( 'wc-block-style' );
                    wp_dequeue_style( 'wp-block-library' );
                    wp_dequeue_style( 'wp-block-library-theme' );
                    // hide the admin bar
                    add_filter( 'show_admin_bar', '__return_false' );
                    // remove admin bar styles
                    remove_action( 'wp_head', '_admin_bar_bump_cb' );
                    remove_action( 'wp_head', 'wp_generator' );
                    // enqueue our scripts and styles
                    $handle = $this->enqueueScript( 'frontend', 'frontend.js', $scriptDeps );
                    $this->enqueueStyle( 'frontend', 'frontend.css' );
                }
            
            }
        }
        
        // Localize script with server-side variables
        if ( isset( $handle ) ) {
            wp_localize_script( $handle, SOCIAL_LITE_SLUG_CAMELCASE, $this->localizeScript( $type ) );
        }
    }
    
    public function getDependencies()
    {
        $dependencies = [ [
            'id'     => 'woocommerce',
            'active' => BioLinkWooCommerce::instance()->isWooCommerceActive(),
        ] ];
        return $dependencies;
    }
    
    /**
     * Localize the WordPress backend and frontend. If you want to provide URLs to the
     * frontend you have to consider that some JS libraries do not support umlauts
     * in their URI builder. For this you can use utils Assets#getAsciiUrl.
     *
     * Also, if you want to use the options typed in your frontend you should
     * adjust the following file too: src/public/ts/store/option.tsx
     *
     * @param string $context
     * @return array
     */
    public function overrideLocalizeScript( $context )
    {
        
        if ( $context === self::$TYPE_ADMIN ) {
            $optionsBackend = [
                'upgradeURL'       => social_fs()->get_upgrade_url(),
                'supportURL'       => social_fs()->contact_url(),
                'siteURL'          => get_home_url(),
                'isPermalinkPlain' => get_option( 'permalink_structure' ) === '',
                'dependencies'     => $this->getDependencies(),
            ];
            return $optionsBackend;
        } elseif ( $context === self::$TYPE_FRONTEND ) {
            $optionsFrontend = [
                '__INITIAL_STATE__' => BioLinkData::instance()->getBioLinkDataForFrontEnd(),
                'dependencies'      => $this->getDependencies(),
            ];
            return $optionsFrontend;
        }
        
        return [];
    }

}