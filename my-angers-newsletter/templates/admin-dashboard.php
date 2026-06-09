<?php
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_subscribers = $wpdb->prefix . 'man_subscribers';
$table_newsletters = $wpdb->prefix . 'man_newsletters';
$table_stats = $wpdb->prefix . 'man_stats';

$total_subscribers = $wpdb->get_var("SELECT COUNT(*) FROM $table_subscribers WHERE status = 'active'");
$total_campaigns = $wpdb->get_var("SELECT COUNT(*) FROM $table_newsletters WHERE status = 'sent'");
$total_opens = $wpdb->get_var("SELECT COUNT(*) FROM $table_stats WHERE action = 'open'");
$total_clicks = $wpdb->get_var("SELECT COUNT(*) FROM $table_stats WHERE action = 'click'");
?>

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-8">
    <header class="flex justify-between items-center mb-12">
        <div>
            <h1 class="text-4xl font-black text-slate-900 tracking-tight">Angers <span class="text-primary">Info</span></h1>
            <p class="text-slate-500 font-medium">L'intelligence artificielle au service de votre audience.</p>
        </div>
        <div class="flex gap-4">
            <a href="admin.php?page=man-campaigns&action=new" class="bg-slate-900 text-white px-6 py-3 rounded-2xl font-bold hover:bg-slate-800 transition-all shadow-xl shadow-slate-200">Nouvelle Campagne</a>
        </div>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="card p-6 rounded-3xl bg-white/70">
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Abonnés</p>
            <h2 class="text-4xl font-black text-slate-900"><?php echo esc_html($total_subscribers); ?></h2>
            <div class="mt-4 flex items-center text-emerald-500 text-sm font-bold">
                <span class="mr-1">↑ 12%</span>
                <span class="text-slate-400 font-medium italic">ce mois</span>
            </div>
        </div>
        <div class="card p-6 rounded-3xl bg-white/70">
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Campagnes</p>
            <h2 class="text-4xl font-black text-slate-900"><?php echo esc_html($total_campaigns); ?></h2>
            <p class="mt-4 text-slate-400 text-sm font-medium italic">Dernière il y a 2 jours</p>
        </div>
        <div class="card p-6 rounded-3xl bg-white/70">
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Ouvertures</p>
            <h2 class="text-4xl font-black text-slate-900"><?php echo esc_html($total_opens); ?></h2>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-2">
                <div class="bg-accent h-2 rounded-full" style="width: 65%"></div>
            </div>
        </div>
        <div class="card p-6 rounded-3xl bg-white/70">
            <p class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-1">Clics</p>
            <h2 class="text-4xl font-black text-slate-900"><?php echo esc_html($total_clicks); ?></h2>
            <div class="mt-4 w-full bg-slate-100 rounded-full h-2">
                <div class="bg-primary h-2 rounded-full" style="width: 42%"></div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
            <div class="card rounded-3xl overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                    <h3 class="font-black text-xl">Dernières Activités</h3>
                    <button class="text-primary font-bold text-sm">Voir tout</button>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left">
                        <thead class="bg-slate-50/50 text-slate-400 text-xs uppercase font-black">
                            <tr>
                                <th class="px-6 py-4">Campagne</th>
                                <th class="px-6 py-4">Statut</th>
                                <th class="px-6 py-4">Date</th>
                                <th class="px-6 py-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php
                            $recent_campaigns = $wpdb->get_results("SELECT * FROM $table_newsletters ORDER BY created_at DESC LIMIT 5");
                            if ($recent_campaigns) :
                                foreach ($recent_campaigns as $campaign) : ?>
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-5 font-bold text-slate-700"><?php echo esc_html($campaign->subject); ?></td>
                                        <td class="px-6 py-5">
                                            <span class="px-3 py-1 rounded-full text-xs font-black uppercase <?php echo $campaign->status === 'sent' ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600'; ?>">
                                                <?php echo esc_html($campaign->status); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-5 text-slate-500 text-sm"><?php echo esc_html(date_i18n('j M, Y', strtotime($campaign->created_at))); ?></td>
                                        <td class="px-6 py-5 text-right">
                                            <a href="admin.php?page=man-campaigns&id=<?php echo $campaign->id; ?>" class="text-slate-400 hover:text-primary transition-colors"><span class="dashicons dashicons-edit"></span></a>
                                        </td>
                                    </tr>
                                <?php endforeach;
                            else : ?>
                                <tr><td colspan="4" class="px-6 py-10 text-center text-slate-400 italic font-medium">Aucune donnée disponible.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="space-y-6">
            <div class="card p-8 rounded-3xl bg-slate-900 text-white shadow-2xl shadow-slate-300">
                <h3 class="text-2xl font-black mb-4 leading-tight">Prêt à diffuser vos actualités ?</h3>
                <p class="text-slate-400 font-medium mb-8">Utilisez notre éditeur intelligent pour créer des campagnes qui convertissent.</p>
                <a href="admin.php?page=man-campaigns&action=new" class="block text-center bg-primary text-white py-4 rounded-2xl font-black hover:scale-105 transition-transform shadow-lg shadow-primary/20">Lancer l'Éditeur</a>
            </div>

            <div class="card p-6 rounded-3xl bg-white border border-slate-100">
                <h3 class="font-black mb-4">Abonnements récents</h3>
                <div class="space-y-4">
                    <?php
                    $recent_subs = $wpdb->get_results("SELECT email, created_at FROM $table_subscribers ORDER BY created_at DESC LIMIT 3");
                    foreach ($recent_subs as $sub) : ?>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                                <span class="dashicons dashicons-admin-users"></span>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700 leading-none mb-1"><?php echo esc_html($sub->email); ?></p>
                                <p class="text-xs text-slate-400 font-medium"><?php echo human_time_diff(strtotime($sub->created_at), current_time('timestamp')); ?> ago</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>
