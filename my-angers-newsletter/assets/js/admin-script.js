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
});
