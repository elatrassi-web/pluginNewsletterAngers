<?php
if (!defined('ABSPATH')) exit;

if (isset($_POST['man_save_settings'])) {
    check_admin_referer('man_save_settings_action');
    update_option('man_double_optin', isset($_POST['double_optin']) ? '1' : '0');
    update_option('man_auto_notify', isset($_POST['auto_notify']) ? '1' : '0');
    update_option('man_daily_digest', isset($_POST['daily_digest']) ? '1' : '0');
    update_option('man_email_template', sanitize_text_field($_POST['email_template']));
    echo '<div class="man-admin-tailwind px-8 pt-8"><div class="bg-emerald-500 text-white p-6 rounded-[2rem] font-black shadow-2xl shadow-emerald-200 animate-bounce flex items-center gap-4"><span class="dashicons dashicons-yes-alt"></span> Réglages enregistrés avec succès !</div></div>';
}

$double_optin = get_option('man_double_optin', '1');
$auto_notify = get_option('man_auto_notify', '0');
$daily_digest = get_option('man_daily_digest', '0');
$email_template = get_option('man_email_template', 'modern');
?>

<div class="man-admin-tailwind min-h-screen bg-[#f1f5f9] p-4 md:p-12">
    <header class="mb-16">
        <h1 class="text-6xl font-black text-[#0f172a] tracking-tighter">Configuration <span class="text-[#f60] italic">Système</span></h1>
        <p class="text-slate-500 font-bold mt-3 text-lg opacity-80">Paramétrez les automatismes de votre écosystème de newsletter.</p>
    </header>

    <form method="post" class="space-y-12">
        <?php wp_nonce_field('man_save_settings_action'); ?>

        <div class="flex flex-col lg:flex-row gap-12 items-start">
            <!-- Left Column -->
            <div class="flex-1 space-y-12 w-full">
                <div class="bg-white rounded-[3.5rem] p-12 shadow-2xl shadow-slate-200/60 border border-white/50">
                    <h3 class="text-3xl font-black mb-12 text-slate-900 border-b-2 border-slate-50 pb-8">Protocoles d'Inscription</h3>

                    <div class="space-y-10">
                        <label class="flex items-start gap-8 cursor-pointer group">
                            <input type="checkbox" name="double_optin" class="w-8 h-8 mt-1 rounded-xl border-3 border-slate-100 text-[#f60] focus:ring-[#f60] transition-all cursor-pointer checked:scale-110" <?php checked($double_optin, '1'); ?>>
                            <div>
                                <span class="block text-2xl font-black text-slate-800 group-hover:text-[#f60] transition-colors">Activer le Double Opt-in</span>
                                <span class="text-slate-400 font-bold text-base leading-relaxed mt-2 block opacity-70">Protégez votre réputation d'expéditeur en demandant une confirmation par email.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-8 cursor-pointer group">
                            <input type="checkbox" name="auto_notify" class="w-8 h-8 mt-1 rounded-xl border-3 border-slate-100 text-[#f60] focus:ring-[#f60] transition-all cursor-pointer checked:scale-110" <?php checked($auto_notify, '1'); ?>>
                            <div>
                                <span class="block text-2xl font-black text-slate-800 group-hover:text-[#f60] transition-colors">Notification instantanée</span>
                                <span class="text-slate-400 font-bold text-base leading-relaxed mt-2 block opacity-70">Envoyer un email automatique dès qu'un nouvel article est publié.</span>
                            </div>
                        </label>

                        <label class="flex items-start gap-8 cursor-pointer group">
                            <input type="checkbox" name="daily_digest" class="w-8 h-8 mt-1 rounded-xl border-3 border-slate-100 text-[#f60] focus:ring-[#f60] transition-all cursor-pointer checked:scale-110" <?php checked($daily_digest, '1'); ?>>
                            <div>
                                <span class="block text-2xl font-black text-slate-800 group-hover:text-[#f60] transition-colors">Récapitulatif Quotidien (Daily Digest)</span>
                                <span class="text-slate-400 font-bold text-base leading-relaxed mt-2 block opacity-70">Envoi groupé de tous les articles de la journée au format Grid dynamique.</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="bg-white rounded-[3.5rem] p-12 shadow-2xl shadow-slate-200/60 border border-white/50">
                    <h3 class="text-3xl font-black mb-12 text-slate-900 border-b-2 border-slate-50 pb-8">Apparence & Templates</h3>
                    <div>
                        <label class="block text-xs font-black uppercase text-slate-300 mb-8 tracking-[0.3em]">MODÈLE D'EMAIL ACTIF</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <?php
                            $templates = [
                                'modern' => 'Moderne',
                                'classic' => 'Classique',
                                'minimal' => 'Minimaliste'
                            ];
                            foreach ($templates as $key => $label) : ?>
                                <label class="cursor-pointer group">
                                    <input type="radio" name="email_template" value="<?php echo $key; ?>" class="hidden" <?php checked($email_template, $key); ?>>
                                    <div class="border-4 rounded-[2.5rem] p-6 transition-all group-hover:border-[#f60]/30 <?php echo $email_template === $key ? 'border-[#f60] bg-[#f60]/5 shadow-2xl shadow-[#f60]/10' : 'border-slate-50 hover:bg-slate-50'; ?>">
                                        <div class="bg-slate-100 h-32 rounded-[1.5rem] mb-6 flex items-center justify-center overflow-hidden shadow-inner relative">
                                            <div class="w-full h-full flex flex-col p-4 gap-3">
                                                <div class="h-12 bg-white rounded-xl shadow-sm"></div>
                                                <div class="h-2 w-3/4 bg-white rounded-full"></div>
                                                <div class="h-2 w-1/2 bg-white rounded-full"></div>
                                            </div>
                                            <?php if($email_template === $key): ?>
                                                <div class="absolute inset-0 bg-[#f60]/10 flex items-center justify-center">
                                                    <span class="dashicons dashicons-yes-alt text-[#f60]" style="font-size: 40px; width: 40px; height: 40px;"></span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <span class="block font-black text-center text-slate-800 text-lg"><?php echo $label; ?></span>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="lg:w-[450px] space-y-12 w-full">
                <div class="bg-[#121826] rounded-[3.5rem] p-12 text-white shadow-[0_40px_80px_-15px_rgba(15,23,42,0.3)] overflow-hidden relative border-0 group">
                    <div class="absolute -right-12 -top-12 w-64 h-64 bg-[#f60]/20 rounded-full blur-[100px] group-hover:scale-150 transition-transform duration-1000"></div>
                    <h3 class="text-3xl font-black mb-8 relative text-white tracking-tight">Intégration Site Web</h3>
                    <p class="text-slate-400 font-bold mb-10 text-lg leading-relaxed relative opacity-80">Copiez ce shortcode pour afficher le module d'inscription n'importe où.</p>

                    <div class="bg-white/5 border-2 border-white/10 p-8 rounded-[2rem] flex justify-between items-center group/code cursor-pointer hover:bg-white/10 hover:border-[#f60]/50 transition-all relative" onclick="navigator.clipboard.writeText('[angers_newsletter]'); alert('Shortcode copié !');">
                        <code class="text-[#f60] font-black text-2xl tracking-wider">[angers_newsletter]</code>
                        <span class="text-slate-500 text-xs font-black uppercase tracking-widest group-hover/code:text-white transition-colors">Copier</span>
                    </div>
                </div>

                <div class="bg-white rounded-[3.5rem] p-12 border border-white/50 shadow-2xl shadow-slate-200/60">
                    <h3 class="text-2xl font-black text-slate-900 mb-10 border-b-2 border-slate-50 pb-6">Identité Visuelle</h3>
                    <div class="space-y-8">
                        <div>
                            <p class="text-[11px] font-black uppercase text-slate-300 mb-2 tracking-[0.2em]">NOM DE L'EXPÉDITEUR</p>
                            <p class="font-black text-slate-800 text-2xl">Angers Info</p>
                        </div>
                        <div>
                            <p class="text-[11px] font-black uppercase text-slate-300 mb-2 tracking-[0.2em]">EMAIL DE RÉPONSE</p>
                            <p class="font-black text-[#f60] text-xl truncate">newsletter@my-angers.info</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Section -->
        <div class="pt-8">
            <button type="submit" name="man_save_settings" class="w-full bg-[#121826] text-white py-8 rounded-[2.5rem] font-black text-2xl hover:bg-slate-900 hover:scale-[1.02] transition-all shadow-2xl shadow-slate-900/20 uppercase tracking-widest">
                Enregistrer les Protocoles
            </button>
        </div>
    </form>
</div>
