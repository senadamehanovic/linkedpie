<?php

namespace linkinbio\Base\Helpers;

class SettingsUtil
{
    protected $namespace;
    protected $name;
    protected $settings = [];
    public function __construct($namespace, $name = null)
    {
        $this->namespace = $namespace;
        $this->name = $name ?? $namespace;
    }
    public function enqueue() : self
    {
        add_action('admin_init', function () {
            $this->init_settings();
        });
        $this->enqueuestyles();
        return $this;
    }
    public function enqueueStyles() : self
    {
        add_action('admin_enqueue_scripts', function ($hook_suffix) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            wp_enqueue_media();
        });
        return $this;
    }
    public function init_settings()
    {
        $locs = get_nav_menu_locations();
        //$menu = wp_get_nav_menu_object( $locs[$loc] );
        //	    var_dump($locs); exit();
        $settingspage = $this->namespace;
        $sectionname = $this->namespace . '_general';
        add_settings_section($sectionname, $this->name, false, $settingspage);
        foreach ($this->settings as $slug => $v) {
            $full_slug = $this->namespace . '_' . $slug;
            $args = [];
            switch ($v->type) {
                case 'color':
                    $args['type'] = 'string';
                    break;
                default:
                    $args['type'] = $v->type;
            }
            $args['sanitize_callback'] = function ($input) use($v) {
                $this->validate($v, $input);
                return $input;
            };
            register_setting($settingspage, $full_slug, $args);
            add_settings_field($full_slug, $v->name, function ($args) {
                $this->show_setting($args);
            }, $settingspage, $sectionname, ['slug' => $slug]);
        }
    }
    protected function show_setting($args)
    {
        $slug = $args['slug'];
        $full_slug = $this->namespace . '_' . $slug;
        $s = $this->settings[$slug];
        $value = esc_textarea($this->get_setting($slug));
        $input_template = "<input type='%s' id='{$full_slug}' name='{$full_slug}' value='{$value}' %s >";
        switch ($s->type) {
            case 'page':
                $pages = get_pages();
                echo "<select id='{$full_slug}' name='{$full_slug}' onchange='(function(){document.getElementById(\"link_{$full_slug}\").style.display = \"none\";})()'>";
                echo "<option value=''" . selected($value, '') . ">" . __("Disabled", 'linkinbio') . "</option>";
                foreach ($pages as $page) {
                    echo "<option value='{$page->ID}'" . selected($value, $page->ID) . ">{$page->post_title}</option>";
                }
                echo "</select>";
                $link = get_permalink($value);
                if ($link !== false) {
                    echo " <a id=\"link_{$full_slug}\" href=\"{$link}\" target=\"_blank\">view page</a>";
                }
                break;
            case 'string':
                echo sprintf($input_template, $s->type, "size='50'");
                break;
            case 'url':
                echo sprintf($input_template, $s->type, "size='50' placeholder='https://'");
                break;
            case 'integer':
                echo sprintf($input_template, 'number', '');
                break;
            case 'color':
                echo sprintf($input_template, 'text', "class='eyeseet-color-picker' " . (!empty(@$s->default) ? "data-default-color='" . $s->default . "'" : ''));
                break;
            case 'textarea':
            case 'text':
                echo "<textarea id='{$full_slug}' name='{$full_slug}'>{$value}</textarea>";
                break;
            case 'image':
                echo "<div>\r\n                            <img class='eyeseet-image' src='{$value}'>\r\n                            <input class='eyeseet-input-image' type='hidden' id='{$full_slug}' name='{$full_slug}' value='{$value}'>\r\n                            <div>\r\n                                <button type='submit' class='eyeseet-upload-image'>" . sprintf(__('Select %s'), $s->name) . "</button>\r\n                                <button type='submit' class='eyeseet-remove-image'>&times;</button>\r\n                            </div>\r\n                        <div>";
                break;
            default:
                echo "{$slug} ({$s->type}): {$value}";
        }
    }
    public function showForm() : self
    {
        echo "<form class='eyeseet-form' action='options.php' method='post'>";
        settings_errors();
        settings_fields($this->namespace);
        do_settings_sections($this->namespace);
        submit_button(__('Update', 'linkinbio'));
        echo "</form>";
        return $this;
    }
    public function add_setting($slug, $name, $type, $optional = false, $default = null) : self
    {
        $s = new \stdClass();
        $s->slug = $slug;
        $s->name = $name;
        $s->type = $type;
        $s->optional = $optional;
        $s->default = $default;
        $this->settings[$slug] = $s;
        return $this;
    }
    /**
     * Checks if the value is in correct format, adds error if this is not the case
     * @param object $setting the settings definition
     * @param mixed $value the submitted value, passed by reference
     */
    protected function validate($setting, &$value)
    {
        if ($setting->optional === true && empty($value)) {
            return;
        }
        $full_slug = $this->namespace . '_' . $setting->slug;
        $format_error = function ($error) use($setting) {
            return sprintf($setting->name . ': %s', $error);
        };
        switch ($setting->type) {
            case 'color':
                if (empty(sanitize_hex_color($value))) {
                    add_settings_error($full_slug, $full_slug, $format_error(__('Invalid Hex Color', 'linkinbio')));
                }
                break;
            case 'page':
            case 'integer':
                if ($value !== 0 && empty(intval($value))) {
                    add_settings_error($full_slug, $full_slug, $format_error($setting->type == 'page' ? __('Invalid Page Selected', 'linkinbio') : __('Invalid Number', 'linkinbio')));
                }
                break;
            case 'url':
            case 'image':
                // check $error below when adding types here
                if (!wp_http_validate_url($value)) {
                    $error = $setting->type == 'url' ? __('Invalid url', 'linkinbio') : __('Invalid image', 'linkinbio');
                    add_settings_error($full_slug, $full_slug, $format_error($error));
                }
                break;
        }
    }
    public function get_setting($slug, $default = "")
    {
        $option = get_option($this->namespace . '_' . $slug);
        return $option !== false ? $option : $this->settings[$slug]->default ?? $default;
    }
    public function set_setting_val($slug, $val)
    {
        if ($this->get_setting($slug, false) === false) {
            add_option($this->namespace . '_' . $slug, $val);
        } else {
            update_option($this->namespace . '_' . $slug, $val);
        }
    }
}