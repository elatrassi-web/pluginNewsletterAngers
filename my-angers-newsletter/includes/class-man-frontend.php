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
        ob_start();
        ?>
        <div class="man-newsletter-form-container">
            <form id="man-newsletter-form" class="man-d-flex">
                <input type="email" name="email" id="man-email" placeholder="E-mail" required>
                <button type="submit" id="man-submit">
                    <span class="man-icon">🔔</span> Je m'abonne
                </button>
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
                wp_die("Merci ! Votre inscription est confirmée.", "Inscription confirmée");
            } else {
                wp_die("Lien de confirmation invalide ou expiré.", "Erreur");
            }
        }
    }
}

new MAN_Frontend();
