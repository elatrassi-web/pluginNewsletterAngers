<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_subscribers = $wpdb->prefix . 'man_subscribers';
$subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers ORDER BY created_at DESC");
?>

<div class="man-admin-tailwind min-h-screen bg-[#f1f5f9] p-4 md:p-12 relative">
    <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-16 gap-6">
        <div>
            <h1 class="text-6xl font-black text-[#0f172a] tracking-tighter">Gestion des <span class="text-[#f60] italic">Abonnés</span></h1>
            <p class="text-slate-500 font-bold mt-3 text-lg opacity-80">Contrôlez et segmentez votre base de données.</p>
        </div>
        <div class="flex gap-4 items-center w-full md:w-auto">
            <label class="flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-200 px-8 py-4 rounded-3xl font-black hover:bg-slate-50 transition-all cursor-pointer text-center shadow-sm">
                Importer CSV
                <input type="file" id="csv_file" class="hidden" accept=".csv">
            </label>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=man-subscribers&action=export_csv'), 'man_export_subscribers'); ?>" class="flex-1 md:flex-none bg-white text-slate-900 border-2 border-slate-200 px-8 py-4 rounded-3xl font-black hover:bg-slate-50 transition-all text-center shadow-sm">Exporter CSV</a>
            <button type="button" class="flex-1 md:flex-none bg-[#f60] text-white px-8 py-4 rounded-3xl font-black hover:scale-105 transition-transform shadow-2xl shadow-[#f60]/30" id="openAddSubscriber">Ajouter manuellement</button>
        </div>
    </header>

    <div class="bg-white rounded-[3.5rem] overflow-hidden shadow-2xl shadow-slate-200/60 border border-white/50">
        <div class="overflow-x-auto custom-scrollbar">
            <table class="w-full text-left border-collapse">
                <thead class="bg-slate-50/80 text-slate-400 text-[11px] uppercase font-black tracking-[0.2em]">
                    <tr>
                        <th class="px-10 py-8 w-20"><input type="checkbox" id="selectAll" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-[#f60] focus:ring-[#f60]"></th>
                        <th class="px-10 py-8">Email</th>
                        <th class="px-10 py-8">Status</th>
                        <th class="px-10 py-8">Inscription</th>
                        <th class="px-10 py-8 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    <?php if ($subscribers) : foreach ($subscribers as $sub) : ?>
                        <tr class="hover:bg-slate-50/30 transition-colors group">
                            <td class="px-10 py-8"><input type="checkbox" class="sub-checkbox w-6 h-6 rounded-lg border-2 border-slate-200 text-[#f60] focus:ring-[#f60]"></td>
                            <td class="px-10 py-8 font-black text-slate-700 text-lg group-hover:text-[#f60] transition-colors"><?php echo esc_html($sub->email); ?></td>
                            <td class="px-10 py-8">
                                <span class="px-4 py-2 rounded-xl text-[10px] font-black uppercase tracking-widest <?php echo $sub->status === 'active' ? 'bg-emerald-100 text-emerald-600' : ($sub->status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600'); ?>">
                                    <?php echo esc_html($sub->status); ?>
                                </span>
                            </td>
                            <td class="px-10 py-8 text-slate-400 font-bold"><?php echo esc_html(date_i18n('j M, Y', strtotime($sub->created_at))); ?></td>
                            <td class="px-10 py-8 text-right">
                                <button class="w-12 h-12 rounded-2xl bg-slate-50 text-slate-400 hover:bg-rose-50 hover:text-rose-500 transition-all flex items-center justify-center ml-auto delete-subscriber" data-id="<?php echo $sub->id; ?>">
                                    <span class="dashicons dashicons-trash"></span>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="5" class="px-10 py-20 text-center text-slate-300 font-black text-2xl italic">Aucun abonné trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add Subscriber Modal - INSIDE tailwind container for CSS application -->
    <div id="addSubscriberModal" class="fixed inset-0 hidden z-[999999] flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-slate-900/90 backdrop-blur-xl transition-opacity duration-500 modal-overlay"></div>
        <div id="modalAddContent" class="relative bg-white rounded-[4rem] w-full max-w-2xl p-16 shadow-[0_0_100px_rgba(0,0,0,0.5)] scale-95 opacity-0 transition-all duration-500 transform border-0">
            <div class="mb-12 text-center">
                <h3 class="text-5xl font-black text-slate-900 tracking-tight">Nouvel <span class="text-[#f60]">Abonné</span></h3>
                <p class="text-slate-400 font-bold mt-4 text-xl">Ajoutez manuellement un email à votre liste.</p>
            </div>
            <form id="addSubscriberForm" class="space-y-12">
                <div>
                    <label class="block text-[11px] font-black uppercase text-slate-400 mb-5 tracking-[0.3em]">ADRESSE EMAIL DE L'ABONNÉ</label>
                    <input type="email" name="email" class="w-full bg-slate-50 border-0 rounded-[2rem] px-10 py-8 focus:ring-[12px] focus:ring-[#f60]/5 focus:bg-white transition-all text-slate-900 font-black text-2xl placeholder:text-slate-200" placeholder="exemple@mail.com" required>
                </div>
                <div class="flex flex-col md:flex-row gap-6 pt-4">
                    <button type="button" class="close-add-modal flex-1 bg-white text-slate-900 border-2 border-slate-100 py-6 rounded-[2rem] font-black text-xl hover:bg-slate-50 transition-all">Annuler</button>
                    <button type="submit" class="flex-1 bg-[#121826] text-white py-6 rounded-[2rem] font-black text-xl hover:bg-[#f60] hover:scale-[1.03] transition-all shadow-2xl shadow-slate-900/20">Confirmer l'Ajout</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    const $modal = $('#addSubscriberModal');
    const $content = $('#modalAddContent');

    $('#openAddSubscriber').on('click', function() {
        $modal.removeClass('hidden').css('display', 'flex');
        setTimeout(() => $content.removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100'), 10);
    });

    function closeAddModal() {
        $content.removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => $modal.addClass('hidden').css('display', 'none'), 500);
    }

    $('.close-add-modal, .modal-overlay').on('click', closeAddModal);

    $('#addSubscriberForm').on('submit', function(e) {
        e.preventDefault();
        const email = $(this).find('input[name="email"]').val();

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
                alert(response.data);
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 10px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 20px; border: 3px solid white; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #cbd5e1; }
</style>
