<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_subscribers = $wpdb->prefix . 'man_subscribers';
$subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers ORDER BY created_at DESC");
?>

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-8">
    <header class="flex justify-between items-center mb-12">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Gestion des <span class="text-primary">Abonnés</span></h1>
            <p class="text-slate-500 font-medium">Contrôlez et segmentez votre base de données.</p>
        </div>
        <div class="flex gap-4 items-center">
            <label class="bg-white text-slate-900 border border-slate-200 px-6 py-3 rounded-2xl font-bold hover:bg-slate-50 transition-all cursor-pointer">
                Importer CSV
                <input type="file" id="csv_file" class="hidden" accept=".csv">
            </label>
            <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=man-subscribers&action=export_csv'), 'man_export_subscribers'); ?>" class="bg-white text-slate-900 border border-slate-200 px-6 py-3 rounded-2xl font-bold hover:bg-slate-50 transition-all">Exporter CSV</a>
            <button class="bg-primary text-white px-6 py-3 rounded-2xl font-bold hover:scale-105 transition-transform shadow-xl shadow-primary/20" onclick="document.getElementById('addSubscriberModal').classList.remove('hidden')">Ajouter manuellement</button>
        </div>
    </header>

    <div class="card rounded-3xl overflow-hidden bg-white shadow-xl shadow-slate-200">
        <div class="overflow-x-auto">
            <table class="w-full text-left">
                <thead class="bg-slate-50/50 text-slate-400 text-xs uppercase font-black">
                    <tr>
                        <th class="px-6 py-4 w-12"><input type="checkbox" id="selectAll" class="rounded border-slate-300 text-primary focus:ring-primary"></th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Inscription</th>
                        <th class="px-6 py-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($subscribers) : foreach ($subscribers as $sub) : ?>
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="px-6 py-5"><input type="checkbox" class="sub-checkbox rounded border-slate-300 text-primary focus:ring-primary" value="<?php echo $sub->id; ?>"></td>
                            <td class="px-6 py-5 font-bold text-slate-700"><?php echo esc_html($sub->email); ?></td>
                            <td class="px-6 py-5">
                                <span class="px-3 py-1 rounded-full text-xs font-black uppercase <?php echo $sub->status === 'active' ? 'bg-emerald-100 text-emerald-600' : ($sub->status === 'pending' ? 'bg-amber-100 text-amber-600' : 'bg-rose-100 text-rose-600'); ?>">
                                    <?php echo esc_html($sub->status); ?>
                                </span>
                            </td>
                            <td class="px-6 py-5 text-slate-500 text-sm"><?php echo esc_html(date_i18n('j M, Y', strtotime($sub->created_at))); ?></td>
                            <td class="px-6 py-5 text-right">
                                <button class="text-slate-400 hover:text-rose-500 transition-colors delete-subscriber" data-id="<?php echo $sub->id; ?>"><span class="dashicons dashicons-trash"></span></button>
                            </td>
                        </tr>
                    <?php endforeach; else : ?>
                        <tr><td colspan="5" class="px-6 py-10 text-center text-slate-400 italic font-medium">Aucun abonné trouvé.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="addSubscriberModal" class="hidden fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl w-full max-w-md p-8 shadow-2xl">
        <h3 class="text-2xl font-black mb-2">Nouvel Abonné</h3>
        <p class="text-slate-500 mb-6 font-medium text-sm">Ajoutez manuellement un email à votre liste.</p>
        <form id="addSubscriberForm">
            <div class="mb-6">
                <label class="block text-xs font-black uppercase text-slate-400 mb-2">Adresse Email</label>
                <input type="email" class="w-full bg-slate-50 border-0 rounded-xl px-4 py-3 focus:ring-2 focus:ring-primary/20 focus:bg-white transition-all text-slate-900 font-bold" placeholder="exemple@mail.com" required>
            </div>
            <div class="flex gap-4">
                <button type="button" class="flex-1 bg-slate-100 text-slate-600 py-3 rounded-xl font-bold hover:bg-slate-200 transition-colors" onclick="document.getElementById('addSubscriberModal').classList.add('hidden')">Annuler</button>
                <button type="submit" class="flex-1 bg-primary text-white py-3 rounded-xl font-bold hover:scale-105 transition-transform">Confirmer</button>
            </div>
        </form>
    </div>
</div>
