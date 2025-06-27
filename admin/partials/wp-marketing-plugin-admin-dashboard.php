<?php
/**
 * Admin dashboard template for the WordPress Marketing Plugin.
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
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <?php
    // Display admin notices
    settings_errors('wp-marketing-plugin-messages');
    
    // Get statistics
    global $wpdb;
    $current_user_id = get_current_user_id();
    $user_query_part = current_user_can('administrator') ? '' : $wpdb->prepare("AND post_author = %d", $current_user_id);
    
    // Count contacts
    $contact_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' $user_query_part",
        'marketing_contact'
    ));
    
    // Count lists
    $list_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' $user_query_part",
        'marketing_list'
    ));
    
    // Count templates
    $template_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' $user_query_part",
        'message_template'
    ));
    
    // Count campaigns
    $campaign_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_status = 'publish' $user_query_part",
        'marketing_campaign'
    ));
    
    // Count active campaigns
    $active_campaigns = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->posts} p JOIN {$wpdb->postmeta} m ON p.ID = m.post_id 
         WHERE p.post_type = %s AND p.post_status = 'publish' $user_query_part
         AND m.meta_key = 'status' AND m.meta_value = 'active'",
        'marketing_campaign'
    ));
    
    // Get recent campaigns
    $recent_campaigns = $wpdb->get_results($wpdb->prepare(
        "SELECT p.ID, p.post_title, p.post_date, m1.meta_value as status, m2.meta_value as total_messages, m3.meta_value as total_sent 
         FROM {$wpdb->posts} p
         LEFT JOIN {$wpdb->postmeta} m1 ON p.ID = m1.post_id AND m1.meta_key = 'status'
         LEFT JOIN {$wpdb->postmeta} m2 ON p.ID = m2.post_id AND m2.meta_key = 'total_messages'
         LEFT JOIN {$wpdb->postmeta} m3 ON p.ID = m3.post_id AND m3.meta_key = 'total_sent'
         WHERE p.post_type = %s AND p.post_status = 'publish' $user_query_part
         ORDER BY p.post_date DESC LIMIT 5",
        'marketing_campaign'
    ));
    ?>
    
    <div class="wp-marketing-dashboard">
        <div class="wp-marketing-card">
            <h3><?php _e('Contacts', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <p class="wp-marketing-card-number"><?php echo esc_html($contact_count); ?></p>
                <p><?php _e('Total Contacts', 'wp-marketing-plugin'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists')); ?>" class="button button-primary"><?php _e('Manage Contacts', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
        
        <div class="wp-marketing-card">
            <h3><?php _e('Lists', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <p class="wp-marketing-card-number"><?php echo esc_html($list_count); ?></p>
                <p><?php _e('Contact Lists', 'wp-marketing-plugin'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists')); ?>" class="button button-primary"><?php _e('Manage Lists', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
        
        <div class="wp-marketing-card">
            <h3><?php _e('Templates', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <p class="wp-marketing-card-number"><?php echo esc_html($template_count); ?></p>
                <p><?php _e('Message Templates', 'wp-marketing-plugin'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates')); ?>" class="button button-primary"><?php _e('Manage Templates', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
        
        <div class="wp-marketing-card">
            <h3><?php _e('Campaigns', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <p class="wp-marketing-card-number"><?php echo esc_html($campaign_count); ?></p>
                <p><?php _e('Total Campaigns', 'wp-marketing-plugin'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-campaigns')); ?>" class="button button-primary"><?php _e('Manage Campaigns', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
        
        <div class="wp-marketing-card">
            <h3><?php _e('Active Campaigns', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <p class="wp-marketing-card-number"><?php echo esc_html($active_campaigns); ?></p>
                <p><?php _e('Currently Running', 'wp-marketing-plugin'); ?></p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-campaigns')); ?>" class="button button-primary"><?php _e('View Active Campaigns', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
        
        <div class="wp-marketing-card">
            <h3><?php _e('Quick Actions', 'wp-marketing-plugin'); ?></h3>
            <div class="wp-marketing-card-content">
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=new')); ?>" class="button"><?php _e('Create Contact List', 'wp-marketing-plugin'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates&action=new')); ?>" class="button"><?php _e('Create Template', 'wp-marketing-plugin'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-campaigns&action=new')); ?>" class="button"><?php _e('Create Campaign', 'wp-marketing-plugin'); ?></a>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-qr-code')); ?>" class="button"><?php _e('Generate QR Code', 'wp-marketing-plugin'); ?></a>
            </div>
        </div>
    </div>
    
    <div class="wp-marketing-recent">
        <h2><?php _e('Recent Campaigns', 'wp-marketing-plugin'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Campaign', 'wp-marketing-plugin'); ?></th>
                    <th><?php _e('Status', 'wp-marketing-plugin'); ?></th>
                    <th><?php _e('Messages', 'wp-marketing-plugin'); ?></th>
                    <th><?php _e('Date', 'wp-marketing-plugin'); ?></th>
                    <th><?php _e('Actions', 'wp-marketing-plugin'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recent_campaigns)): ?>
                <tr>
                    <td colspan="5"><?php _e('No campaigns found.', 'wp-marketing-plugin'); ?></td>
                </tr>
                <?php else: ?>
                    <?php foreach ($recent_campaigns as $campaign): ?>
                    <tr>
                        <td>
                            <strong>
                                <a href="<?php echo esc_url(admin_url('post.php?post=' . $campaign->ID . '&action=edit')); ?>">
                                    <?php echo esc_html($campaign->post_title); ?>
                                </a>
                            </strong>
                        </td>
                        <td>
                            <?php 
                            $status = !empty($campaign->status) ? $campaign->status : 'draft';
                            $status_label = '';
                            $status_class = '';
                            
                            switch ($status) {
                                case 'pending':
                                    $status_label = __('Pending', 'wp-marketing-plugin');
                                    $status_class = 'status-pending';
                                    break;
                                case 'active':
                                    $status_label = __('Active', 'wp-marketing-plugin');
                                    $status_class = 'status-active';
                                    break;
                                case 'completed':
                                    $status_label = __('Completed', 'wp-marketing-plugin');
                                    $status_class = 'status-completed';
                                    break;
                                case 'scheduled':
                                    $status_label = __('Scheduled', 'wp-marketing-plugin');
                                    $status_class = 'status-scheduled';
                                    break;
                                default:
                                    $status_label = __('Draft', 'wp-marketing-plugin');
                                    $status_class = 'status-draft';
                            }
                            ?>
                            <span class="wp-marketing-status <?php echo esc_attr($status_class); ?>">
                                <?php echo esc_html($status_label); ?>
                            </span>
                        </td>
                        <td>
                            <?php
                            $total_messages = !empty($campaign->total_messages) ? intval($campaign->total_messages) : 0;
                            $total_sent = !empty($campaign->total_sent) ? intval($campaign->total_sent) : 0;
                            if ($total_messages > 0) {
                                echo esc_html($total_sent . '/' . $total_messages);
                                $percent = round(($total_sent / $total_messages) * 100);
                                echo '<div class="wp-marketing-progress"><div class="wp-marketing-progress-bar" style="width: ' . esc_attr($percent) . '%;"></div></div>';
                            } else {
                                echo '0';
                            }
                            ?>
                        </td>
                        <td>
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($campaign->post_date))); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $campaign->ID . '&action=edit')); ?>" class="button button-small">
                                <?php _e('View', 'wp-marketing-plugin'); ?>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .wp-marketing-dashboard {
        margin-top: 20px;
    }
    .wp-marketing-card {
        padding: 10px;
    }
    .wp-marketing-card-number {
        font-size: 36px;
        font-weight: bold;
        margin: 10px 0;
    }
    .wp-marketing-card-content {
        text-align: center;
    }
    .wp-marketing-card-content .button {
        margin-top: 10px;
    }
    .wp-marketing-recent {
        margin-top: 30px;
    }
    .wp-marketing-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
        font-size: 12px;
        font-weight: bold;
    }
    .status-pending {
        background-color: #f8dda7;
        color: #94660c;
    }
    .status-active {
        background-color: #c6e1c6;
        color: #5b841b;
    }
    .status-completed {
        background-color: #c8d7e1;
        color: #2e4453;
    }
    .status-scheduled {
        background-color: #e5f5fa;
        color: #0e648d;
    }
    .status-draft {
        background-color: #e5e5e5;
        color: #646970;
    }
    .wp-marketing-progress {
        background: #e5e5e5;
        height: 5px;
        margin-top: 5px;
        border-radius: 2px;
        overflow: hidden;
    }
    .wp-marketing-progress-bar {
        background: #0073aa;
        height: 100%;
    }
</style>