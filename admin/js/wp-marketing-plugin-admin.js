/**
 * Admin-specific JavaScript file for the WordPress Marketing Plugin.
 *
 * @link       https://yourwebsite.com
 * @since      1.0.0
 *
 * @package    WP_Marketing_Plugin
 * @subpackage WP_Marketing_Plugin/admin/js
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        
        // Import contacts functionality
        if ($('#wp_marketing_import_form').length) {
            $('#wp_marketing_import_form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = new FormData(this);
                formData.append('action', 'wp_marketing_import_contacts');
                formData.append('nonce', wp_marketing_plugin_data.nonce);
                
                $.ajax({
                    url: wp_marketing_plugin_data.ajax_url,
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    beforeSend: function() {
                        $('#import_status').html('<div class="notice notice-warning"><p>Importing contacts, please wait...</p></div>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#import_status').html('<div class="notice notice-success"><p>' + response.data.message + '</p></div>');
                            setTimeout(function() {
                                location.reload();
                            }, 2000);
                        } else {
                            $('#import_status').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#import_status').html('<div class="notice notice-error"><p>An error occurred during import. Please try again.</p></div>');
                    }
                });
            });
        }
        
        // Template variables insertion
        if ($('#template_content').length && $('#insert_variable').length) {
            $('#insert_variable').on('change', function() {
                var variable = $(this).val();
                if (variable) {
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
            
            $('#template_content').on('input', function() {
                updateTemplatePreview();
            });
            
            function updateTemplatePreview() {
                var template = $('#template_content').val();
                var previewHtml = template.replace(/\{\{([^}]+)\}\}/g, '<span class="variable-highlight">{{$1}}</span>');
                $('#template_preview').html(previewHtml);
            }
        }
        
        // WhatsApp QR code generation
        if ($('#generate_qr_form').length) {
            $('#generate_qr_form').on('submit', function(e) {
                e.preventDefault();
                
                var formData = {
                    'action': 'wp_marketing_generate_qr',
                    'nonce': wp_marketing_plugin_data.nonce,
                    'phone_number': $('#qr_phone_number').val(),
                    'message': $('#qr_message').val()
                };
                
                $.ajax({
                    url: wp_marketing_plugin_data.ajax_url,
                    type: 'POST',
                    data: formData,
                    beforeSend: function() {
                        $('#qr_status').html('<div class="notice notice-warning"><p>Generating QR code, please wait...</p></div>');
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#qr_status').html('');
                            $('#qr_code_container').html('<img src="' + response.data.qr_url + '" alt="WhatsApp QR Code" />');
                            $('#qr_download_container').html(
                                '<a href="' + response.data.qr_url + '" download="whatsapp-qr.png" class="button button-primary">Download PNG</a> ' +
                                '<a href="' + response.data.qr_svg_url + '" download="whatsapp-qr.svg" class="button button-secondary">Download SVG</a>'
                            );
                        } else {
                            $('#qr_status').html('<div class="notice notice-error"><p>' + response.data.message + '</p></div>');
                        }
                    },
                    error: function() {
                        $('#qr_status').html('<div class="notice notice-error"><p>An error occurred. Please try again.</p></div>');
                    }
                });
            });
        }
        
        // Campaign scheduling functionality
        if ($('#schedule_campaign').length) {
            $('#schedule_campaign').change(function() {
                if ($(this).is(':checked')) {
                    $('.campaign-schedule-options').show();
                } else {
                    $('.campaign-schedule-options').hide();
                }
            }).change();
        }
    });

})(jQuery);