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
                    <button type="submit" id="man-submit">
                        <span class="man-icon">🔔</span> Je m'abonne
                    </button>
                </div>

                <?php if (!empty($enabled_categories)) : ?>
                <div class="man-category-selection">
                    <p class="man-category-title">Choisissez vos éditions (départements) :</p>
                    <div class="man-category-grid">
                        <?php foreach ($enabled_categories as $cat_id) :
                            $cat = get_category($cat_id);
                            if (!$cat) continue;
                        ?>
                            <label class="man-category-label">
                                <input type="checkbox" name="categories[]" value="<?php echo $cat_id; ?>" checked>
                                <span><?php echo esc_html($cat->name); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
            </form>
            <div id="man-message"></div>
        </div>
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
