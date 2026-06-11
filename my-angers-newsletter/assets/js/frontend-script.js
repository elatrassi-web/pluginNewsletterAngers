jQuery(document).ready(function($) {
    const $modal = $('#man-category-modal');
    const $form = $('#man-newsletter-form');
    const $trigger = $('#man-submit-trigger');
    const $confirm = $('#man-confirm-subscription');
    const $message = $('#man-message');

    $form.on('submit', function(e) {
        e.preventDefault();
        const email = $('#man-email').val();
        if (!email) return;

        if ($modal.length > 0) {
            $modal.removeClass('man-modal-hidden');
        } else {
            submitForm();
        }
    });

    $('.man-modal-close, .man-modal-overlay').on('click', function() {
        $modal.addClass('man-modal-hidden');
    });

    $confirm.on('click', function() {
        submitForm();
    });

    function submitForm() {
        const email = $('#man-email').val();
        const categories = [];
        $('input[name="categories[]"]:checked').each(function() {
            categories.push($(this).val());
        });

        $confirm.prop('disabled', true).text('Patientez...');
        $trigger.prop('disabled', true).css('opacity', '0.7');
        $message.removeClass('error success').text('Envoi en cours...');

        $.ajax({
            url: man_ajax.url,
            type: 'POST',
            data: {
                action: 'man_add_subscriber',
                email: email,
                categories: categories
            },
            success: function(response) {
                if (response.success) {
                    $message.addClass('success').text(response.data);
                    $('#man-email').val('');
                    $modal.addClass('man-modal-hidden');
                } else {
                    $message.addClass('error').text(response.data);
                }
            },
            error: function() {
                $message.addClass('error').text('Une erreur est survenue.');
            },
            complete: function() {
                $confirm.prop('disabled', false).text("Confirmer l'inscription");
                $trigger.prop('disabled', false).css('opacity', '1');
            }
        });
    }
});
