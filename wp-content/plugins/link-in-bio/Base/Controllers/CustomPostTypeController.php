<?php

namespace linkinbio\Base\Controllers;

/**
 * Base class so for easy implementation of custom post types (by inheriting this class).
 * 
 * Maps the different wordpress hooks to protected functions which can be overidden.
 * @author EyeSeeT
 *
 */
abstract class CustomPostTypeController extends AbstractController
{
    private $on_save_function;
    private $filter_post_saved;
    protected $post_type_slugs = [];
    protected function __construct(array $post_type_slugs)
    {
        parent::__construct();
        $this->post_type_slugs = $post_type_slugs;
        // call register of super class
        add_action('init', function () {
            foreach ($this->post_type_slugs as $slug) {
                $this->register_post_type($slug);
            }
        });
        // add filters for save
        $this->init_save_functions();
        foreach ($this->post_type_slugs as $slug) {
            add_action('save_post_' . $slug, $this->on_save_function, 2, 2);
        }
        add_filter('wp_insert_post_data', $this->filter_post_saved, 2, 2);
        add_action('wp_trash_post', function ($post_id) {
            $this->on_trash_post($post_id);
        });
        add_action('delete_post', function ($post_id, \WP_POST $post) {
            $this->on_delete_post($post_id, $post);
        }, 2, 2);
        add_filter('template_include', function ($template) {
            foreach ($this->post_type_slugs as $slug) {
                if (is_post_type_archive($slug)) {
                    return $this->get_custom_archive_template_file($slug, $template);
                }
            }
            return $template;
        });
        register_activation_hook(__FILE__, [self::class, 'set_should_flush_rewrite_rules']);
        add_action('admin_init', [self::class, 'check_flush_rewrite_rules']);
    }
    public static function set_should_flush_rewrite_rules($should_flush = true)
    {
        set_transient('eyeseet_flush', $should_flush);
    }
    /**
     * Check whether rewrite rules should be flushed, if so calls flush_rewrite_rules
     */
    public static function check_flush_rewrite_rules()
    {
        if (get_transient('eyeseet_flush')) {
            self::set_should_flush_rewrite_rules(false);
            flush_rewrite_rules();
        }
    }
    private function init_save_functions()
    {
        // TODO: Replace these with RecursionProtectedAction and RecursionProtectedFilter
        $this->on_save_function = function ($post_id, \WP_Post $post) {
            if (!current_user_can('edit_post', $post_id) || !in_array($post->post_type, $this->post_type_slugs) || $post->post_title == 'Auto Draft') {
                return $post_id;
            }
            // remove in order to prevent unlimited recursion, add afterwards
            remove_action('save_post_' . $post->post_type, $this->on_save_function, 2, 2);
            $this->on_save_post($post_id, $post);
            add_action('save_post_' . $post->post_type, $this->on_save_function, 2, 2);
        };
        $this->filter_post_saved = function ($data, $post_arr) {
            if (!in_array($data['post_type'], $this->post_type_slugs)) {
                return $data;
            }
            // remove in order to prevent unlimited recursion, add afterwards
            remove_filter('wp_insert_post_data', $this->filter_post_saved, 2, 2);
            $r = $this->filter_save_post($data, get_post($post_arr['ID']));
            add_filter('wp_insert_post_data', $this->filter_post_saved, 2, 2);
            return $r;
        };
    }
    protected abstract function register_post_type($type);
    protected function on_save_post($post_id, \WP_Post $post)
    {
    }
    protected function filter_save_post(array $new_data, ?\WP_POST $old_data) : array
    {
        return $new_data;
    }
    protected function on_trash_post($post_id)
    {
    }
    protected function on_delete_post($post_id, \WP_Post $post)
    {
    }
    protected function get_custom_archive_template_file($type, $template)
    {
        return $template;
    }
}