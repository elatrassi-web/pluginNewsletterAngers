<?php
if (!defined('ABSPATH')) exit;

$campaign_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
global $wpdb;
$table = $wpdb->prefix . 'man_newsletters';
$table_subscribers = $wpdb->prefix . 'man_subscribers';

$campaign = $campaign_id ? $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $campaign_id)) : null;

$subject = $campaign ? $campaign->subject : '';
$content = $campaign ? $campaign->content : '';

$subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers WHERE status = 'active' ORDER BY email ASC");
?>

<div class="wrap man-admin-wrap">
    <div class="container-fluid mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="display-6 fw-bold"><?php echo $campaign_id ? 'Modifier la Campagne' : 'Nouvelle Campagne'; ?></h1>
            <div>
                <button type="button" id="save-campaign" class="btn btn-outline-primary me-2">Enregistrer le brouillon</button>
                <button type="button" id="send-campaign" class="btn btn-success">Envoyer la campagne</button>
            </div>
        </div>

        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Sujet de l'email</label>
                            <input type="text" id="campaign-subject" class="form-control form-control-lg" value="<?php echo esc_attr($subject); ?>" placeholder="Sujet de votre newsletter...">
                        </div>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-2">
                                <label class="form-label fw-bold">Contenu</label>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#insertPostsModal">Insérer des articles</button>
                            </div>
                            <?php wp_editor($content, 'campaign-content', array('textarea_name' => 'content', 'editor_height' => 400)); ?>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">Destinataires</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <select id="recipient-type" class="form-select mb-3">
                                <option value="all">Tous les abonnés actifs</option>
                                <option value="manual">Sélection manuelle</option>
                            </select>
                        </div>
                        <div id="manual-selection" class="d-none" style="max-height: 300px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px;">
                            <?php foreach ($subscribers as $sub) : ?>
                                <div class="form-check">
                                    <input class="form-check-input recipient-checkbox" type="checkbox" value="<?php echo $sub->id; ?>" id="sub-<?php echo $sub->id; ?>">
                                    <label class="form-check-label" for="sub-<?php echo $sub->id; ?>">
                                        <?php echo esc_html($sub->email); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Insert Posts Modal -->
<div class="modal fade" id="insertPostsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Insérer les derniers articles</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="posts-list" class="list-group">
                    <!-- Loaded via AJAX -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="confirm-insert-posts">Insérer la sélection</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let campaignId = <?php echo $campaign_id; ?>;

    $('#recipient-type').on('change', function() {
        if ($(this).val() === 'manual') {
            $('#manual-selection').removeClass('d-none');
        } else {
            $('#manual-selection').addClass('d-none');
        }
    });

    $('#save-campaign').on('click', function() {
        saveCampaign(function(response) {
            alert(response.data.message);
        });
    });

    $('#send-campaign').on('click', function() {
        const recipientType = $('#recipient-type').val();
        let selectedIds = [];
        if (recipientType === 'manual') {
            $('.recipient-checkbox:checked').each(function() {
                selectedIds.push($(this).val());
            });
            if (selectedIds.length === 0) {
                alert('Veuillez sélectionner au moins un destinataire.');
                return;
            }
        }

        if (!confirm('Êtes-vous sûr de vouloir envoyer cette campagne ?')) return;

        saveCampaign(function(response) {
            $.ajax({
                url: man_admin.ajax_url,
                type: 'POST',
                data: {
                    action: 'man_send_campaign',
                    id: campaignId,
                    recipient_type: recipientType,
                    selected_ids: selectedIds,
                    nonce: man_admin.nonce
                },
                success: function(res) {
                    if (res.success) {
                        alert(res.data);
                        location.reload();
                    } else {
                        alert(res.data);
                    }
                }
            });
        });
    });

    function saveCampaign(callback) {
        const subject = $('#campaign-subject').val();
        const content = (typeof tinymce !== 'undefined' && tinymce.get('campaign-content')) ? tinymce.get('campaign-content').getContent() : $('#campaign-content').val();

        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'man_save_campaign',
                id: campaignId,
                subject: subject,
                content: content,
                nonce: man_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    campaignId = response.data.id;
                    if (callback) callback(response);
                }
            }
        });
    }

    $('#insertPostsModal').on('show.bs.modal', function() {
        $('#posts-list').html('<div class="text-center py-4"><div class="spinner-border text-primary"></div></div>');
        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'man_get_posts',
                nonce: man_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    let html = '';
                    response.data.forEach(post => {
                        html += `
                            <label class="list-group-item d-flex align-items-center">
                                <input class="form-check-input me-3 post-select" type="checkbox" value="${post.id}" data-title="${post.title}" data-url="${post.url}" data-excerpt="${post.excerpt}" data-thumb="${post.thumbnail}">
                                <div class="flex-grow-1">
                                    <div class="fw-bold">${post.title}</div>
                                    <div class="small text-muted">${post.excerpt}</div>
                                </div>
                            </label>
                        `;
                    });
                    $('#posts-list').html(html);
                }
            }
        });
    });

    $('#confirm-insert-posts').on('click', function() {
        let postsHtml = '<div class="man-posts-container" style="padding: 20px 0;">';
        $('.post-select:checked').each(function() {
            const post = $(this).data();
            postsHtml += `
                <div class="man-post-item" style="margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px;">
                    ${post.thumb ? `<img src="${post.thumb}" style="width: 100%; max-width: 600px; height: auto; border-radius: 8px; margin-bottom: 15px;">` : ''}
                    <h2 style="margin: 0 0 10px 0; font-size: 24px; color: #333;">${post.title}</h2>
                    <p style="color: #666; font-size: 16px; line-height: 1.6;">${post.excerpt}</p>
                    <a href="${post.url}" style="display: inline-block; background-color: #f60; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;">Lire la suite</a>
                </div>
            `;
        });
        postsHtml += '</div>';

        if (typeof tinymce !== 'undefined' && tinymce.get('campaign-content')) {
            tinymce.get('campaign-content').insertContent(postsHtml);
        } else {
            $('#campaign-content').val($('#campaign-content').val() + postsHtml);
        }
        $('#insertPostsModal').modal('hide');
    });
});
</script>
