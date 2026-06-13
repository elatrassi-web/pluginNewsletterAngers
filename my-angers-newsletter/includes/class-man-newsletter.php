<?php

if (!defined('ABSPATH')) {
    exit;
}

class MAN_Newsletter {
    public function __construct() {
        add_action('wp_ajax_man_save_campaign', array($this, 'ajax_save_campaign'));
        add_action('wp_ajax_man_get_posts', array($this, 'ajax_get_posts'));
        add_action('wp_ajax_man_send_campaign', array($this, 'ajax_send_campaign'));
        add_action('wp_ajax_man_send_test_email', array($this, 'ajax_send_test_email'));
    }

    private function check_permission() {
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission refusée');
        }
        check_ajax_referer('man_admin_nonce', 'nonce');
    }

    public function ajax_send_campaign() {
        $this->check_permission();

        global $wpdb;
        $table_newsletters = $wpdb->prefix . 'man_newsletters';
        $table_subscribers = $wpdb->prefix . 'man_subscribers';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $recipient_type = isset($_POST['recipient_type']) ? sanitize_text_field($_POST['recipient_type']) : 'all';
        $selected_ids = isset($_POST['selected_ids']) ? array_map('intval', (array)$_POST['selected_ids']) : array();

        if (!$id) wp_send_json_error('ID de campagne manquant');

        $campaign = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_newsletters WHERE id = %d", $id));
        if (!$campaign) wp_send_json_error('Campagne non trouvée');

        if ($recipient_type === 'manual' && !empty($selected_ids)) {
            $placeholders = implode(',', array_fill(0, count($selected_ids), '%d'));
            $subscribers = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_subscribers WHERE id IN ($placeholders) AND status = 'active'", ...$selected_ids));
        } else {
            $subscribers = $wpdb->get_results("SELECT * FROM $table_subscribers WHERE status = 'active'");
        }

        if (!$subscribers) wp_send_json_error('Aucun abonné actif trouvé');

        $count = 0;
        foreach ($subscribers as $sub) {
            $content = $this->prepare_email_content($campaign->content, $campaign->subject, $campaign->id, $sub);
            $headers = array('Content-Type: text/html; charset=UTF-8');
            if (MyAngersNewsletter::send_mail($sub->email, $campaign->subject, $content, $headers)) {
                $count++;
            }
        }

        $wpdb->update($table_newsletters,
            array('status' => 'sent', 'sent_at' => current_time('mysql')),
            array('id' => $id)
        );

        wp_send_json_success("Campagne envoyée à $count abonnés !");
    }

    public function ajax_send_test_email() {
        $this->check_permission();

        $test_email = isset($_POST['test_email']) ? sanitize_email($_POST['test_email']) : '';
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if (!is_email($test_email)) {
            wp_send_json_error('Adresse email invalide');
        }

        // Mock subscriber object for preview
        $sub = (object) array(
            'id' => 0,
            'email' => $test_email
        );

        $final_content = $this->prepare_email_content($content, $subject, 0, $sub);
        $headers = array('Content-Type: text/html; charset=UTF-8');

        if (MyAngersNewsletter::send_mail($test_email, "[TEST] " . $subject, $final_content, $headers)) {
            wp_send_json_success('Email de test envoyé avec succès !');
        } else {
            wp_send_json_error("Erreur lors de l'envoi de l'email de test.");
        }
    }

    public function prepare_email_content($raw_content, $subject, $newsletter_id, $sub, $forced_template = null) {
        $template_type = $forced_template ? $forced_template : get_option('man_email_template', 'modern');
        $content = $raw_content;

        // Process links for tracking if sub exists and not a mock
        if ($sub && $sub->id > 0) {
            $content = preg_replace_callback('/href="([^"]+)"/', function($matches) use ($newsletter_id, $sub) {
                $url = $matches[1];
                if (strpos($url, 'mailto:') === 0 || strpos($url, '#') === 0) return $matches[0];
                $tracked_url = MAN_Stats::get_tracking_url($newsletter_id, $sub->id, $url);
                return 'href="' . $tracked_url . '"';
            }, $content);
            $unsubscribe_url = MAN_Stats::get_unsubscribe_url($sub->id);
            $pixel = '<img src="' . MAN_Stats::get_pixel_url($newsletter_id, $sub->id) . '" width="1" height="1" style="display:none;">';
        } else {
            $unsubscribe_url = '#';
            $pixel = '';
        }

        $logo_url = MAN_URL . 'assets/logo.jpg';
        $logo_html = '<div style="text-align:center; padding: 40px 0;">';
        $logo_html .= '<img src="' . esc_url($logo_url) . '" alt="Angers Info" style="max-width: 280px; height: auto; display: inline-block;">';
        $logo_html .= '</div>';

        $footer_html = '<div style="margin-top:60px; padding:40px 20px; border-top:1px solid #f1f5f9; text-align:center;">';
        $footer_html .= '<p style="font-family:sans-serif; font-size:12px; color:#94a3b8; line-height:1.6;">';
        $footer_html .= 'Vous recevez cet email car vous êtes inscrit à la newsletter d\'Angers Info. <br>';
        $footer_html .= '<a href="' . $unsubscribe_url . '" style="color:#f60; font-weight:bold; text-decoration:none;">Se désabonner instantanément</a>';
        $footer_html .= '</p></div>';

        switch ($template_type) {
            case 'classic':
                $final_html = '<html><body style="margin:0; padding:0; background-color:#ffffff; color:#1a1a1a;">';
                $final_html .= '<div style="font-family: Georgia, serif; max-width:650px; margin:0 auto; padding: 20px;">';
                $final_html .= $logo_html;
                $final_html .= '<div style="padding:0 40px;">';
                $final_html .= '<h1 style="font-size:32px; border-bottom:2px solid #1a1a1a; padding-bottom:20px; margin-bottom:40px; text-align:center;">' . esc_html($subject) . '</h1>';
                $final_html .= '<div style="font-size:18px; line-height:1.8; color:#1a1a1a;">' . $content . '</div>';
                $final_html .= '</div>' . $footer_html . $pixel . '</div></body></html>';
                break;
            case 'minimal':
                $final_html = '<html><body style="margin:0; padding:0; background-color:#ffffff; color:#334155;">';
                $final_html .= '<div style="font-family: -apple-system, BlinkMacSystemFont, sans-serif; max-width:550px; margin:0 auto; padding:60px 20px;">';
                $final_html .= '<div style="margin-bottom:60px; text-align:left;">';
                $final_html .= '<h2 style="color:#f60; font-weight:900; margin:0; font-size:24px;">Angers Info</h2>';
                $final_html .= '</div>';
                $final_html .= '<div style="font-size:16px; line-height:1.6;">' . $content . '</div>';
                $final_html .= '<div style="margin-top:100px; border-top:1px solid #e2e8f0; padding-top:20px; font-size:12px; color:#94a3b8; text-align:left;">';
                $final_html .= 'Angers Info • <a href="' . $unsubscribe_url . '" style="color:#334155; text-decoration:none;">Désinscription</a>';
                $final_html .= '</div>' . $pixel . '</div></body></html>';
                break;
            case 'modern':
            default:
                $final_html = '<html><head><meta name="viewport" content="width=device-width, initial-scale=1.0"><style>';
                $final_html .= '@media only screen and (max-width: 600px) {';
                $final_html .= '.man-mobile-padding { padding: 30px 15px !important; }';
                $final_html .= '.man-mobile-inner-padding { padding: 30px 20px !important; }';
                $final_html .= '.man-mobile-card { border-radius: 20px !important; }';
                $final_html .= '.man-column { display: block !important; width: 100% !important; padding: 10px 0 !important; }';
                $final_html .= '.man-mobile-text-center { text-align: center !important; }';
                $final_html .= '}';
                $final_html .= '</style></head><body style="margin:0; padding:0; background-color:#f8fafc;">';
                $final_html .= '<div class="man-mobile-padding" style="background-color:#f8fafc; padding:60px 20px;">';
                $final_html .= '<div class="man-mobile-card" style="max-width:650px; margin:0 auto; background-color:#ffffff; border-radius:40px; overflow:hidden; box-shadow:0 30px 60px -12px rgba(0,0,0,0.1); border: 1px solid #f1f5f9;">';

                // Gradient header area
                $final_html .= '<div style="background: linear-gradient(135deg, #ffffff 0%, #fffbf5 100%); border-bottom: 1px solid #f1f5f9;">' . $logo_html . '</div>';

                $final_html .= '<div class="man-mobile-inner-padding" style="padding:40px 60px 60px 60px; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, sans-serif; color:#1e293b; font-size:17px; line-height:1.8;">';
                $final_html .= '<div style="background-color:#f8fafc; border-radius:30px; padding:40px; margin-bottom:50px; border:1px solid #f1f5f9; text-align:center;">';
                $final_html .= '<h2 style="margin:0; font-size:18px; font-weight:900; color:#0f172a; letter-spacing: -0.5px;">' . esc_html($subject) . '</h2>';
                $final_html .= '</div>';
                $final_html .= $content;
                $final_html .= '</div>';

                $final_html .= '<div class="man-mobile-inner-padding" style="background-color:#121826; padding:80px 60px; text-align:center;">';
                $final_html .= '<div style="margin-bottom:40px;">';
                $final_html .= '<span style="color:#f60; font-weight:900; font-size:24px; text-transform:uppercase; letter-spacing:2px;">Angers Info</span>';
                $final_html .= '</div>';
                $final_html .= '<p style="color:#64748b; font-size:14px; line-height:1.8; font-family:sans-serif; max-width:400px; margin:0 auto 30px auto;">';
                $final_html .= 'Merci de faire partie de notre communauté. Nous nous efforçons de vous apporter le meilleur de l\'actualité locale chaque jour.';
                $final_html .= '</p>';
                $final_html .= '<div style="border-top:1px solid #1e293b; padding-top:30px;">';
                $final_html .= '<p style="color:#475569; font-size:12px; font-family:sans-serif;">';
                $final_html .= '<a href="' . $unsubscribe_url . '" style="color:#94a3b8; font-weight:bold; text-decoration:none; text-transform:uppercase; letter-spacing:1px;">Se désinscrire</a>';
                $final_html .= '</p></div></div></div></div>' . $pixel . '</body></html>';
                break;
        }

        return $final_html;
    }

    public function ajax_save_campaign() {
        $this->check_permission();

        global $wpdb;
        $table = $wpdb->prefix . 'man_newsletters';

        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $subject = isset($_POST['subject']) ? sanitize_text_field($_POST['subject']) : '';
        $content = isset($_POST['content']) ? wp_kses_post($_POST['content']) : '';

        if ($id) {
            $wpdb->update($table,
                array('subject' => $subject, 'content' => $content),
                array('id' => $id)
            );
        } else {
            $wpdb->insert($table,
                array('subject' => $subject, 'content' => $content, 'status' => 'draft', 'created_at' => current_time('mysql'))
            );
            $id = $wpdb->insert_id;
        }

        wp_send_json_success(array('id' => $id, 'message' => 'Campagne enregistrée !'));
    }

    public function ajax_get_posts() {
        $this->check_permission();

        $posts = get_posts(array(
            'posts_per_page' => 12,
            'post_status' => 'publish'
        ));

        $result = array();
        foreach ($posts as $post) {
            $result[] = array(
                'id' => $post->ID,
                'title' => get_the_title($post->ID),
                'excerpt' => wp_trim_words(get_the_excerpt($post->ID), 20),
                'url' => get_permalink($post->ID),
                'thumbnail' => get_the_post_thumbnail_url($post->ID, 'medium')
            );
        }

        wp_send_json_success($result);
    }
}

new MAN_Newsletter();
