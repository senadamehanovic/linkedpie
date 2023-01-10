<?php

namespace linkinbio\Base\Models\AdminMenu;

class MenuItem
{
    public $title, $action;
    public function __construct($title, $action)
    {
        $this->title = $title;
        $this->action = $action;
    }
}
namespace linkinbio\Base\Models;

use linkinbio\Base\Models\AdminMenu\MenuItem;
class AdminMenu
{
    protected static $ajax_initialized = false;
    protected $default_page;
    public function __construct($default_page)
    {
        $this->default_page = $default_page;
    }
    public function show_menu($menus, $page = null, $prefix = null)
    {
        if ($page == null) {
            $page = plugin_basename($this->default_page);
        }
        echo "<div class=\"eyeseet-admin-menu\">";
        if ($prefix != null) {
            echo "<strong>" . $prefix . ": </strong>";
        }
        foreach ($menus as $key => $menu) {
            if (is_array($menu)) {
                if (!isset($menu['title'])) {
                    $this->show_menu($menu, $page, is_string($key) ? $key : null);
                } else {
                    $item_page = isset($menu['page']) ? $menu['page'] : $page;
                    $action = @$menu['action'];
                    if (isset($menu['action']) || isset($menu['page'])) {
                        if (empty($menu['parent_cpt'])) {
                            echo "<a href=\"" . admin_url() . "admin.php?page={$item_page}&action={$action}\" class=\"button\">{$menu['title']}</a>";
                        } else {
                            echo "<a href=\"" . admin_url() . "edit.php?post_type={$menu['parent_cpt']}&page={$item_page}&action={$action}\" class=\"button\">{$menu['title']}</a>";
                        }
                    } else {
                        if (isset($menu['ajax'])) {
                            $threads = isset($menu['threads']) ? "threads=\"{$menu['threads']}\"" : '';
                            $single = @$menu['single'] ? 'single="true"' : '';
                            echo "<a href=\"\" page=\"{$item_page}\" action=\"{$menu['ajax']}\" {$threads} {$single} class=\"button ajax\">{$menu['title']}</a>";
                        }
                    }
                }
            }
            if ($menu instanceof MenuItem) {
                echo "<a href=\"" . admin_url() . "admin.php?page={$page}&action={$menu->action}\" class=\"button\">{$menu->title}</a>";
            }
        }
        echo "</div>";
    }
}