<?php

namespace linkinbio\Base\Controllers;

use linkinbio\Base\Helpers\SettingsUtil;
/**
 * Base class to easily make a settings page for your plugin
 *
 * @author EyeSeeT
 *
 */
abstract class AdminSettingsController extends AbstractAdminController
{
    protected $settingsUtil;
    protected $namespace;
    protected $settings = [];
    public function __construct($namespace, $name, $parent = null)
    {
        parent::__construct($name, $parent);
        $this->settingsUtil = new SettingsUtil($namespace);
        $this->namespace = $namespace;
        add_action('admin_init', function () {
            $this->settingsUtil->init_settings();
        });
    }
    public function index()
    {
        $this->settingsUtil->showForm();
    }
    protected function onControllerActive()
    {
        parent::onControllerActive();
        $this->settingsUtil->enqueueStyles();
    }
    protected function add_setting($slug, $name, $type, $optional = false, $default = null)
    {
        $this->settingsUtil->add_setting($slug, $name, $type, $optional, $default);
    }
    public function get_setting($slug)
    {
        return $this->settingsUtil->get_setting($slug);
    }
}