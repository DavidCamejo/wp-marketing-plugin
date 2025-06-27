<?php
/**
 * Handle REST API endpoints for the plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * Handle REST API endpoints for the plugin.
 *
 * This class defines all code necessary to handle REST API
 * endpoints for integration with n8n and Evolution API.
 *
 * @since      1.0.0
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_Plugin_API {

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

        $this->register_rest_routes();
    }

    /**
     * Register the REST API routes for the plugin.
     *
     * @since    1.0.0
     */
    public function register_rest_routes() {
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
    }

    /**
     * Register the API endpoints.
     *
     * @since    1.0.0
     */
    public function register_api_endpoints() {
        // Register route for n8n to update campaign message status
        register_rest_route('wp-marketing/v1', '/campaign/update-message', array(
            'methods' => 'POST',
            'callback' => array($this, 'update_campaign_message_status'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'campaign_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'contact_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'status' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return in_array($param, array('sent', 'delivered', 'failed'));
                    }
                ),
                'message_id' => array(
                    'required' => false,
                ),
                'error_message' => array(
                    'required' => false,
                ),
            ),
        ));

        // Register route for n8n to get campaign details
        register_rest_route('wp-marketing/v1', '/campaign/(?P<id>\d+)', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_campaign_details'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));

        // Register route for n8n to get contacts from a list
        register_rest_route('wp-marketing/v1', '/list/(?P<id>\d+)/contacts', array(
            'methods' => 'GET',
            'callback' => array($this, 'get_list_contacts'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'id' => array(
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
            ),
        ));
    }

    /**
     * Validate the API key for REST API requests.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   bool|WP_Error
     */
    public function validate_api_key($request) {
        $headers = $request->get_headers();
        
        if (!isset($headers['x_api_key']) || empty($headers['x_api_key'][0])) {
            return new WP_Error(
                'rest_forbidden',
                __('API key is required.', 'wp-marketing-plugin'),
                array('status' => 403)
            );
        }
        
        $api_key = $headers['x_api_key'][0];
        $stored_api_key = get_option('wp_marketing_api_key');
        
        if (empty($stored_api_key) || $api_key !== $stored_api_key) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid API key.', 'wp-marketing-plugin'),
                array('status' => 403)
            );
        }
        
        return true;
    }

    /**
     * Update campaign message status.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   WP_REST_Response|WP_Error
     */
    public function update_campaign_message_status($request) {
        global $wpdb;
        
        $campaign_id = intval($request['campaign_id']);
        $contact_id = intval($request['contact_id']);
        $status = sanitize_text_field($request['status']);
        $message_id = isset($request['message_id']) ? sanitize_text_field($request['message_id']) : '';
        $error_message = isset($request['error_message']) ? sanitize_text_field($request['error_message']) : '';
        
        $table_name = $wpdb->prefix . 'marketing_campaign_messages';
        
        $campaign_exists = get_post($campaign_id);
        $contact_exists = get_post($contact_id);
        
        if (!$campaign_exists || 'marketing_campaign' !== get_post_type($campaign_id)) {
            return new WP_Error(
                'rest_not_found',
                __('Campaign not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        if (!$contact_exists || 'marketing_contact' !== get_post_type($contact_id)) {
            return new WP_Error(
                'rest_not_found',
                __('Contact not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Check if record exists
        $existing_record = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM $table_name WHERE campaign_id = %d AND contact_id = %d",
            $campaign_id,
            $contact_id
        ));
        
        $data = array(
            'status' => $status,
            'message_id' => $message_id,
            'error_message' => $error_message,
            'updated_at' => current_time('mysql')
        );
        
        if ($status === 'sent' || $status === 'delivered') {
            $data['sent_at'] = current_time('mysql');
        }
        
        if ($existing_record) {
            // Update existing record
            $wpdb->update(
                $table_name,
                $data,
                array(
                    'campaign_id' => $campaign_id,
                    'contact_id' => $contact_id
                )
            );
        } else {
            // Create new record
            $data['campaign_id'] = $campaign_id;
            $data['contact_id'] = $contact_id;
            $data['created_at'] = current_time('mysql');
            
            $wpdb->insert($table_name, $data);
        }
        
        // Update campaign meta with counts
        $this->update_campaign_stats($campaign_id);
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Message status updated successfully.', 'wp-marketing-plugin')
        ), 200);
    }

    /**
     * Get campaign details.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   WP_REST_Response|WP_Error
     */
    public function get_campaign_details($request) {
        $campaign_id = intval($request['id']);
        
        $campaign = get_post($campaign_id);
        
        if (!$campaign || 'marketing_campaign' !== get_post_type($campaign_id)) {
            return new WP_Error(
                'rest_not_found',
                __('Campaign not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Get campaign meta
        $list_id = get_post_meta($campaign_id, 'list_id', true);
        $template_id = get_post_meta($campaign_id, 'template_id', true);
        $status = get_post_meta($campaign_id, 'status', true);
        $scheduled_time = get_post_meta($campaign_id, 'scheduled_time', true);
        
        // Get template content
        $template_content = '';
        if ($template_id) {
            $template = get_post($template_id);
            if ($template && 'message_template' === get_post_type($template_id)) {
                $template_content = $template->post_content;
            }
        }
        
        $campaign_data = array(
            'id' => $campaign_id,
            'title' => $campaign->post_title,
            'status' => $status,
            'scheduled_time' => $scheduled_time,
            'list_id' => $list_id,
            'template_id' => $template_id,
            'template_content' => $template_content,
            'created_at' => $campaign->post_date,
            'author_id' => $campaign->post_author
        );
        
        return new WP_REST_Response($campaign_data, 200);
    }

    /**
     * Get contacts from a list.
     *
     * @since    1.0.0
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   WP_REST_Response|WP_Error
     */
    public function get_list_contacts($request) {
        global $wpdb;
        
        $list_id = intval($request['id']);
        
        $list = get_post($list_id);
        
        if (!$list || 'marketing_list' !== get_post_type($list_id)) {
            return new WP_Error(
                'rest_not_found',
                __('List not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Get contacts in this list from the relation table
        $table_name = $wpdb->prefix . 'marketing_list_contact';
        $fields_table = $wpdb->prefix . 'marketing_contact_fields';
        
        $contact_ids = $wpdb->get_col($wpdb->prepare(
            "SELECT contact_id FROM $table_name WHERE list_id = %d",
            $list_id
        ));
        
        $contacts = array();
        
        foreach ($contact_ids as $contact_id) {
            $contact = get_post($contact_id);
            
            if ($contact && 'marketing_contact' === get_post_type($contact_id)) {
                // Get standard contact fields
                $phone_number = get_post_meta($contact_id, 'phone_number', true);
                $first_name = get_post_meta($contact_id, 'first_name', true);
                $last_name = get_post_meta($contact_id, 'last_name', true);
                $email = get_post_meta($contact_id, 'email', true);
                
                // Get custom fields
                $custom_fields = $wpdb->get_results($wpdb->prepare(
                    "SELECT meta_key, meta_value FROM $fields_table WHERE contact_id = %d",
                    $contact_id
                ), ARRAY_A);
                
                $contact_data = array(
                    'id' => $contact_id,
                    'name' => $contact->post_title,
                    'phone_number' => $phone_number,
                    'first_name' => $first_name,
                    'last_name' => $last_name,
                    'email' => $email,
                    'custom_fields' => array()
                );
                
                foreach ($custom_fields as $field) {
                    $contact_data['custom_fields'][$field['meta_key']] = $field['meta_value'];
                }
                
                $contacts[] = $contact_data;
            }
        }
        
        return new WP_REST_Response($contacts, 200);
    }
    
    /**
     * Update campaign statistics.
     *
     * @since    1.0.0
     * @param    int    $campaign_id    Campaign ID.
     */
    private function update_campaign_stats($campaign_id) {
        global $wpdb;
        
        $table_name = $wpdb->prefix . 'marketing_campaign_messages';
        
        // Count total messages
        $total_messages = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d",
            $campaign_id
        ));
        
        // Count sent messages
        $total_sent = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d AND (status = 'sent' OR status = 'delivered')",
            $campaign_id
        ));
        
        // Count delivered messages
        $total_delivered = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d AND status = 'delivered'",
            $campaign_id
        ));
        
        // Count failed messages
        $total_failed = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d AND status = 'failed'",
            $campaign_id
        ));
        
        // Update campaign meta
        update_post_meta($campaign_id, 'total_messages', $total_messages);
        update_post_meta($campaign_id, 'total_sent', $total_sent);
        update_post_meta($campaign_id, 'total_delivered', $total_delivered);
        update_post_meta($campaign_id, 'total_failed', $total_failed);
        
        // Update campaign status if all messages are processed
        $pending = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d AND status = 'pending'",
            $campaign_id
        ));
        
        if ($pending == 0 && $total_messages > 0) {
            update_post_meta($campaign_id, 'status', 'completed');
        }
    }
}