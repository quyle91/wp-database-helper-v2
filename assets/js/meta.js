jQuery(document).ready(function ($) {

    // Khi click vào container, show nút save
    $(document).on('click', '.wpdh-admin-column', function (e) {
        e.stopPropagation();
        $('.wpdh-save-meta').addClass('hidden');
        $(this).find('.wpdh-save-meta').removeClass('hidden');
    });

    // Khi click ra ngoài, ẩn tất cả nút save
    $(document).on('click', function () {
        $('.wpdh-save-meta').addClass('hidden');
    });

    // save
    $(document).on('click', '.wpdh-save-meta', function (e) {
        e.preventDefault();

        const container = $(this).closest('.wpdh-admin-column');
        const postId = container.data('post-id');
        const fieldName = container.data('field-name');
        const status = container.find('.wpdh-saved-status');

        // Clone container ra để lấy input data
        const clone = container.clone();
        const form = $('<form></form>').append(clone);
        const formData = new FormData(form[0]);

        formData.append('action', 'wpdh_save_meta');
        formData.append('post_id', postId);
        formData.append('field_name', fieldName);
        formData.append('nonce', Wpdh.nonce);

        status.text('Saving...').css('color', '#2271b1');

        $.ajax({
            url: Wpdh.ajax_url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    status.text(response.data.message || 'Saved!');
                } else {
                    status.text(response.data?.message || 'Error saving');
                    console.error(response.data);
                }
            },
            error: function (xhr, statusText, error) {
                status.text('AJAX error: ' + error);
            },
        });
    });
});
