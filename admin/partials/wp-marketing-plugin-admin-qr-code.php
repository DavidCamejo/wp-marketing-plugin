<?php
/**
 * Provide a admin area view for generating WhatsApp QR codes
 *
 * This file is used to markup the admin-facing aspects of the plugin
 * for QR code generation.
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

// Get current user ID for filtering
$current_user_id = get_current_user_id();

// Get any previously generated QR codes
global $wpdb;
$qr_codes_table = $wpdb->prefix . 'marketing_qr_codes';

// Create table if it doesn't exist
if ($wpdb->get_var("SHOW TABLES LIKE '$qr_codes_table'") != $qr_codes_table) {
    $charset_collate = $wpdb->get_charset_collate();
    $sql = "CREATE TABLE $qr_codes_table (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) NOT NULL,
        phone_number varchar(20) NOT NULL,
        message text,
        qr_url varchar(255) NOT NULL,
        qr_svg_url varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Get user's QR codes
$qr_codes = $wpdb->get_results(
    $wpdb->prepare("SELECT * FROM $qr_codes_table WHERE user_id = %d ORDER BY created_at DESC", $current_user_id)
);

// Phone number validation function
function validate_phone_number($phone) {
    // Remove any non-numeric characters except + at the beginning
    $phone = preg_replace('/[^0-9\+]/', '', $phone);
    
    // Ensure it starts with a + followed by numbers
    if (preg_match('/^\+[0-9]{10,15}$/', $phone)) {
        return $phone;
    }
    
    return false;
}

// Handle form submission for QR code generation
if (isset($_POST['generate_qr']) && isset($_POST['qr_nonce']) && wp_verify_nonce($_POST['qr_nonce'], 'generate_qr_nonce')) {
    $phone_number = isset($_POST['phone_number']) ? sanitize_text_field($_POST['phone_number']) : '';
    $message = isset($_POST['message']) ? sanitize_textarea_field($_POST['message']) : '';
    
    // Validate phone number
    $formatted_phone = validate_phone_number($phone_number);
    
    if (!$formatted_phone) {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Please enter a valid phone number with country code (e.g. +12345678901).', 'wp-marketing-plugin') . '</p></div>';
    } else {
        // Generate WhatsApp QR code URL
        $whatsapp_url = 'https://wa.me/' . substr($formatted_phone, 1); // Remove the + prefix
        
        if (!empty($message)) {
            $whatsapp_url .= '?text=' . urlencode($message);
        }
        
        // Generate QR code using Google Charts API
        $qr_size = '300x300';
        $qr_url = 'https://chart.googleapis.com/chart?chs=' . $qr_size . '&cht=qr&chl=' . urlencode($whatsapp_url) . '&choe=UTF-8';
        
        // Generate SVG version using QRickit API
        $qr_svg_url = 'https://qrickit.com/api/qr.php?d=' . urlencode($whatsapp_url) . '&qrsize=300&t=s&e=m';
        
        // Save to database
        $wpdb->insert(
            $qr_codes_table,
            array(
                'user_id' => $current_user_id,
                'phone_number' => $formatted_phone,
                'message' => $message,
                'qr_url' => $qr_url,
                'qr_svg_url' => $qr_svg_url,
                'created_at' => current_time('mysql')
            )
        );
        
        if ($wpdb->insert_id) {
            // Refresh the list of QR codes
            $qr_codes = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $qr_codes_table WHERE user_id = %d ORDER BY created_at DESC", $current_user_id)
            );
            
            echo '<div class="notice notice-success is-dismissible"><p>' . __('QR code generated successfully.', 'wp-marketing-plugin') . '</p></div>';
        } else {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to generate QR code. Please try again.', 'wp-marketing-plugin') . '</p></div>';
        }
    }
}

// Handle QR code deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete_qr' && isset($_GET['qr_id']) && isset($_GET['_wpnonce'])) {
    $qr_id = intval($_GET['qr_id']);
    
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_qr_' . $qr_id)) {
        // Check if this QR code belongs to the current user
        $qr_code = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $qr_codes_table WHERE id = %d AND user_id = %d", $qr_id, $current_user_id)
        );
        
        if ($qr_code) {
            $wpdb->delete($qr_codes_table, array('id' => $qr_id));
            echo '<div class="notice notice-success is-dismissible"><p>' . __('QR code deleted successfully.', 'wp-marketing-plugin') . '</p></div>';
            
            // Refresh the list of QR codes
            $qr_codes = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $qr_codes_table WHERE user_id = %d ORDER BY created_at DESC", $current_user_id)
            );
        }
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="qr-code-generator-container">
        <div class="qr-code-generator">
            <h2><?php _e('Generate WhatsApp QR Code', 'wp-marketing-plugin'); ?></h2>
            
            <form id="generate_qr_form" method="post" action="">
                <?php wp_nonce_field('generate_qr_nonce', 'qr_nonce'); ?>
                
                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label for="phone_number"><?php _e('WhatsApp Phone Number', 'wp-marketing-plugin'); ?></label>
                            </th>
                            <td>
                                <input name="phone_number" type="text" id="qr_phone_number" value="" class="regular-text" required>
                                <p class="description">
                                    <?php _e('Enter your phone number with country code (e.g. +12345678901).', 'wp-marketing-plugin'); ?>
                                </p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="message"><?php _e('Pre-filled Message (optional)', 'wp-marketing-plugin'); ?></label>
                            </th>
                            <td>
                                <textarea name="message" id="qr_message" class="large-text" rows="4"></textarea>
                                <p class="description">
                                    <?php _e('Enter an optional message that will be pre-filled when someone scans the QR code.', 'wp-marketing-plugin'); ?>
                                </p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <p class="submit">
                    <input type="submit" name="generate_qr" class="button button-primary" value="<?php _e('Generate QR Code', 'wp-marketing-plugin'); ?>">
                </p>
            </form>
            
            <div id="qr_status"></div>
        </div>
        
        <div class="qr-code-display">
            <div id="qr_code_container" class="qr-code-preview">
                <!-- QR code will be displayed here via AJAX -->
            </div>
            <div id="qr_download_container" class="qr-download-buttons">
                <!-- Download buttons will be displayed here via AJAX -->
            </div>
        </div>
    </div>
    
    <?php if (!empty($qr_codes)) : ?>
        <h2><?php _e('Your QR Codes', 'wp-marketing-plugin'); ?></h2>
        
        <div class="qr-codes-list">
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-qr"><?php _e('QR Code', 'wp-marketing-plugin'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Phone Number', 'wp-marketing-plugin'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Message', 'wp-marketing-plugin'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Created', 'wp-marketing-plugin'); ?></th>
                        <th scope="col" class="manage-column"><?php _e('Actions', 'wp-marketing-plugin'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($qr_codes as $qr_code) : ?>
                        <tr>
                            <td class="column-qr">
                                <img src="<?php echo esc_url($qr_code->qr_url); ?>" alt="WhatsApp QR Code" width="100" height="100">
                            </td>
                            <td><?php echo esc_html($qr_code->phone_number); ?></td>
                            <td>
                                <?php if (!empty($qr_code->message)) : ?>
                                    <div class="qr-message-preview"><?php echo esc_html(wp_trim_words($qr_code->message, 10)); ?></div>
                                    <?php if (str_word_count($qr_code->message) > 10) : ?>
                                        <a href="#" class="toggle-full-message"><?php _e('Show more', 'wp-marketing-plugin'); ?></a>
                                        <div class="full-message" style="display: none;">
                                            <?php echo esc_html($qr_code->message); ?>
                                        </div>
                                    <?php endif; ?>
                                <?php else : ?>
                                    <em><?php _e('No message', 'wp-marketing-plugin'); ?></em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($qr_code->created_at))); ?>
                            </td>
                            <td class="actions">
                                <a href="<?php echo esc_url($qr_code->qr_url); ?>" download="whatsapp-qr-<?php echo esc_attr($qr_code->id); ?>.png" class="button button-small">
                                    <?php _e('Download PNG', 'wp-marketing-plugin'); ?>
                                </a>
                                <a href="<?php echo esc_url($qr_code->qr_svg_url); ?>" download="whatsapp-qr-<?php echo esc_attr($qr_code->id); ?>.svg" class="button button-small">
                                    <?php _e('Download SVG', 'wp-marketing-plugin'); ?>
                                </a>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-qr-code&action=delete_qr&qr_id=' . $qr_code->id), 'delete_qr_' . $qr_code->id)); ?>" class="button button-small delete-qr" onclick="return confirm('<?php _e('Are you sure you want to delete this QR code?', 'wp-marketing-plugin'); ?>');">
                                    <?php _e('Delete', 'wp-marketing-plugin'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else : ?>
        <div class="notice notice-info">
            <p><?php _e('No QR codes generated yet. Use the form above to create your first WhatsApp QR code.', 'wp-marketing-plugin'); ?></p>
        </div>
    <?php endif; ?>
</div>

<style>
    .qr-code-generator-container {
        display: flex;
        flex-wrap: wrap;
        margin-top: 20px;
    }
    
    .qr-code-generator {
        flex: 1 1 60%;
        margin-right: 20px;
        margin-bottom: 20px;
    }
    
    .qr-code-display {
        flex: 1 1 30%;
        min-width: 300px;
    }
    
    .qr-code-preview {
        margin-top: 25px;
        padding: 10px;
        text-align: center;
    }
    
    .qr-code-preview img {
        max-width: 100%;
        height: auto;
    }
    
    .qr-download-buttons {
        margin-top: 10px;
        text-align: center;
    }
    
    .qr-codes-list {
        margin-top: 20px;
    }
    
    .column-qr {
        width: 120px;
    }
    
    .qr-message-preview {
        margin-bottom: 5px;
    }
    
    .full-message {
        background-color: #f9f9f9;
        padding: 10px;
        border-radius: 3px;
        margin-top: 5px;
    }
    
    @media (max-width: 782px) {
        .qr-code-generator {
            flex: 1 1 100%;
            margin-right: 0;
        }
    }
</style>

<script>
    jQuery(document).ready(function($) {
        // Toggle full message display
        $('.toggle-full-message').on('click', function(e) {
            e.preventDefault();
            var $fullMessage = $(this).next('.full-message');
            
            if ($fullMessage.is(':visible')) {
                $fullMessage.hide();
                $(this).text('<?php _e('Show more', 'wp-marketing-plugin'); ?>');
            } else {
                $fullMessage.show();
                $(this).text('<?php _e('Show less', 'wp-marketing-plugin'); ?>');
            }
        });
    });
</script>