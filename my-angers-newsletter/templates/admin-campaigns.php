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

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-4 md:p-12">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-12 gap-6">
        <div>
            <h1 class="text-5xl font-black text-slate-900 tracking-tighter">Studio <span class="text-primary italic">Créatif</span></h1>
            <p class="text-slate-500 font-medium mt-2">Composez une expérience mémorable pour vos lecteurs.</p>
        </div>
        <div class="flex gap-4 w-full md:w-auto">
            <button type="button" id="save-campaign" class="flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-200 px-8 py-4 rounded-2xl font-black hover:bg-slate-50 transition-all">Enregistrer le brouillon</button>
            <button type="button" id="send-campaign" class="flex-1 md:flex-none bg-primary text-white px-8 py-4 rounded-2xl font-black hover:scale-105 transition-transform shadow-xl shadow-primary/30">Diffuser maintenant</button>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-10">
        <!-- Main Editor -->
        <div class="lg:col-span-2 space-y-8">
            <div class="card rounded-[2.5rem] p-10 bg-white shadow-xl shadow-slate-200/50 border-0">
                <div class="mb-10">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-4 tracking-widest">OBJET DU MESSAGE</label>
                    <input type="text" id="campaign-subject" class="w-full text-3xl font-black bg-slate-50 border-0 rounded-[1.5rem] px-8 py-6 focus:ring-4 focus:ring-primary/10 focus:bg-white transition-all text-slate-900" value="<?php echo esc_attr($subject); ?>" placeholder="Qu'allez-vous raconter aujourd'hui ?">
                </div>
                <div>
                    <div class="flex justify-between items-end mb-6">
                        <label class="block text-xs font-black uppercase text-slate-400 tracking-widest">CORPS DE L'EMAIL</label>
                        <button type="button" class="bg-[#121826] text-white px-6 py-3 rounded-xl font-black text-sm hover:bg-primary hover:scale-105 transition-all shadow-lg" onclick="document.getElementById('insertPostsModal').classList.remove('hidden')">
                            + Insérer des articles
                        </button>
                    </div>
                    <div class="rounded-3xl overflow-hidden border-2 border-slate-50 shadow-inner min-h-[500px]">
                        <?php wp_editor($content, 'campaign-content', array('textarea_name' => 'content', 'editor_height' => 500, 'tinymce' => array('border' => 'none'))); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-8">
            <div class="card rounded-[2.5rem] p-10 bg-white shadow-xl shadow-slate-200/50 border-0">
                <h3 class="text-2xl font-black mb-8 text-slate-900 border-b border-slate-50 pb-4">Paramètres d'envoi</h3>
                <div class="mb-8">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-3 tracking-widest">AUDIENCE CIBLE</label>
                    <select id="recipient-type" class="w-full bg-slate-50 border-0 rounded-2xl px-6 py-4 font-black text-slate-700 focus:ring-4 focus:ring-primary/10">
                        <option value="all">Tous les abonnés actifs (<?php echo count($subscribers); ?>)</option>
                        <option value="manual">Sélection manuelle</option>
                    </select>
                </div>

                <div id="manual-selection" class="hidden max-h-[400px] overflow-y-auto border-2 border-slate-50 rounded-3xl p-6 bg-slate-50/30 space-y-3 custom-scrollbar">
                    <?php foreach ($subscribers as $sub) : ?>
                        <label class="flex items-center gap-4 p-3 hover:bg-white rounded-2xl transition-all cursor-pointer group border-2 border-transparent hover:border-primary/20">
                            <input type="checkbox" class="recipient-checkbox w-5 h-5 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary" value="<?php echo $sub->id; ?>">
                            <span class="text-sm font-black text-slate-600 group-hover:text-slate-900 truncate"><?php echo esc_html($sub->email); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="card rounded-[2.5rem] p-10 bg-emerald-50 border-0 shadow-lg shadow-emerald-100">
                <div class="w-14 h-14 bg-emerald-500 rounded-2xl flex items-center justify-center text-white mb-6 shadow-xl shadow-emerald-200">
                    <span class="dashicons dashicons-shield-check" style="font-size: 28px; width: 28px; height: 28px;"></span>
                </div>
                <h4 class="text-emerald-900 text-xl font-black mb-3">Conformité RGPD</h4>
                <p class="text-emerald-700/70 text-sm font-medium leading-relaxed">Un lien de désinscription sera automatiquement ajouté en bas de chaque email pour respecter les normes européennes.</p>
            </div>
        </div>
    </div>
</div>

<!-- Hyper Modern Post Insertion Modal -->
<div id="insertPostsModal" class="hidden fixed inset-0 bg-slate-900/80 backdrop-blur-md z-[9999] flex items-center justify-center p-6">
    <div class="bg-white rounded-[3rem] w-full max-w-5xl max-h-[85vh] flex flex-col shadow-2xl overflow-hidden border-0">
        <div class="p-10 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
            <div>
                <h3 class="text-3xl font-black text-slate-900 tracking-tight">Bibliothèque d'articles</h3>
                <p class="text-slate-500 font-medium mt-1">Sélectionnez vos meilleurs contenus à partager.</p>
            </div>
            <button class="w-12 h-12 rounded-2xl bg-white border-2 border-slate-100 flex items-center justify-center hover:bg-rose-50 hover:border-rose-100 hover:text-rose-500 transition-all group" onclick="document.getElementById('insertPostsModal').classList.add('hidden')">
                <span class="font-black text-xl">✕</span>
            </button>
        </div>

        <div id="posts-list" class="p-10 overflow-y-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 bg-white custom-scrollbar">
            <!-- Loaded via AJAX -->
        </div>

        <div class="p-10 bg-slate-50/80 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-slate-400 font-bold text-sm italic">Cochez les articles et cliquez sur insérer.</p>
            <div class="flex gap-4 w-full md:w-auto">
                <button class="flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-200 px-8 py-4 rounded-2xl font-black hover:bg-slate-100 transition-all" onclick="document.getElementById('insertPostsModal').classList.add('hidden')">Annuler</button>
                <button id="confirm-insert-posts" class="flex-1 md:flex-none bg-[#121826] text-white px-10 py-4 rounded-2xl font-black hover:bg-primary hover:scale-105 transition-all shadow-xl shadow-slate-900/20">Insérer la sélection</button>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 8px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

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

    $('[onclick*="insertPostsModal"]').on('click', function() {
        if($('#insertPostsModal').hasClass('hidden')) return;

        $('#posts-list').html('<div class="col-span-full py-20 flex flex-col items-center"><div class="w-16 h-16 border-4 border-primary border-t-transparent rounded-full animate-spin mb-6"></div><p class="font-black text-slate-400 text-lg">Exploration de vos contenus...</p></div>');
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
                            <label class="group relative bg-slate-50 border-2 border-transparent hover:border-primary/30 rounded-3xl p-6 transition-all cursor-pointer hover:bg-white hover:shadow-xl hover:shadow-primary/5 flex flex-col h-full">
                                <input type="checkbox" class="post-select absolute top-4 right-4 w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary z-10" value="${post.id}" data-title="${post.title}" data-url="${post.url}" data-excerpt="${post.excerpt}" data-thumb="${post.thumbnail}">
                                <div class="aspect-video rounded-2xl bg-slate-200 mb-4 overflow-hidden shadow-inner">
                                    ${post.thumbnail ? `<img src="${post.thumbnail}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">` : `<div class="w-full h-full flex items-center justify-center text-slate-400 italic">No image</div>`}
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-black text-slate-800 leading-tight mb-2 group-hover:text-primary transition-colors text-lg line-clamp-2">${post.title}</h4>
                                    <p class="text-sm text-slate-400 font-medium line-clamp-3 leading-relaxed italic">${post.excerpt}</p>
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
        let postsHtml = '<div class="man-posts-container" style="padding: 20px 0; font-family: -apple-system, sans-serif;">';
        $('.post-select:checked').each(function() {
            const post = $(this).data();
            postsHtml += `
                <div class="man-post-item" style="margin-bottom: 50px; border-bottom: 1px solid #f1f5f9; padding-bottom: 40px; text-align: left;">
                    ${post.thumb ? `<img src="${post.thumb}" style="width: 100%; max-width: 600px; height: auto; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 25px rgba(0,0,0,0.08);">` : ''}
                    <h2 style="margin: 0 0 15px 0; font-size: 28px; color: #0f172a; font-weight: 900; line-height: 1.2;">${post.title}</h2>
                    <p style="color: #475569; font-size: 17px; line-height: 1.8; margin-bottom: 25px;">${post.excerpt}</p>
                    <a href="${post.url}" style="display: inline-block; background-color: #f60; color: #fff; padding: 14px 32px; text-decoration: none; border-radius: 12px; font-weight: 800; font-size: 16px; box-shadow: 0 4px 12px rgba(255,102,0,0.3);">Découvrir l'article complet →</a>
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
