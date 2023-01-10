<?php

namespace linkinbio\Base\Controllers;

use linkinbio\Base\Helpers\FormBuilder;
use linkinbio\Base\Helpers\FormBuilder\FormInput;
use linkinbio\Base\Models\AdminMenu;
abstract class AbstractAdminController extends AbstractController
{
    const OPTIONS_PREFIX = "wp_screen_options_eyeseet";
    protected $name;
    protected $menu_slug;
    protected $parent;
    protected $title;
    /**
     * @var string whether page matches menu_slug
     */
    protected $isCurrentPage;
    // properties for top menu
    protected $menu = [];
    protected $menu_view;
    // wordpress menu properties
    protected $menu_icon = null;
    protected $menu_position = 10;
    protected $capability = "manage_options";
    protected $screen_options = [];
    /**
     * 
     * @param string $name name of the menu
     * @param string $parent slug of the parent
     */
    protected function __construct($name, $parent = null)
    {
        $this->title = $this->name = $name;
        $this->menu_slug = sanitize_key($this->name);
        $this->parent = $parent;
        add_action('wp_loaded', function () {
            if (is_admin()) {
                $this->menu_view = new AdminMenu($this->menu_slug);
                add_action("admin_menu", [$this, 'add_to_admin_menu'], $this->parent == null ? 9 : 11);
                if ($this->parent != null) {
                    add_action("admin_menu", [$this, 'check_parent_menu'], 10);
                }
                add_action('wp_ajax_' . $this->menu_slug . '_route', [$this, 'ajax_route']);
                if (($_GET['page'] ?? null) == $this->menu_slug) {
                    $this->onControllerActive();
                    add_action("admin_init", function () {
                        $this->page_init();
                        $this->route_init();
                    });
                    add_action('admin_enqueue_scripts', function () {
                        $this->do_enqueue_styles();
                    });
                }
            }
        });
    }
    /**
     * SHOULD BE CALLED BEFORE wp_loaded (PREFERABLY IN THE CONSTRUCTOR)
     * sets a different capability and add the capability to specified roles
     * @param $capability
     * @param $roles \WP_Role[]|string array of WP_Role object or comma seperated string
     */
    protected function change_roles($capability, $roles)
    {
        $this->capability = $capability;
        if (is_string($roles)) {
            $roles = array_filter(array_map(fn($s) => get_role($s), explode(",", $roles)));
        }
        foreach ($roles as $role) {
            if (!isset($role->capabilities[$capability])) {
                $role->add_cap($capability);
            }
        }
    }
    public function route_init()
    {
        $action = sanitize_key(@$_GET['action']);
        if (empty($action)) {
            $action = 'index';
        }
        if (isset($this->screen_options[$action])) {
            $this->show_screen_options($this->screen_options[$action]);
        }
        $action = 'init_' . $action;
        if (method_exists($this, $action)) {
            $this->{$action}();
        }
    }
    /**
     * routes the action parameter to a function of this class
     */
    public function route()
    {
        $this->before_content();
        $action = sanitize_key(@$_GET['action']);
        if (empty($action)) {
            $this->index();
        } else {
            $this->{$action}();
        }
        $this->after_content();
    }
    /**
     * routes the doaction parameter to an ajax_ function of this class
     * SHOULD EXIT
     */
    public function ajax_route()
    {
        $action = 'ajax_' . sanitize_key($_POST['doaction']);
        if (method_exists($this, $action)) {
            $this->{$action}();
            exit;
        } else {
            status_header(500);
            die("ajax method {$action} not found on " . get_class($this));
        }
    }
    /**
     * Execute on wp_loaded only if page matches menu_slug
     */
    protected function onControllerActive()
    {
    }
    /**
     * Excecuted on admin_init only if the page on inheriting controller is shown.
     */
    protected function page_init()
    {
        $this->register_script('admin_menu', plugins_url('../assets/admin/js/admin.js', __FILE__));
        $this->register_style('cnc_admin_css', plugins_url('../assets/admin/css/admin.css', __FILE__));
        $this->register_script('jquery-ui-core');
        $this->register_script('jquery-ui-progressbar');
        $this->register_style('jquery-ui-css', plugins_url('../assets/admin/css/jquery-ui-fresh.css', __FILE__));
    }
    /**
     * adds a admin menu referring to the index of the inheriting controller
     * @return string|false The resulting page's hook_suffix, or false if the user does not have the capability required.
     */
    public function add_to_admin_menu()
    {
        if ($this->parent != null) {
            return add_submenu_page($this->get_parent_slug(), $this->name, $this->name, $this->capability, $this->menu_slug, array($this, 'route'));
        } else {
            return add_menu_page($this->name, $this->name, $this->capability, $this->menu_slug, [$this, 'route'], $this->menu_icon, $this->menu_position);
        }
    }
    public function check_parent_menu()
    {
        $parent_slug = $this->get_parent_slug();
        foreach ($GLOBALS['menu'] as $i) {
            if (isset($i[2]) && $i[2] == $parent_slug) {
                if (!current_user_can($i[1])) {
                    // replace
                    remove_menu_page($this->parent);
                    add_menu_page($this->parent, $this->parent, $this->capability, $parent_slug, null, null, $this->menu_position);
                }
                return;
            }
        }
        add_menu_page($this->parent, $this->parent, $this->capability, $parent_slug, null, null, $this->menu_position);
        add_action('admin_head', function () {
            // REMOVE auto created child
            unset($GLOBALS['submenu'][$this->get_parent_slug()][0]);
        });
    }
    private function get_parent_slug()
    {
        if ($this->parent == null) {
            return null;
        } else {
            if (preg_match('/^edit\\.php\\?post_type=[a-z]+$/', $this->parent)) {
                return strtolower($this->parent);
            } else {
                return sanitize_key($this->parent);
            }
        }
    }
    /**
     * Adds the style to the admin_enqueue_scripts hook.
     * {@inheritDoc} overides adding the style to enqueue_scripts hook.
     */
    public function register_style($name, $src = '')
    {
        $this->styles_scripts[$name] = ['src' => $src, 'type' => 'css'];
    }
    /**
     * Adds the style to the admin_enqueue_scripts hook.
     * {@inheritDoc} overides adding the style to enqueue_scripts hook.
     */
    public function register_script($name, $src = '')
    {
        $this->styles_scripts[$name] = ['src' => $src, 'type' => 'js'];
    }
    protected function add_screen_option_per_page()
    {
        if (isset($this->screen_options["index"]["per_page"])) {
            return;
        }
        $this->screen_options["index"]["per_page"] = fn() => add_screen_option("per_page", ["label" => "Items", "default" => 50, "option" => "items_per_page"]);
        add_filter('set-screen-option', function ($s, $o, $v) {
            if ($o == 'items_per_page') {
                return intval($v);
            }
            return $s;
        }, 10, 3);
    }
    protected function add_screen_option($option, $default_value = "", $type = null, $label = null, $action = "index")
    {
        if (empty($this->screen_options)) {
            add_action('wp_loaded', function () use($action) {
                if (($user = wp_get_current_user()) && isset($_POST[static::OPTIONS_PREFIX])) {
                    foreach ($this->screen_options[$action] as $o) {
                        $o = $o['option'];
                        if (isset($_POST[static::OPTIONS_PREFIX][$o])) {
                            $val = $_POST[static::OPTIONS_PREFIX][$o];
                            if ($this->validate_option($o, $val, $action)) {
                                update_user_meta($user->ID, static::OPTIONS_PREFIX . "_" . $o, $val);
                            }
                        }
                    }
                }
            });
        }
        $this->screen_options[$action][] = ["option" => $option, "default_value" => $default_value, "type" => $type, "label" => $label];
    }
    protected function validate_option($option, $value, $action)
    {
        return true;
    }
    public static function get_screen_option($option)
    {
        $value = null;
        if ($uid = get_current_user_id()) {
            $id = static::OPTIONS_PREFIX . "_" . $option;
            $screen = get_current_screen();
            $user_meta = get_user_meta($uid, $id);
            if (!empty($user_meta)) {
                $value = $user_meta[0];
            } else {
                if ($screen != null) {
                    $value = $screen->get_option($id, 'value');
                }
            }
        }
        return $value;
    }
    protected function show_screen_options($options)
    {
        add_filter('screen_options_show_submit', '__return_true');
        add_action("admin_head", function () use($options) {
            foreach ($options as $o) {
                try {
                    if (is_array($o)) {
                        $option = $o['option'];
                        $default_value = $o['default_value'];
                        add_screen_option($option, ['option' => static::OPTIONS_PREFIX . "_" . $option, 'value' => $default_value]);
                    } else {
                        if ($o instanceof \Closure) {
                            $o();
                        }
                    }
                } catch (\Exception $ex) {
                    echo $ex->getMessage();
                }
            }
        });
        add_filter("screen_settings", function ($r) use($options) {
            ob_start();
            $formbuilder = new FormBuilder();
            echo "<fieldset class=\"eyeseet-screen-options\">";
            echo "<legend>{$this->title} Settings</legend>";
            foreach ($options as $o) {
                if (!is_array($o)) {
                    continue;
                }
                $option = $o['option'];
                $default_value = $o['default_value'];
                $id = static::OPTIONS_PREFIX . "_" . $option;
                $input = new FormInput($o['label'] ?? ucfirst($option), $o['type'] ?? "text");
                $input->name = Static::OPTIONS_PREFIX . "[{$option}]";
                $input->id = $id;
                $input->value = static::get_screen_option($option) ?? $default_value;
                $formbuilder->printInput($input, "<div class=\"eyeseet-screen-options-row\">", "</div>");
            }
            echo "</fieldset>";
            return $r . ob_get_clean();
        });
    }
    protected function show_ajax_progress($amount_left, $current, $current_id = null)
    {
        ob_start();
        echo json_encode(['id' => intval($_POST['id']), 'amount_left' => $amount_left, 'current' => utf8_encode($current), 'last_item' => $current_id]);
        $output = ob_get_clean();
        if (empty($output)) {
            echo "output error";
            var_dump($_POST);
            var_dump($amount_left);
            echo $current;
        }
        echo $output;
        exit;
    }
    protected function before_content()
    {
        echo "<div class=\"wrap\">";
        echo "<h2>{$this->title}</h2>";
        if (!empty($this->menu)) {
            $this->menu_view->show_menu($this->menu);
        }
        echo "<div id=\"eyeseet-content\">";
    }
    public function after_content()
    {
        echo "</div></div>";
    }
    public function get_url($action = 'index', $additional_args = '')
    {
        return admin_url() . 'admin.php?page=' . $this->menu_slug . '&action=' . $action . $additional_args;
    }
    protected function redirect_to($action = 'index', $additional_args = '')
    {
        $url = $this->get_url($action, $additional_args);
        if (!headers_sent()) {
            wp_redirect($url);
            exit;
        } else {
            echo "redirecting...";
            echo "<script>window.location=\"{$url}\";</script>";
            exit;
        }
    }
    public abstract function index();
}