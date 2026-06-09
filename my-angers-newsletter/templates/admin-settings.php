<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['man_save_settings'])) {
    check_admin_referer('man_save_settings_action');
    update_option('man_double_optin', isset($_POST['double_optin']) ? '1' : '0');
    update_option('man_auto_notify', isset($_POST['auto_notify']) ? '1' : '0');
    update_option('man_email_template', sanitize_text_field($_POST['email_template']));
    echo '<div class="man-admin-tailwind px-8 pt-8"><div class="bg-emerald-500 text-white p-4 rounded-2xl font-bold shadow-lg shadow-emerald-200 animate-bounce">✓ Réglages enregistrés avec succès !</div></div>';
}

$double_optin = get_option('man_double_optin', '1');
$auto_notify = get_option('man_auto_notify', '0');
$email_template = get_option('man_email_template', 'modern');
?>

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-8">
    <header class="mb-12">
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Configuration <span class="text-primary">Système</span></h1>
        <p class="text-slate-500 font-medium">Paramétrez les automatismes de votre écosystème de newsletter.</p>
    </header>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <form method="post" class="space-y-8">
            <?php wp_nonce_field('man_save_settings_action'); ?>

            <div class="card rounded-3xl p-10 bg-white">
                <h3 class="text-xl font-black mb-8 border-b border-slate-50 pb-4">Protocoles d'Inscription</h3>

                <div class="space-y-8">
                    <label class="flex items-start gap-5 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="double_optin" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($double_optin, '1'); ?>>
                        </div>
                        <div>
                            <span class="block text-lg font-black text-slate-800 group-hover:text-primary transition-colors">Activer le Double Opt-in</span>
                            <span class="text-slate-400 font-medium text-sm leading-relaxed">Protégez votre réputation d'expéditeur en demandant une confirmation par email. (Hautement recommandé en 2026).</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-5 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="auto_notify" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($auto_notify, '1'); ?>>
                        </div>
                        <div>
                            <span class="block text-lg font-black text-slate-800 group-hover:text-primary transition-colors">Pilotage Automatique</span>
                            <span class="text-slate-400 font-medium text-sm leading-relaxed">Envoyer instantanément une notification dès qu'un nouvel article est publié sur my-angers.info.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card rounded-3xl p-10 bg-white">
                <h3 class="text-xl font-black mb-8 border-b border-slate-50 pb-4">Apparence & Templates</h3>
                <div class="mb-6">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2">Modèle d'Email</label>
                    <select name="email_template" class="w-full bg-slate-50 border-0 rounded-xl px-5 py-4 font-bold text-slate-700 focus:ring-2 focus:ring-primary/20">
                        <option value="modern" <?php selected($email_template, 'modern'); ?>>Moderne (Épuré & Visuel)</option>
                        <option value="classic" <?php selected($email_template, 'classic'); ?>>Classique (Textuel & Sobre)</option>
                        <option value="minimal" <?php selected($email_template, 'minimal'); ?>>Minimaliste (Direct au but)</option>
                    </select>
                </div>
                <div class="mb-6">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-2">Nom de l'expéditeur</label>
                    <input type="text" class="w-full bg-slate-100 border-0 rounded-xl px-5 py-4 font-bold text-slate-400 cursor-not-allowed" value="Angers Info" disabled>
                </div>
            </div>

            <button type="submit" name="man_save_settings" class="bg-slate-900 text-white px-10 py-5 rounded-2xl font-black hover:scale-105 transition-transform shadow-2xl shadow-slate-300">Enregistrer les Protocoles</button>
        </form>

        <div class="space-y-8">
            <div class="card rounded-3xl p-10 bg-slate-900 text-white shadow-2xl shadow-slate-400 overflow-hidden relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/20 rounded-full blur-3xl"></div>
                <h3 class="text-2xl font-black mb-6 relative">Intégration Site Web</h3>
                <p class="text-slate-400 font-medium mb-8">Copiez et collez ce code court n'importe où sur votre site (dans le footer, une page ou un article) pour afficher le module d'inscription.</p>

                <div class="bg-slate-800/50 border border-slate-700 p-6 rounded-2xl flex justify-between items-center group cursor-pointer hover:border-primary/50 transition-all" onclick="navigator.clipboard.writeText('[angers_newsletter]'); alert('Shortcode copié !');">
                    <code class="text-primary font-black text-xl">[angers_newsletter]</code>
                    <span class="text-slate-500 text-xs font-black uppercase group-hover:text-white transition-colors">Copier</span>
                </div>
            </div>

            <div class="card rounded-3xl p-10 bg-white border border-slate-100">
                <h3 class="font-black text-slate-900 mb-4">Statut des Serveurs</h3>
                <div class="flex items-center gap-3">
                    <div class="w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
                    <span class="text-slate-700 font-bold">Moteur d'envoi opérationnel</span>
                </div>
                <p class="mt-4 text-xs text-slate-400 font-medium italic">Utilise la fonction native wp_mail() via votre infrastructure actuelle.</p>
            </div>
        </div>
    </div>
</div>
