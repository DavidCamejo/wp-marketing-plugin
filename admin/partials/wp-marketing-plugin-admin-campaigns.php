<?php
/**
 * Provide a admin area view for managing campaigns
 *
 * This file is used to markup the admin-facing aspects of the plugin
 * for campaign management.
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

// Query marketing campaigns
$args = array(
    'post_type' => 'marketing_campaign',
    'posts_per_page' => -1,
    'orderby' => 'date',
    'order' => 'DESC',
);

// Filter by user for non-admins
if (!current_user_can('administrator')) {
    $args['author'] = $current_user_id;
}

$campaigns = get_posts($args);

// Get contact lists for dropdown
$list_args = array(
    'post_type' => 'marketing_list',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
);

// Filter lists by user for non-admins
if (!current_user_can('administrator')) {
    $list_args['author'] = $current_user_id;
}

$lists = get_posts($list_args);

// Get message templates for dropdown
$template_args = array(
    'post_type' => 'message_template',
    'posts_per_page' => -1,
    'orderby' => 'title',
    'order' => 'ASC',
);

// Filter templates by user for non-admins
if (!current_user_can('administrator')) {
    $template_args['author'] = $current_user_id;
}

$templates = get_posts($template_args);

// Handle form submissions
if (isset($_POST['create_campaign']) && isset($_POST['campaign_nonce']) && wp_verify_nonce($_POST['campaign_nonce'], 'create_campaign_nonce')) {
    $campaign_title = sanitize_text_field($_POST['campaign_title']);
    $list_id = intval($_POST['list_id']);
    $template_id = intval($_POST['template_id']);
    $scheduled = isset($_POST['schedule_campaign']) ? true : false;
    
    // Default status is draft, will be pending when started
    $status = 'draft';
    
    // Create campaign post
    $campaign_id = wp_insert_post(array(
        'post_title' => $campaign_title,
        'post_type' => 'marketing_campaign',
        'post_status' => 'publish',
        'post_author' => $current_user_id,
    ));
    
    if ($campaign_id) {
        // Save campaign meta
        update_post_meta($campaign_id, 'list_id', $list_id);
        update_post_meta($campaign_id, 'template_id', $template_id);
        update_post_meta($campaign_id, 'status', $status);
        update_post_meta($campaign_id, 'total_messages', 0);
        update_post_meta($campaign_id, 'total_sent', 0);
        update_post_meta($campaign_id, 'total_delivered', 0);
        update_post_meta($campaign_id, 'total_failed', 0);
        
        // Handle scheduling if enabled
        if ($scheduled) {
            $schedule_date = sanitize_text_field($_POST['schedule_date']);
            $schedule_time = sanitize_text_field($_POST['schedule_time']);
            $scheduled_time = $schedule_date . ' ' . $schedule_time . ':00';
            update_post_meta($campaign_id, 'scheduled_time', $scheduled_time);
            update_post_meta($campaign_id, 'is_scheduled', true);
        } else {
            update_post_meta($campaign_id, 'is_scheduled', false);
        }
        
        echo '<div class="notice notice-success is-dismissible"><p>' . __('Campaign created successfully.', 'wp-marketing-plugin') . '</p></div>';
        
        // Refresh the campaigns list
        $campaigns = get_posts($args);
    } else {
        echo '<div class="notice notice-error is-dismissible"><p>' . __('Failed to create campaign.', 'wp-marketing-plugin') . '</p></div>';
    }
}

// Handle campaign actions (start, pause, resume)
if (isset($_GET['action']) && isset($_GET['campaign_id']) && isset($_GET['_wpnonce'])) {
    $action = sanitize_text_field($_GET['action']);
    $campaign_id = intval($_GET['campaign_id']);
    
    if (wp_verify_nonce($_GET['_wpnonce'], 'campaign_action_' . $campaign_id)) {
        $campaign = get_post($campaign_id);
        
        // Verify ownership
        if ($campaign && ($campaign->post_author == $current_user_id || current_user_can('administrator'))) {
            $redirect = admin_url('admin.php?page=wp-marketing-campaigns');
            
            switch ($action) {
                case 'start':
                    // Start campaign
                    update_post_meta($campaign_id, 'status', 'pending');
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Campaign started.', 'wp-marketing-plugin') . '</p></div>';
                    break;
                    
                case 'pause':
                    // Pause campaign
                    update_post_meta($campaign_id, 'status', 'paused');
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Campaign paused.', 'wp-marketing-plugin') . '</p></div>';
                    break;
                    
                case 'resume':
                    // Resume campaign
                    update_post_meta($campaign_id, 'status', 'pending');
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Campaign resumed.', 'wp-marketing-plugin') . '</p></div>';
                    break;
                    
                case 'delete':
                    // Delete campaign
                    wp_delete_post($campaign_id, true);
                    echo '<div class="notice notice-success is-dismissible"><p>' . __('Campaign deleted.', 'wp-marketing-plugin') . '</p></div>';
                    break;
            }
            
            // Refresh the campaigns list
            $campaigns = get_posts($args);
        }
    }
}
?>

<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="tablenav top">
        <div class="alignleft actions">
            <a href="#create-campaign" class="button button-primary"><?php _e('Create New Campaign', 'wp-marketing-plugin'); ?></a>
        </div>
        <br class="clear">
    </div>
    
    <h2><?php _e('Your Campaigns', 'wp-marketing-plugin'); ?></h2>
    
    <?php if (empty($campaigns)) : ?>
        <div class="notice notice-info">
            <p><?php _e('No campaigns found. Create your first campaign using the form below.', 'wp-marketing-plugin'); ?></p>
        </div>
    <?php else : ?>
        <table class="wp-list-table widefat fixed striped posts">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-title column-primary"><?php _e('Campaign', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Status', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Contact List', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Template', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Scheduled', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Progress', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column"><?php _e('Actions', 'wp-marketing-plugin'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign) : 
                    $campaign_id = $campaign->ID;
                    $status = get_post_meta($campaign_id, 'status', true);
                    $list_id = get_post_meta($campaign_id, 'list_id', true);
                    $template_id = get_post_meta($campaign_id, 'template_id', true);
                    $is_scheduled = get_post_meta($campaign_id, 'is_scheduled', true);
                    $scheduled_time = get_post_meta($campaign_id, 'scheduled_time', true);
                    $total_messages = get_post_meta($campaign_id, 'total_messages', true) ?: 0;
                    $total_sent = get_post_meta($campaign_id, 'total_sent', true) ?: 0;
                    $total_delivered = get_post_meta($campaign_id, 'total_delivered', true) ?: 0;
                    $total_failed = get_post_meta($campaign_id, 'total_failed', true) ?: 0;
                    
                    $progress_percent = $total_messages > 0 ? round(($total_sent / $total_messages) * 100) : 0;
                    
                    // Get list title
                    $list_title = '';
                    if ($list_id) {
                        $list = get_post($list_id);
                        if ($list) {
                            $list_title = $list->post_title;
                        }
                    }
                    
                    // Get template title
                    $template_title = '';
                    if ($template_id) {
                        $template = get_post($template_id);
                        if ($template) {
                            $template_title = $template->post_title;
                        }
                    }
                    
                    // Status label and class
                    $status_labels = array(
                        'draft' => __('Draft', 'wp-marketing-plugin'),
                        'pending' => __('In Progress', 'wp-marketing-plugin'),
                        'completed' => __('Completed', 'wp-marketing-plugin'),
                        'paused' => __('Paused', 'wp-marketing-plugin'),
                    );
                    
                    $status_classes = array(
                        'draft' => 'status-draft',
                        'pending' => 'status-pending',
                        'completed' => 'status-completed',
                        'paused' => 'status-paused',
                    );
                    
                    $status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
                    $status_class = isset($status_classes[$status]) ? $status_classes[$status] : '';
                ?>
                <tr>
                    <td class="title column-title has-row-actions column-primary">
                        <strong><?php echo esc_html($campaign->post_title); ?></strong>
                        <div class="row-actions">
                            <span class="edit"><a href="<?php echo esc_url(admin_url('post.php?post=' . $campaign_id . '&action=edit')); ?>"><?php _e('Edit', 'wp-marketing-plugin'); ?></a> | </span>
                            <span class="delete"><a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-campaigns&action=delete&campaign_id=' . $campaign_id), 'campaign_action_' . $campaign_id)); ?>" class="delete-campaign" onclick="return confirm('<?php _e('Are you sure you want to delete this campaign?', 'wp-marketing-plugin'); ?>');"><?php _e('Delete', 'wp-marketing-plugin'); ?></a></span>
                        </div>
                    </td>
                    <td>
                        <span class="status-indicator <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span>
                    </td>
                    <td><?php echo esc_html($list_title); ?></td>
                    <td><?php echo esc_html($template_title); ?></td>
                    <td>
                        <?php if ($is_scheduled && !empty($scheduled_time)) : ?>
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($scheduled_time))); ?>
                        <?php else : ?>
                            <?php _e('No', 'wp-marketing-plugin'); ?>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($total_messages > 0) : ?>
                            <div class="campaign-progress">
                                <div class="progress-bar">
                                    <div class="progress-bar-fill" style="width: <?php echo esc_attr($progress_percent); ?>%;"></div>
                                </div>
                                <div class="progress-text">
                                    <?php echo esc_html(sprintf(__('%d/%d sent (%d delivered, %d failed)', 'wp-marketing-plugin'), 
                                        $total_sent, 
                                        $total_messages,
                                        $total_delivered,
                                        $total_failed
                                    )); ?>
                                </div>
                            </div>
                        <?php else : ?>
                            <?php _e('Not started', 'wp-marketing-plugin'); ?>
                        <?php endif; ?>
                    </td>
                    <td class="actions">
                        <?php if ($status === 'draft') : ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-campaigns&action=start&campaign_id=' . $campaign_id), 'campaign_action_' . $campaign_id)); ?>" class="button button-primary action-start"><?php _e('Start', 'wp-marketing-plugin'); ?></a>
                        <?php elseif ($status === 'pending') : ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-campaigns&action=pause&campaign_id=' . $campaign_id), 'campaign_action_' . $campaign_id)); ?>" class="button action-pause"><?php _e('Pause', 'wp-marketing-plugin'); ?></a>
                        <?php elseif ($status === 'paused') : ?>
                            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-campaigns&action=resume&campaign_id=' . $campaign_id), 'campaign_action_' . $campaign_id)); ?>" class="button action-resume"><?php _e('Resume', 'wp-marketing-plugin'); ?></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <h2 id="create-campaign" class="title"><?php _e('Create New Campaign', 'wp-marketing-plugin'); ?></h2>
    
    <?php if (empty($lists)) : ?>
        <div class="notice notice-warning">
            <p><?php _e('You need to create a contact list before creating a campaign.', 'wp-marketing-plugin'); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists')); ?>" class="button button-secondary"><?php _e('Create Contact List', 'wp-marketing-plugin'); ?></a></p>
        </div>
    <?php elseif (empty($templates)) : ?>
        <div class="notice notice-warning">
            <p><?php _e('You need to create a message template before creating a campaign.', 'wp-marketing-plugin'); ?></p>
            <p><a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates')); ?>" class="button button-secondary"><?php _e('Create Message Template', 'wp-marketing-plugin'); ?></a></p>
        </div>
    <?php else : ?>
        <form method="post" action="" class="campaign-form">
            <?php wp_nonce_field('create_campaign_nonce', 'campaign_nonce'); ?>
            
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row">
                            <label for="campaign_title"><?php _e('Campaign Name', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input name="campaign_title" type="text" id="campaign_title" value="" class="regular-text" required>
                            <p class="description"><?php _e('Enter a name to identify this campaign.', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="list_id"><?php _e('Contact List', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <select name="list_id" id="list_id" class="regular-text" required>
                                <option value=""><?php _e('Select a contact list', 'wp-marketing-plugin'); ?></option>
                                <?php foreach ($lists as $list) : ?>
                                    <option value="<?php echo esc_attr($list->ID); ?>"><?php echo esc_html($list->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select the contact list to use for this campaign.', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="template_id"><?php _e('Message Template', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <select name="template_id" id="template_id" class="regular-text" required>
                                <option value=""><?php _e('Select a message template', 'wp-marketing-plugin'); ?></option>
                                <?php foreach ($templates as $template) : ?>
                                    <option value="<?php echo esc_attr($template->ID); ?>"><?php echo esc_html($template->post_title); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php _e('Select the message template to use for this campaign.', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <?php _e('Schedule Campaign', 'wp-marketing-plugin'); ?>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text">
                                    <span><?php _e('Schedule Campaign', 'wp-marketing-plugin'); ?></span>
                                </legend>
                                <label for="schedule_campaign">
                                    <input name="schedule_campaign" type="checkbox" id="schedule_campaign" value="1">
                                    <?php _e('Schedule this campaign for later', 'wp-marketing-plugin'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    <tr class="campaign-schedule-options" style="display: none;">
                        <th scope="row">
                            <label for="schedule_date"><?php _e('Schedule Date & Time', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input name="schedule_date" type="date" id="schedule_date" class="regular-text">
                            <input name="schedule_time" type="time" id="schedule_time" class="regular-text">
                            <p class="description"><?php _e('Select the date and time when this campaign should be sent.', 'wp-marketing-plugin'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <p class="submit">
                <input type="submit" name="create_campaign" class="button button-primary" value="<?php _e('Create Campaign', 'wp-marketing-plugin'); ?>">
            </p>
        </form>
    <?php endif; ?>
</div>

<style>
    .status-indicator {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 3px;
        font-weight: 500;
    }
    
    .status-draft {
        background-color: #e5e5e5;
    }
    
    .status-pending {
        background-color: #ffb900;
    }
    
    .status-completed {
        background-color: #46b450;
        color: white;
    }
    
    .status-paused {
        background-color: #dc3232;
        color: white;
    }
    
    .campaign-progress {
        width: 100%;
    }
    
    .progress-bar {
        background-color: #f0f0f0;
        height: 20px;
        border-radius: 10px;
        overflow: hidden;
        margin-bottom: 5px;
    }
    
    .progress-bar-fill {
        height: 100%;
        background-color: #2271b1;
    }
    
    .progress-text {
        font-size: 12px;
        color: #666;
    }
</style>