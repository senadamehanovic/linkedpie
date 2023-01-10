<?php

namespace linkinbio\Base;

use linkinbio\Base\Controllers\AbstractController;
class PluginHelper
{
    /**
     * Registers an autoloader for the wordpress plugin
     * @param string $file must specify the path of a file in the root directory
     * @param string $base_namespace must specify the base namespace for all classes in the plugin, if none is provided the filename of the $file parameter will be use as base
     */
    public static function create_plugin_autoloader($file, $base_namespace = null)
    {
        $GLOBALS['eyeseet'][static::plugin_name($file)] ??= ['path' => plugin_dir_path($file)];
        if ($base_namespace == null) {
            $matches = null;
            preg_match('/([a-z]+)\\.php$/i', $file, $matches);
            $base_namespace = $matches[1];
        }
        $plugin_directory = plugin_dir_path($file);
        spl_autoload_register(function ($class_name) use($plugin_directory, $base_namespace) {
            if (strpos($class_name, $base_namespace . '\\') === 0) {
                // Only do autoload for our plugin files
                $class_name = substr($class_name, strlen($base_namespace . '\\'));
                $class_file = str_replace('\\', DIRECTORY_SEPARATOR, $class_name) . '.php';
                if (!file_exists($plugin_directory . $class_file)) {
                    // not found, check parent class
                    $parent_file = $plugin_directory . substr($class_file, 0, strrpos($class_file, DIRECTORY_SEPARATOR)) . '.php';
                    if (file_exists($parent_file)) {
                        require_once $parent_file;
                        return;
                    }
                }
                require_once $plugin_directory . $class_file;
            }
        });
    }
    public static function register_controllers($file, ...$controllers)
    {
        add_action('plugins_loaded', function () use($file, $controllers) {
            $plugin_name = static::plugin_name($file);
            $matches = [];
            if (preg_match('/\\\\([a-z]+)\\.php/i', $file, $matches)) {
                $file = strtolower($matches[1]);
            }
            foreach ($controllers as $controller) {
                $c = $controller::instance();
                if ($c instanceof AbstractController && $c->get_type() == "shortcode") {
                    $GLOBALS['eyeseet'][$plugin_name]['shortcodes'][$c->get_name()] = $c->get_attributes();
                }
            }
        }, 2);
        register_deactivation_hook($file, array(AbstractController::class, 'deactivate_all'));
    }
    public static function register_models($file, ...$models)
    {
        foreach ($models as $model) {
            $GLOBALS['eyeseet'][static::plugin_name($file)]['models'][] = $model;
        }
        register_activation_hook($file, function () use($file, $models) {
            foreach ($models as $model) {
                $model::install_db();
                register_uninstall_hook($file, array($model, 'uninstall_db'));
            }
        });
    }
    public static function register_update_function($file, $current_version, $function)
    {
        $GLOBALS['eyseet'][static::plugin_name($file)]['version'] = $current_version;
        add_action('admin_init', function () use($file, $current_version, $function) {
            $prev_version = get_option($file . '_version');
            if ($prev_version != $current_version) {
                if ($function($prev_version) !== FALSE) {
                    if ($prev_version === false) {
                        delete_option($file . '_version');
                        add_option($file . '_version', $current_version);
                    } else {
                        update_option($file . '_version', $current_version);
                    }
                    register_uninstall_hook($file, [self::class, 'delete_option_' . $file]);
                }
            }
        });
    }
    public static function plugin_name($file)
    {
        return basename(plugin_dir_path($file));
    }
    public static function register_settings_url($file, $url, $redirect_on_activation = true, $settings_name = null)
    {
        $settings_name = $settings_name ?? __("Settings", 'linkinbio');
        add_filter('plugin_action_links_' . plugin_basename($file), function ($links) use($url, $settings_name) {
            $links[] = "<a href=\"" . admin_url($url) . "\">" . $settings_name . "</a>";
            return $links;
        });
        if ($redirect_on_activation) {
            add_action('activated_plugin', function ($plugin) use($file, $url) {
                if ($plugin == plugin_basename($file)) {
                    exit(wp_redirect(admin_url($url)));
                }
            });
        }
    }
    public static function __callStatic(string $name, array $arguments = [])
    {
        $matches = [];
        if (preg_match('/delete_option_(.+)/ui', $name, $matches)) {
            $option = $matches[1] . '_version';
            delete_option($option);
        }
    }
    public static function register_languages($file = '', $slug = null, $directory = '/languages')
    {
        $plugin_rel_path = basename(dirname($file)) . $directory;
        /* Relative to WP_PLUGIN_DIR */
        $slug = $slug ?? basename(dirname($file));
        add_action('plugins_loaded', function () use($slug, $plugin_rel_path) {
            load_plugin_textdomain($slug, false, $plugin_rel_path);
        }, 1);
    }
}