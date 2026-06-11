jQuery(document).ready(function($) {
    console.log("My Angers Newsletter: Frontend script version 1.1.0 loaded");

    function submitNewsletterForm($container) {
        const $modal = $container.find('.man-category-modal');
        const isModalVisible = $modal.length > 0 && !$modal.hasClass('man-modal-hidden');

        const $form = $container.find('.man-newsletter-form');
        const $emailInput = $form.find('.man-email');
        const $triggerBtn = $form.find('.man-submit-trigger');
        const $confirmBtn = $container.find('.man-confirm-subscription');

        const $mainMessage = $container.find('.man-message');
        const $modalMessage = $container.find('.man-modal-message');

        // Decide which message container to use
        const $activeMessage = isModalVisible ? $modalMessage : $mainMessage;

        const email = $emailInput.val();
        const categories = [];
        $container.find('input[name="categories[]"]:checked').each(function() {
            categories.push($(this).val());
        });

        console.log("Submitting form - Email:", email, "Categories:", categories, "Modal Visible:", isModalVisible);

        if (!email) {
            $activeMessage.removeClass('man-modal-hidden success').addClass('error').text("Veuillez saisir votre e-mail.").show();
            return;
        }

        // UI state: Loading
        $confirmBtn.prop('disabled', true).text('Patientez...');
        $triggerBtn.prop('disabled', true).css('opacity', '0.5');

        $activeMessage.removeClass('man-modal-hidden error success').text('Traitement en cours...').show();

        const ajaxUrl = (typeof man_ajax !== 'undefined') ? man_ajax.url : '/wp-admin/admin-ajax.php';
        const nonce = (typeof man_ajax !== 'undefined') ? man_ajax.nonce : '';

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'man_add_subscriber',
                email: email,
                categories: categories,
                nonce: nonce
            },
            success: function(response) {
                console.log("Server response:", response);
                if (response.success) {
                    $activeMessage.removeClass('error').addClass('success').text(response.data);

                    if (isModalVisible) {
                        // After success in modal, wait 2s, close modal, and show success in main area
                        setTimeout(function() {
                            $modal.addClass('man-modal-hidden');
                            $mainMessage.removeClass('error').addClass('success').text(response.data).show();
                            $emailInput.val('');
                        }, 2500);
                    } else {
                        $emailInput.val('');
                    }
                } else {
                    $activeMessage.removeClass('success').addClass('error').text(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error:", status, error, xhr.responseText);
                $activeMessage.removeClass('success').addClass('error').text('Une erreur technique est survenue. Veuillez réessayer.');
            },
            complete: function() {
                $confirmBtn.prop('disabled', false).text('Confirmer l\'inscription');
                $triggerBtn.prop('disabled', false).css('opacity', '1');
            }
        });
    }

    // Event delegation for multiple forms
    $(document).on('submit', '.man-newsletter-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $container = $form.closest('.man-newsletter-form-container');
        const $modal = $container.find('.man-category-modal');

        if ($modal.length > 0) {
            console.log("Opening category selection modal...");
            $modal.removeClass('man-modal-hidden');
            $container.find('.man-modal-message').addClass('man-modal-hidden').text('');
            $container.find('.man-message').hide().text('');
        } else {
            submitNewsletterForm($container);
        }
    });

    // Confirmation button in modal
    $(document).on('click', '.man-confirm-subscription', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log("User confirmed categories. Proceeding with AJAX...");
        const $container = $(this).closest('.man-newsletter-form-container');
        submitNewsletterForm($container);
    });

    // Closing modal
    $(document).on('click', '.man-modal-close, .man-modal-overlay', function(e) {
        e.preventDefault();
        $(this).closest('.man-category-modal').addClass('man-modal-hidden');
    });
});
