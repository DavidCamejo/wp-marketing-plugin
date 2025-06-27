<?php
/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Marketing_Plugin_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct() {
        if (defined('WP_MARKETING_PLUGIN_VERSION')) {
            $this->version = WP_MARKETING_PLUGIN_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'wp-marketing-plugin';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_post_types();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * Include the following files that make up the plugin:
     *
     * - WP_Marketing_Plugin_Loader. Orchestrates the hooks of the plugin.
     * - WP_Marketing_Plugin_i18n. Defines internationalization functionality.
     * - WP_Marketing_Plugin_Admin. Defines all hooks for the admin area.
     * - WP_Marketing_Plugin_Post_Types. Registers custom post types and taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-marketing-plugin-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-marketing-plugin-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'admin/class-wp-marketing-plugin-admin.php';

        /**
         * The class responsible for registering custom post types and taxonomies.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-marketing-plugin-post-types.php';

        /**
         * The class responsible for handling REST API endpoints.
         */
        require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-marketing-plugin-api.php';

        /**
         * Load Carbon Fields if not already loaded.
         */
        if (!class_exists('\\Carbon_Fields\\Carbon_Fields')) {
            require_once plugin_dir_path(dirname(__FILE__)) . 'vendor/autoload.php';
        }

        $this->loader = new WP_Marketing_Plugin_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the WP_Marketing_Plugin_i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale() {
        $plugin_i18n = new WP_Marketing_Plugin_i18n();
        $this->loader->add_action('plugins_loaded', $plugin_i18n, 'load_plugin_textdomain');
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new WP_Marketing_Plugin_Admin($this->get_plugin_name(), $this->get_version());

        // Admin assets
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        
        // Admin menu
        $this->loader->add_action('admin_menu', $plugin_admin, 'register_admin_menu');
        
        // AJAX handlers
        $this->loader->add_action('wp_ajax_wp_marketing_import_contacts', $plugin_admin, 'handle_import_contacts');
        $this->loader->add_action('wp_ajax_wp_marketing_generate_qr', $plugin_admin, 'handle_generate_qr');
        $this->loader->add_action('wp_ajax_wp_marketing_send_campaign', $plugin_admin, 'handle_send_campaign');
        
        // Carbon Fields
        $this->loader->add_action('carbon_fields_register_fields', $plugin_admin, 'register_carbon_fields');
        $this->loader->add_action('after_setup_theme', $plugin_admin, 'load_carbon_fields');
    }

    /**
     * Register custom post types and taxonomies.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_post_types() {
        $plugin_post_types = new WP_Marketing_Plugin_Post_Types();
        
        // Register custom post types
        $this->loader->add_action('init', $plugin_post_types, 'register_post_types');
        
        // Register custom taxonomies
        $this->loader->add_action('init', $plugin_post_types, 'register_taxonomies');
        
        // Filter queries to respect multi-user isolation
        $this->loader->add_action('pre_get_posts', $plugin_post_types, 'filter_user_posts');
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    WP_Marketing_Plugin_Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}