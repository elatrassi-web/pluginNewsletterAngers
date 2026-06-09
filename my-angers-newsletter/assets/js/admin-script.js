jQuery(document).ready(function($) {
    // Select All Checkboxes
    $('#selectAll').on('change', function() {
        $('.sub-checkbox').prop('checked', $(this).prop('checked'));
    });

    // Add Subscriber AJAX
    $('#addSubscriberForm').on('submit', function(e) {
        e.preventDefault();
        const email = $(this).find('input[type="email"]').val();

        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'man_add_subscriber',
                email: email,
                is_admin: '1',
                nonce: man_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data);
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Delete Subscriber AJAX
    $('.delete-subscriber').on('click', function() {
        if (!confirm('Voulez-vous vraiment supprimer cet abonné ?')) return;
        const id = $(this).data('id');

        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'man_delete_subscriber',
                id: id,
                nonce: man_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert(response.data);
                }
            }
        });
    });

    // Import CSV AJAX
    $('#csv_file').on('change', function() {
        const file_data = $(this).prop('files')[0];
        const form_data = new FormData();
        form_data.append('csv_file', file_data);
        form_data.append('action', 'man_import_csv');
        form_data.append('nonce', man_admin.nonce);

        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: form_data,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response.data);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
