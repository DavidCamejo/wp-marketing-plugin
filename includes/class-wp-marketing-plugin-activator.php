<?php
/**
 * Fired during plugin activation.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since    1.0.0
     */
    public static function activate() {
        // Create custom database tables
        self::create_tables();
        
        // Register custom post types to flush rewrite rules
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-marketing-plugin-post-types.php';
        $plugin_post_types = new WP_Marketing_Plugin_Post_Types();
        $plugin_post_types->register_post_types();
        
        // Flush rewrite rules
        flush_rewrite_rules();
        
        // Set default plugin options
        self::set_default_options();
    }
    
    /**
     * Create custom database tables for the plugin.
     *
     * @since    1.0.0
     */
    private static function create_tables() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        
        // List Contact Relations Table
        $table_name = $wpdb->prefix . 'marketing_list_contact';
        $sql = "CREATE TABLE $table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            list_id bigint(20) NOT NULL,
            contact_id bigint(20) NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY list_id (list_id),
            KEY contact_id (contact_id)
        ) $charset_collate;";
        
        // Contact Custom Fields Table
        $table_name_fields = $wpdb->prefix . 'marketing_contact_fields';
        $sql .= "CREATE TABLE $table_name_fields (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            contact_id bigint(20) NOT NULL,
            meta_key varchar(255) NOT NULL,
            meta_value longtext NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY contact_id (contact_id),
            KEY meta_key (meta_key)
        ) $charset_collate;";
        
        // Campaign Messages Table
        $table_name_campaign_messages = $wpdb->prefix . 'marketing_campaign_messages';
        $sql .= "CREATE TABLE $table_name_campaign_messages (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            campaign_id bigint(20) NOT NULL,
            contact_id bigint(20) NOT NULL,
            status varchar(50) NOT NULL,
            message_id varchar(255) NULL,
            error_message text NULL,
            sent_at datetime NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id),
            KEY campaign_id (campaign_id),
            KEY contact_id (contact_id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    /**
     * Set default plugin options.
     *
     * @since    1.0.0
     */
    private static function set_default_options() {
        $default_options = array(
            'n8n_url' => '',
            'api_key' => '',
            'enable_qr_code' => 1,
            'max_contacts_per_import' => 1000,
            'max_contacts_per_campaign' => 500,
        );
        
        foreach ($default_options as $key => $value) {
            if (!get_option('wp_marketing_' . $key)) {
                update_option('wp_marketing_' . $key, $value);
            }
        }
    }
}