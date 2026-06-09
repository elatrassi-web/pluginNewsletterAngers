jQuery(document).ready(function($) {
    $('#man-newsletter-form').on('submit', function(e) {
        e.preventDefault();

        const email = $('#man-email').val();
        const $message = $('#man-message');
        const $submit = $('#man-submit');

        $submit.prop('disabled', true).css('opacity', '0.7');
        $message.removeClass('error success').text('Envoi en cours...');

        $.ajax({
            url: man_ajax.url,
            type: 'POST',
            data: {
                action: 'man_add_subscriber',
                email: email
            },
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data);
                    $('#man-email').val('');
                } else {
                    $message.addClass('error').text(response.data);
                }
            },
            error: function() {
                $message.addClass('error').text('Une erreur est survenue.');
            },
            complete: function() {
                $submit.prop('disabled', false).css('opacity', '1');
            }
        });
    });
});
