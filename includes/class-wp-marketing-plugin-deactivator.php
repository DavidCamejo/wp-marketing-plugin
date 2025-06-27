<?php
/**
 * Fired during plugin deactivation.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_Deactivator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function deactivate() {
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Maybe clean up scheduled events
        wp_clear_scheduled_hook('wp_marketing_plugin_scheduled_campaign');
    }
}