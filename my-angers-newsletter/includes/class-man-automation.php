<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Automation {
    public function __construct() {
        add_action('publish_post', array($this, 'handle_new_post_notification'), 10, 2);
    }

    public static function send_confirmation_email($email, $token) {
        $confirm_url = home_url('/?man_action=confirm&token=' . $token);
        $subject = "Confirmez votre inscription - Angers Info";
        $message = "Merci de vous être inscrit à la newsletter d'Angers Info.\n\n";
        $message .= "Veuillez cliquer sur le lien suivant pour confirmer votre inscription :\n";
        $message .= $confirm_url;

        wp_mail($email, $subject, $message);
    }

    public static function send_welcome_email($email) {
        $subject = "Bienvenue chez Angers Info !";
        $message = "Votre inscription à la newsletter est confirmée. Vous recevrez désormais nos dernières actualités directement dans votre boîte mail.";

        wp_mail($email, $subject, $message);
    }

    public function handle_new_post_notification($ID, $post) {
        if (get_option('man_auto_notify', '0') !== '1') {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'man_subscribers';
        $subscribers = $wpdb->get_results("SELECT email FROM $table WHERE status = 'active'");

        if (!$subscribers) return;

        $subject = "Nouvel article : " . get_the_title($ID);
        $message = "Un nouvel article vient d'être publié sur Angers Info :\n\n";
        $message .= get_the_title($ID) . "\n";
        $message .= get_permalink($ID);

        foreach ($subscribers as $sub) {
            wp_mail($sub->email, $subject, $message);
        }
    }
}

new MAN_Automation();
