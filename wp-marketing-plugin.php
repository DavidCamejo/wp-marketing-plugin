<?php
/**
 * The plugin bootstrap file
 *
 * @link              https://yourwebsite.com
 * @since             1.0.0
 * @package           WP_Marketing_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       WordPress Marketing Plugin
 * Plugin URI:        https://yourwebsite.com/wp-marketing-plugin
 * Description:       A multi-user digital marketing plugin for WordPress to manage contact lists, templates, and campaigns via Evolution API.
 * Version:           1.0.0
 * Author:            Your Name
 * Author URI:        https://yourwebsite.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp-marketing-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

/**
 * Current plugin version.
 */
define('WP_MARKETING_PLUGIN_VERSION', '1.0.0');

/**
 * Plugin basename.
 */
define('WP_MARKETING_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Plugin directory path.
 */
define('WP_MARKETING_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Plugin directory URL.
 */
define('WP_MARKETING_PLUGIN_URL', plugin_dir_url(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wp-marketing-plugin-activator.php
 */
function activate_wp_marketing_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-marketing-plugin-activator.php';
    WP_Marketing_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wp-marketing-plugin-deactivator.php
 */
function deactivate_wp_marketing_plugin() {
    require_once plugin_dir_path(__FILE__) . 'includes/class-wp-marketing-plugin-deactivator.php';
    WP_Marketing_Plugin_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_marketing_plugin');
register_deactivation_hook(__FILE__, 'deactivate_wp_marketing_plugin');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path(__FILE__) . 'includes/class-wp-marketing-plugin.php';

/**
 * Begins execution of the plugin.
 *
 * @since    1.0.0
 */
function run_wp_marketing_plugin() {
    $plugin = new WP_Marketing_Plugin();
    $plugin->run();
}

run_wp_marketing_plugin();