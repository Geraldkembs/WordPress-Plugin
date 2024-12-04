// assets/js/admin.js
(function ($) {
    'use strict';

    $(document).ready(function () {
        // Initialize sortable
        $('#smm-sortable').sortable({
            handle: '.smm-drag-handle',
            placeholder: 'ui-sortable-placeholder',
            update: function (event, ui) {
                updateOrder();
            }
        });

        // Handle form submission
        $('#smm-form').on('submit', function (e) {
            e.preventDefault();
            saveSettings();
        });

        $('#smm-display-type').on('change', function() {
            const displayType = $(this).val();
            if (displayType === 'floating') {
                $('#float-position-group').slideDown();
            } else {
                $('#float-position-group').slideUp();
            }
        });

        // Function to update order after drag and drop
        function updateOrder() {
            const order = [];
            $('.smm-social-item').each(function (index) {
                const id = $(this).data('id');
                order.push(id);
            });

            $.ajax({
                url: smmAjax.ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_social_media_order',
                    nonce: smmAjax.nonce,
                    order: order
                },
                success: function (response) {
                    if (response.success) {
                        showNotice('Order updated successfully', 'success');
                    } else {
                        showNotice('Error updating order', 'error');
                    }
                },
                error: function () {
                    showNotice('Error updating order', 'error');
                }
            });
        }

        // Function to save all settings
        function saveSettings() {
            const formData = new FormData();
        formData.append('action', 'save_social_media_settings');
        formData.append('nonce', smmAjax.nonce);
        formData.append('display_type', $('#smm-display-type').val());
        formData.append('float_position', $('#smm-float-position').val());


            // Get social media data
            $('.smm-social-item').each(function (index) {
                const id = $(this).data('id');
                const url = $(this).find('input[type="url"]').val();
                const active = $(this).find('input[type="checkbox"]').is(':checked');

                formData.append(`socials[${id}][url]`, url);
                formData.append(`socials[${id}][active]`, active);
                formData.append(`socials[${id}][order]`, index + 1);
            });

            // Disable submit button while saving
            const submitButton = $('#smm-form button[type="submit"]');
            submitButton.prop('disabled', true);
            submitButton.text('Saving...');

            // Save settings via AJAX
            $.ajax({
                url: smmAjax.ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    if (response.success) {
                        showNotice('Settings saved successfully', 'success');
                    } else {
                        showNotice('Error saving settings', 'error');
                    }
                },
                error: function () {
                    showNotice('Error saving settings', 'error');
                },
                complete: function () {
                    submitButton.prop('disabled', false);
                    submitButton.text('Save Changes');
                }
            });
        }

        // Function to show admin notices
        function showNotice(message, type) {
            const noticeClass = type === 'success' ? 'notice-success' : 'notice-error';
            const notice = $(`
                <div class="notice ${noticeClass} is-dismissible">
                    <p>${message}</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            // Remove existing notices
            $('.notice').remove();

            // Add new notice
            $('.smm-container').prepend(notice);

            // Handle notice dismissal
            notice.find('.notice-dismiss').on('click', function () {
                notice.fadeOut(300, function () {
                    notice.remove();
                });
            });

            // Auto-dismiss after 3 seconds
            setTimeout(function () {
                notice.fadeOut(300, function () {
                    notice.remove();
                });
            }, 3000);
        }
    });
})(jQuery);