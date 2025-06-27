<?php
/**
 * Register custom post types and taxonomies for the plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * Register custom post types and taxonomies for the plugin.
 *
 * This class defines all code necessary to register the custom post types
 * and taxonomies for the plugin.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_Post_Types {

    /**
     * Register custom post types for the plugin.
     *
     * @since    1.0.0
     */
    public function register_post_types() {
        // Marketing List post type
        register_post_type('marketing_list', array(
            'labels' => array(
                'name'               => _x('Contact Lists', 'post type general name', 'wp-marketing-plugin'),
                'singular_name'      => _x('Contact List', 'post type singular name', 'wp-marketing-plugin'),
                'menu_name'          => _x('Contact Lists', 'admin menu', 'wp-marketing-plugin'),
                'name_admin_bar'     => _x('Contact List', 'add new on admin bar', 'wp-marketing-plugin'),
                'add_new'            => _x('Add New', 'contact list', 'wp-marketing-plugin'),
                'add_new_item'       => __('Add New Contact List', 'wp-marketing-plugin'),
                'new_item'           => __('New Contact List', 'wp-marketing-plugin'),
                'edit_item'          => __('Edit Contact List', 'wp-marketing-plugin'),
                'view_item'          => __('View Contact List', 'wp-marketing-plugin'),
                'all_items'          => __('All Contact Lists', 'wp-marketing-plugin'),
                'search_items'       => __('Search Contact Lists', 'wp-marketing-plugin'),
                'parent_item_colon'  => __('Parent Contact Lists:', 'wp-marketing-plugin'),
                'not_found'          => __('No contact lists found.', 'wp-marketing-plugin'),
                'not_found_in_trash' => __('No contact lists found in Trash.', 'wp-marketing-plugin')
            ),
            'description'         => __('Contact Lists for marketing campaigns', 'wp-marketing-plugin'),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => array('slug' => 'marketing-list'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title')
        ));

        // Marketing Contact post type
        register_post_type('marketing_contact', array(
            'labels' => array(
                'name'               => _x('Contacts', 'post type general name', 'wp-marketing-plugin'),
                'singular_name'      => _x('Contact', 'post type singular name', 'wp-marketing-plugin'),
                'menu_name'          => _x('Contacts', 'admin menu', 'wp-marketing-plugin'),
                'name_admin_bar'     => _x('Contact', 'add new on admin bar', 'wp-marketing-plugin'),
                'add_new'            => _x('Add New', 'contact', 'wp-marketing-plugin'),
                'add_new_item'       => __('Add New Contact', 'wp-marketing-plugin'),
                'new_item'           => __('New Contact', 'wp-marketing-plugin'),
                'edit_item'          => __('Edit Contact', 'wp-marketing-plugin'),
                'view_item'          => __('View Contact', 'wp-marketing-plugin'),
                'all_items'          => __('All Contacts', 'wp-marketing-plugin'),
                'search_items'       => __('Search Contacts', 'wp-marketing-plugin'),
                'parent_item_colon'  => __('Parent Contacts:', 'wp-marketing-plugin'),
                'not_found'          => __('No contacts found.', 'wp-marketing-plugin'),
                'not_found_in_trash' => __('No contacts found in Trash.', 'wp-marketing-plugin')
            ),
            'description'         => __('Contacts for marketing campaigns', 'wp-marketing-plugin'),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => array('slug' => 'marketing-contact'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title')
        ));

        // Message Template post type
        register_post_type('message_template', array(
            'labels' => array(
                'name'               => _x('Message Templates', 'post type general name', 'wp-marketing-plugin'),
                'singular_name'      => _x('Message Template', 'post type singular name', 'wp-marketing-plugin'),
                'menu_name'          => _x('Message Templates', 'admin menu', 'wp-marketing-plugin'),
                'name_admin_bar'     => _x('Message Template', 'add new on admin bar', 'wp-marketing-plugin'),
                'add_new'            => _x('Add New', 'message template', 'wp-marketing-plugin'),
                'add_new_item'       => __('Add New Message Template', 'wp-marketing-plugin'),
                'new_item'           => __('New Message Template', 'wp-marketing-plugin'),
                'edit_item'          => __('Edit Message Template', 'wp-marketing-plugin'),
                'view_item'          => __('View Message Template', 'wp-marketing-plugin'),
                'all_items'          => __('All Message Templates', 'wp-marketing-plugin'),
                'search_items'       => __('Search Message Templates', 'wp-marketing-plugin'),
                'parent_item_colon'  => __('Parent Message Templates:', 'wp-marketing-plugin'),
                'not_found'          => __('No message templates found.', 'wp-marketing-plugin'),
                'not_found_in_trash' => __('No message templates found in Trash.', 'wp-marketing-plugin')
            ),
            'description'         => __('Message Templates for marketing campaigns', 'wp-marketing-plugin'),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => array('slug' => 'message-template'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title', 'editor')
        ));

        // Marketing Campaign post type
        register_post_type('marketing_campaign', array(
            'labels' => array(
                'name'               => _x('Campaigns', 'post type general name', 'wp-marketing-plugin'),
                'singular_name'      => _x('Campaign', 'post type singular name', 'wp-marketing-plugin'),
                'menu_name'          => _x('Campaigns', 'admin menu', 'wp-marketing-plugin'),
                'name_admin_bar'     => _x('Campaign', 'add new on admin bar', 'wp-marketing-plugin'),
                'add_new'            => _x('Add New', 'campaign', 'wp-marketing-plugin'),
                'add_new_item'       => __('Add New Campaign', 'wp-marketing-plugin'),
                'new_item'           => __('New Campaign', 'wp-marketing-plugin'),
                'edit_item'          => __('Edit Campaign', 'wp-marketing-plugin'),
                'view_item'          => __('View Campaign', 'wp-marketing-plugin'),
                'all_items'          => __('All Campaigns', 'wp-marketing-plugin'),
                'search_items'       => __('Search Campaigns', 'wp-marketing-plugin'),
                'parent_item_colon'  => __('Parent Campaigns:', 'wp-marketing-plugin'),
                'not_found'          => __('No campaigns found.', 'wp-marketing-plugin'),
                'not_found_in_trash' => __('No campaigns found in Trash.', 'wp-marketing-plugin')
            ),
            'description'         => __('Marketing Campaigns', 'wp-marketing-plugin'),
            'public'              => false,
            'publicly_queryable'  => false,
            'show_ui'             => true,
            'show_in_menu'        => false,
            'query_var'           => false,
            'rewrite'             => array('slug' => 'marketing-campaign'),
            'capability_type'     => 'post',
            'has_archive'         => false,
            'hierarchical'        => false,
            'menu_position'       => null,
            'supports'            => array('title')
        ));
    }

    /**
     * Register custom taxonomies for the plugin.
     *
     * @since    1.0.0
     */
    public function register_taxonomies() {
        // Register custom taxonomies if needed
    }

    /**
     * Filter queries to respect multi-user isolation.
     * Only show posts that belong to the current user unless user is admin.
     *
     * @since    1.0.0
     * @param    WP_Query    $query    The WordPress query instance.
     */
    public function filter_user_posts($query) {
        // Don't filter for admin users or in admin
        if (is_admin() && current_user_can('administrator')) {
            return;
        }

        // Only filter our custom post types
        $post_types = array(
            'marketing_list',
            'marketing_contact',
            'message_template',
            'marketing_campaign'
        );

        if ($query->is_main_query() && 
            isset($query->query['post_type']) && 
            in_array($query->query['post_type'], $post_types)) {
            
            // Get current user
            $current_user_id = get_current_user_id();
            
            // Make sure we only show posts owned by the current user
            if ($current_user_id) {
                $query->set('author', $current_user_id);
            }
        }
    }
}