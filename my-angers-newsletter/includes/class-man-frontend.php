<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Frontend {
    public function __construct() {
        add_shortcode('angers_newsletter', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_assets'));
        add_action('init', array($this, 'handle_confirmation'));
    }

    public function enqueue_assets() {
        wp_enqueue_style('man-frontend', MAN_URL . 'assets/css/frontend-style.css', array(), MAN_VERSION);
        wp_enqueue_script('man-frontend', MAN_URL . 'assets/js/frontend-script.js', array('jquery'), MAN_VERSION, true);
        wp_localize_script('man-frontend', 'man_ajax', array('url' => admin_url('admin-ajax.php')));
    }

    public function render_shortcode() {
        $enabled_categories = get_option('man_enabled_categories', array());
        ob_start();
        ?>
        <div class="man-newsletter-form-container">
            <form id="man-newsletter-form">
                <div class="man-d-flex">
                    <input type="email" name="email" id="man-email" placeholder="Votre E-mail" required>
                    <button type="submit" id="man-submit-trigger">
                        <span class="man-icon">🔔</span> Je m'abonne
                    </button>
                </div>
            </form>
            <div id="man-message"></div>

            <?php if (!empty($enabled_categories)) : ?>
            <!-- Category Selection Modal -->
            <div id="man-category-modal" class="man-modal-hidden">
                <div class="man-modal-overlay"></div>
                <div class="man-modal-content">
                    <div class="man-modal-header">
                        <h3>Personnalisez <span class="highlight">votre actu</span></h3>
                        <p>Sélectionnez les éditions (départements) qui vous intéressent.</p>
                        <button type="button" class="man-modal-close">✕</button>
                    </div>
                    <div class="man-category-grid">
                        <?php foreach ($enabled_categories as $cat_id) :
                            $cat = get_category($cat_id);
                            if (!$cat) continue;
                        ?>
                            <label class="man-category-label">
                                <input type="checkbox" name="categories[]" value="<?php echo $cat_id; ?>" checked>
                                <span class="man-cat-name"><?php echo esc_html($cat->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="man-modal-footer">
                        <button type="button" id="man-confirm-subscription">Confirmer l'inscription</button>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <style>
            #man-category-modal.man-modal-hidden { display: none; }
            #man-category-modal { position: fixed; inset: 0; z-index: 999999; display: flex; align-items: center; justify-content: center; padding: 20px; font-family: 'Plus Jakarta Sans', sans-serif; }
            .man-modal-overlay { position: absolute; inset: 0; bg-color: #0f172a; opacity: 0.9; backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.9); }
            .man-modal-content { position: relative; background: white; border-radius: 30px; width: 100%; max-width: 600px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); color: #0f172a; }
            .man-modal-header { text-align: center; margin-bottom: 30px; }
            .man-modal-header h3 { font-size: 28px; font-weight: 900; margin: 0 0 10px 0; text-transform: uppercase; letter-spacing: -1px; }
            .man-modal-header h3 .highlight { color: #f60; font-style: italic; }
            .man-modal-header p { color: #64748b; font-weight: 500; font-size: 15px; }
            .man-modal-close { position: absolute; top: 20px; right: 20px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 10px; cursor: pointer; font-weight: bold; color: #64748b; }

            .man-modal-content .man-category-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; max-height: 300px; overflow-y: auto; padding-right: 10px; margin-bottom: 30px; }
            .man-modal-content .man-category-grid::-webkit-scrollbar { width: 6px; }
            .man-modal-content .man-category-grid::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 10px; }

            .man-modal-content .man-category-label { display: flex; align-items: center; gap: 10px; background: #f8fafc; padding: 12px 15px; border-radius: 12px; cursor: pointer; border: 2px solid transparent; transition: all 0.2s; }
            .man-modal-content .man-category-label:hover { background: #f1f5f9; border-color: #f603; }
            .man-modal-content .man-category-label input:checked + .man-cat-name { color: #f60; font-weight: 800; }
            .man-modal-content .man-category-label input { width: 18px; height: 18px; accent-color: #f60; }
            .man-modal-content .man-cat-name { font-size: 14px; font-weight: 600; color: #475569; }

            #man-confirm-subscription { width: 100%; background: #121826; color: white; border: none; padding: 18px; border-radius: 15px; font-weight: 800; font-size: 16px; cursor: pointer; text-transform: uppercase; letter-spacing: 1px; transition: all 0.2s; }
            #man-confirm-subscription:hover { background: #f60; transform: translateY(-2px); box-shadow: 0 10px 20px rgba(246, 102, 0, 0.3); }
        </style>
        <?php
        return ob_get_clean();
    }

    public function handle_confirmation() {
        if (isset($_GET['man_action']) && $_GET['man_action'] === 'confirm' && isset($_GET['token'])) {
            global $wpdb;
            $table = $wpdb->prefix . 'man_subscribers';
            $token = sanitize_text_field($_GET['token']);

            $subscriber = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE token = %s", $token));
            if ($subscriber) {
                $wpdb->update($table, array('status' => 'active', 'token' => ''), array('id' => $subscriber->id));
                MAN_Automation::send_welcome_email($subscriber->email);
                $this->render_branded_page('Inscription Confirmée', '🎉 Merci ! Votre inscription est désormais active.', 'Préparez-vous à recevoir le meilleur de l\'actualité d\'Angers directement dans votre boîte mail.');
            } else {
                $this->render_branded_page('Oups !', 'Lien de confirmation invalide ou expiré.', 'Si vous rencontrez un problème, n\'hésitez pas à vous réinscrire sur notre site.', 'error');
            }
        }
    }

    private function render_branded_page($title, $headline, $subline, $type = 'success') {
        $color = ($type === 'success') ? '#f60' : '#ef4444';
        ?>
        <!DOCTYPE html>
        <html lang="fr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title><?php echo esc_html($title); ?> - Angers Info</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;700;800&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Plus Jakarta Sans', sans-serif; }
            </style>
        </head>
        <body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-6">
            <div class="max-w-xl w-full bg-white rounded-[3rem] p-12 shadow-2xl shadow-slate-200 text-center border border-slate-100">
                <div class="mb-10">
                    <h1 class="text-3xl font-black uppercase tracking-tighter" style="color: <?php echo $color; ?>;">Angers<span class="text-[#121826] italic">Info</span></h1>
                </div>

                <div class="mb-8">
                    <div class="w-24 h-24 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-xl" style="background-color: <?php echo $color; ?>10; color: <?php echo $color; ?>;">
                        <?php if($type === 'success'): ?>
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        <?php else: ?>
                            <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"></path></svg>
                        <?php endif; ?>
                    </div>
                    <h2 class="text-4xl font-black text-[#0f172a] mb-4 tracking-tight"><?php echo esc_html($headline); ?></h2>
                    <p class="text-slate-500 font-bold text-lg leading-relaxed"><?php echo esc_html($subline); ?></p>
                </div>

                <a href="<?php echo home_url(); ?>" class="inline-block bg-[#121826] text-white px-10 py-5 rounded-2xl font-black hover:scale-105 transition-transform shadow-xl shadow-slate-900/10 uppercase tracking-widest text-sm">Retour au site</a>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

new MAN_Frontend();
