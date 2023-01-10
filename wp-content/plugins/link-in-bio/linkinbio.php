<?php

/**
 * Plugin Name: Link In Bio
 * Plugin URI: https://contentandcreations.nl/link-in-bio/
 * Description: Create a link bio page (linktree&trade; like) for your wordpress site.
 * Author: Content & Creations
 * Author URI: https://contentandcreations.nl/
 * Text Domain: linkinbio
 * Domain Path: /languages/
 * Version: 1.0.3
 */
namespace linkinbio;

if (!defined('ABSPATH')) {
    // Exit if accessed directly
    exit;
}
include_once dirname(__FILE__) . '/Base/PluginHelper.php';
use linkinbio\Base\PluginHelper;
use linkinbio\Controllers\LinkInBioCPT;
use linkinbio\Controllers\LinkInBioPage;
use linkinbio\Controllers\LinkInBioSettings;
PluginHelper::create_plugin_autoloader(__FILE__);
PluginHelper::register_languages(__FILE__, "linkinbio");
PluginHelper::register_controllers(__FILE__, LinkInBioCPT::class, LinkInBioSettings::class, LinkInBioPage::class);
PluginHelper::register_settings_url(__FILE__, 'edit.php?post_type=linkinbio&page=settings', false);