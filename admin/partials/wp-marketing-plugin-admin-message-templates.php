<?php
/**
 * Admin message templates template for the WordPress Marketing Plugin.
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

// Get current action
$action = isset($_GET['action']) ? sanitize_text_field($_GET['action']) : '';
$template_id = isset($_GET['template_id']) ? intval($_GET['template_id']) : 0;

// Handle form submissions
if (isset($_POST['wp_marketing_template_submit'])) {
    check_admin_referer('wp_marketing_template_nonce', 'wp_marketing_template_nonce');
    
    $template_title = sanitize_text_field($_POST['template_title']);
    $template_content = wp_kses_post($_POST['template_content']);
    $template_type = sanitize_text_field($_POST['template_type']);
    
    if (empty($template_title)) {
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-template-error',
            __('Template title cannot be empty.', 'wp-marketing-plugin'),
            'error'
        );
    } elseif (empty($template_content)) {
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-template-error',
            __('Template content cannot be empty.', 'wp-marketing-plugin'),
            'error'
        );
    } else {
        // Create or update template
        $template_data = array(
            'post_title'   => $template_title,
            'post_content' => $template_content,
            'post_status'  => 'publish',
            'post_type'    => 'message_template',
            'post_author'  => get_current_user_id()
        );
        
        if ($template_id > 0) {
            $template_data['ID'] = $template_id;
            wp_update_post($template_data);
            
            // Update meta
            update_post_meta($template_id, 'template_type', $template_type);
            
            add_settings_error(
                'wp-marketing-plugin-messages',
                'wp-marketing-template-updated',
                __('Message template updated successfully.', 'wp-marketing-plugin'),
                'updated'
            );
        } else {
            $template_id = wp_insert_post($template_data);
            
            // Add meta
            update_post_meta($template_id, 'template_type', $template_type);
            
            add_settings_error(
                'wp-marketing-plugin-messages',
                'wp-marketing-template-created',
                __('Message template created successfully.', 'wp-marketing-plugin'),
                'updated'
            );
        }
        
        // Redirect to templates list
        wp_redirect(admin_url('admin.php?page=wp-marketing-message-templates'));
        exit;
    }
}

// Handle template delete
if ($action === 'delete' && $template_id > 0) {
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_template_' . $template_id)) {
        wp_delete_post($template_id, true);
        
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-template-deleted',
            __('Message template deleted successfully.', 'wp-marketing-plugin'),
            'updated'
        );
        
        wp_redirect(admin_url('admin.php?page=wp-marketing-message-templates'));
        exit;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php 
        if ($action === 'new') {
            _e('Create Message Template', 'wp-marketing-plugin');
        } elseif ($action === 'edit' && $template_id > 0) {
            _e('Edit Message Template', 'wp-marketing-plugin');
        } else {
            _e('Message Templates', 'wp-marketing-plugin');
        }
        ?>
    </h1>
    
    <?php 
    if (empty($action) || $action === 'list') { 
        // Add new template button
        ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates&action=new')); ?>" class="page-title-action">
            <?php _e('Add New', 'wp-marketing-plugin'); ?>
        </a>
    <?php } ?>
    
    <hr class="wp-header-end">
    
    <?php
    // Display admin notices
    settings_errors('wp-marketing-plugin-messages');
    
    // Display appropriate view based on action
    if ($action === 'new' || $action === 'edit') {
        // Edit/Create form
        $template_title = '';
        $template_content = '';
        $template_type = 'text';
        
        if ($action === 'edit' && $template_id > 0) {
            $template = get_post($template_id);
            if ($template && $template->post_type === 'message_template') {
                $template_title = $template->post_title;
                $template_content = $template->post_content;
                $template_type = get_post_meta($template_id, 'template_type', true) ?: 'text';
            }
        }
        ?>
        <div class="wp-marketing-template-editor">
            <div class="wp-marketing-template-form">
                <form method="post" action="">
                    <?php wp_nonce_field('wp_marketing_template_nonce', 'wp_marketing_template_nonce'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="template_title"><?php _e('Template Name', 'wp-marketing-plugin'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="template_title" name="template_title" class="regular-text" value="<?php echo esc_attr($template_title); ?>" required />
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="template_type"><?php _e('Template Type', 'wp-marketing-plugin'); ?></label>
                            </th>
                            <td>
                                <select id="template_type" name="template_type">
                                    <option value="text" <?php selected($template_type, 'text'); ?>><?php _e('Text Message', 'wp-marketing-plugin'); ?></option>
                                    <option value="image" <?php selected($template_type, 'image'); ?>><?php _e('Image Message', 'wp-marketing-plugin'); ?></option>
                                    <option value="document" <?php selected($template_type, 'document'); ?>><?php _e('Document Message', 'wp-marketing-plugin'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label for="template_content"><?php _e('Template Content', 'wp-marketing-plugin'); ?></label>
                            </th>
                            <td>
                                <textarea id="template_content" name="template_content" class="large-text code" rows="10" required><?php echo esc_textarea($template_content); ?></textarea>
                                <p class="description">
                                    <?php _e('Use {{variable_name}} placeholders to insert dynamic content. For example: {{first_name}}', 'wp-marketing-plugin'); ?>
                                </p>
                                
                                <div class="template-variables">
                                    <label for="insert_variable"><?php _e('Insert Variable:', 'wp-marketing-plugin'); ?></label>
                                    <select id="insert_variable">
                                        <option value=""><?php _e('-- Select --', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{first_name}}"><?php _e('First Name', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{last_name}}"><?php _e('Last Name', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{full_name}}"><?php _e('Full Name', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{phone_number}}"><?php _e('Phone Number', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{email}}"><?php _e('Email', 'wp-marketing-plugin'); ?></option>
                                        <option value="{{custom_field}}"><?php _e('Custom Field...', 'wp-marketing-plugin'); ?></option>
                                    </select>
                                    <div id="custom_field_container" style="display:none; margin-top:5px;">
                                        <input type="text" id="custom_field_name" placeholder="<?php _e('Enter custom field name', 'wp-marketing-plugin'); ?>" />
                                        <button type="button" id="add_custom_field" class="button"><?php _e('Add', 'wp-marketing-plugin'); ?></button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="wp_marketing_template_submit" class="button button-primary" value="<?php echo $action === 'edit' ? __('Update Template', 'wp-marketing-plugin') : __('Create Template', 'wp-marketing-plugin'); ?>" />
                        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates')); ?>" class="button"><?php _e('Cancel', 'wp-marketing-plugin'); ?></a>
                    </p>
                </form>
            </div>
            
            <div class="wp-marketing-template-preview">
                <h3><?php _e('Preview', 'wp-marketing-plugin'); ?></h3>
                <div id="template_preview_container">
                    <div id="template_preview"></div>
                </div>
                
                <h4><?php _e('Preview with sample data', 'wp-marketing-plugin'); ?></h4>
                <button type="button" id="preview_sample" class="button"><?php _e('Apply Sample Data', 'wp-marketing-plugin'); ?></button>
            </div>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Insert variable into template
            $('#insert_variable').on('change', function() {
                var variable = $(this).val();
                if (variable === '{{custom_field}}') {
                    $('#custom_field_container').show();
                } else if (variable) {
                    var textarea = $('#template_content');
                    var cursorPos = textarea.prop('selectionStart');
                    var textBefore = textarea.val().substring(0, cursorPos);
                    var textAfter = textarea.val().substring(cursorPos, textarea.val().length);
                    
                    textarea.val(textBefore + variable + textAfter);
                    
                    // Reset the dropdown
                    $(this).val('');
                    
                    // Update preview
                    updateTemplatePreview();
                }
            });
            
            // Add custom field variable
            $('#add_custom_field').on('click', function() {
                var customField = $('#custom_field_name').val().trim();
                if (customField) {
                    var variable = '{{' + customField + '}}';
                    var textarea = $('#template_content');
                    var cursorPos = textarea.prop('selectionStart');
                    var textBefore = textarea.val().substring(0, cursorPos);
                    var textAfter = textarea.val().substring(cursorPos, textarea.val().length);
                    
                    textarea.val(textBefore + variable + textAfter);
                    
                    // Reset fields
                    $('#custom_field_name').val('');
                    $('#custom_field_container').hide();
                    $('#insert_variable').val('');
                    
                    // Update preview
                    updateTemplatePreview();
                }
            });
            
            // Update preview on content change
            $('#template_content').on('input', function() {
                updateTemplatePreview();
            });
            
            // Initial preview
            updateTemplatePreview();
            
            // Apply sample data
            $('#preview_sample').on('click', function() {
                var template = $('#template_content').val();
                
                // Replace variables with sample data
                var preview = template
                    .replace(/\{\{first_name\}\}/g, 'John')
                    .replace(/\{\{last_name\}\}/g, 'Doe')
                    .replace(/\{\{full_name\}\}/g, 'John Doe')
                    .replace(/\{\{phone_number\}\}/g, '+1234567890')
                    .replace(/\{\{email\}\}/g, 'john.doe@example.com')
                    // Replace any remaining variables with the variable name without braces
                    .replace(/\{\{([^}]+)\}\}/g, function(match, varName) {
                        return '<em>' + varName + ' value</em>';
                    });
                
                $('#template_preview').html(preview);
            });
            
            // Function to update template preview
            function updateTemplatePreview() {
                var template = $('#template_content').val();
                var previewHtml = template.replace(/\{\{([^}]+)\}\}/g, '<span class="variable-highlight">{{$1}}</span>');
                $('#template_preview').html(previewHtml);
            }
        });
        </script>
        
        <style>
        .wp-marketing-template-editor {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .wp-marketing-template-form {
            flex: 1 1 500px;
        }
        
        .wp-marketing-template-preview {
            flex: 1 1 400px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 20px;
        }
        
        #template_preview_container {
            margin: 20px 0;
            border: 1px solid #eee;
            padding: 15px;
            min-height: 200px;
            background: #f9f9f9;
            border-radius: 4px;
        }
        
        .variable-highlight {
            background-color: #e6f7ff;
            padding: 2px 4px;
            border-radius: 3px;
            border: 1px solid #b3e0ff;
        }
        
        .template-variables {
            margin-top: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 3px;
        }
        </style>
        <?php
    } else {
        // List all message templates
        global $wpdb;
        
        // Get current user
        $current_user_id = get_current_user_id();
        $user_query_part = current_user_can('administrator') ? '' : $wpdb->prepare("AND post_author = %d", $current_user_id);
        
        // Get all templates
        $templates_query = $wpdb->prepare(
            "SELECT p.*, pm.meta_value as template_type
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'template_type'
            WHERE p.post_type = %s 
            AND p.post_status = 'publish'
            $user_query_part
            ORDER BY p.post_title ASC",
            'message_template'
        );
        
        $templates = $wpdb->get_results($templates_query);
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-name column-primary"><?php _e('Template Name', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-type"><?php _e('Type', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-preview"><?php _e('Preview', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-author"><?php _e('Author', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Date', 'wp-marketing-plugin'); ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php if (empty($templates)): ?>
                <tr>
                    <td colspan="5"><?php _e('No message templates found.', 'wp-marketing-plugin'); ?></td>
                </tr>
                <?php else: ?>
                    <?php foreach ($templates as $template): ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates&action=edit&template_id=' . $template->ID)); ?>">
                                    <?php echo esc_html($template->post_title); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="edit">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-message-templates&action=edit&template_id=' . $template->ID)); ?>"><?php _e('Edit', 'wp-marketing-plugin'); ?></a> | 
                                </span>
                                <span class="trash">
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-message-templates&action=delete&template_id=' . $template->ID), 'delete_template_' . $template->ID)); ?>" class="submitdelete" onclick="return confirm('<?php _e('Are you sure you want to delete this template?', 'wp-marketing-plugin'); ?>');"><?php _e('Delete', 'wp-marketing-plugin'); ?></a>
                                </span>
                            </div>
                        </td>
                        <td class="column-type">
                            <?php 
                            $type = $template->template_type ?: 'text';
                            switch ($type) {
                                case 'text':
                                    _e('Text', 'wp-marketing-plugin');
                                    break;
                                case 'image':
                                    _e('Image', 'wp-marketing-plugin');
                                    break;
                                case 'document':
                                    _e('Document', 'wp-marketing-plugin');
                                    break;
                                default:
                                    _e('Text', 'wp-marketing-plugin');
                            }
                            ?>
                        </td>
                        <td class="column-preview">
                            <?php 
                            echo wp_trim_words($template->post_content, 10, '...'); 
                            
                            // Show variable placeholders used
                            preg_match_all('/\{\{([^}]+)\}\}/', $template->post_content, $matches);
                            if (!empty($matches[1])) {
                                echo '<div class="template-vars">';
                                echo '<small>' . __('Variables: ', 'wp-marketing-plugin') . '</small>';
                                foreach (array_unique($matches[1]) as $var) {
                                    echo '<span class="template-var">' . esc_html($var) . '</span>';
                                }
                                echo '</div>';
                            }
                            ?>
                        </td>
                        <td class="column-author">
                            <?php 
                            $author = get_user_by('id', $template->post_author);
                            echo $author ? esc_html($author->display_name) : '—';
                            ?>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($template->post_date))); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <style>
        .template-vars {
            margin-top: 5px;
        }
        .template-var {
            display: inline-block;
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            margin-right: 5px;
            margin-bottom: 5px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        </style>
        <?php
    }
    ?>
</div>