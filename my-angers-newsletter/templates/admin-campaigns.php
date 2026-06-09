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

<div class="man-admin-tailwind min-h-screen bg-[#f1f5f9] p-4 md:p-12 relative">
    <!-- Header -->
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-16 gap-6">
        <div>
            <h1 class="text-6xl font-black text-[#0f172a] tracking-tighter">Studio <span class="text-[#f60] italic">Créatif</span></h1>
            <p class="text-slate-500 font-bold mt-3 text-lg opacity-80">Composez une expérience mémorable pour vos lecteurs.</p>
        </div>
        <div class="flex gap-4 w-full md:w-auto">
            <button type="button" id="save-campaign" class="flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-200 px-10 py-5 rounded-3xl font-black hover:bg-slate-50 transition-all shadow-sm">Enregistrer le brouillon</button>
            <button type="button" id="send-campaign" class="flex-1 md:flex-none bg-[#f60] text-white px-10 py-5 rounded-3xl font-black hover:scale-105 transition-transform shadow-2xl shadow-[#f60]/30">Diffuser maintenant</button>
        </div>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-12">
        <!-- Main Editor -->
        <div class="lg:col-span-2 space-y-10">
            <div class="bg-white rounded-[3rem] p-12 shadow-2xl shadow-slate-200/60 border border-white/50">
                <div class="mb-12">
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-5 tracking-[0.2em]">OBJET DU MESSAGE</label>
                    <input type="text" id="campaign-subject" class="w-full text-4xl font-black bg-slate-50 border-0 rounded-[2rem] px-10 py-8 focus:ring-[12px] focus:ring-[#f60]/5 focus:bg-white transition-all text-slate-900 placeholder:text-slate-200" value="<?php echo esc_attr($subject); ?>" placeholder="Qu'allez-vous raconter aujourd'hui ?">
                </div>

                <div>
                    <div class="flex justify-between items-center mb-8">
                        <label class="block text-[11px] font-black uppercase text-slate-400 tracking-[0.2em]">CORPS DE L'EMAIL</label>
                        <button type="button" id="open-post-modal" class="bg-[#121826] text-white px-8 py-4 rounded-2xl font-black text-sm hover:bg-[#f60] hover:scale-105 transition-all shadow-xl shadow-slate-900/20">
                            + Insérer des articles
                        </button>
                    </div>

                    <div class="rounded-[2rem] overflow-hidden border-2 border-slate-50 shadow-inner bg-slate-50">
                        <?php wp_editor($content, 'campaign-content', array(
                            'textarea_name' => 'content',
                            'editor_height' => 500,
                            'tinymce' => array(
                                'border_width' => 0,
                                'setup' => 'function(ed) { ed.on("init", function() { this.getContainer().style.border = "0"; }); }'
                            )
                        )); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-10">
            <div class="bg-white rounded-[3rem] p-12 shadow-2xl shadow-slate-200/60 border border-white/50">
                <h3 class="text-2xl font-black mb-10 text-slate-900 border-b-2 border-slate-50 pb-6">Paramètres d'envoi</h3>
                <div class="mb-10">
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-4 tracking-[0.2em]">AUDIENCE CIBLE</label>
                    <div class="relative">
                        <select id="recipient-type" class="w-full bg-slate-50 border-0 rounded-[1.5rem] px-8 py-5 font-black text-slate-700 focus:ring-8 focus:ring-[#f60]/5 appearance-none">
                            <option value="all">Tous les abonnés actifs (<?php echo count($subscribers); ?>)</option>
                            <option value="manual">Sélection manuelle</option>
                        </select>
                        <div class="absolute right-6 top-1/2 -translate-y-1/2 pointer-events-none opacity-20">
                            <span class="dashicons dashicons-arrow-down-alt2"></span>
                        </div>
                    </div>
                </div>

                <div id="manual-selection" class="hidden max-h-[400px] overflow-y-auto border-2 border-slate-50 rounded-[2rem] p-6 bg-slate-50/30 space-y-3 custom-scrollbar">
                    <?php foreach ($subscribers as $sub) : ?>
                        <label class="flex items-center gap-5 p-4 hover:bg-white rounded-2xl transition-all cursor-pointer group border-2 border-transparent hover:border-[#f60]/20">
                            <input type="checkbox" class="recipient-checkbox w-6 h-6 rounded-lg border-2 border-slate-200 text-[#f60] focus:ring-[#f60] transition-all" value="<?php echo $sub->id; ?>">
                            <span class="text-sm font-black text-slate-500 group-hover:text-slate-900 truncate"><?php echo esc_html($sub->email); ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-[#ecfdf5] rounded-[3rem] p-12 border-0 shadow-xl shadow-emerald-100/50 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-16 h-16 bg-emerald-500 rounded-3xl flex items-center justify-center text-white mb-8 shadow-2xl shadow-emerald-200">
                    <span class="dashicons dashicons-shield-check" style="font-size: 32px; width: 32px; height: 32px;"></span>
                </div>
                <h4 class="text-[#064e3b] text-2xl font-black mb-4">Conformité RGPD</h4>
                <p class="text-[#065f46]/70 text-base font-bold leading-relaxed">Un lien de désinscription sera automatiquement ajouté pour respecter les normes.</p>
            </div>
        </div>
    </div>

    <!-- Hyper Modern Modal INSIDE tailwind container -->
    <div id="insertPostsModal" class="fixed inset-0 hidden z-[999999] flex items-center justify-center p-4 md:p-12">
        <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-xl transition-opacity duration-500 modal-overlay"></div>
        <div class="relative bg-white rounded-[4rem] w-full max-w-6xl max-h-[90vh] flex flex-col shadow-[0_0_100px_rgba(0,0,0,0.5)] overflow-hidden border-0 scale-95 opacity-0 transition-all duration-500 transform" id="modalContent">
            <div class="p-12 border-b border-slate-50 flex justify-between items-center bg-white">
                <div>
                    <h3 class="text-4xl font-black text-slate-900 tracking-tight">Articles <span class="text-[#f60]">Récents</span></h3>
                    <p class="text-slate-400 font-bold mt-2 text-lg">Sélectionnez les pépites à partager avec votre audience.</p>
                </div>
                <button type="button" class="close-modal w-16 h-16 rounded-3xl bg-slate-50 border-2 border-slate-100 flex items-center justify-center hover:bg-rose-50 hover:border-rose-100 hover:text-rose-500 transition-all shadow-sm">
                    <span class="font-black text-2xl">✕</span>
                </button>
            </div>

            <div id="posts-list" class="p-12 overflow-y-auto grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 bg-white custom-scrollbar">
                <!-- Loaded via AJAX -->
            </div>

            <div class="p-12 bg-slate-50/50 border-t border-slate-50 flex flex-col md:flex-row justify-between items-center gap-8">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-full bg-[#f60]/10 flex items-center justify-center text-[#f60]">
                        <span class="dashicons dashicons-info" style="font-size: 24px; width: 24px; height: 24px;"></span>
                    </div>
                    <p class="text-slate-500 font-black text-sm uppercase tracking-widest">Cochez vos articles préférés</p>
                </div>
                <div class="flex gap-4 w-full md:w-auto">
                    <button type="button" class="close-modal flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-100 px-12 py-5 rounded-[2rem] font-black hover:bg-slate-50 transition-all">Annuler</button>
                    <button type="button" id="confirm-insert-posts" class="flex-1 md:flex-none bg-[#121826] text-white px-16 py-5 rounded-[2rem] font-black hover:bg-[#f60] hover:scale-105 transition-all shadow-2xl shadow-slate-900/20">Insérer maintenant</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; border: 3px solid white; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>

<script>
jQuery(document).ready(function($) {
    let campaignId = <?php echo $campaign_id; ?>;

    const $modal = $('#insertPostsModal');
    const $modalContent = $('#modalContent');

    $('#open-post-modal').on('click', function() {
        $modal.removeClass('hidden').css('display', 'flex');
        setTimeout(() => {
            $modalContent.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
        }, 10);
        loadPosts();
    });

    function closeModal() {
        $modalContent.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $modal.addClass('hidden').css('display', 'none');
        }, 500);
    }

    $('.close-modal, .modal-overlay').on('click', closeModal);

    $('#recipient-type').on('change', function() {
        if ($(this).val() === 'manual') {
            $('#manual-selection').removeClass('hidden').addClass('animate-in fade-in slide-in-from-top-4 duration-500');
        } else {
            $('#manual-selection').addClass('hidden');
        }
    });

    function loadPosts() {
        $('#posts-list').html('<div class="col-span-full py-32 flex flex-col items-center justify-center"><div class="w-20 h-20 border-[6px] border-[#f60] border-t-transparent rounded-full animate-spin mb-8 shadow-xl shadow-[#f60]/10"></div><p class="font-black text-slate-400 text-2xl tracking-tight">Exploration du contenu...</p></div>');
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
                            <label class="group relative bg-white border-3 border-slate-50 hover:border-[#f60]/40 rounded-[2.5rem] p-8 transition-all cursor-pointer hover:shadow-[0_20px_60px_-15px_rgba(246,102,0,0.1)] flex flex-col h-full overflow-hidden">
                                <input type="checkbox" class="post-select absolute top-6 right-6 w-8 h-8 rounded-xl border-3 border-slate-100 text-[#f60] focus:ring-[#f60] z-20 transition-all checked:scale-110" value="${post.id}" data-title="${post.title}" data-url="${post.url}" data-excerpt="${post.excerpt}" data-thumb="${post.thumbnail}">
                                <div class="aspect-[4/3] rounded-[2rem] bg-slate-100 mb-6 overflow-hidden shadow-inner relative">
                                    ${post.thumbnail ? `<img src="${post.thumbnail}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">` : `<div class="w-full h-full flex items-center justify-center text-slate-300"><span class="dashicons dashicons-format-image" style="font-size:48px; width:48px; height:48px;"></span></div>`}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                                </div>
                                <div class="flex-grow">
                                    <h4 class="font-black text-slate-800 leading-tight mb-3 group-hover:text-[#f60] transition-colors text-xl line-clamp-2 uppercase tracking-tight">${post.title}</h4>
                                    <p class="text-sm text-slate-400 font-bold line-clamp-3 leading-relaxed italic opacity-70">${post.excerpt}</p>
                                </div>
                            </label>
                        `;
                    });
                    $('#posts-list').html(html);
                }
            }
        });
    }

    $('#confirm-insert-posts').on('click', function() {
        let postsHtml = '<div class="man-posts-container" style="padding: 20px 0; font-family: -apple-system, system-ui, sans-serif;">';
        $('.post-select:checked').each(function() {
            const post = $(this).data();
            postsHtml += `
                <div class="man-post-item" style="margin-bottom: 60px; border-bottom: 2px solid #f1f5f9; padding-bottom: 50px; text-align: left;">
                    ${post.thumb ? `<img src="${post.thumb}" style="width: 100%; max-width: 600px; height: auto; border-radius: 30px; margin-bottom: 30px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.15);">` : ''}
                    <h2 style="margin: 0 0 20px 0; font-size: 32px; color: #0f172a; font-weight: 900; line-height: 1.1; letter-spacing: -0.02em; text-transform: uppercase;">${post.title}</h2>
                    <p style="color: #475569; font-size: 19px; line-height: 1.8; margin-bottom: 35px; font-weight: 500;">${post.excerpt}</p>
                    <a href="${post.url}" style="display: inline-block; background-color: #f60; color: #fff; padding: 18px 40px; text-decoration: none; border-radius: 20px; font-weight: 900; font-size: 18px; box-shadow: 0 15px 30px rgba(255,102,0,0.4); text-transform: uppercase; letter-spacing: 0.05em;">Découvrir l'article complet</a>
                </div>
            `;
        });
        postsHtml += '</div>';

        if (typeof tinymce !== 'undefined' && tinymce.get('campaign-content')) {
            tinymce.get('campaign-content').insertContent(postsHtml);
        } else {
            $('#campaign-content').val($('#campaign-content').val() + postsHtml);
        }
        closeModal();
    });

    $('#save-campaign').on('click', function() {
        saveCampaign(function(response) { alert(response.data.message); });
    });

    $('#send-campaign').on('click', function() {
        const recipientType = $('#recipient-type').val();
        let selectedIds = [];
        if (recipientType === 'manual') {
            $('.recipient-checkbox:checked').each(function() { selectedIds.push($(this).val()); });
            if (selectedIds.length === 0) { alert('Veuillez sélectionner au moins un destinataire.'); return; }
        }
        if (!confirm('Êtes-vous sûr de vouloir diffuser cette campagne ?')) return;
        saveCampaign(function(response) {
            $.ajax({
                url: man_admin.ajax_url,
                type: 'POST',
                data: { action: 'man_send_campaign', id: campaignId, recipient_type: recipientType, selected_ids: selectedIds, nonce: man_admin.nonce },
                success: function(res) { if (res.success) { alert(res.data); location.reload(); } else { alert(res.data); } }
            });
        });
    });

    function saveCampaign(callback) {
        const subject = $('#campaign-subject').val();
        const content = (typeof tinymce !== 'undefined' && tinymce.get('campaign-content')) ? tinymce.get('campaign-content').getContent() : $('#campaign-content').val();
        $.ajax({
            url: man_admin.ajax_url,
            type: 'POST',
            data: { action: 'man_save_campaign', id: campaignId, subject: subject, content: content, nonce: man_admin.nonce },
            success: function(response) { if (response.success) { campaignId = response.data.id; if (callback) callback(response); } }
        });
    }
});
</script>
