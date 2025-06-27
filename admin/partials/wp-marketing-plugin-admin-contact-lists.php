<?php
/**
 * Admin contact lists template for the WordPress Marketing Plugin.
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
$list_id = isset($_GET['list_id']) ? intval($_GET['list_id']) : 0;

// Handle form submissions
if (isset($_POST['wp_marketing_list_submit'])) {
    check_admin_referer('wp_marketing_list_nonce', 'wp_marketing_list_nonce');
    
    $list_title = sanitize_text_field($_POST['list_title']);
    $list_description = sanitize_textarea_field($_POST['list_description']);
    
    if (empty($list_title)) {
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-list-error',
            __('List title cannot be empty.', 'wp-marketing-plugin'),
            'error'
        );
    } else {
        // Create or update list
        $list_data = array(
            'post_title'   => $list_title,
            'post_content' => $list_description,
            'post_status'  => 'publish',
            'post_type'    => 'marketing_list',
            'post_author'  => get_current_user_id()
        );
        
        if ($list_id > 0) {
            $list_data['ID'] = $list_id;
            wp_update_post($list_data);
            $list_id = $list_data['ID'];
            
            add_settings_error(
                'wp-marketing-plugin-messages',
                'wp-marketing-list-updated',
                __('Contact list updated successfully.', 'wp-marketing-plugin'),
                'updated'
            );
        } else {
            $list_id = wp_insert_post($list_data);
            
            add_settings_error(
                'wp-marketing-plugin-messages',
                'wp-marketing-list-created',
                __('Contact list created successfully.', 'wp-marketing-plugin'),
                'updated'
            );
        }
        
        // Redirect to list contacts view
        if ($list_id) {
            wp_redirect(admin_url('admin.php?page=wp-marketing-contact-lists&action=view&list_id=' . $list_id));
            exit;
        }
    }
}

// Handle contact import
if (isset($_POST['wp_marketing_import_submit']) && $list_id > 0) {
    check_admin_referer('wp_marketing_import_nonce', 'wp_marketing_import_nonce');
    
    if (!empty($_FILES['import_file']['tmp_name'])) {
        $file_type = pathinfo($_FILES['import_file']['name'], PATHINFO_EXTENSION);
        
        if ($file_type != 'csv') {
            add_settings_error(
                'wp-marketing-plugin-messages',
                'wp-marketing-import-error',
                __('Please upload a valid CSV file.', 'wp-marketing-plugin'),
                'error'
            );
        } else {
            // Process CSV import
            $mapped_fields = isset($_POST['field_mapping']) ? $_POST['field_mapping'] : array();
            $import_result = wp_marketing_process_contact_import($_FILES['import_file']['tmp_name'], $list_id, $mapped_fields);
            
            if (is_wp_error($import_result)) {
                add_settings_error(
                    'wp-marketing-plugin-messages',
                    'wp-marketing-import-error',
                    $import_result->get_error_message(),
                    'error'
                );
            } else {
                add_settings_error(
                    'wp-marketing-plugin-messages',
                    'wp-marketing-import-success',
                    sprintf(__('%d contacts imported successfully.', 'wp-marketing-plugin'), $import_result),
                    'updated'
                );
            }
        }
    } else {
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-import-error',
            __('Please select a file to import.', 'wp-marketing-plugin'),
            'error'
        );
    }
}

// Handle contact delete
if ($action === 'delete_contact' && isset($_GET['contact_id']) && $list_id > 0) {
    $contact_id = intval($_GET['contact_id']);
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_contact_' . $contact_id)) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'marketing_list_contact';
        
        $wpdb->delete(
            $table_name,
            array(
                'list_id' => $list_id,
                'contact_id' => $contact_id
            )
        );
        
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-contact-deleted',
            __('Contact removed from list successfully.', 'wp-marketing-plugin'),
            'updated'
        );
        
        wp_redirect(admin_url('admin.php?page=wp-marketing-contact-lists&action=view&list_id=' . $list_id));
        exit;
    }
}

// Handle list delete
if ($action === 'delete' && $list_id > 0) {
    if (isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'delete_list_' . $list_id)) {
        wp_delete_post($list_id, true);
        
        // Delete relationships from the custom table
        global $wpdb;
        $table_name = $wpdb->prefix . 'marketing_list_contact';
        $wpdb->delete($table_name, array('list_id' => $list_id));
        
        add_settings_error(
            'wp-marketing-plugin-messages',
            'wp-marketing-list-deleted',
            __('Contact list deleted successfully.', 'wp-marketing-plugin'),
            'updated'
        );
        
        wp_redirect(admin_url('admin.php?page=wp-marketing-contact-lists'));
        exit;
    }
}
?>

<div class="wrap">
    <h1 class="wp-heading-inline">
        <?php 
        if ($action === 'new') {
            _e('Create Contact List', 'wp-marketing-plugin');
        } elseif ($action === 'edit' && $list_id > 0) {
            _e('Edit Contact List', 'wp-marketing-plugin');
        } elseif ($action === 'view' && $list_id > 0) {
            $list = get_post($list_id);
            echo esc_html($list->post_title);
        } else {
            _e('Contact Lists', 'wp-marketing-plugin');
        }
        ?>
    </h1>
    
    <?php 
    if (empty($action) || $action === 'list') { 
        // Add new list button
        ?>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=new')); ?>" class="page-title-action">
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
        $list_title = '';
        $list_description = '';
        
        if ($action === 'edit' && $list_id > 0) {
            $list = get_post($list_id);
            if ($list && $list->post_type === 'marketing_list') {
                $list_title = $list->post_title;
                $list_description = $list->post_content;
            }
        }
        ?>
        <div class="wp-marketing-list-form">
            <form method="post" action="">
                <?php wp_nonce_field('wp_marketing_list_nonce', 'wp_marketing_list_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="list_title"><?php _e('List Name', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="list_title" name="list_title" class="regular-text" value="<?php echo esc_attr($list_title); ?>" required />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="list_description"><?php _e('Description', 'wp-marketing-plugin'); ?></label>
                        </th>
                        <td>
                            <textarea id="list_description" name="list_description" class="large-text" rows="5"><?php echo esc_textarea($list_description); ?></textarea>
                        </td>
                    </tr>
                </table>
                
                <p class="submit">
                    <input type="submit" name="wp_marketing_list_submit" class="button button-primary" value="<?php echo $action === 'edit' ? __('Update List', 'wp-marketing-plugin') : __('Create List', 'wp-marketing-plugin'); ?>" />
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists')); ?>" class="button"><?php _e('Cancel', 'wp-marketing-plugin'); ?></a>
                </p>
            </form>
        </div>
        <?php
    } elseif ($action === 'view' && $list_id > 0) {
        // View list contacts
        $list = get_post($list_id);
        if ($list && $list->post_type === 'marketing_list') {
            global $wpdb;
            $table_name = $wpdb->prefix . 'marketing_list_contact';
            
            // Get contacts in this list
            $contacts_query = $wpdb->prepare(
                "SELECT c.*, p.post_title
                FROM {$table_name} c
                JOIN {$wpdb->posts} p ON c.contact_id = p.ID
                WHERE c.list_id = %d
                ORDER BY p.post_title ASC",
                $list_id
            );
            
            $contacts = $wpdb->get_results($contacts_query);
            $contact_count = count($contacts);
            ?>
            
            <div class="wp-marketing-list-header">
                <p class="list-description"><?php echo esc_html($list->post_content); ?></p>
                
                <div class="list-actions">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=edit&list_id=' . $list_id)); ?>" class="button">
                        <?php _e('Edit List', 'wp-marketing-plugin'); ?>
                    </a>
                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=delete&list_id=' . $list_id), 'delete_list_' . $list_id)); ?>" class="button" onclick="return confirm('<?php _e('Are you sure you want to delete this list? This action cannot be undone.', 'wp-marketing-plugin'); ?>')">
                        <?php _e('Delete List', 'wp-marketing-plugin'); ?>
                    </a>
                </div>
            </div>
            
            <div class="wp-marketing-tabs">
                <nav class="nav-tab-wrapper">
                    <a href="#contacts" class="nav-tab nav-tab-active"><?php _e('Contacts', 'wp-marketing-plugin'); ?> (<?php echo esc_html($contact_count); ?>)</a>
                    <a href="#import" class="nav-tab"><?php _e('Import', 'wp-marketing-plugin'); ?></a>
                </nav>
                
                <div class="tab-content" id="contacts-tab">
                    <div class="tablenav top">
                        <div class="alignleft actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=add_contacts&list_id=' . $list_id)); ?>" class="button">
                                <?php _e('Add Contacts', 'wp-marketing-plugin'); ?>
                            </a>
                        </div>
                        
                        <div class="tablenav-pages">
                            <span class="displaying-num"><?php echo sprintf(_n('%s contact', '%s contacts', $contact_count, 'wp-marketing-plugin'), number_format_i18n($contact_count)); ?></span>
                        </div>
                    </div>
                    
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th scope="col" class="manage-column column-name"><?php _e('Name', 'wp-marketing-plugin'); ?></th>
                                <th scope="col" class="manage-column column-phone"><?php _e('Phone Number', 'wp-marketing-plugin'); ?></th>
                                <th scope="col" class="manage-column column-email"><?php _e('Email', 'wp-marketing-plugin'); ?></th>
                                <th scope="col" class="manage-column column-actions"><?php _e('Actions', 'wp-marketing-plugin'); ?></th>
                            </tr>
                        </thead>
                        
                        <tbody>
                            <?php if (empty($contacts)): ?>
                            <tr>
                                <td colspan="4"><?php _e('No contacts found in this list.', 'wp-marketing-plugin'); ?></td>
                            </tr>
                            <?php else: ?>
                                <?php foreach ($contacts as $contact): ?>
                                <?php
                                $phone_number = get_post_meta($contact->contact_id, 'phone_number', true);
                                $email = get_post_meta($contact->contact_id, 'email', true);
                                ?>
                                <tr>
                                    <td>
                                        <strong>
                                            <a href="<?php echo esc_url(admin_url('post.php?post=' . $contact->contact_id . '&action=edit')); ?>">
                                                <?php echo esc_html($contact->post_title); ?>
                                            </a>
                                        </strong>
                                    </td>
                                    <td><?php echo esc_html($phone_number); ?></td>
                                    <td><?php echo esc_html($email); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(admin_url('post.php?post=' . $contact->contact_id . '&action=edit')); ?>" class="button button-small">
                                            <?php _e('Edit', 'wp-marketing-plugin'); ?>
                                        </a>
                                        <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=delete_contact&list_id=' . $list_id . '&contact_id=' . $contact->contact_id), 'delete_contact_' . $contact->contact_id)); ?>" class="button button-small" onclick="return confirm('<?php _e('Are you sure you want to remove this contact from the list?', 'wp-marketing-plugin'); ?>')">
                                            <?php _e('Remove', 'wp-marketing-plugin'); ?>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                
                <div class="tab-content" id="import-tab" style="display: none;">
                    <div class="wp-marketing-import-form">
                        <h3><?php _e('Import Contacts', 'wp-marketing-plugin'); ?></h3>
                        <p><?php _e('Upload a CSV file to import contacts into this list.', 'wp-marketing-plugin'); ?></p>
                        
                        <form method="post" enctype="multipart/form-data" id="wp_marketing_import_form">
                            <?php wp_nonce_field('wp_marketing_import_nonce', 'wp_marketing_import_nonce'); ?>
                            
                            <div class="form-row">
                                <label for="import_file"><?php _e('CSV File', 'wp-marketing-plugin'); ?></label>
                                <input type="file" name="import_file" id="import_file" accept=".csv" required />
                                <p class="description"><?php _e('File should be in CSV format with headers in the first row.', 'wp-marketing-plugin'); ?></p>
                            </div>
                            
                            <div id="field_mapping_container" style="display: none;">
                                <h4><?php _e('Map CSV Columns to Contact Fields', 'wp-marketing-plugin'); ?></h4>
                                <div class="field-mapping-rows"></div>
                            </div>
                            
                            <div id="import_status"></div>
                            
                            <p class="submit">
                                <button type="button" id="analyze_csv" class="button button-secondary"><?php _e('Analyze CSV', 'wp-marketing-plugin'); ?></button>
                                <input type="submit" name="wp_marketing_import_submit" id="import_submit" class="button button-primary" value="<?php _e('Import Contacts', 'wp-marketing-plugin'); ?>" style="display: none;" />
                            </p>
                        </form>
                        
                        <div class="import-instructions">
                            <h4><?php _e('CSV Format Instructions', 'wp-marketing-plugin'); ?></h4>
                            <p><?php _e('Your CSV file should include the following columns:', 'wp-marketing-plugin'); ?></p>
                            <ul>
                                <li><strong>name</strong> - <?php _e('Contact\'s full name', 'wp-marketing-plugin'); ?></li>
                                <li><strong>phone_number</strong> - <?php _e('Contact\'s phone number (with country code)', 'wp-marketing-plugin'); ?></li>
                                <li><strong>email</strong> - <?php _e('Contact\'s email address', 'wp-marketing-plugin'); ?></li>
                                <li><strong>first_name</strong> - <?php _e('Contact\'s first name (optional)', 'wp-marketing-plugin'); ?></li>
                                <li><strong>last_name</strong> - <?php _e('Contact\'s last name (optional)', 'wp-marketing-plugin'); ?></li>
                            </ul>
                            <p><?php _e('You can also include additional custom fields which will be imported as contact metadata.', 'wp-marketing-plugin'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            
            <script>
            jQuery(document).ready(function($) {
                // Tab functionality
                $('.wp-marketing-tabs .nav-tab').on('click', function(e) {
                    e.preventDefault();
                    var target = $(this).attr('href').substr(1);
                    
                    // Update active tab
                    $('.wp-marketing-tabs .nav-tab').removeClass('nav-tab-active');
                    $(this).addClass('nav-tab-active');
                    
                    // Show target tab content
                    $('.tab-content').hide();
                    $('#' + target + '-tab').show();
                });
                
                // Analyze CSV button
                $('#analyze_csv').on('click', function() {
                    var fileInput = $('#import_file')[0];
                    if (fileInput.files.length === 0) {
                        $('#import_status').html('<div class="notice notice-error"><p><?php _e('Please select a CSV file first.', 'wp-marketing-plugin'); ?></p></div>');
                        return;
                    }
                    
                    var file = fileInput.files[0];
                    var reader = new FileReader();
                    
                    reader.onload = function(e) {
                        try {
                            var csv = e.target.result;
                            var lines = csv.split("\n");
                            
                            if (lines.length < 2) {
                                $('#import_status').html('<div class="notice notice-error"><p><?php _e('CSV file appears to be empty or invalid.', 'wp-marketing-plugin'); ?></p></div>');
                                return;
                            }
                            
                            // Get headers
                            var headers = lines[0].split(',').map(function(header) {
                                return header.trim().replace(/^["']|["']$/g, '');
                            });
                            
                            // Create field mapping inputs
                            var mappingHtml = '';
                            headers.forEach(function(header, index) {
                                mappingHtml += '<div class="field-mapping">';
                                mappingHtml += '<label>' + header + '</label>';
                                mappingHtml += '<select name="field_mapping[' + index + ']">';
                                mappingHtml += '<option value=""><?php _e('Do not import', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="name" ' + (header.toLowerCase() === 'name' ? 'selected' : '') + '><?php _e('Name', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="phone_number" ' + (header.toLowerCase() === 'phone_number' || header.toLowerCase() === 'phone' ? 'selected' : '') + '><?php _e('Phone Number', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="email" ' + (header.toLowerCase() === 'email' ? 'selected' : '') + '><?php _e('Email', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="first_name" ' + (header.toLowerCase() === 'first_name' ? 'selected' : '') + '><?php _e('First Name', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="last_name" ' + (header.toLowerCase() === 'last_name' ? 'selected' : '') + '><?php _e('Last Name', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '<option value="custom"><?php _e('Custom Field', 'wp-marketing-plugin'); ?></option>';
                                mappingHtml += '</select>';
                                mappingHtml += '<input type="text" name="custom_field_name[' + index + ']" placeholder="<?php _e('Custom field name', 'wp-marketing-plugin'); ?>" style="display:none;" />';
                                mappingHtml += '</div>';
                            });
                            
                            $('.field-mapping-rows').html(mappingHtml);
                            $('#field_mapping_container').show();
                            $('#import_submit').show();
                            $('#import_status').html('');
                            
                            // Handle custom field selection
                            $('select[name^="field_mapping"]').on('change', function() {
                                var customField = $(this).parent().find('input[name^="custom_field_name"]');
                                if ($(this).val() === 'custom') {
                                    customField.show();
                                } else {
                                    customField.hide();
                                }
                            });
                            
                        } catch (error) {
                            $('#import_status').html('<div class="notice notice-error"><p><?php _e('Error analyzing CSV file:', 'wp-marketing-plugin'); ?> ' + error.message + '</p></div>');
                        }
                    };
                    
                    reader.onerror = function() {
                        $('#import_status').html('<div class="notice notice-error"><p><?php _e('Error reading the file.', 'wp-marketing-plugin'); ?></p></div>');
                    };
                    
                    reader.readAsText(file);
                });
            });
            </script>
            
            <style>
            .wp-marketing-list-header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
            }
            .wp-marketing-tabs {
                margin-top: 20px;
            }
            .tab-content {
                background: #fff;
                border: 1px solid #ccd0d4;
                border-top: none;
                padding: 20px;
            }
            .field-mapping {
                display: flex;
                align-items: center;
                margin-bottom: 10px;
            }
            .field-mapping label {
                width: 150px;
                margin-right: 10px;
            }
            .import-instructions {
                margin-top: 30px;
                border-top: 1px solid #eee;
                padding-top: 15px;
            }
            </style>
            <?php
        } else {
            // List not found
            ?>
            <div class="notice notice-error">
                <p><?php _e('Contact list not found.', 'wp-marketing-plugin'); ?></p>
            </div>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists')); ?>" class="button"><?php _e('Go Back', 'wp-marketing-plugin'); ?></a>
            </p>
            <?php
        }
    } else {
        // List all contact lists
        global $wpdb;
        
        // Get current user
        $current_user_id = get_current_user_id();
        $user_query_part = current_user_can('administrator') ? '' : $wpdb->prepare("AND post_author = %d", $current_user_id);
        
        // Get all lists
        $lists_query = $wpdb->prepare(
            "SELECT p.*, 
            (SELECT COUNT(*) FROM {$wpdb->prefix}marketing_list_contact c WHERE c.list_id = p.ID) as contact_count
            FROM {$wpdb->posts} p
            WHERE p.post_type = %s 
            AND p.post_status = 'publish'
            $user_query_part
            ORDER BY p.post_title ASC",
            'marketing_list'
        );
        
        $lists = $wpdb->get_results($lists_query);
        ?>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th scope="col" class="manage-column column-name column-primary"><?php _e('List Name', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-description"><?php _e('Description', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-count"><?php _e('Contacts', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-author"><?php _e('Author', 'wp-marketing-plugin'); ?></th>
                    <th scope="col" class="manage-column column-date"><?php _e('Date', 'wp-marketing-plugin'); ?></th>
                </tr>
            </thead>
            
            <tbody>
                <?php if (empty($lists)): ?>
                <tr>
                    <td colspan="5"><?php _e('No contact lists found.', 'wp-marketing-plugin'); ?></td>
                </tr>
                <?php else: ?>
                    <?php foreach ($lists as $list): ?>
                    <tr>
                        <td class="column-name column-primary">
                            <strong>
                                <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=view&list_id=' . $list->ID)); ?>">
                                    <?php echo esc_html($list->post_title); ?>
                                </a>
                            </strong>
                            <div class="row-actions">
                                <span class="view">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=view&list_id=' . $list->ID)); ?>"><?php _e('View', 'wp-marketing-plugin'); ?></a> | 
                                </span>
                                <span class="edit">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=edit&list_id=' . $list->ID)); ?>"><?php _e('Edit', 'wp-marketing-plugin'); ?></a> | 
                                </span>
                                <span class="trash">
                                    <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=wp-marketing-contact-lists&action=delete&list_id=' . $list->ID), 'delete_list_' . $list->ID)); ?>" class="submitdelete" onclick="return confirm('<?php _e('Are you sure you want to delete this list?', 'wp-marketing-plugin'); ?>');"><?php _e('Delete', 'wp-marketing-plugin'); ?></a>
                                </span>
                            </div>
                        </td>
                        <td class="column-description">
                            <?php echo !empty($list->post_content) ? esc_html(wp_trim_words($list->post_content, 10)) : '—'; ?>
                        </td>
                        <td class="column-count">
                            <?php echo esc_html($list->contact_count); ?>
                        </td>
                        <td class="column-author">
                            <?php 
                            $author = get_user_by('id', $list->post_author);
                            echo $author ? esc_html($author->display_name) : '—';
                            ?>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($list->post_date))); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
        <?php
    }
    ?>
</div>