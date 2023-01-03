<?php


namespace ChadwickMarketing\SocialLite\view\bio;

use ChadwickMarketing\SocialLite\base\analytics\BioLinkAnalytics;
use ChadwickMarketing\SocialLite\base\data\BioLinkData;

use WP_Query;

class BioLink {

	public function register() {

    add_filter('template_include', [
        $this,
        'renderSocialTemplate'
    ]);
    add_filter('post_type_link', [
        $this,
        'removeCPTLandingpageSlug'
    ], 10, 3);
    add_filter('pre_get_posts', [
        $this,
        'parseRequest'
    ]);
    add_filter( 'pre_handle_404',
        function ($value, $query ) {
            return $this->parseRequestOnError( $value, $query );
        },
    10, 2 );

}



/**
* If on the single template 'links-shortened', return the single template for shortened links
*
* @return $single_template for shortened links
* @since 1.0.0
*/


public function renderSocialTemplate($single_template ) {

    global $post;

    if (BioLinkData::instance()->isBioLinkAvailable()) {

        if (BioLinkData::instance()->getBioLinkData()['meta']['homepage']) {

            if (is_front_page() || is_home()) {

                $single_template = SOCIAL_LITE_INC . 'view/bio/BioLinkTemplate.php';

            }

        }

        if ((isset($post->post_type) && $post->post_type === 'cms-landingpages') && !is_search()) {

                if (!BioLinkData::instance()->getBioLinkData()['meta']['homepage']) {

                    $single_template = SOCIAL_LITE_INC . 'view/bio/BioLinkTemplate.php';

                } else {

                    wp_redirect( home_url());

                    exit;

                }

        }

    }

    if (strpos($single_template, SOCIAL_LITE_INC) !== false) {

        BioLinkAnalytics::instance()->handleAnalyticsAction();

        if (!BioLinkData::instance()->getBioLinkData()['meta']['index']) {

            add_filter( 'wp_robots', 'wp_robots_no_robots' );

        }

    }

    return $single_template;

}





/**
* Remove the slug thats automatically being generated for cpts, in this case 'links-shortened' or 'landingpage'
*
* @return $post_link
* @since 1.0.0
*/

public function removeCPTLandingpageSlug($post_link, $post, $leavename) {

    if ( 'cms-landingpages' != $post->post_type || 'publish' != $post->post_status ) {
        return $post_link;
    }

    return str_replace( '/' . $post->post_type . '/', '/', $post_link );

}

/**
* Parse request for shortened links and link in bio pages
*
* @since 1.0.0
*/


public function parseRequest($query) {

    if ( ! $query->is_main_query() ||  ! isset( $query->query['page']) || isset( $query->query['post_type']) ) {
        return;
    }

    if ( ! empty( $query->query['name']  ) ) {

      $query->set('post_type', [ 'post', 'page', 'cms-links-shortened' ] );

    } elseif ( ! empty( $query->query['pagename'] ) && false === strpos( $query->query['pagename'], '/' ) ) {

    if (get_post_type(get_page_by_title($query->query['pagename'], OBJECT, ['cms-links-shortened', 'cms-landingpages'])->ID ?? null) === 'cms-links-shortened' ) {

        $query->set( 'post_type', [ 'post', 'page', 'cms-links-shortened' ] );
        $query->set( 'name', $query->query['pagename'] );

    } else {

         $query->set( 'post_type', [ 'post', 'page', 'cms-landingpages' ] );
         $query->set( 'name', $query->query['pagename'] );

    }

    }

}



/**
* Handle 404
*
* This method runs after a page is not found in the database, but before a page is returned as a 404.
* These cases are handled in this filter callback, that runs on the 'pre_handle_404' filter.
*
* @since 1.0.0
*/

public function parseRequestOnError($value, $query) {

    global $wp_query;

    if ( $value ) {
        return $value;
    }

    if (! $query->is_main_query() || ! empty( $query->posts ) || ! empty( $query->tax_query->table_aliases ) ) {

        return false;

    }

    if ( ! empty($query->query['name']) ) {

        $new_query = new WP_Query( [
            'post_type' => ['cms-landingpages'],
            'name' => $query->query['name'],
        ] );

    } else {

        $new_query = new WP_Query( [
            'post_type' => ['cms-landingpages'],
            'name' => strtolower(preg_replace('/[\W\s\/]+/', '-', ltrim($_SERVER['REQUEST_URI'], '/'))),
        ] );

    }

    if ( ! empty( $new_query->posts ) ) {
        $wp_query = $new_query;
    }

    return false;
}


  /**
     * New instance.
     */
    public static function instance() {
        return new BioLink();
    }


}