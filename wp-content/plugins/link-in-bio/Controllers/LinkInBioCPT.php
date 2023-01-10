<?php

namespace linkinbio\Controllers;

use linkinbio\Base\Controllers\CustomPostTypeController;
class LinkInBioCPT extends CustomPostTypeController
{
    const POST_TYPE_SLUG = 'linkinbio';
    const META_URL = 'linkinbio_url';
    public function __construct()
    {
        parent::__construct([static::POST_TYPE_SLUG]);
    }
    protected function register_post_type($type)
    {
        $labels = ['name' => __('Link In Bio', "linkinbio"), 'singular_name' => __('Link In Bio', "linkinbio")];
        register_post_type($type, ['labels' => $labels, 'show_ui' => true, 'supports' => ['title', 'page-attributes'], 'menu_icon' => 'dashicons-admin-links', 'menu_position' => 41, 'register_meta_box_cb' => function () {
            add_meta_box('linkinbio_link', "URL", [$this, 'add_meta_box_url'], null, 'normal', 'high');
        }]);
    }
    public function add_meta_box_url()
    {
        global $post;
        $url = get_post_meta($post->ID, static::META_URL, true);
        ?>
        <p>
        	<label for="linkinbio-url"><?php 
        _e('Link URL', 'linkinbio');
        ?></label>
        	<input type="text" name="url" value="<?php 
        echo @$url;
        ?>" style="width: 100%">
        </p>
        <?php 
    }
    protected function on_save_post($post_id, \WP_Post $post)
    {
        if (isset($_POST['url'])) {
            update_post_meta($post_id, static::META_URL, esc_url_raw($_POST['url']));
        }
    }
}