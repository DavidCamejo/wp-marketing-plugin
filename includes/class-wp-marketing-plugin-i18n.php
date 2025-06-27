<?php
/**
 * Define the internationalization functionality.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_i18n {

    /**
     * Load the plugin text domain for translation.
     *
     * @since    1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'wp-marketing-plugin',
            false,
            dirname(dirname(plugin_basename(__FILE__))) . '/languages/'
        );
    }
}