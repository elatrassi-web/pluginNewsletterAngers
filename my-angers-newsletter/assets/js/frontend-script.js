jQuery(document).ready(function($) {
    console.log("My Angers Newsletter: Frontend script initialized");

    // Function to submit the newsletter form
    function submitNewsletterForm($container) {
        const $form = $container.find('.man-newsletter-form');
        const $emailInput = $form.find('.man-email');
        const $triggerBtn = $form.find('.man-submit-trigger');
        const $modal = $container.find('.man-category-modal');
        const $confirmBtn = $container.find('.man-confirm-subscription');
        const $mainMessage = $container.find('.man-message');
        const $modalMessage = $container.find('.man-modal-message');

        const email = $emailInput.val();
        const categories = [];
        $container.find('input[name="categories[]"]:checked').each(function() {
            categories.push($(this).val());
        });

        console.log("Attempting subscription for:", email, "Categories:", categories);

        if (!email) {
            alert("Veuillez saisir votre adresse e-mail.");
            return;
        }

        // Visual feedback
        const originalConfirmText = $confirmBtn.text();
        $confirmBtn.prop('disabled', true).text('Chargement...');
        $triggerBtn.prop('disabled', true).css('opacity', '0.5');

        const $activeMessage = ($modal.length > 0 && !$modal.hasClass('man-modal-hidden')) ? $modalMessage : $mainMessage;
        $activeMessage.removeClass('man-modal-hidden error success').text('Traitement en cours...').show();

        const ajaxUrl = (typeof man_ajax !== 'undefined') ? man_ajax.url : '/wp-admin/admin-ajax.php';
        const nonce = (typeof man_ajax !== 'undefined') ? man_ajax.nonce : '';

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'man_add_subscriber',
                email: email,
                categories: categories,
                nonce: nonce
            },
            success: function(response) {
                console.log("AJAX Response received:", response);
                if (response.success) {
                    $activeMessage.addClass('success').text(response.data);
                    $emailInput.val('');

                    if ($modal.length > 0) {
                        setTimeout(function() {
                            $modal.addClass('man-modal-hidden');
                            $mainMessage.addClass('success').text(response.data).show();
                        }, 2000);
                    }
                } else {
                    $activeMessage.addClass('error').text(response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error("AJAX Error Details:", {status, error, response: xhr.responseText});
                $activeMessage.addClass('error').text('Une erreur technique est survenue. Veuillez réessayer.');
            },
            complete: function() {
                $confirmBtn.prop('disabled', false).text(originalConfirmText);
                $triggerBtn.prop('disabled', false).css('opacity', '1');
            }
        });
    }

    // Use event delegation for all interactions to handle multiple instances and dynamic content
    $(document).on('submit', '.man-newsletter-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $container = $form.closest('.man-newsletter-form-container');
        const $modal = $container.find('.man-category-modal');
        const email = $form.find('.man-email').val();

        console.log("Form submission triggered for:", email);

        if ($modal.length > 0) {
            console.log("Showing modal...");
            $modal.removeClass('man-modal-hidden');
            $modal.find('.man-modal-message').addClass('man-modal-hidden');
        } else {
            submitNewsletterForm($container);
        }
    });

    $(document).on('click', '.man-confirm-subscription', function(e) {
        e.preventDefault();
        e.stopImmediatePropagation();
        console.log("Confirm button clicked!");
        const $container = $(this).closest('.man-newsletter-form-container');
        submitNewsletterForm($container);
    });

    $(document).on('click', '.man-modal-close, .man-modal-overlay', function(e) {
        e.preventDefault();
        $(this).closest('.man-category-modal').addClass('man-modal-hidden');
    });
});
