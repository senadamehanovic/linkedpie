<?php

namespace ChadwickMarketing\SocialLite\base\data;

use WP_Query;

// @codeCoverageIgnoreStart
defined('ABSPATH') or die('No script kiddies please!'); // Avoid direct file request
// @codeCoverageIgnoreEnd

/**
 * Manage bio link data.
 */

class BioLinkData {


    private $bioLinks;

    /**
     * C'tor.
     */
    public function __construct() {

        $this->bioLinks = get_posts([
            'post_type' => 'cms-landingpages',
            'posts_per_page' => 1,
            'orderby' => 'date',
            'order' => 'DESC'
        ]);

    }

    /**
     * Check if bio link is available.
     *
     * @return bool
     */
    public function isBioLinkAvailable() {
        return count($this->bioLinks) > 0;
    }


    /**
     * Get bio link data.
     *
     * @return array
     */
    public function getBioLinkData() {

       if (!$this->isBioLinkAvailable()) {
           return [];
       }

       $bioLinkData = get_post_meta($this->bioLinks[0]->ID, 'cms-landingpage-data', true);

       return $bioLinkData;
    }

    /**
     * Get bio link data for the front end.
     *
     * @return array
     */
    public function getBioLinkDataForFrontEnd() {

        $bioLinkData = $this->getBioLinkData();

        foreach ($bioLinkData['content'] as $key => $item) {

            // Get edit action key.
            $editActionKey = current(array_filter(array_keys($item['content']), function ($item) {
                return strpos($item, 'Edit') !== false;
            }));

            // Remove every property with __private__ prefix.
            if (isset($item['content'][$editActionKey])) {
               $bioLinkData['content'][$key]['content'][$editActionKey] = array_filter($bioLinkData['content'][$key]['content'][$editActionKey], function ($key) {
                    return strpos($key, '__private__') === false;
                }, ARRAY_FILTER_USE_KEY);
            }

            // Remove clicks from the front end.
            if (isset($item['clicks'])) {
                unset($bioLinkData['content'][$key]['clicks']);
            }
        }

        return $bioLinkData;

    }




    /**
     * Get bio link analytics.
     *
     *
     * @return array
     */
    public function getBioLinkAnalyticsData() {

        $analyticsData = get_post_meta($this->bioLinks[0]->ID, 'cms-landingpage-analytics', true);

        if (empty($analyticsData) || !isset($analyticsData['page']) || !isset($analyticsData['content']) ) {
            $analyticsData = [
                'page' => [],
                'content' => []
            ];

        }

        return $analyticsData;

    }

    /**
     * Generate a slug.
     *
     * @param string $desiredSlug
     */

    public function generateBioLinkSlug($desiredSlug) {

        global $wpdb;

        // check if slug already exists
        $slug_exists = $wpdb->get_var( $wpdb->prepare( "SELECT count(post_title) FROM $wpdb->posts WHERE post_name = '%s'", $desiredSlug ) );

        // if slug exists, add a number to the end of the slug
        $slug = $slug_exists < 1 ? $desiredSlug : $desiredSlug . '-' . gmdate('YmdHis');

        // return the slug
        return $slug;

    }

     /**
     * Check if user is upgrading from a previous version.
     *
     * @return bool
     */
    public function isUpgrading() {

        // Because this setting was set on all new installs of the plugin until version 1.1.0, we can use it to verify an old install.
        return get_option('chadwickmarketingsocial_auth_key') !== false;

    }

    /**
     * Update the slug of the bio link.
     *
     * @param int $bioLinkId
     * @param string $slug
     *
     * return string $slug
     */

    public function updateBioLinkSlug($bioLinkId, $slug) {

           // generate a slug
          $new_slug = $this->generateBioLinkSlug($slug);

          // update post slug
          wp_update_post([
              'ID' => $bioLinkId,
              'post_name' => $new_slug
          ]);

          return $new_slug;

    }


    /**
     * Update bio link data.
     *
     * @param array $data
     * @param int $bioLinkId Optional.
     *
     */

    public function updateBioLinkData($data, $id = null) {

        if (self::isBioLinkAvailable()) {

            if (isset($this->getBioLinkData()['meta']['slug'])) {

               // check if data meta slug has changed
                if (strtolower($this->getBioLinkData()['meta']['slug']) !== strtolower($data['meta']['slug'])) {

                    // update post meta slug
                    $data['meta']['slug'] = $this->updateBioLinkSlug(isset($id) ? $id : $this->bioLinks[0]->ID, $data['meta']['slug']);

                }

            }

            // remove action current for each item in the date content array
            foreach ($data['content'] as $key => $value) {

                // set action active to false
                $data['content'][$key]['action']['active'] = false;

                // unset action current
                unset($data['content'][$key]['action']['current']);

            }

        }

        update_post_meta(isset($id) ? $id : $this->bioLinks[0]->ID, 'cms-landingpage-data', $data);

    }




    /**
     * Update bio link analytics data.
     *
     * @param array $data
     */
    public function updateBioLinkAnalyticsData($data, $linkId = null) {

        if (!$this->isBioLinkAvailable()) {
            return;
        }

        $analyticsData = $this->getBioLinkAnalyticsData();

        if ( empty($linkId) ) {

            $analyticsData['page'] = array_unique(array_merge( $analyticsData['page'], $data ), SORT_REGULAR);

        } else {

            $analyticsData['content'][ $linkId ] = $data;

        }

        update_post_meta($this->bioLinks[0]->ID, 'cms-landingpage-analytics', $analyticsData);

    }


    /**
     * Function to migrate link data from previous versions of the plugin.
     *
     * @param int $bioLinkId
     *
     * @return array
     */
    public function migrateBioLinkLinkData($bioLinkId) {

        // Get all links
        $oldBioLinkLinks = new WP_Query([
            'posts_per_page' => -1,
            'post_type'      => 'cms-cards-shortened',
            'meta_key'       => 'cm-card-position',
            'orderby'        => 'meta_value_num',
            'order'          => 'ASC',
            'meta_query'     => [[
                'key'   => 'cm-card-page-id',
                'value' => $bioLinkId
           ]]
        ]);

        $migratedBioLinkLinks = [];

        if ( $oldBioLinkLinks->have_posts() ) {

            while ( $oldBioLinkLinks->have_posts() ) {

                $oldBioLinkLinks->the_post();

                $oldBioLinkLinkId = get_the_ID();

                $migratedBioLinkLinks[] = [
                    'id' => wp_generate_uuid4(),
                    'type' => 'Link',
                    'premium' => false,
                    'visible' => filter_var(get_post_meta( $oldBioLinkLinkId, 'cm-card-published', true ), FILTER_VALIDATE_BOOLEAN),
                    'title' => get_the_title(),
                    'error' => [
                        'error' => false,
                        'message' => ''
                    ],
                    'action' => [
                        'active' => false,
                        'current' => '',
                    ],
                    'content' => [
                        'link' => get_post_field( 'post_content', get_post_meta( $oldBioLinkLinkId, 'cm-card-link', true ) ),
                        'description' => get_post_meta( $oldBioLinkLinkId, 'cm-card-desc', true ),
                        'AddThumbnail' => [
                            'type' => !empty(get_post_meta( $oldBioLinkLinkId, 'cm-card-icon', true )) ? 'icon' : 'image',
							'url' => !empty(get_post_meta( $oldBioLinkLinkId, 'cm-card-icon', true )) ? get_post_meta( $oldBioLinkLinkId, 'cm-card-icon', true ) : get_post_meta( $oldBioLinkLinkId, 'cm-card-icon-image', true ),
                        ]
                    ]
                ];

            }

        }

        return $migratedBioLinkLinks;

    }


    /**
     * Function to migrate data from previous versions of the plugin.
     *
     * @param int $bioLinkId
     *
     * @return array $bioLinkData
     */
    public function migrateBioLinkData($bioLinkId) {

        // Get old meta data (e.g. name, slug, etc.)
        $oldBioLinkMeta = get_post_meta($bioLinkId, 'cm-landingpage-meta', true);

        // Get old content data (e.g. theme, avatar etc.)
        $oldBioLinkContent = get_post_meta($bioLinkId, 'cm-landingpage', true);

        // Migrate old data to new data structure
        return [
            'meta' => [
                'name' => $oldBioLinkMeta['title'],
                'index' => $oldBioLinkMeta['seo']['index'],
                'slug' => get_post_field('post_name', $bioLinkId),
                'homepage' => false,
                'author' => wp_get_current_user()->ID,
                'created' => gmdate('Y-m-d'),
            ],
            'options' => [
                'avatar' => isset($oldBioLinkContent['appearance']['avatar']) ? $oldBioLinkContent['appearance']['avatar'] : $oldBioLinkContent['connections']['instagram']['avatar'],
                'title' => isset($oldBioLinkContent['content']['profileTitle']) ? $oldBioLinkContent['content']['profileTitle'] : (!empty($landingpage_data['connections']['instagram']['connected']) ? $oldBioLinkContent['connections']['instagram']['username'] : get_bloginfo('name')),
                'description' => $oldBioLinkContent['content']['title'],
                'socialIcons' => [],
                'footerLinks' => array_map(
                    function($link) use($oldBioLinkContent) {
                            return [
                                'id' => wp_generate_uuid4(),
                                'title' => $link['text'],
                                'url' => preg_replace('(^https?://)', '', $link['url'] ), // Put the URL in the right format for Social version >= 1.1.0.
                                'visible' => isset($oldBioLinkContent['content']['links']['shown']) ? $oldBioLinkContent['content']['links']['shown'] : true
                            ];
                    },
                    array_filter(
                        $oldBioLinkContent['content']['links'],
                        function($link) {
                            return isset($link['text']) // Make sure we only get the footer links that have a title.
                                && !empty($link['text']);
                        }
                    )
                ),
                'theme' => ['id' => strtolower($oldBioLinkContent['appearance']['theme'])],
                'badge' => true,
            ],
            'content' => self::migrateBioLinkLinkData($bioLinkId),
        ];

    }


    /**
     * Add initial bio link data. Gets called when the plugin is activated.
     *
     * Also handles migration from previous versions of the plugin.
     *
     * @return void;
     */
    public function addInitialBioLinkData() {

        // Check if the user is upgrading from version 1.0.9 or lower.
        $isUpgrading = self::isUpgrading() && self::isBioLinkAvailable();

        // If the user isn't upgrading, we need to generate a new slug for the bio link.
        if (!$isUpgrading) {

            $bioLinkSlug = self::generateBioLinkSlug('links');

        }

        // If the user is upgrading, get the id of the old bio link, otherwise create a new one.
        $bioLinkId = $isUpgrading ? $this->bioLinks[0]->ID :
            wp_insert_post([
				'post_title' => 'Links',
				'post_name' => $bioLinkSlug,
				'post_content' => '',
				'post_status' => 'publish',
				'post_type' => 'cms-landingpages',
				'post_author' => 1,
            ]);

        // If the user is upgrading, get the old data, otherwise use the default data.
        $bioLinkData = $isUpgrading ? self::migrateBioLinkData($bioLinkId) :
            [
				'meta' => [
					'name' => 'Links',
					'index' => true,
					'slug' => $bioLinkSlug,
					'homepage' => false,
					'author' => wp_get_current_user()->ID,
					'created' => gmdate('Y-m-d'),
				],
				'options' => [
					'avatar' => '',
					'title' => get_bloginfo('name'),
					'description' => get_bloginfo('name'),
					'socialIcons' => [],
					'footerLinks' =>  [],
					'theme' => ['id' => 'light'],
					'badge' => true,
				],
				'content' => [],
			];

        self::updateBioLinkData($bioLinkData, $bioLinkId);

    }

    /**
     * New instance.
     *
     */
    public static function instance() {
        return new BioLinkData();
    }


}