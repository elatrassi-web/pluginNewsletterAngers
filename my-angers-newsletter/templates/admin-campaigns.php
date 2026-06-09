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

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-8">
    <header class="flex justify-between items-center mb-12">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Studio <span class="text-primary">Créatif</span></h1>
            <p class="text-slate-500 font-medium">Composez une expérience mémorable pour vos lecteurs.</p>
        </div>
        <div class="flex gap-4">
            <button type="button" id="save-campaign" class="bg-white text-slate-900 border border-slate-200 px-6 py-3 rounded-2xl font-bold hover:bg-slate-50 transition-all">Enregistrer le brouillon</button>
            <button type="button" id="send-campaign" class="bg-primary text-white px-6 py-3 rounded-2xl font-bold hover:scale-105 transition-transform shadow-xl shadow-primary/20">Diffuser maintenant</button>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
            <div class="card rounded-3xl p-8 bg-white">
                <div class="mb-8">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2">Objet du message</label>
                    <input type="text" id="campaign-subject" class="w-full text-2xl font-black bg-slate-50 border-0 rounded-2xl px-6 py-4 focus:ring-4 focus:ring-primary/10 focus:bg-white transition-all" value="<?php echo esc_attr($subject); ?>" placeholder="Qu'allez-vous raconter aujourd'hui ?">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-4">
                        <label class="block text-xs font-black uppercase text-slate-400">Corps de l'email</label>
                        <button type="button" class="text-xs font-black bg-slate-900 text-white px-4 py-2 rounded-lg hover:bg-primary transition-colors" onclick="document.getElementById('insertPostsModal').classList.remove('hidden')">+ Insérer des articles</button>
                    </div>
                    <div class="rounded-2xl overflow-hidden border border-slate-100 shadow-inner">
                        <?php wp_editor($content, 'campaign-content', array('textarea_name' => 'content', 'editor_height' => 450, 'tinymce' => array('border' => 'none'))); ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-6">
            <div class="card rounded-3xl p-8 bg-white">
                <h3 class="text-xl font-black mb-6">Paramètres d'envoi</h3>
                <div class="mb-6">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2">Audience cible</label>
                    <select id="recipient-type" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 font-bold focus:ring-2 focus:ring-primary/20">
                        <option value="all">Tous les abonnés actifs (<?php echo count($subscribers); ?>)</option>
                        <option value="manual">Sélection manuelle</option>
                    </select>
                </div>

                <div id="manual-selection" class="hidden max-h-64 overflow-y-auto border border-slate-100 rounded-2xl p-4 bg-slate-50/50 space-y-2">
                    <?php foreach ($subscribers as $sub) : ?>
                        <label class="flex items-center gap-3 p-2 hover:bg-white rounded-xl transition-colors cursor-pointer group">
                            <input type="checkbox" class="recipient-checkbox rounded border-slate-300 text-primary focus:ring-primary" value="<?php echo $sub->id; ?>">
                            <span class="text-sm font-bold text-slate-600 group-hover:text-slate-900"><?php echo esc_html($sub->email); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card rounded-3xl p-8 bg-emerald-50 border border-emerald-100">
                <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center text-white mb-4 shadow-lg shadow-emerald-200">
                    <span class="dashicons dashicons-shield-check"></span>
                </div>
                <h4 class="text-emerald-900 font-black mb-2">Conformité RGPD</h4>
                <p class="text-emerald-700/70 text-sm font-medium">Un lien de désinscription sera automatiquement ajouté en bas de chaque email pour respecter les normes européennes.</p>
            </div>
        </div>
    </div>
</div>

<!-- Insert Posts Modal -->
<div id="insertPostsModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-4xl max-h-[80vh] flex flex-col shadow-2xl overflow-hidden">
        <div class="p-8 border-b border-slate-100 flex justify-between items-center">
            <div>
                <h3 class="text-2xl font-black">Bibliothèque d'articles</h3>
                <p class="text-slate-500 text-sm font-medium">Sélectionnez les contenus à importer dans votre campagne.</p>
            </div>
            <button class="w-10 h-10 rounded-full hover:bg-slate-100 transition-colors" onclick="document.getElementById('insertPostsModal').classList.add('hidden')">✕</button>
        </div>
        <div id="posts-list" class="p-8 overflow-y-auto grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Loaded via AJAX -->
        </div>
        <div class="p-8 bg-slate-50 flex justify-end gap-4">
            <button class="bg-white text-slate-900 border border-slate-200 px-6 py-3 rounded-xl font-bold hover:bg-slate-100" onclick="document.getElementById('insertPostsModal').classList.add('hidden')">Annuler</button>
            <button id="confirm-insert-posts" class="bg-primary text-white px-8 py-3 rounded-xl font-bold hover:scale-105 transition-transform shadow-lg shadow-primary/20">Insérer la sélection</button>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    let campaignId = <?php echo $campaign_id; ?>;

    $('#recipient-type').on('change', function() {
        if ($(this).val() === 'manual') {
            $('#manual-selection').removeClass('hidden');
        } else {
            $('#manual-selection').addClass('hidden');
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

        if (!confirm('Êtes-vous sûr de vouloir diffuser cette campagne ?')) return;

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

    // Modal behavior handled by inline onclicks for brevity in this template,
    // but the AJAX for post fetching still uses jQuery below:

    $('[onclick*="insertPostsModal"]').on('click', function() {
        if($('#insertPostsModal').hasClass('hidden')) return;

        $('#posts-list').html('<div class="col-span-full py-12 flex flex-col items-center"><div class="w-12 h-12 border-4 border-primary border-t-transparent rounded-full animate-spin mb-4"></div><p class="font-bold text-slate-400">Recherche de vos pépites...</p></div>');
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
                            <label class="flex items-start gap-4 p-4 border border-slate-100 rounded-2xl hover:border-primary/30 hover:bg-slate-50 transition-all cursor-pointer group">
                                <input type="checkbox" class="post-select mt-1 rounded border-slate-300 text-primary focus:ring-primary" value="${post.id}" data-title="${post.title}" data-url="${post.url}" data-excerpt="${post.excerpt}" data-thumb="${post.thumbnail}">
                                <div class="flex-grow">
                                    <h4 class="font-bold text-slate-700 leading-tight mb-1 group-hover:text-slate-900">${post.title}</h4>
                                    <p class="text-xs text-slate-400 font-medium line-clamp-2">${post.excerpt}</p>
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
        let postsHtml = '<div class="man-posts-container" style="padding: 20px 0; font-family: sans-serif;">';
        $('.post-select:checked').each(function() {
            const post = $(this).data();
            postsHtml += `
                <div class="man-post-item" style="margin-bottom: 40px; border-bottom: 1px solid #eee; padding-bottom: 30px;">
                    ${post.thumb ? `<img src="${post.thumb}" style="width: 100%; max-width: 600px; height: auto; border-radius: 12px; margin-bottom: 20px; box-shadow: 0 4px 12px rgba(0,0,0,0.1);">` : ''}
                    <h2 style="margin: 0 0 12px 0; font-size: 26px; color: #111; font-weight: 800; line-height: 1.2;">${post.title}</h2>
                    <p style="color: #444; font-size: 17px; line-height: 1.7; margin-bottom: 20px;">${post.excerpt}</p>
                    <a href="${post.url}" style="display: inline-block; background-color: #f60; color: #fff; padding: 12px 28px; text-decoration: none; border-radius: 8px; font-weight: 800; font-size: 16px;">Lire l'article complet</a>
                </div>
            `;
        });
        postsHtml += '</div>';

        if (typeof tinymce !== 'undefined' && tinymce.get('campaign-content')) {
            tinymce.get('campaign-content').insertContent(postsHtml);
        } else {
            $('#campaign-content').val($('#campaign-content').val() + postsHtml);
        }
        $('#insertPostsModal').addClass('hidden');
    });
});
</script>
