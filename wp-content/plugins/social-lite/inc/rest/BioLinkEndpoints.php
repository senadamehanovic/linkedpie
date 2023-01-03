<?php

namespace ChadwickMarketing\SocialLite\rest;

use  ChadwickMarketing\SocialLite\base\analytics\BioLinkAnalytics ;
use  ChadwickMarketing\Utils\Service ;
use  ChadwickMarketing\SocialLite\base\UtilsProvider ;
use  ChadwickMarketing\SocialLite\base\data\BioLinkData ;
use  ChadwickMarketing\SocialLite\base\forms\BioLinkForms ;
use  ChadwickMarketing\SocialLite\base\woocommerce\BioLinkWooCommerce ;
use  WP_REST_Request ;
use  WP_REST_Response ;
// @codeCoverageIgnoreStart
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// Avoid direct file request
// @codeCoverageIgnoreEnd
/**
 * Create rest service for bio link data.
 *
 * @codeCoverageIgnore Example implementations gets deleted the most time after plugin creation!
 */
class BioLinkEndpoints
{
    use  UtilsProvider ;
    /**
     * Register endpoints.
     */
    public function rest_api_init()
    {
        $namespace = Service::getNamespace( $this );
        // Bio link options.
        register_rest_route( $namespace, '/bio-link/options', [
            'methods'             => [ 'POST', 'GET' ],
            'callback'            => [ $this, 'handleBioLinkOptions' ],
            'args'                => [
            'data' => [
            'required'    => false,
            'type'        => 'object',
            'description' => 'The data to push to the bio link.',
        ],
        ],
            'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
        ] );
        // Bio link analytics.
        register_rest_route( $namespace, '/bio-link/analytics', [
            'methods'             => [ 'GET' ],
            'callback'            => [ $this, 'handleBioLinkAnalytics' ],
            'permission_callback' => function () {
            return current_user_can( 'manage_options' );
        },
        ] );
        register_rest_route( $namespace, '/bio-link/analytics', [
            'methods'             => [ 'POST' ],
            'callback'            => [ $this, 'handleBioLinkAnalytics' ],
            'permission_callback' => '__return_true',
        ] );
        // Bio link forms.
        register_rest_route( $namespace, '/bio-link/forms/token', [
            'methods'             => [ 'GET' ],
            'callback'            => [ $this, 'handleBioLinkFormToken' ],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( $namespace, '/bio-link/forms/send', [
            'methods'             => [ 'POST' ],
            'callback'            => [ $this, 'handleBioLinkFormSubmission' ],
            'args'                => [
            'data' => [
            'required'    => true,
            'type'        => 'object',
            'description' => 'The form data.',
        ],
        ],
            'permission_callback' => '__return_true',
        ] );
        // WooCommerce integration.
        register_rest_route( $namespace, '/bio-link/wc/products', [
            'methods'             => [ 'GET', 'POST' ],
            'callback'            => [ $this, 'handleBioLinkWooCommerceProducts' ],
            'permission_callback' => '__return_true',
        ] );
        register_rest_route( $namespace, '/bio-link/wc/categories', [
            'methods'             => [ 'GET', 'POST' ],
            'callback'            => [ $this, 'handleBioLinkWooCommerceCategories' ],
            'permission_callback' => '__return_true',
        ] );
    }
    
    /**
     * Handle bio link WooCommerce products request.
     *
     * @api {get} social-lite/v1/bio-link/wc/products Get WooCommerce Products.
     * @apiHeader {String} X-WP-Nonce
     * @apiName GetWooCommerceProducts
     * @apiGroup BioLinkWooCommerce
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *    {data: []}
     * }
     *
     * @apiVersion 1.1.7
     */
    public function handleBioLinkWooCommerceProducts( WP_REST_Request $request )
    {
        if ( $request->get_method() === 'GET' && !current_user_can( 'manage_options' ) ) {
            return new WP_REST_Response( [
                'data' => [
                'products' => [],
            ],
            ] );
        }
        return new WP_REST_Response( [
            'data' => BioLinkWooCommerce::instance()->getProducts( ( $request->get_method() === 'POST' ? rest_sanitize_array( $request->get_json_params()['ids'] ) : null ) ),
        ] );
    }
    
    /**
     * Handle bio link WooCommerce categories request.
     *
     * @api {get} social-lite/v1/bio-link/wc/categories Get WooCommerce Categories.
     * @apiHeader {String} X-WP-Nonce
     * @apiName GetWooCommerceCategories
     * @apiGroup BioLinkWooCommerce
     *
     * @apiSuccessExample {json} Success-Response:
     *  {
     *     {data: []}
     * }
     *
     * @apiVersion 1.1.7
     */
    public function handleBioLinkWooCommerceCategories( WP_REST_Request $request )
    {
        return new WP_REST_Response( [
            'data' => [
            'categories' => [],
            'products'   => [],
        ],
        ] );
    }
    
    /**
     * Handle bio link form submission.
     *
     * @api {post} social-lite/v1/bio-link/forms/send Send Bio Link Form.
     * @apiHeader {String} X-WP-Nonce
     * @apiName Send
     * @apiGroup BioLinkForms
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {success: true}
     * }
     * @apiVersion 1.1.3
     *
     * */
    public function handleBioLinkFormSubmission( WP_REST_Request $request )
    {
        return new WP_REST_Response( [
            'success' => BioLinkForms::instance()->handleFormSubmission( $request->get_json_params()['data'] ),
        ] );
    }
    
    /**
     * Handle bio form token request.
     *
     * @api {get} social-lite/v1/bio-link/forms/token Get bio link form token.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Token
     * @apiGroup BioLinkForms
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *  data: {
     *  token: 'token'
     *  }
     * }
     * @apiVersion 1.1.3
     */
    public function handleBioLinkFormToken()
    {
        return new WP_REST_Response( [
            'data' => [
            'token' => BioLinkForms::instance()->getToken(),
        ],
        ] );
    }
    
    /**
     * See API docs.
     *
     * @api {get} /social-lite/v1/bio-link/options Get bio link options.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Options
     * @apiGroup BioLink
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {data: success}
     * }
     * @apiVersion 0.1.0
     */
    public function handleBioLinkOptions( WP_REST_Request $request )
    {
        // check if request is get
        if ( $request->get_method() === 'GET' ) {
            return new WP_REST_Response( [
                'data' => BioLinkAnalytics::instance()->supplyLinkAnalyticsData( BioLinkData::instance()->getBioLinkData() ),
            ], 200 );
        }
        // check if request is post
        
        if ( $request->get_method() === 'POST' ) {
            // update the bio link
            BioLinkData::instance()->updateBioLinkData( $request->get_json_params()['data'] );
            // return success response
            return new WP_REST_Response( [
                'data' => 'success',
            ], 200 );
        }
    
    }
    
    /**
     * See API docs.
     *
     * @api {post} /social-lite/v1/bio-link/analytics Send analytics.
     * @apiHeader {string} X-WP-Nonce
     * @apiName Analytics
     * @apiGroup BioLink
     *
     * @apiSuccessExample {json} Success-Response:
     * {
     *     {data: success}
     * }
     * @apiVersion 0.1.0
     */
    public function handleBioLinkAnalytics( WP_REST_Request $request )
    {
        if ( $request->get_method() === 'GET' ) {
            return new WP_REST_Response( [
                'data' => BioLinkAnalytics::instance()->getAnalytics( sanitize_text_field( $request->get_param( 'start' ) ), sanitize_text_field( $request->get_param( 'end' ) ), sanitize_text_field( $request->get_param( 'id' ) ) ),
            ], 200 );
        }
        
        if ( $request->get_method() === 'POST' ) {
            if ( empty($request->get_json_params()['data']['id']) ) {
                return new WP_REST_Response( [
                    'data' => 'No valid id supplied.',
                ], 400 );
            }
            BioLinkAnalytics::instance()->handleAnalyticsAction( sanitize_text_field( $request->get_json_params()['data']['id'] ) );
            return new WP_REST_Response( [
                'data' => 'success',
            ], 200 );
        }
    
    }
    
    /**
     * New instance.
     */
    public static function instance()
    {
        return new BioLinkEndpoints();
    }

}