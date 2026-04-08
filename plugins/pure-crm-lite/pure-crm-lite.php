<?php
/**
 * Plugin Name: Pure CRM Lite
 * Description: Frontend-first WordPress CRM with contacts, funnels, tasks, and multi-SMTP email sending.
 * Version: 0.1.0
 * Author: PureTheme
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: pure-crm-lite
 */

if (! defined('ABSPATH')) {
    exit;
}

define('PCRM_VERSION', '0.1.0');
define('PCRM_PLUGIN_FILE', __FILE__);
define('PCRM_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('PCRM_PLUGIN_URL', plugin_dir_url(__FILE__));

require_once PCRM_PLUGIN_PATH . 'includes/class-pcrm-db.php';
require_once PCRM_PLUGIN_PATH . 'includes/class-pcrm-mailer.php';
require_once PCRM_PLUGIN_PATH . 'includes/class-pcrm-rest.php';
require_once PCRM_PLUGIN_PATH . 'includes/class-pcrm-frontend.php';

register_activation_hook(__FILE__, array('PCRM_DB', 'activate'));

final class Pure_CRM_Lite
{
    public static function init()
    {
        add_action('init', array(__CLASS__, 'load_textdomain'));
        add_action('plugins_loaded', array('PCRM_DB', 'maybe_upgrade'));
        add_action('rest_api_init', array('PCRM_REST', 'register_routes'));
        add_action('plugins_loaded', array('PCRM_Frontend', 'init'));
    }

    public static function load_textdomain()
    {
        load_plugin_textdomain('pure-crm-lite', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
}

Pure_CRM_Lite::init();
