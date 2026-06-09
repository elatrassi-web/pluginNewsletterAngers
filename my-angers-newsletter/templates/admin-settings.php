<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['man_save_settings'])) {
    check_admin_referer('man_save_settings_action');
    update_option('man_double_optin', isset($_POST['double_optin']) ? '1' : '0');
    update_option('man_auto_notify', isset($_POST['auto_notify']) ? '1' : '0');
    update_option('man_daily_digest', isset($_POST['daily_digest']) ? '1' : '0');
    update_option('man_email_template', sanitize_text_field($_POST['email_template']));
    echo '<div class="man-admin-tailwind px-8 pt-8"><div class="bg-emerald-500 text-white p-4 rounded-2xl font-bold shadow-lg shadow-emerald-200 animate-bounce">✓ Réglages enregistrés avec succès !</div></div>';
}

$double_optin = get_option('man_double_optin', '1');
$auto_notify = get_option('man_auto_notify', '0');
$daily_digest = get_option('man_daily_digest', '0');
$email_template = get_option('man_email_template', 'modern');
?>

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-8">
    <header class="mb-12">
        <h1 class="text-4xl font-black text-slate-900 tracking-tight">Configuration <span class="text-primary">Système</span></h1>
        <p class="text-slate-500 font-medium">Paramétrez les automatismes de votre écosystème de newsletter.</p>
    </header>

    <form method="post" class="flex flex-col lg:flex-row gap-8">
        <?php wp_nonce_field('man_save_settings_action'); ?>

        <div class="flex-1 space-y-8">
            <div class="card rounded-3xl p-10 bg-white">
                <h3 class="text-xl font-black mb-8 border-b border-slate-50 pb-4">Protocoles d'Inscription</h3>

                <div class="space-y-8">
                    <label class="flex items-start gap-5 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="double_optin" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($double_optin, '1'); ?>>
                        </div>
                        <div>
                            <span class="block text-lg font-black text-slate-800 group-hover:text-primary transition-colors">Activer le Double Opt-in</span>
                            <span class="text-slate-400 font-medium text-sm leading-relaxed">Protégez votre réputation d'expéditeur en demandant une confirmation par email.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-5 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="auto_notify" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($auto_notify, '1'); ?>>
                        </div>
                        <div>
                            <span class="block text-lg font-black text-slate-800 group-hover:text-primary transition-colors">Notification instantanée</span>
                            <span class="text-slate-400 font-medium text-sm leading-relaxed">Envoyer un email dès qu'un nouvel article est publié.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-5 cursor-pointer group">
                        <div class="relative flex items-center">
                            <input type="checkbox" name="daily_digest" class="w-6 h-6 rounded-lg border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($daily_digest, '1'); ?>>
                        </div>
                        <div>
                            <span class="block text-lg font-black text-slate-800 group-hover:text-primary transition-colors">Récapitulatif Quotidien (Daily Digest)</span>
                            <span class="text-slate-400 font-medium text-sm leading-relaxed">Chaque soir, envoie un email regroupant tous les articles de la journée dans un format Grid/Masonry.</span>
                        </div>
                    </label>
                </div>
            </div>

            <div class="card rounded-3xl p-10 bg-white">
                <h3 class="text-xl font-black mb-8 border-b border-slate-50 pb-4">Apparence & Templates</h3>
                <div class="mb-8">
                    <label class="block text-xs font-black uppercase text-slate-400 mb-4">Modèle d'Email</label>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="cursor-pointer group">
                            <input type="radio" name="email_template" value="modern" class="hidden" <?php checked($email_template, 'modern'); ?>>
                            <div class="border-2 rounded-2xl p-4 transition-all group-hover:border-primary/50 <?php echo $email_template === 'modern' ? 'border-primary bg-primary/5' : 'border-slate-100'; ?>">
                                <div class="bg-slate-200 h-24 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <div class="w-full h-full flex flex-col p-2 gap-1">
                                        <div class="h-8 bg-white rounded shadow-sm"></div>
                                        <div class="h-2 w-3/4 bg-white/50 rounded"></div>
                                        <div class="h-2 w-1/2 bg-white/50 rounded"></div>
                                    </div>
                                </div>
                                <span class="block font-black text-center text-sm">Moderne</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="email_template" value="classic" class="hidden" <?php checked($email_template, 'classic'); ?>>
                            <div class="border-2 rounded-2xl p-4 transition-all group-hover:border-primary/50 <?php echo $email_template === 'classic' ? 'border-primary bg-primary/5' : 'border-slate-100'; ?>">
                                <div class="bg-slate-200 h-24 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <div class="w-full h-full flex flex-col p-2 gap-1 items-center">
                                        <div class="h-4 w-4 bg-white rounded-full shadow-sm mb-1"></div>
                                        <div class="h-1 w-3/4 bg-white/50 rounded"></div>
                                        <div class="h-1 w-1/2 bg-white/50 rounded"></div>
                                        <div class="h-1 w-2/3 bg-white/50 rounded"></div>
                                    </div>
                                </div>
                                <span class="block font-black text-center text-sm">Classique</span>
                            </div>
                        </label>
                        <label class="cursor-pointer group">
                            <input type="radio" name="email_template" value="minimal" class="hidden" <?php checked($email_template, 'minimal'); ?>>
                            <div class="border-2 rounded-2xl p-4 transition-all group-hover:border-primary/50 <?php echo $email_template === 'minimal' ? 'border-primary bg-primary/5' : 'border-slate-100'; ?>">
                                <div class="bg-slate-200 h-24 rounded-lg mb-3 flex items-center justify-center overflow-hidden">
                                    <div class="w-full h-full flex flex-col p-4 gap-2">
                                        <div class="h-1 w-full bg-white/50 rounded"></div>
                                        <div class="h-1 w-full bg-white/50 rounded"></div>
                                        <div class="h-1 w-full bg-white/50 rounded"></div>
                                    </div>
                                </div>
                                <span class="block font-black text-center text-sm">Minimaliste</span>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            <button type="submit" name="man_save_settings" class="w-full bg-slate-900 text-white px-10 py-5 rounded-2xl font-black hover:scale-[1.02] transition-transform shadow-2xl shadow-slate-300">Enregistrer les Protocoles</button>
        </div>

        <div class="lg:w-96 space-y-8">
            <div class="card rounded-3xl p-10 bg-slate-900 text-white shadow-2xl shadow-slate-400 overflow-hidden relative">
                <div class="absolute -right-10 -top-10 w-40 h-40 bg-primary/20 rounded-full blur-3xl"></div>
                <h3 class="text-2xl font-black mb-6 relative">Intégration Site Web</h3>
                <p class="text-slate-400 font-medium mb-8 text-sm">Copiez et collez ce code court n'importe où sur votre site pour afficher le module d'inscription.</p>

                <div class="bg-slate-800/50 border border-slate-700 p-6 rounded-2xl flex justify-between items-center group cursor-pointer hover:border-primary/50 transition-all" onclick="navigator.clipboard.writeText('[angers_newsletter]'); alert('Shortcode copié !');">
                    <code class="text-primary font-black text-lg">[angers_newsletter]</code>
                    <span class="text-slate-500 text-[10px] font-black uppercase group-hover:text-white transition-colors">Copier</span>
                </div>
            </div>

            <div class="card rounded-3xl p-10 bg-white border border-slate-100">
                <h3 class="font-black text-slate-900 mb-4">Identité de l'Expéditeur</h3>
                <div class="space-y-4">
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Nom</p>
                        <p class="font-bold text-slate-700">Angers Info</p>
                    </div>
                    <div>
                        <p class="text-[10px] font-black uppercase text-slate-400 mb-1">Email</p>
                        <p class="font-bold text-slate-700">newsletter@my-angers.info</p>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
