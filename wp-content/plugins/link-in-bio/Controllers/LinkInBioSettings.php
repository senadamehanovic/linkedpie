<?php

namespace linkinbio\Controllers;

use linkinbio\Base\Controllers\AdminSettingsController;
use WP_Post;
class LinkInBioSettings extends AdminSettingsController
{
    protected function __construct()
    {
        parent::__construct('linkinbio', __('Settings', 'linkinbio'), 'edit.php?post_type=linkinbio');
        $this->title = "Link In Bio";
        $this->add_setting('endpoint', __('Page', 'linkinbio'), 'page', true);
        $this->add_setting('thumb', __('Optional Logo', 'linkinbio'), 'image', true);
        $this->add_setting('thumb_size', __('Logo size (px)', 'linkinbio'), 'integer', false, 96);
        $this->add_setting('name', __('Optional Name', 'linkinbio'), 'string', true);
        $this->add_setting('name_url', __('Optional URL', 'linkinbio'), 'url', true);
        $this->add_setting('bg_color', __('Background Color', 'linkinbio'), 'color', false, '#fff');
        $this->add_setting('btn_color', __('Button Color', 'linkinbio'), 'color', false, '#000');
        $this->add_setting('btn_hover_color', __('Button Hover Color', 'linkinbio'), 'color', false, '#000');
        $this->add_setting('link_color', __('Text Color', 'linkinbio'), 'color', false, '#fff');
        $this->add_setting('link_hover_color', __('Text Hover Color', 'linkinbio'), 'color', false, '#fff');
        add_filter('display_post_states', array($this, 'add_display_post_states'), 10, 2);
    }
    /**
     * Add a post display state for special page in the page list table.
     *
     * @param array   $post_states An array of post display states.
     * @param WP_Post $post        The current post object.
     */
    public function add_display_post_states($post_states, WP_POST $post)
    {
        if ($this->get_setting('endpoint') == $post->ID) {
            $post_states['linkinbio_page'] = __('Link In Bio Page', 'linkinbio');
        }
        return $post_states;
    }
}