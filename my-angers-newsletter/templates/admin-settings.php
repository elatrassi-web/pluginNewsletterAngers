<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['man_save_settings'])) {
    check_admin_referer('man_save_settings_action');
    update_option('man_double_optin', isset($_POST['double_optin']) ? '1' : '0');
    update_option('man_auto_notify', isset($_POST['auto_notify']) ? '1' : '0');
    update_option('man_daily_digest', isset($_POST['daily_digest']) ? '1' : '0');
    update_option('man_email_template', sanitize_text_field($_POST['email_template']));
    echo '<div class="man-admin-tailwind px-8 pt-8"><div class="bg-emerald-500 text-white p-4 rounded-2xl font-bold shadow-lg shadow-emerald-200 animate-pulse">✓ Réglages enregistrés !</div></div>';
}

$double_optin = get_option('man_double_optin', '1');
$auto_notify = get_option('man_auto_notify', '0');
$daily_digest = get_option('man_daily_digest', '0');
$email_template = get_option('man_email_template', 'modern');
?>

<div class="man-admin-tailwind min-h-screen bg-slate-50 p-4 md:p-12">
    <header class="mb-12">
        <h1 class="text-5xl font-black text-slate-900 tracking-tighter">Configuration <span class="text-primary italic">Système</span></h1>
        <p class="text-slate-500 font-medium mt-2">Paramétrez les automatismes de votre écosystème de newsletter.</p>
    </header>

    <form method="post" class="space-y-8">
        <?php wp_nonce_field('man_save_settings_action'); ?>

        <div class="flex flex-col lg:flex-row gap-8 items-start">
            <!-- Left Column -->
            <div class="flex-1 space-y-8 w-full">
                <div class="card rounded-[2.5rem] p-10 bg-white shadow-xl shadow-slate-200/50">
                    <h3 class="text-2xl font-black mb-8 text-slate-900 border-b border-slate-50 pb-4">Protocoles d'Inscription</h3>

                    <div class="space-y-8">
                        <label class="flex items-start gap-6 cursor-pointer group">
                            <input type="checkbox" name="double_optin" class="w-7 h-7 mt-1 rounded-xl border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($double_optin, '1'); ?>>
                            <div>
                                <span class="block text-xl font-black text-slate-800 group-hover:text-primary transition-colors">Activer le Double Opt-in</span>
                                <span class="text-slate-400 font-medium text-sm leading-relaxed">Protégez votre réputation d'expéditeur en demandant une confirmation par email.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-6 cursor-pointer group">
                            <input type="checkbox" name="auto_notify" class="w-7 h-7 mt-1 rounded-xl border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($auto_notify, '1'); ?>>
                            <div>
                                <span class="block text-xl font-black text-slate-800 group-hover:text-primary transition-colors">Notification instantanée</span>
                                <span class="text-slate-400 font-medium text-sm leading-relaxed">Envoyer un email dès qu'un nouvel article est publié.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-6 cursor-pointer group">
                            <input type="checkbox" name="daily_digest" class="w-7 h-7 mt-1 rounded-xl border-2 border-slate-200 text-primary focus:ring-primary transition-all cursor-pointer" <?php checked($daily_digest, '1'); ?>>
                            <div>
                                <span class="block text-xl font-black text-slate-800 group-hover:text-primary transition-colors">Récapitulatif Quotidien (Daily Digest)</span>
                                <span class="text-slate-400 font-medium text-sm leading-relaxed">Envoi groupé de tous les articles de la journée au format Masonry.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="card rounded-[2.5rem] p-10 bg-white shadow-xl shadow-slate-200/50">
                    <h3 class="text-2xl font-black mb-8 text-slate-900 border-b border-slate-50 pb-4">Apparence & Templates</h3>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-400 mb-6 tracking-widest">Modèle d'Email</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <?php
                            $templates = [
                                'modern' => 'Moderne',
                                'classic' => 'Classique',
                                'minimal' => 'Minimaliste'
                            ];
                            foreach ($templates as $key => $label) : ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="email_template" value="<?php echo $key; ?>" class="hidden" <?php checked($email_template, $key); ?>>
                                    <div class="border-3 rounded-3xl p-5 transition-all group-hover:border-primary/50 <?php echo $email_template === $key ? 'border-primary bg-primary/5 shadow-lg shadow-primary/10' : 'border-slate-100 hover:bg-slate-50'; ?>">
                                        <div class="bg-slate-200 h-28 rounded-2xl mb-4 flex items-center justify-center overflow-hidden">
                                            <div class="w-full h-full flex flex-col p-3 gap-2">
                                                <div class="h-10 bg-white rounded-lg shadow-sm"></div>
                                                <div class="h-2 w-3/4 bg-white/60 rounded"></div>
                                                <div class="h-2 w-1/2 bg-white/60 rounded"></div>
                                            </div>
                                        </div>
                                        <span class="block font-black text-center text-slate-700"><?php echo $label; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:w-[400px] space-y-8 w-full">
                <div class="card rounded-[2.5rem] p-10 bg-[#121826] text-white shadow-2xl shadow-slate-900/20 overflow-hidden relative border-0">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-primary/30 rounded-full blur-[80px]"></div>
                    <h3 class="text-2xl font-black mb-6 relative text-white">Intégration Site Web</h3>
                    <p class="text-slate-400 font-medium mb-8 text-sm leading-relaxed relative">Copiez et collez ce code court pour afficher le module d'inscription dans votre site.</p>

                    <div class="bg-white/5 border border-white/10 p-6 rounded-2xl flex justify-between items-center group cursor-pointer hover:bg-white/10 transition-all relative" onclick="navigator.clipboard.writeText('[angers_newsletter]'); alert('Shortcode copié !');">
                        <code class="text-primary font-black text-xl">[angers_newsletter]</code>
                        <span class="text-slate-500 text-[10px] font-black uppercase group-hover:text-white transition-colors">Copier</span>
                    </div>
                </div>

                <div class="card rounded-[2.5rem] p-10 bg-white border border-slate-100 shadow-xl shadow-slate-200/50">
                    <h3 class="text-xl font-black text-slate-900 mb-6">Identité de l'Expéditeur</h3>
                    <div class="space-y-6">
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1 tracking-widest">NOM</p>
                            <p class="font-bold text-slate-800 text-lg">Angers Info</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-black uppercase text-slate-400 mb-1 tracking-widest">EMAIL</p>
                            <p class="font-bold text-slate-800 text-lg">newsletter@my-angers.info</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Fixed Action Bar -->
        <div class="w-full">
            <button type="submit" name="man_save_settings" class="w-full bg-[#121826] text-white py-6 rounded-3xl font-black text-xl hover:bg-slate-900 hover:scale-[1.01] transition-all shadow-2xl shadow-slate-400">Enregistrer les Protocoles</button>
        </div>
    </form>
</div>
