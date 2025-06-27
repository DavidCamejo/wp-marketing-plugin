<?php
/**
 * Admin settings template for the WordPress Marketing Plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Save settings if form is submitted
if (isset($_POST['wp_marketing_settings_submit']) && current_user_can('administrator')) {
    check_admin_referer('wp_marketing_settings_nonce', 'wp_marketing_settings_nonce');
    
    // Sanitize and save all settings
    $n8n_url = isset($_POST['n8n_url']) ? esc_url_raw($_POST['n8n_url']) : '';
    update_option('wp_marketing_n8n_url', $n8n_url);
    
    $api_key = isset($_POST['api_key']) ? sanitize_text_field($_POST['api_key']) : '';
    update_option('wp_marketing_api_key', $api_key);
    
    $evolution_api_url = isset($_POST['evolution_api_url']) ? esc_url_raw($_POST['evolution_api_url']) : '';
    update_option('wp_marketing_evolution_api_url', $evolution_api_url);
    
    $enable_qr_code = isset($_POST['enable_qr_code']) ? 1 : 0;
    update_option('wp_marketing_enable_qr_code', $enable_qr_code);
    
    $max_contacts_per_import = isset($_POST['max_contacts_per_import']) ? absint($_POST['max_contacts_per_import']) : 1000;
    update_option('wp_marketing_max_contacts_per_import', $max_contacts_per_import);
    
    $max_contacts_per_campaign = isset($_POST['max_contacts_per_campaign']) ? absint($_POST['max_contacts_per_campaign']) : 500;
    update_option('wp_marketing_max_contacts_per_campaign', $max_contacts_per_campaign);
    
    // Display success message
    add_settings_error(
        'wp-marketing-plugin-messages',
        'wp-marketing-settings-updated',
        __('Settings saved successfully.', 'wp-marketing-plugin'),
        'updated'
    );
}

// Get current settings
$n8n_url = get_option('wp_marketing_n8n_url', '');
$api_key = get_option('wp_marketing_api_key', '');
$evolution_api_url = get_option('wp_marketing_evolution_api_url', '');
$enable_qr_code = get_option('wp_marketing_enable_qr_code', 1);
$max_contacts_per_import = get_option('wp_marketing_max_contacts_per_import', 1000);
$max_contacts_per_campaign = get_option('wp_marketing_max_contacts_per_campaign', 500);
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Display admin notices
    settings_errors('wp-marketing-plugin-messages');
    ?>
    
    <div class="wp-marketing-settings-container">
        <form method="post" action="">
            <?php wp_nonce_field('wp_marketing_settings_nonce', 'wp_marketing_settings_nonce'); ?>
            
            <div class="wp-marketing-settings-section">
                <h2><?php _e('API Connections', 'wp-marketing-plugin'); ?></h2>
                <p class="description"><?php _e('Configure connections to n8n and Evolution API for campaign execution.', 'wp-marketing-plugin'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="n8n_url"><?php _e('n8n URL', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="n8n_url" name="n8n_url" class="regular-text" value="<?php echo esc_attr($n8n_url); ?>" placeholder="https://your-n8n-instance.com" />
                            <p class="description"><?php _e('URL of your n8n instance, including protocol (e.g., https://)', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="evolution_api_url"><?php _e('Evolution API URL', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="evolution_api_url" name="evolution_api_url" class="regular-text" value="<?php echo esc_attr($evolution_api_url); ?>" placeholder="https://your-evolution-api.com" />
                            <p class="description"><?php _e('URL of your Evolution API instance, including protocol (e.g., https://)', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="api_key"><?php _e('API Key', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="password" id="api_key" name="api_key" class="regular-text" value="<?php echo esc_attr($api_key); ?>" />
                            <p class="description"><?php _e('API key for authentication between WordPress and n8n/Evolution API', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
                
                <div class="wp-marketing-connection-test">
                    <button type="button" id="test_n8n_connection" class="button"><?php _e('Test n8n Connection', 'wp-marketing-plugin'); ?></button>
                    <button type="button" id="test_evolution_api_connection" class="button"><?php _e('Test Evolution API Connection', 'wp-marketing-plugin'); ?></button>
                    <span id="connection_test_result"></span>
                </div>
            </div>
            
            <div class="wp-marketing-settings-section">
                <h2><?php _e('Campaign Settings', 'wp-marketing-plugin'); ?></h2>
                <p class="description"><?php _e('Configure default settings for marketing campaigns.', 'wp-marketing-plugin'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="enable_qr_code"><?php _e('Enable QR Code Generation', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="enable_qr_code" name="enable_qr_code" value="1" <?php checked(1, $enable_qr_code); ?> />
                                <?php _e('Enable WhatsApp QR code generation feature', 'wp-marketing-plugin'); ?>
                            </label>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_contacts_per_import"><?php _e('Max Contacts per Import', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="max_contacts_per_import" name="max_contacts_per_import" class="small-text" value="<?php echo esc_attr($max_contacts_per_import); ?>" min="10" max="10000" />
                            <p class="description"><?php _e('Maximum number of contacts that can be imported at once', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="max_contacts_per_campaign"><?php _e('Max Contacts per Campaign', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="max_contacts_per_campaign" name="max_contacts_per_campaign" class="small-text" value="<?php echo esc_attr($max_contacts_per_campaign); ?>" min="10" max="5000" />
                            <p class="description"><?php _e('Maximum number of contacts that can be targeted in a single campaign', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <div class="wp-marketing-settings-section">
                <h2><?php _e('User Access', 'wp-marketing-plugin'); ?></h2>
                <p class="description"><?php _e('Manage which user roles can access marketing features.', 'wp-marketing-plugin'); ?></p>
                
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('User Roles', 'wp-marketing-plugin'); ?></th>
                        <td>
                            <?php
                            $wp_roles = wp_roles();
                            $allowed_roles = get_option('wp_marketing_allowed_roles', array('administrator', 'editor'));
                            
                            foreach ($wp_roles->get_names() as $role => $role_name) {
                                if ($role == 'administrator') {
                                    continue; // Skip administrator since they always have access
                                }
                                $checked = in_array($role, $allowed_roles) ? 'checked="checked"' : '';
                                ?>
                                <label>
                                    <input type="checkbox" name="allowed_roles[]" value="<?php echo esc_attr($role); ?>" <?php echo $checked; ?> />
                                    <?php echo esc_html($role_name); ?>
                                </label><br>
                                <?php
                            }
                            ?>
                            <p class="description"><?php _e('Select which user roles can access marketing features. Administrators always have access.', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>
            
            <p class="submit">
                <input type="submit" name="wp_marketing_settings_submit" class="button button-primary" value="<?php _e('Save Settings', 'wp-marketing-plugin'); ?>" />
            </p>
        </form>
    </div>
</div>

<script>
(function($) {
    $(document).ready(function() {
        // Test n8n connection
        $('#test_n8n_connection').on('click', function() {
            var n8nUrl = $('#n8n_url').val();
            var apiKey = $('#api_key').val();
            
            if (!n8nUrl) {
                $('#connection_test_result').html('<span class="error"><?php _e('Please enter n8n URL first', 'wp-marketing-plugin'); ?></span>');
                return;
            }
            
            $('#connection_test_result').html('<span class="testing"><?php _e('Testing connection...', 'wp-marketing-plugin'); ?></span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_marketing_test_n8n_connection',
                    nonce: '<?php echo wp_create_nonce('wp_marketing_test_connection'); ?>',
                    url: n8nUrl,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        $('#connection_test_result').html('<span class="success">' + response.data.message + '</span>');
                    } else {
                        $('#connection_test_result').html('<span class="error">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $('#connection_test_result').html('<span class="error"><?php _e('Connection test failed', 'wp-marketing-plugin'); ?></span>');
                }
            });
        });
        
        // Test Evolution API connection
        $('#test_evolution_api_connection').on('click', function() {
            var evolutionApiUrl = $('#evolution_api_url').val();
            var apiKey = $('#api_key').val();
            
            if (!evolutionApiUrl) {
                $('#connection_test_result').html('<span class="error"><?php _e('Please enter Evolution API URL first', 'wp-marketing-plugin'); ?></span>');
                return;
            }
            
            $('#connection_test_result').html('<span class="testing"><?php _e('Testing connection...', 'wp-marketing-plugin'); ?></span>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wp_marketing_test_evolution_api_connection',
                    nonce: '<?php echo wp_create_nonce('wp_marketing_test_connection'); ?>',
                    url: evolutionApiUrl,
                    api_key: apiKey
                },
                success: function(response) {
                    if (response.success) {
                        $('#connection_test_result').html('<span class="success">' + response.data.message + '</span>');
                    } else {
                        $('#connection_test_result').html('<span class="error">' + response.data.message + '</span>');
                    }
                },
                error: function() {
                    $('#connection_test_result').html('<span class="error"><?php _e('Connection test failed', 'wp-marketing-plugin'); ?></span>');
                }
            });
        });
    });
})(jQuery);
</script>

<style>
    .wp-marketing-settings-container {
        max-width: 900px;
    }
    .wp-marketing-settings-section {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-radius: 4px;
        margin-bottom: 20px;
        padding: 15px 25px;
    }
    .wp-marketing-settings-section h2 {
        border-bottom: 1px solid #eee;
        padding-bottom: 10px;
    }
    .wp-marketing-connection-test {
        margin: 15px 0;
        padding: 10px;
        background: #f5f5f5;
        border-radius: 3px;
    }
    .wp-marketing-connection-test span {
        display: inline-block;
        margin-left: 10px;
        padding: 5px 0;
    }
    .wp-marketing-connection-test .success {
        color: #46b450;
    }
    .wp-marketing-connection-test .error {
        color: #dc3232;
    }
    .wp-marketing-connection-test .testing {
        color: #ffb900;
    }
</style>