<?php

namespace linkinbio\Controllers;

use linkinbio\Base\Controllers\AbstractController;
class LinkInBioPage extends AbstractController
{
    protected function __construct()
    {
        parent::__construct();
        add_filter('template_include', [$this, 'template_include']);
    }
    public function template_include(string $template)
    {
        $link_page_id = LinkInBioSettings::instance()->get_setting('endpoint');
        global $post;
        if (@$post->ID != null && @$post->ID == $link_page_id) {
            $template = plugin_dir_path(__FILE__) . '../template/linkinbio.php';
            $this->register_style('link-in-bio', plugin_dir_url(__FILE__) . '../css/linkinbio.css');
        }
        return $template;
    }
}