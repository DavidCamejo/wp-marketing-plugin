<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two hooks for
 * enqueuing the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/admin
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_Admin {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/wp-marketing-plugin-admin.css', array(), $this->version, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/wp-marketing-plugin-admin.js', array('jquery'), $this->version, false);
        
        // Localize the script with new data
        $script_data = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wp_marketing_plugin_nonce')
        );
        wp_localize_script($this->plugin_name, 'wp_marketing_plugin_data', $script_data);
    }

    /**
     * Register the admin menu items.
     *
     * @since    1.0.0
     */
    public function register_admin_menu() {
        // Main plugin menu
        add_menu_page(
            __('Marketing Hub', 'wp-marketing-plugin'),
            __('Marketing Hub', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-plugin',
            array($this, 'display_dashboard_page'),
            'dashicons-megaphone',
            30
        );
        
        // Dashboard submenu
        add_submenu_page(
            'wp-marketing-plugin',
            __('Dashboard', 'wp-marketing-plugin'),
            __('Dashboard', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-plugin',
            array($this, 'display_dashboard_page')
        );
        
        // Contact Lists submenu
        add_submenu_page(
            'wp-marketing-plugin',
            __('Contact Lists', 'wp-marketing-plugin'),
            __('Contact Lists', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-contact-lists',
            array($this, 'display_contact_lists_page')
        );
        
        // Message Templates submenu
        add_submenu_page(
            'wp-marketing-plugin',
            __('Message Templates', 'wp-marketing-plugin'),
            __('Message Templates', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-message-templates',
            array($this, 'display_message_templates_page')
        );
        
        // Campaigns submenu
        add_submenu_page(
            'wp-marketing-plugin',
            __('Campaigns', 'wp-marketing-plugin'),
            __('Campaigns', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-campaigns',
            array($this, 'display_campaigns_page')
        );
        
        // WhatsApp QR Code submenu
        add_submenu_page(
            'wp-marketing-plugin',
            __('WhatsApp QR Code', 'wp-marketing-plugin'),
            __('WhatsApp QR Code', 'wp-marketing-plugin'),
            'manage_options',
            'wp-marketing-qr-code',
            array($this, 'display_qr_code_page')
        );
        
        // Settings submenu - only for admin users
        add_submenu_page(
            'wp-marketing-plugin',
            __('Settings', 'wp-marketing-plugin'),
            __('Settings', 'wp-marketing-plugin'),
            'administrator',
            'wp-marketing-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display the dashboard page
     *
     * @since    1.0.0
     */
    public function display_dashboard_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-dashboard.php';
    }

    /**
     * Display the contact lists page
     *
     * @since    1.0.0
     */
    public function display_contact_lists_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-contact-lists.php';
    }

    /**
     * Display the message templates page
     *
     * @since    1.0.0
     */
    public function display_message_templates_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-message-templates.php';
    }

    /**
     * Display the campaigns page
     *
     * @since    1.0.0
     */
    public function display_campaigns_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-campaigns.php';
    }

    /**
     * Display the QR code page
     *
     * @since    1.0.0
     */
    public function display_qr_code_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-qr-code.php';
    }

    /**
     * Display the settings page
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        include_once WP_MARKETING_PLUGIN_DIR . 'admin/partials/wp-marketing-plugin-admin-settings.php';
    }
}