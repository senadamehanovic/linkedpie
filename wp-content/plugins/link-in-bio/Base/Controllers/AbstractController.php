<?php

namespace linkinbio\Base\Controllers;

/**
 * Base class to allow Controllers to be initialized only once (to prevent unnecesary actions and filters to be added) and provide base functions
 * @author EyeSeeT
 *
 */
abstract class AbstractController
{
    private static $instances = [];
    private $cronjobs = [];
    protected $styles_scripts = [];
    /**
     * Called only once, when controller is registered
     */
    protected function __construct()
    {
    }
    /**
     * Will schedule a cronjob calling the function every specified interval (daily, hourly, etc)
     * Must be called in constructor
     * 
     * @param string $name
     * @param string $interval
     * @param callable $function
     * @param array $args
     */
    protected function register_cronjob($name, $interval, $function, $args = [])
    {
        $this->cronjobs[] = $name;
        add_action($name, $function, 10, sizeof($args));
        if (!wp_next_scheduled($name, $args)) {
            wp_schedule_event(time(), $interval, $name, $args);
        }
    }
    public function register_style($name, $src = '')
    {
        if (empty($this->styles_scripts)) {
            add_action('wp_enqueue_scripts', function () {
                $this->do_enqueue_styles();
            });
        }
        $this->styles_scripts[$name] = ['src' => $src, 'type' => 'css'];
    }
    public function register_script($name, $src = '')
    {
        if (empty($this->styles_scripts)) {
            add_action('wp_enqueue_scripts', function () {
                $this->do_enqueue_styles();
            });
        }
        $this->styles_scripts[$name] = ['src' => $src, 'type' => 'js'];
    }
    protected function do_enqueue_styles()
    {
        foreach ($this->styles_scripts as $k => $v) {
            if ($v['type'] == 'css') {
                wp_enqueue_style($k, $v['src']);
            }
            if ($v['type'] == 'js') {
                wp_enqueue_script($k, $v['src']);
            }
        }
    }
    private function deactivate()
    {
        foreach ($this->cronjobs as $cron) {
            wp_clear_scheduled_hook($cron);
        }
    }
    /**
     * @return static
     */
    public static function instance()
    {
        $class = get_called_class();
        return static::register_instance($class, function () use($class) {
            return new $class();
        });
    }
    protected static function register_instance(string $name, callable $create_function)
    {
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = $create_function();
        }
        return self::$instances[$name];
    }
    public static function deactivate_all()
    {
        foreach (self::$instances as $instance) {
            $instance->deactivate();
        }
    }
    public function get_type()
    {
        return "base";
    }
}