<?php
/**
 * N8N Integrator class for the WordPress Marketing Plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.1
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 */

/**
 * The N8N integration functionality of the plugin.
 *
 * Handles all communication with n8n automation platform:
 * - Sending campaigns to n8n for processing
 * - Managing webhooks and callbacks
 * - Tracking workflow execution status
 * - Error handling and logging
 *
 * @since      1.0.1
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/includes
 * @author     Your Name <email@example.com>
 */
class WP_Marketing_N8N_Integrator {

    /**
     * The ID of this plugin.
     *
     * @since    1.0.1
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     *
     * @since    1.0.1
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private $version;

    /**
     * The n8n base URL.
     *
     * @since    1.0.1
     * @access   private
     * @var      string    $n8n_url    The base URL of the n8n instance.
     */
    private $n8n_url;

    /**
     * The API key used for authorization.
     *
     * @since    1.0.1
     * @access   private
     * @var      string    $api_key    The API key for n8n authorization.
     */
    private $api_key;

    /**
     * Flag to indicate if n8n integration is properly configured.
     *
     * @since    1.0.1
     * @access   private
     * @var      bool    $is_configured    Whether n8n integration is configured.
     */
    private $is_configured;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.1
     * @param    string    $plugin_name       The name of this plugin.
     * @param    string    $version           The version of this plugin.
     */
    public function __construct($plugin_name, $version) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;

        // Get n8n settings
        $this->n8n_url = get_option('wp_marketing_n8n_url', '');
        $this->api_key = get_option('wp_marketing_api_key', '');
        $this->is_configured = !empty($this->n8n_url) && !empty($this->api_key);

        // Register REST API endpoints for n8n callbacks
        $this->register_rest_routes();
    }

    /**
     * Register REST API routes for n8n callbacks.
     *
     * @since    1.0.1
     */
    public function register_rest_routes() {
        add_action('rest_api_init', array($this, 'register_api_endpoints'));
    }

    /**
     * Register the API endpoints for n8n callbacks.
     *
     * @since    1.0.1
     */
    public function register_api_endpoints() {
        // Register n8n webhook callback route
        register_rest_route('wp-marketing/v1', '/n8n-webhook/(?P<webhook_id>\w+)', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_n8n_webhook'),
            'permission_callback' => array($this, 'validate_webhook_request'),
            'args' => array(
                'webhook_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_string($param) && !empty($param);
                    }
                ),
            ),
        ));

        // Register n8n workflow execution status callback route
        register_rest_route('wp-marketing/v1', '/n8n-workflow/status', array(
            'methods' => 'POST',
            'callback' => array($this, 'handle_workflow_status'),
            'permission_callback' => array($this, 'validate_api_key'),
            'args' => array(
                'execution_id' => array(
                    'required' => true,
                ),
                'campaign_id' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return is_numeric($param);
                    }
                ),
                'status' => array(
                    'required' => true,
                    'validate_callback' => function($param) {
                        return in_array($param, array('started', 'success', 'error', 'warning'));
                    }
                ),
                'message' => array(
                    'required' => false,
                ),
            ),
        ));
    }

    /**
     * Validate webhook requests from n8n.
     *
     * @since    1.0.1
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   bool|WP_Error
     */
    public function validate_webhook_request($request) {
        $headers = $request->get_headers();
        $webhook_id = $request['webhook_id'];
        
        // Retrieve the webhook secret for this specific webhook_id
        $stored_webhook_secret = get_option('wp_marketing_webhook_' . $webhook_id, '');
        
        if (empty($stored_webhook_secret)) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid webhook ID.', 'wp-marketing-plugin'),
                array('status' => 403)
            );
        }
        
        // Check if webhook signature is present
        if (!isset($headers['x_n8n_webhook_signature']) || empty($headers['x_n8n_webhook_signature'][0])) {
            return new WP_Error(
                'rest_forbidden',
                __('Webhook signature is required.', 'wp-marketing-plugin'),
                array('status' => 403)
            );
        }
        
        $signature = $headers['x_n8n_webhook_signature'][0];
        $payload = file_get_contents('php://input');
        
        // Calculate expected signature
        $expected_signature = hash_hmac('sha256', $payload, $stored_webhook_secret);
        
        if (!hash_equals($expected_signature, $signature)) {
            return new WP_Error(
                'rest_forbidden',
                __('Invalid webhook signature.', 'wp-marketing-plugin'),
                array('status' => 403)
            );
        }
        
        return true;
    }
    
    /**
     * Validate the API key for REST API requests.
     *
     * @since    1.0.1
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
     * Handle webhook callbacks from n8n.
     *
     * @since    1.0.1
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   WP_REST_Response|WP_Error
     */
    public function handle_n8n_webhook($request) {
        $webhook_id = $request['webhook_id'];
        $payload = json_decode($request->get_body(), true);
        
        // Get webhook type from database by webhook_id
        $webhook_type = get_option('wp_marketing_webhook_type_' . $webhook_id, '');
        
        if (empty($webhook_type)) {
            return new WP_Error(
                'rest_not_found',
                __('Webhook type not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Log webhook payload for debugging
        error_log('N8N Webhook received: ' . $webhook_id . ' - Type: ' . $webhook_type . ' - Payload: ' . print_r($payload, true));
        
        // Process the webhook based on its type
        switch ($webhook_type) {
            case 'campaign_status':
                return $this->process_campaign_status_webhook($payload);
            
            case 'contact_update':
                return $this->process_contact_update_webhook($payload);
                
            case 'qr_code_generated':
                return $this->process_qr_code_webhook($payload);
                
            default:
                return new WP_Error(
                    'rest_invalid_webhook',
                    __('Unsupported webhook type.', 'wp-marketing-plugin'),
                    array('status' => 400)
                );
        }
    }
    
    /**
     * Process campaign status webhook data.
     *
     * @since    1.0.1
     * @param    array    $payload    Webhook payload data.
     * @return   WP_REST_Response|WP_Error
     */
    private function process_campaign_status_webhook($payload) {
        // Validate required fields
        if (!isset($payload['campaign_id']) || !isset($payload['status'])) {
            return new WP_Error(
                'rest_invalid_params',
                __('Missing required fields: campaign_id and status.', 'wp-marketing-plugin'),
                array('status' => 400)
            );
        }
        
        $campaign_id = intval($payload['campaign_id']);
        $status = sanitize_text_field($payload['status']);
        $message = isset($payload['message']) ? sanitize_text_field($payload['message']) : '';
        
        // Validate campaign exists
        $campaign = get_post($campaign_id);
        if (!$campaign || 'marketing_campaign' !== get_post_type($campaign_id)) {
            return new WP_Error(
                'rest_not_found',
                __('Campaign not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Update campaign status
        update_post_meta($campaign_id, 'status', $status);
        update_post_meta($campaign_id, 'last_status_message', $message);
        update_post_meta($campaign_id, 'last_updated_at', current_time('mysql'));
        
        // Additional processing based on status
        switch ($status) {
            case 'sent':
                update_post_meta($campaign_id, 'sent_at', current_time('mysql'));
                break;
                
            case 'completed':
                update_post_meta($campaign_id, 'completed_at', current_time('mysql'));
                // Trigger action for campaign completion
                do_action('wp_marketing_campaign_completed', $campaign_id, $payload);
                break;
                
            case 'failed':
                // Trigger action for campaign failure
                do_action('wp_marketing_campaign_failed', $campaign_id, $message, $payload);
                break;
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Campaign status updated successfully.', 'wp-marketing-plugin')
        ), 200);
    }
    
    /**
     * Process contact update webhook data.
     *
     * @since    1.0.1
     * @param    array    $payload    Webhook payload data.
     * @return   WP_REST_Response|WP_Error
     */
    private function process_contact_update_webhook($payload) {
        // Validate required fields
        if (!isset($payload['contact_id']) || !isset($payload['data'])) {
            return new WP_Error(
                'rest_invalid_params',
                __('Missing required fields: contact_id and data.', 'wp-marketing-plugin'),
                array('status' => 400)
            );
        }
        
        $contact_id = sanitize_text_field($payload['contact_id']);
        $contact_data = $payload['data'];
        
        // Check if the contact exists in our system
        $contact = $this->get_contact_by_external_id($contact_id);
        
        if ($contact) {
            // Update existing contact
            $result = $this->update_contact($contact->ID, $contact_data);
        } else {
            // Create new contact
            $result = $this->create_contact($contact_id, $contact_data);
        }
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Contact updated successfully.', 'wp-marketing-plugin'),
            'contact_id' => $result
        ), 200);
    }
    
    /**
     * Process QR code webhook data.
     *
     * @since    1.0.1
     * @param    array    $payload    Webhook payload data.
     * @return   WP_REST_Response|WP_Error
     */
    private function process_qr_code_webhook($payload) {
        // Validate required fields
        if (!isset($payload['qr_code_id']) || !isset($payload['qr_code_url'])) {
            return new WP_Error(
                'rest_invalid_params',
                __('Missing required fields: qr_code_id and qr_code_url.', 'wp-marketing-plugin'),
                array('status' => 400)
            );
        }
        
        $qr_code_id = intval($payload['qr_code_id']);
        $qr_code_url = esc_url_raw($payload['qr_code_url']);
        
        // Update QR code record
        update_post_meta($qr_code_id, 'qr_code_url', $qr_code_url);
        update_post_meta($qr_code_id, 'status', 'generated');
        update_post_meta($qr_code_id, 'generated_at', current_time('mysql'));
        
        // Trigger action for QR code generation
        do_action('wp_marketing_qr_code_generated', $qr_code_id, $qr_code_url, $payload);
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('QR code processed successfully.', 'wp-marketing-plugin')
        ), 200);
    }
    
    /**
     * Handle workflow execution status updates from n8n.
     *
     * @since    1.0.1
     * @param    WP_REST_Request    $request    Full data about the request.
     * @return   WP_REST_Response|WP_Error
     */
    public function handle_workflow_status($request) {
        $execution_id = sanitize_text_field($request['execution_id']);
        $campaign_id = intval($request['campaign_id']);
        $status = sanitize_text_field($request['status']);
        $message = isset($request['message']) ? sanitize_text_field($request['message']) : '';
        
        // Validate campaign exists
        $campaign = get_post($campaign_id);
        if (!$campaign || 'marketing_campaign' !== get_post_type($campaign_id)) {
            return new WP_Error(
                'rest_not_found',
                __('Campaign not found.', 'wp-marketing-plugin'),
                array('status' => 404)
            );
        }
        
        // Update campaign meta with workflow execution info
        update_post_meta($campaign_id, 'workflow_execution_id', $execution_id);
        update_post_meta($campaign_id, 'workflow_status', $status);
        update_post_meta($campaign_id, 'workflow_message', $message);
        update_post_meta($campaign_id, 'workflow_updated_at', current_time('mysql'));
        
        // If workflow failed or succeeded, update campaign status
        if ($status === 'error') {
            update_post_meta($campaign_id, 'status', 'failed');
            update_post_meta($campaign_id, 'last_status_message', $message);
        } elseif ($status === 'success') {
            update_post_meta($campaign_id, 'status', 'queued');
        } elseif ($status === 'started') {
            update_post_meta($campaign_id, 'status', 'processing');
        }
        
        return new WP_REST_Response(array(
            'success' => true,
            'message' => __('Workflow status updated successfully.', 'wp-marketing-plugin')
        ), 200);
    }
    
    /**
     * Trigger a campaign execution via n8n.
     *
     * @since    1.0.1
     * @param    int       $campaign_id    Campaign ID.
     * @return   array                     Result of the operation with success status and message.
     */
    public function trigger_campaign($campaign_id) {
        if (!$this->is_configured) {
            return array(
                'success' => false,
                'message' => __('n8n integration is not properly configured.', 'wp-marketing-plugin')
            );
        }
        
        $campaign = get_post($campaign_id);
        if (!$campaign || 'marketing_campaign' !== get_post_type($campaign_id)) {
            return array(
                'success' => false,
                'message' => __('Campaign not found.', 'wp-marketing-plugin')
            );
        }
        
        // Get campaign details
        $list_id = get_post_meta($campaign_id, 'list_id', true);
        $template_id = get_post_meta($campaign_id, 'template_id', true);
        
        if (empty($list_id) || empty($template_id)) {
            return array(
                'success' => false,
                'message' => __('Campaign has missing list or template.', 'wp-marketing-plugin')
            );
        }
        
        // Prepare webhook trigger endpoint on n8n
        $trigger_url = trailingslashit($this->n8n_url) . 'webhook/marketing-campaign-trigger';
        
        // Prepare campaign data
        $campaign_data = array(
            'campaign_id' => $campaign_id,
            'campaign_title' => $campaign->post_title,
            'list_id' => $list_id,
            'template_id' => $template_id,
            'user_id' => $campaign->post_author,
            'callback_url' => rest_url('wp-marketing/v1/n8n-workflow/status'),
            'timestamp' => current_time('timestamp')
        );
        
        // Make API request to n8n webhook
        $response = wp_remote_post($trigger_url, array(
            'headers' => array(
                'Content-Type' => 'application/json',
                'X-API-Key' => $this->api_key
            ),
            'body' => json_encode($campaign_data),
            'timeout' => 30
        ));
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            
            // Update campaign status
            update_post_meta($campaign_id, 'status', 'failed');
            update_post_meta($campaign_id, 'last_status_message', $error_message);
            
            return array(
                'success' => false,
                'message' => __('Failed to connect to n8n: ', 'wp-marketing-plugin') . $error_message
            );
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code !== 200) {
            $error_message = isset($response_body['message']) ? $response_body['message'] : __('Unknown error', 'wp-marketing-plugin');
            
            // Update campaign status
            update_post_meta($campaign_id, 'status', 'failed');
            update_post_meta($campaign_id, 'last_status_message', $error_message);
            
            return array(
                'success' => false,
                'message' => __('n8n returned an error: ', 'wp-marketing-plugin') . $error_message
            );
        }
        
        // Update campaign status
        update_post_meta($campaign_id, 'status', 'processing');
        update_post_meta($campaign_id, 'started_at', current_time('mysql'));
        
        // Store workflow execution id if provided
        if (isset($response_body['executionId'])) {
            update_post_meta($campaign_id, 'workflow_execution_id', $response_body['executionId']);
        }
        
        return array(
            'success' => true,
            'message' => __('Campaign triggered successfully.', 'wp-marketing-plugin')
        );
    }
    
    /**
     * Check if n8n integration is properly configured.
     *
     * @since    1.0.1
     * @return   bool    Whether n8n integration is configured.
     */
    public function is_configured() {
        return $this->is_configured;
    }
    
    /**
     * Helper method to get a contact by external ID.
     *
     * @since    1.0.1
     * @param    string    $external_id    External contact ID.
     * @return   WP_Post|null              Contact post or null if not found.
     */
    private function get_contact_by_external_id($external_id) {
        $args = array(
            'post_type' => 'marketing_contact',
            'meta_query' => array(
                array(
                    'key' => 'external_id',
                    'value' => $external_id,
                    'compare' => '='
                )
            ),
            'posts_per_page' => 1
        );
        
        $query = new WP_Query($args);
        
        if ($query->have_posts()) {
            return $query->posts[0];
        }
        
        return null;
    }
    
    /**
     * Helper method to create a new contact.
     *
     * @since    1.0.1
     * @param    string    $external_id     External contact ID.
     * @param    array     $contact_data    Contact data.
     * @return   int|WP_Error               Contact ID or WP_Error.
     */
    private function create_contact($external_id, $contact_data) {
        $name = isset($contact_data['name']) ? sanitize_text_field($contact_data['name']) : __('Unknown', 'wp-marketing-plugin');
        $phone = isset($contact_data['phone']) ? sanitize_text_field($contact_data['phone']) : '';
        $email = isset($contact_data['email']) ? sanitize_email($contact_data['email']) : '';
        
        // Create contact post
        $post_id = wp_insert_post(array(
            'post_title' => $name,
            'post_type' => 'marketing_contact',
            'post_status' => 'publish'
        ));
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // Save contact meta
        update_post_meta($post_id, 'external_id', $external_id);
        update_post_meta($post_id, 'phone', $phone);
        update_post_meta($post_id, 'email', $email);
        
        // Save additional fields as meta
        foreach ($contact_data as $key => $value) {
            if (!in_array($key, array('name', 'phone', 'email'))) {
                update_post_meta($post_id, sanitize_key($key), sanitize_text_field($value));
            }
        }
        
        return $post_id;
    }
    
    /**
     * Helper method to update an existing contact.
     *
     * @since    1.0.1
     * @param    int      $contact_id      Contact post ID.
     * @param    array    $contact_data    Contact data.
     * @return   int|WP_Error              Contact ID or WP_Error.
     */
    private function update_contact($contact_id, $contact_data) {
        $update_data = array(
            'ID' => $contact_id
        );
        
        // Update contact title if name is provided
        if (isset($contact_data['name'])) {
            $update_data['post_title'] = sanitize_text_field($contact_data['name']);
            wp_update_post($update_data);
        }
        
        // Update standard fields
        if (isset($contact_data['phone'])) {
            update_post_meta($contact_id, 'phone', sanitize_text_field($contact_data['phone']));
        }
        
        if (isset($contact_data['email'])) {
            update_post_meta($contact_id, 'email', sanitize_email($contact_data['email']));
        }
        
        // Update additional fields
        foreach ($contact_data as $key => $value) {
            if (!in_array($key, array('name', 'phone', 'email'))) {
                update_post_meta($contact_id, sanitize_key($key), sanitize_text_field($value));
            }
        }
        
        return $contact_id;
    }
    
    /**
     * Register a webhook with n8n.
     *
     * @since    1.0.1
     * @param    string    $webhook_id      Webhook unique identifier.
     * @param    string    $webhook_type    Type of the webhook.
     * @return   array                      Result of the operation.
     */
    public function register_webhook($webhook_id, $webhook_type) {
        if (!$this->is_configured) {
            return array(
                'success' => false,
                'message' => __('n8n integration is not properly configured.', 'wp-marketing-plugin')
            );
        }
        
        // Generate a webhook secret
        $webhook_secret = wp_generate_password(32, false);
        
        // Save webhook details to options
        update_option('wp_marketing_webhook_' . $webhook_id, $webhook_secret);
        update_option('wp_marketing_webhook_type_' . $webhook_id, $webhook_type);
        
        // Generate the webhook URL
        $webhook_url = rest_url('wp-marketing/v1/n8n-webhook/' . $webhook_id);
        
        return array(
            'success' => true,
            'webhook_id' => $webhook_id,
            'webhook_url' => $webhook_url,
            'webhook_secret' => $webhook_secret,
            'message' => __('Webhook registered successfully.', 'wp-marketing-plugin')
        );
    }
}
